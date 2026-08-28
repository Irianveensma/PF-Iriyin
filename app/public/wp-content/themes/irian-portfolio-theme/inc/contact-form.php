<?php
/**
 * Irian Portfolio - contactformulier.
 *
 * Rendert het formulier (irian_contact_form) en verwerkt de inzending via
 * admin-post.php (werkt ook uitgelogd). Verstuurt met wp_mail(); op Local
 * landt dat in Mailpit. Het ontvangstadres staat NIET in de HTML: de handler
 * leest het server-side uit de contact-panel van de voorpagina.
 *
 * @package IrianPortfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ontvanger van het formulier: het e-mailadres uit de contact-panel van de
 * statische voorpagina, anders het admin-adres.
 */
function irian_contact_recipient() {
	$front_id = (int) get_option( 'page_on_front' );
	if ( $front_id ) {
		$panels = (array) get_post_meta( $front_id, '_irian_panels', true );
		foreach ( $panels as $p ) {
			if ( 'contact' === ( $p['type'] ?? '' ) && ! empty( $p['data']['email'] ) && is_email( $p['data']['email'] ) ) {
				return $p['data']['email'];
			}
		}
	}
	return get_option( 'admin_email' );
}

/**
 * Render het formulier.
 *
 * @param array $args project_types (array), note (string), redirect (url)
 */
function irian_contact_form( $args = array() ) {
	$types    = ! empty( $args['project_types'] ) && is_array( $args['project_types'] ) ? $args['project_types'] : array();
	$note     = $args['note'] ?? '';
	$redirect = $args['redirect'] ?? home_url( '/' );
	if ( function_exists( 'irian_is_en' ) && irian_is_en() ) {
		$redirect = add_query_arg( 'lang', 'en', $redirect );
	}

	$status = isset( $_GET['contact'] ) ? sanitize_key( $_GET['contact'] ) : '';
	ob_start();
	?>
	<form class="ipb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>#contact" novalidate>
		<input type="hidden" name="action" value="irian_contact">
		<input type="hidden" name="irian_redirect" value="<?php echo esc_url( $redirect ); ?>">
		<input type="hidden" name="irian_t" value="<?php echo esc_attr( time() ); ?>">
		<?php wp_nonce_field( 'irian_contact', 'irian_contact_nonce' ); ?>
		<p class="ipb-form-hp" aria-hidden="true">
			<label><?php echo esc_html( irian_str( 'form_hp' ) ); ?> <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
		</p>

		<?php if ( 'ok' === $status ) : ?>
			<p class="ipb-form-msg is-ok" role="status"><?php echo esc_html( irian_str( 'form_ok' ) ); ?></p>
		<?php elseif ( 'err' === $status ) : ?>
			<p class="ipb-form-msg is-err" role="alert"><?php echo esc_html( irian_str( 'form_err' ) ); ?></p>
		<?php endif; ?>

		<div class="ipb-form-row">
			<label class="ipb-form-field">
				<span><?php echo esc_html( irian_str( 'form_name' ) ); ?></span>
				<input type="text" name="naam" required>
			</label>
			<label class="ipb-form-field">
				<span><?php echo esc_html( irian_str( 'form_email' ) ); ?></span>
				<input type="email" name="email" required>
			</label>
		</div>

		<?php if ( $types ) : ?>
			<label class="ipb-form-field">
				<span><?php echo esc_html( irian_str( 'form_type' ) ); ?></span>
				<select name="type">
					<?php foreach ( $types as $t ) : ?>
						<option value="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $t ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endif; ?>

		<label class="ipb-form-field">
			<span><?php echo esc_html( irian_str( 'form_message' ) ); ?></span>
			<textarea name="bericht" rows="5" required></textarea>
		</label>

		<div class="ipb-form-actions">
			<button type="submit" class="ipb-btn ipb-btn-primary"><?php echo esc_html( irian_str( 'form_submit' ) ); ?></button>
			<?php if ( '' !== $note ) : ?>
				<span class="ipb-form-note"><?php echo esc_html( $note ); ?></span>
			<?php endif; ?>
		</div>
	</form>
	<?php
	return ob_get_clean();
}

/**
 * Verwerk de inzending.
 */
function irian_handle_contact() {
	$redirect = isset( $_POST['irian_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['irian_redirect'] ) ) : home_url( '/' );
	$back     = static function ( $state ) use ( $redirect ) {
		wp_safe_redirect( add_query_arg( 'contact', $state, remove_query_arg( 'contact', $redirect ) ) . '#contact' );
		exit;
	};

	if ( ! isset( $_POST['irian_contact_nonce'] ) || ! wp_verify_nonce( $_POST['irian_contact_nonce'], 'irian_contact' ) ) {
		$back( 'err' );
	}
	// Honeypot + tijd-check (bots vullen het veld of zijn te snel).
	if ( ! empty( $_POST['website'] ) ) {
		$back( 'ok' ); // stil doen alsof het lukte
	}
	if ( isset( $_POST['irian_t'] ) && ( time() - (int) $_POST['irian_t'] ) < 2 ) {
		$back( 'err' );
	}

	$naam    = sanitize_text_field( wp_unslash( $_POST['naam'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$type    = sanitize_text_field( wp_unslash( $_POST['type'] ?? '' ) );
	$bericht = sanitize_textarea_field( wp_unslash( $_POST['bericht'] ?? '' ) );

	if ( '' === $naam || ! is_email( $email ) || '' === $bericht ) {
		$back( 'err' );
	}

	$recipient = irian_contact_recipient();
	$subject   = sprintf( '[%s] Nieuw bericht van %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $naam );
	$body      = "Naam: {$naam}\nE-mail: {$email}\n";
	if ( '' !== $type ) {
		$body .= "Type project: {$type}\n";
	}
	$body .= "\n{$bericht}\n";

	$headers = array(
		'Reply-To: ' . $naam . ' <' . $email . '>',
		'Content-Type: text/plain; charset=UTF-8',
	);

	$sent = wp_mail( $recipient, $subject, $body, $headers );
	$back( $sent ? 'ok' : 'err' );
}
add_action( 'admin_post_nopriv_irian_contact', 'irian_handle_contact' );
add_action( 'admin_post_irian_contact', 'irian_handle_contact' );
