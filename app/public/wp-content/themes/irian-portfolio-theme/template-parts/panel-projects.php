<?php
/**
 * Panel: Projects (grotere builds).
 *
 * Uitgebreidere schrijf-ups van de grotere dingen die Irian gebouwd heeft
 * (Prompt Studio, Nieuws Website). Gestapelde kaarten, zelfde uitgefreesde
 * behandeling als de Work-blokken.
 *
 * @param array $args Panel data.
 * @package IrianPortfolio
 */
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
if ( ! $items ) {
	return;
}

/** Maak van "voorbeeld.nl" een nette https-URL. */
$to_href = static function ( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	return preg_match( '#^https?://#i', $url ) ? $url : 'https://' . preg_replace( '#^//#', '', $url );
};
?>
<section class="ipb-section" id="platforms">
	<div class="ipb-section-label"><?php echo esc_html( $args['section_label'] ?? '' ); ?></div>
	<h2 class="ipb-section-title"><?php echo esc_html( $args['section_title'] ?? '' ); ?></h2>
	<?php if ( ! empty( $args['section_intro'] ) ) : ?>
		<p class="ipb-section-intro"><?php echo esc_html( $args['section_intro'] ); ?></p>
	<?php endif; ?>

	<div class="ipb-projects">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$name     = $item['name'] ?? '';
			$tagline  = $item['tagline'] ?? '';
			$tags     = $item['tags'] ?? '';
			$desc     = $item['description'] ?? '';
			$features = isset( $item['features'] ) && is_array( $item['features'] ) ? $item['features'] : array();
			$roles    = isset( $item['roles'] ) && is_array( $item['roles'] ) ? $item['roles'] : array();
			$href     = $to_href( $item['url'] ?? '' );
			$image    = ! empty( $item['image'] ) ? wp_get_attachment_image_url( $item['image'], 'large' ) : '';
			if ( '' === $name ) {
				continue;
			}
			?>
			<article class="ipb-project-card">
				<div class="ipb-project-main">
					<header class="ipb-project-head">
						<h3 class="ipb-project-name"><?php echo esc_html( $name ); ?></h3>
						<?php if ( '' !== $tags ) : ?>
							<div class="ipb-project-tags"><?php echo esc_html( $tags ); ?></div>
						<?php endif; ?>
					</header>

					<?php if ( '' !== $tagline ) : ?>
						<p class="ipb-project-tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>

					<?php if ( '' !== $desc ) : ?>
						<div class="ipb-project-desc"><?php echo wp_kses_post( wpautop( $desc ) ); ?></div>
					<?php endif; ?>

					<?php if ( $roles ) : ?>
						<div class="ipb-project-roles">
							<?php foreach ( $roles as $role ) : ?>
								<span class="ipb-project-role"><?php echo esc_html( $role ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( $href ) : ?>
						<a class="ipb-work-link" href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html( irian_str( 'work_view_site' ) ); ?> <span aria-hidden="true">&#8599;</span>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( $features || $image ) : ?>
					<aside class="ipb-project-aside">
						<?php if ( $image ) : ?>
							<div class="ipb-project-shot">
								<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( sprintf( 'Beeld van %s', $name ) ); ?>" loading="lazy">
							</div>
						<?php endif; ?>
						<?php if ( $features ) : ?>
							<ul class="ipb-project-features">
								<?php foreach ( $features as $feature ) : ?>
									<li><span class="ipb-project-feature-mark" aria-hidden="true">+</span><?php echo esc_html( $feature ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</aside>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
</section>
