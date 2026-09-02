<?php
/**
 * Panel: Lab Grid, de "Modules"-sectie.
 *
 * Elke tegel met inhoud (blurb / demo / code / afbeelding) wordt een knop;
 * klik toont een paneel onder de grid met die inhoud. Eén open tegelijk,
 * zelfde patroon als de stack-tags.
 *
 * @param array $args Panel data.
 * @package IrianPortfolio
 */
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
$uid   = wp_unique_id( 'ipb-mod-' );

/** Heeft dit item iets om te tonen? */
$has_content = static function ( $it ) {
	return ! empty( $it['blurb'] )
		|| ( 'code' === ( $it['content_type'] ?? '' ) && ! empty( $it['code'] ) )
		|| ( 'demo' === ( $it['content_type'] ?? '' ) && ! empty( $it['demo'] ) )
		|| ( 'image' === ( $it['content_type'] ?? '' ) && ! empty( $it['image'] ) )
		|| ! empty( $it['url'] );
};
$to_href = static function ( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	return preg_match( '#^https?://#i', $url ) ? $url : 'https://' . preg_replace( '#^//#', '', $url );
};
?>
<section class="ipb-section" id="lab">
	<div class="ipb-section-label"><?php echo esc_html( irian_section_label( $args['section_label'] ?? '' ) ); ?></div>
	<h2 class="ipb-section-title"><?php echo esc_html( $args['section_title'] ?? '' ); ?></h2>
	<?php if ( ! empty( $args['section_intro'] ) ) : ?>
		<p class="ipb-section-intro"><?php echo esc_html( $args['section_intro'] ); ?></p>
	<?php endif; ?>

	<div class="ipb-lab-grid">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php $active = $has_content( $item ); ?>
			<?php if ( $active ) : ?>
				<button type="button" class="ipb-lab-tile ipb-lab-tile--btn"
					aria-controls="<?php echo esc_attr( "{$uid}-panel" ); ?>"
					aria-expanded="false"
					data-target="<?php echo esc_attr( $i ); ?>">
					<span class="ipb-lab-tile-title"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
					<span class="ipb-lab-tile-tag"><?php echo esc_html( $item['tag'] ?? '' ); ?></span>
					<span class="ipb-lab-tile-cue" aria-hidden="true"><?php echo esc_html( irian_str( 'module_cue' ) ); ?></span>
				</button>
			<?php else : ?>
				<div class="ipb-lab-tile">
					<span class="ipb-lab-tile-title"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
					<span class="ipb-lab-tile-tag"><?php echo esc_html( $item['tag'] ?? '' ); ?></span>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<div class="ipb-modules-detail" id="<?php echo esc_attr( "{$uid}-panel" ); ?>">
		<?php
		foreach ( $items as $i => $item ) :
			if ( ! $has_content( $item ) ) {
				continue;
			}
			$ctype = $item['content_type'] ?? 'none';
			$href  = $to_href( $item['url'] ?? '' );
			?>
			<article class="ipb-module-panel" data-panel="<?php echo esc_attr( $i ); ?>" hidden>
				<header class="ipb-module-panel__head">
					<h3><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
					<span class="ipb-module-panel__tag"><?php echo esc_html( $item['tag'] ?? '' ); ?></span>
				</header>

				<?php if ( ! empty( $item['blurb'] ) ) : ?>
					<p class="ipb-module-panel__blurb"><?php echo esc_html( $item['blurb'] ); ?></p>
				<?php endif; ?>

				<?php if ( 'code' === $ctype && ! empty( $item['code'] ) ) : ?>
					<figure class="ipb-code">
						<?php if ( ! empty( $item['code_lang'] ) ) : ?>
							<figcaption><?php echo esc_html( $item['code_lang'] ); ?></figcaption>
						<?php endif; ?>
						<pre><code><?php echo esc_html( $item['code'] ); ?></code></pre>
					</figure>

				<?php elseif ( 'demo' === $ctype && ! empty( $item['demo'] ) ) : ?>
					<?php echo irian_module_demo( $item['demo'] ); // phpcs:ignore -- vaste, veilige HTML ?>

				<?php elseif ( 'image' === $ctype && ! empty( $item['image'] ) ) : ?>
					<div class="ipb-module-panel__img">
						<?php echo wp_get_attachment_image( $item['image'], 'large', false, array( 'loading' => 'lazy' ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $href ) : ?>
					<a class="ipb-work-link" href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html( irian_str( 'module_view' ) ); ?> <span aria-hidden="true">&#8599;</span>
					</a>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
</section>
