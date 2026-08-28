<?php
/**
 * Panel: Hero
 *
 * Met een "Foto" ingevuld wordt de hero tweekoloms (tekst links, portret rechts),
 * zoals de inspiratie-site. Zonder foto blijft 't enkelkoloms.
 *
 * @param array $args Panel data (from get_template_part's $args).
 * @package IrianPortfolio
 */
$photo = ! empty( $args['photo'] ) ? wp_get_attachment_image( $args['photo'], 'medium_large', false, array(
	'class'   => 'ipb-hero-photo__img',
	'loading' => 'eager',
	'alt'     => 'Irian Veensma',
) ) : '';
?>
<header class="ipb-hero <?php echo $photo ? 'has-photo' : ''; ?>">
	<div class="ipb-hero-text">
		<span class="ipb-hero-eyebrow">// <?php echo esc_html( $args['eyebrow'] ?? '' ); ?></span>
		<h1 class="ipb-hero-title">
			<?php echo esc_html( $args['title_before'] ?? '' ); ?>
			<span class="ipb-hero-accent"><?php echo esc_html( $args['title_accent'] ?? '' ); ?></span>
		</h1>
		<p class="ipb-hero-sub"><?php echo esc_html( $args['subtitle'] ?? '' ); ?></p>
		<div class="ipb-hero-actions">
			<?php if ( ! empty( $args['primary_label'] ) ) : ?>
				<a href="<?php echo esc_url( $args['primary_url'] ?? '#' ); ?>" class="ipb-btn ipb-btn-primary"><?php echo esc_html( $args['primary_label'] ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $args['secondary_label'] ) ) : ?>
				<a href="<?php echo esc_url( $args['secondary_url'] ?? '#' ); ?>" class="ipb-btn ipb-btn-ghost"><?php echo esc_html( $args['secondary_label'] ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $photo ) : ?>
		<div class="ipb-hero-photo">
			<?php echo $photo; ?>
		</div>
	<?php endif; ?>
</header>
