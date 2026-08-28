<?php
/**
 * Panel: Work Grid (case studies)
 *
 * Elke kaart toont de live site in een MacBook + telefoon-mockup.
 * Vul per project "Screenshot desktop" en "Screenshot mobiel" in de metabox;
 * zonder screenshots valt de kaart terug op een gradient met de URL.
 *
 * @param array $args Panel data.
 * @package IrianPortfolio
 */
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

/** Maak van "pedicure-paulina.nl" een nette https-URL. */
$to_href = static function ( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	return preg_match( '#^https?://#i', $url ) ? $url : 'https://' . preg_replace( '#^//#', '', $url );
};
?>
<section class="ipb-section" id="work">
	<div class="ipb-section-label"><?php echo esc_html( $args['section_label'] ?? '' ); ?></div>
	<h2 class="ipb-section-title"><?php echo esc_html( $args['section_title'] ?? '' ); ?></h2>
	<?php if ( ! empty( $args['section_intro'] ) ) : ?>
		<p class="ipb-section-intro"><?php echo esc_html( $args['section_intro'] ); ?></p>
	<?php endif; ?>

	<div class="ipb-work-grid">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$desktop = ! empty( $item['visual'] ) ? wp_get_attachment_image_url( $item['visual'], 'large' ) : '';
			$mobile  = ! empty( $item['visual_mobile'] ) ? wp_get_attachment_image_url( $item['visual_mobile'], 'medium' ) : '';
			$href    = $to_href( $item['url'] ?? '' );
			$has_shot = $desktop || $mobile;
			?>
			<article class="ipb-work-card">
				<div class="ipb-work-stage <?php echo $has_shot ? 'has-shot' : 'is-empty'; ?>">
					<?php if ( $has_shot ) : ?>
						<div class="ipb-device ipb-device--mac">
							<div class="ipb-device--mac__screen">
								<?php if ( $desktop ) : ?>
									<img src="<?php echo esc_url( $desktop ); ?>" alt="<?php echo esc_attr( sprintf( 'Screenshot van %s', $item['name'] ?? '' ) ); ?>" loading="lazy">
								<?php endif; ?>
							</div>
							<div class="ipb-device--mac__base"><span class="ipb-device--mac__notch"></span></div>
						</div>
						<?php if ( $mobile ) : ?>
							<div class="ipb-device ipb-device--phone">
								<div class="ipb-device--phone__screen">
									<img src="<?php echo esc_url( $mobile ); ?>" alt="" loading="lazy">
								</div>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<span class="ipb-work-stage__url"><?php echo esc_html( $item['url'] ?? '' ); ?></span>
					<?php endif; ?>
				</div>

				<div class="ipb-work-body">
					<div class="ipb-work-meta">
						<?php foreach ( ( $item['tags'] ?? array() ) as $tag ) : ?>
							<span class="ipb-work-tag"><?php echo esc_html( $tag ); ?></span>
						<?php endforeach; ?>
					</div>
					<h3 class="ipb-work-name"><?php echo esc_html( $item['name'] ?? '' ); ?></h3>
					<div class="ipb-work-url"><?php echo esc_html( $item['url'] ?? '' ); ?></div>
					<?php if ( $href ) : ?>
						<a class="ipb-work-link" href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html( irian_str( 'work_view_site' ) ); ?> <span aria-hidden="true">&#8599;</span>
						</a>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<?php if ( ! empty( $args['note'] ) ) : ?>
		<p class="ipb-work-note"><?php echo esc_html( $args['note'] ); ?></p>
	<?php endif; ?>
</section>
