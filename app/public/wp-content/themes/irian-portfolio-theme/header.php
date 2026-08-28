<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/svg+xml" href="<?php echo esc_url( get_template_directory_uri() . '/assets/favicon.svg' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="ipb-skip-link" href="#ipb-content"><?php echo esc_html( irian_str( 'skip' ) ); ?></a>

<nav class="ipb-nav" id="top">
	<div class="ipb-nav-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ipb-logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . irian_str( 'home_aria' ) ); ?>">
			<img class="ipb-logo-mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/logo-mark.svg' ); ?>" alt="" width="30" height="30">
			<span class="ipb-logo-word">iriyin</span>
		</a>
		<div class="ipb-nav-links">
			<a href="#work"><?php echo esc_html( irian_str( 'nav_work' ) ); ?></a>
			<a href="#platforms"><?php echo esc_html( irian_str( 'nav_platforms' ) ); ?></a>
			<a href="#lab"><?php echo esc_html( irian_str( 'nav_modules' ) ); ?></a>
			<a href="#faq"><?php echo esc_html( irian_str( 'nav_faq' ) ); ?></a>
			<a href="#contact"><?php echo esc_html( irian_str( 'nav_contact' ) ); ?></a>
		</div>
		<div class="ipb-nav-tools">
			<a class="ipb-lang" href="<?php echo irian_lang_switch_url(); ?>"
				hreflang="<?php echo irian_is_en() ? 'nl' : 'en'; ?>"
				aria-label="<?php echo esc_attr( irian_str( 'lang_aria' ) ); ?>">
				<?php echo esc_html( irian_str( 'lang_to' ) ); ?>
			</a>
			<button type="button" class="ipb-kbd-hint" data-ipb-cmdk-open aria-label="<?php echo esc_attr( irian_str( 'kbd_aria' ) ); ?>">
				<span class="ipb-kbd-keys"><kbd>&#8984;</kbd><kbd>K</kbd></span>
				<span class="ipb-kbd-label"><?php echo esc_html( irian_str( 'kbd_label' ) ); ?></span>
			</button>
		</div>
	</div>
</nav>
