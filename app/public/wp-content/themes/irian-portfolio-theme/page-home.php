<?php
/**
 * Template Name: Home (Panels)
 *
 * Panels worden beheerd via de "Homepage Panels" metabox op deze pagina
 * (geen ACF meer nodig, dit is onze eigen gratis flexible-content-achtige oplossing).
 *
 * @package IrianPortfolio
 */

get_header();

$panels = function_exists( 'irian_panels_data' )
	? irian_panels_data( get_the_ID() )
	: (array) get_post_meta( get_the_ID(), '_irian_panels', true );
?>

<main id="ipb-content" class="ipb-main">
	<?php if ( ! empty( $panels ) ) : ?>
		<?php foreach ( $panels as $panel ) : ?>
			<?php
			$type = $panel['type'] ?? '';
			$data = $panel['data'] ?? array();
			if ( $type ) {
				get_template_part( 'template-parts/panel', $type, $data );
			}
			?>
		<?php endforeach; ?>
	<?php else : ?>
		<p>Nog geen panelen toegevoegd. Ga naar de pagina-editor en voeg panelen toe via de "Homepage Panels" box.</p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
