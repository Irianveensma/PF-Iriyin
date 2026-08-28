<?php
/**
 * Panel: Contact
 *
 * De CTA-tekst plus een contactformulier (zie inc/contact-form.php). Het
 * e-mailadres is alleen de ontvanger van het formulier en staat niet zichtbaar
 * op de pagina.
 *
 * @param array $args Panel data.
 * @package IrianPortfolio
 */
$show_form = ! empty( $args['show_form'] );
?>
<section class="ipb-section ipb-contact" id="contact">
	<div class="ipb-section-label"><?php echo esc_html( $args['section_label'] ?? '' ); ?></div>
	<div class="ipb-contact-inner">
		<div class="ipb-contact-cta"><?php echo esc_html( $args['cta_text'] ?? '' ); ?></div>
	</div>

	<?php
	if ( $show_form && function_exists( 'irian_contact_form' ) ) {
		echo irian_contact_form( array(  // phpcs:ignore -- gebouwd met escapes in de functie
			'project_types' => $args['project_types'] ?? array(),
			'note'          => $args['form_note'] ?? '',
			'redirect'      => get_permalink(),
		) );
	}
	?>
</section>
