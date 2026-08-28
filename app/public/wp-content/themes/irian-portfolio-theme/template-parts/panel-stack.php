<?php
/**
 * Panel: Stack (skill tags)
 *
 * Tags met een "note" worden klikbare knoppen; klik toont een paneel met een
 * visueel beeld van die skill + de uitleg. Eén paneel per skill, JS wisselt.
 *
 * Data-vorm: array van ['label' => string, 'note' => string].
 *
 * @param array $args Panel data.
 * @package IrianPortfolio
 */
$raw  = isset( $args['tags'] ) && is_array( $args['tags'] ) ? $args['tags'] : array();
$tags = array();
foreach ( $raw as $t ) {
	if ( is_array( $t ) ) {
		$tags[] = array( 'label' => $t['label'] ?? '', 'note' => $t['note'] ?? '' );
	} elseif ( '' !== (string) $t ) {
		$tags[] = array( 'label' => (string) $t, 'note' => '' );
	}
}
if ( ! $tags ) {
	return;
}
$has_notes = (bool) array_filter( wp_list_pluck( $tags, 'note' ) );
$uid       = wp_unique_id( 'ipb-stack-' );
?>
<section class="ipb-stack" id="stack">
	<div class="ipb-stack-row" <?php echo $has_notes ? 'role="tablist" aria-label="Stack"' : ''; ?>>
		<?php foreach ( $tags as $i => $tag ) : ?>
			<?php $slug = irian_skill_slug( $tag['label'] ); ?>
			<?php if ( '' !== $tag['note'] ) : ?>
				<button type="button"
					class="ipb-stack-tag ipb-stack-tag--btn"
					id="<?php echo esc_attr( "{$uid}-tab-{$i}" ); ?>"
					role="tab"
					aria-controls="<?php echo esc_attr( "{$uid}-panel-{$slug}" ); ?>"
					aria-expanded="false"
					data-target="<?php echo esc_attr( $slug ); ?>">
					<?php echo esc_html( $tag['label'] ); ?>
					<span class="ipb-stack-tag__plus" aria-hidden="true">+</span>
				</button>
			<?php else : ?>
				<span class="ipb-stack-tag"><?php echo esc_html( $tag['label'] ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ( $has_notes ) : ?>
		<div class="ipb-stack-panels">
			<?php foreach ( $tags as $tag ) : ?>
				<?php
				if ( '' === $tag['note'] ) {
					continue;
				}
				$slug   = irian_skill_slug( $tag['label'] );
				$visual = irian_skill_visual( $tag['label'] );
				?>
				<div class="ipb-stack-panel" id="<?php echo esc_attr( "{$uid}-panel-{$slug}" ); ?>" data-panel="<?php echo esc_attr( $slug ); ?>" role="region" hidden>
					<?php if ( $visual ) : ?>
						<div class="ipb-stack-panel__visual"><?php echo $visual; // phpcs:ignore -- vaste, veilige inline SVG ?></div>
					<?php endif; ?>
					<div class="ipb-stack-panel__body">
						<span class="ipb-stack-panel__label"><?php echo esc_html( $tag['label'] ); ?></span>
						<p class="ipb-stack-panel__note"><?php echo esc_html( $tag['note'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
