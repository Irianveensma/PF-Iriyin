<?php
/**
 * Fallback template. Wordt alleen gebruikt als een pagina niet het
 * "Home (Panels)" sjabloon heeft. Voor het testen van de panelen:
 * gebruik page-home.php (via Pagina-attributen, Sjabloon).
 *
 * @package IrianPortfolio
 */

get_header();
?>

<main class="ipb-main">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		<?php endwhile; ?>
	<?php else : ?>
		<p>Niets gevonden.</p>
	<?php endif; ?>
</main>

<?php get_footer(); ?>
