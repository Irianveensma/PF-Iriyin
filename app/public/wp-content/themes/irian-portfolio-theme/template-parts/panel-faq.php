<?php
/**
 * Panel: FAQ (accordion via native <details>).
 *
 * @param array $args Panel data.
 * @package IrianPortfolio
 */
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
if ( ! $items ) {
	return;
}
?>
<section class="ipb-section" id="faq">
	<div class="ipb-section-label"><?php echo esc_html( irian_section_label( $args['section_label'] ?? '' ) ); ?></div>
	<h2 class="ipb-section-title"><?php echo esc_html( $args['section_title'] ?? '' ); ?></h2>
	<?php if ( ! empty( $args['section_intro'] ) ) : ?>
		<p class="ipb-section-intro"><?php echo esc_html( $args['section_intro'] ); ?></p>
	<?php endif; ?>

	<div class="ipb-faq">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$q = $item['question'] ?? '';
			$a = $item['answer'] ?? '';
			if ( '' === $q ) {
				continue;
			}
			?>
			<details class="ipb-faq-item">
				<summary>
					<span><?php echo esc_html( $q ); ?></span>
					<span class="ipb-faq-icon" aria-hidden="true">+</span>
				</summary>
				<div class="ipb-faq-answer">
					<?php echo wp_kses_post( wpautop( $a ) ); ?>
				</div>
			</details>
		<?php endforeach; ?>
	</div>
</section>
