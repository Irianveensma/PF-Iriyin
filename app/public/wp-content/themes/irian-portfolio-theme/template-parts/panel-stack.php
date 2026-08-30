<?php
/**
 * Panel: Stack (skill tags)
 *
 * Tags met een "note" (of "children") worden klikbare knoppen; klik toont een
 * paneel met een visueel beeld van die skill + de uitleg. Eén paneel per tag,
 * JS wisselt. Een tag met "children" (bv. "Content Management Systems") toont
 * meerdere sub-skills in één paneel in plaats van één note/why-paar.
 *
 * Data-vorm: array van ['label' => string, 'note' => string, 'why' => string]
 * of, voor een groep: ['label' => string, 'children' => array van hetzelfde
 * ['label', 'note', 'why']-drietal]. "why" is optioneel: uitleg waarom deze
 * skill ertoe doet, naast "note" (wat het is).
 *
 * @param array $args Panel data.
 * @package IrianPortfolio
 */
$raw  = isset( $args['tags'] ) && is_array( $args['tags'] ) ? $args['tags'] : array();
$tags = array();
foreach ( $raw as $t ) {
	if ( is_array( $t ) ) {
		$children = array();
		if ( ! empty( $t['children'] ) && is_array( $t['children'] ) ) {
			foreach ( $t['children'] as $c ) {
				$children[] = array(
					'label' => $c['label'] ?? '',
					'note'  => $c['note'] ?? '',
					'why'   => $c['why'] ?? '',
				);
			}
		}
		$tags[] = array(
			'label'    => $t['label'] ?? '',
			'note'     => $t['note'] ?? '',
			'why'      => $t['why'] ?? '',
			'children' => $children,
		);
	} elseif ( '' !== (string) $t ) {
		$tags[] = array( 'label' => (string) $t, 'note' => '', 'why' => '', 'children' => array() );
	}
}
if ( ! $tags ) {
	return;
}

/** Klikbaar zodra er een note is, of een groep sub-skills. */
$is_interactive = static function ( $tag ) {
	return '' !== $tag['note'] || ! empty( $tag['children'] );
};

/** Print visual + label + note (+ optionele why) voor één skill-entry. */
$render_entry = static function ( $entry ) {
	$visual = irian_skill_visual( $entry['label'] );
	?>
	<?php if ( $visual ) : ?>
		<div class="ipb-stack-panel__visual"><?php echo $visual; // phpcs:ignore -- vaste, veilige inline SVG ?></div>
	<?php endif; ?>
	<div class="ipb-stack-panel__body">
		<span class="ipb-stack-panel__label"><?php echo esc_html( $entry['label'] ); ?></span>
		<p class="ipb-stack-panel__note"><?php echo esc_html( $entry['note'] ); ?></p>
		<?php if ( '' !== $entry['why'] ) : ?>
			<div class="ipb-stack-panel__why">
				<span class="ipb-stack-panel__why-label"><?php echo esc_html( irian_str( 'stack_why_label' ) ); ?></span>
				<p><?php echo esc_html( $entry['why'] ); ?></p>
			</div>
		<?php endif; ?>
	</div>
	<?php
};

$has_notes = (bool) array_filter( $tags, $is_interactive );
$uid       = wp_unique_id( 'ipb-stack-' );
?>
<section class="ipb-stack" id="stack">
	<div class="ipb-stack-row" <?php echo $has_notes ? 'role="tablist" aria-label="Stack"' : ''; ?>>
		<?php foreach ( $tags as $i => $tag ) : ?>
			<?php $slug = irian_skill_slug( $tag['label'] ); ?>
			<?php if ( $is_interactive( $tag ) ) : ?>
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
				if ( ! $is_interactive( $tag ) ) {
					continue;
				}
				$slug = irian_skill_slug( $tag['label'] );
				?>
				<div class="ipb-stack-panel<?php echo $tag['children'] ? ' ipb-stack-panel--group' : ''; ?>" id="<?php echo esc_attr( "{$uid}-panel-{$slug}" ); ?>" data-panel="<?php echo esc_attr( $slug ); ?>" role="region" hidden>
					<?php if ( $tag['children'] ) : ?>
						<?php foreach ( $tag['children'] as $child ) : ?>
							<div class="ipb-stack-panel__item">
								<?php $render_entry( $child ); ?>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<?php $render_entry( $tag ); ?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
