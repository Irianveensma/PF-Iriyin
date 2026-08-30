<?php
/**
 * Irian Portfolio - kleine tweetalige laag (NL / EN).
 *
 * De site is standaard Nederlands. Een knop in de nav zet 'm op Engels via
 * ?lang=en (onthouden in een cookie). De inhoud van de panels heeft een
 * Engelse tegenhanger in post meta "_irian_panels_en"; de vaste teksten in
 * het thema komen uit de strings-tabel hieronder.
 *
 * @package IrianPortfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Actieve taal: 'nl' of 'en'.
 */
function irian_lang() {
	$allowed = array( 'nl', 'en' );

	if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], $allowed, true ) ) {
		return $_GET['lang'];
	}
	if ( isset( $_COOKIE['irian_lang'] ) && in_array( $_COOKIE['irian_lang'], $allowed, true ) ) {
		return $_COOKIE['irian_lang'];
	}
	return 'nl';
}

/**
 * Snelle check.
 */
function irian_is_en() {
	return 'en' === irian_lang();
}

/**
 * Zet de taalkeuze in een cookie zodra ?lang= meekomt, zodat een kale reload
 * of een form-redirect de keuze niet verliest.
 */
function irian_lang_persist() {
	if ( ! isset( $_GET['lang'] ) || ! in_array( $_GET['lang'], array( 'nl', 'en' ), true ) ) {
		return;
	}
	$lang = $_GET['lang'];
	if ( ! isset( $_COOKIE['irian_lang'] ) || $_COOKIE['irian_lang'] !== $lang ) {
		$path = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
		$dom  = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
		@setcookie( 'irian_lang', $lang, time() + YEAR_IN_SECONDS, $path, $dom );
	}
	$_COOKIE['irian_lang'] = $lang;
}
add_action( 'init', 'irian_lang_persist' );

/**
 * URL van de huidige pagina met de taal omgezet (of naar $to gezet).
 */
function irian_lang_switch_url( $to = null ) {
	if ( null === $to ) {
		$to = irian_is_en() ? 'nl' : 'en';
	}
	return esc_url( add_query_arg( 'lang', $to ) );
}

/**
 * Vaste thema-teksten per taal.
 */
function irian_strings() {
	static $s = null;
	if ( null !== $s ) {
		return $s;
	}

	$s = array(
		'nl' => array(
			'skip'                => 'Naar inhoud',
			'home_aria'           => ', naar home',
			'nav_work'            => 'Work',
			'nav_platforms'       => 'Platforms',
			'nav_modules'         => 'Modules',
			'nav_faq'             => 'FAQ',
			'nav_contact'         => 'Contact',
			'kbd_label'           => 'snel navigeren',
			'kbd_aria'            => 'Open command palette',
			'lang_to'             => 'EN',
			'lang_aria'           => 'Switch to English',
			'to_top'              => 'Naar boven',
			'work_view_site'      => 'Bekijk de site',
			'module_view'         => 'Bekijk',
			'module_cue'          => 'Bekijk +',
			'stack_aria'          => 'Stack',
			'stack_why_label'     => 'Waarom dit ertoe doet',

			'palette_placeholder' => 'Spring naar… (typ om te zoeken)',
			'palette_search_aria' => 'Zoek een actie',
			'palette_aria'        => 'Command palette',
			'palette_empty'       => 'Niks gevonden.',
			'palette_nav'         => 'navigeren',
			'palette_open'        => 'openen',
			'palette_close'       => 'sluiten',
			'pal_go'              => 'Ga naar %s',
			'pal_top'             => 'Naar boven',
			'pal_hint_section'    => 'sectie',
			'pal_hint_external'   => 'extern',

			'console_hi'          => 'Aangenaam, developer',
			'console_sub'         => 'Je bent hier niet per ongeluk. Stuur een berichtje via het formulier onderaan.',

			'form_name'           => 'Naam',
			'form_email'          => 'E-mail',
			'form_type'           => 'Type project',
			'form_message'        => 'Waar kan ik mee helpen?',
			'form_submit'         => 'Verstuur',
			'form_hp'             => 'Vul dit niet in',
			'form_ok'             => 'Bericht verstuurd. Ik reageer meestal binnen een dag.',
			'form_err'            => 'Er ging iets mis. Controleer je gegevens en probeer het nog eens.',

			'demo_palette_btn'    => 'Open de command palette',
			'demo_palette_hint'   => 'Dit is exact dezelfde component als in de nav.',
			'demo_cursor_move'    => 'Beweeg je muis hier',
			'demo_cursor_target'  => 'hover mij',
			'demo_report_hint'    => 'Voorbeelduitvoer. De echte tool crawlt een opgegeven URL.',
			'seo_r1_l'            => 'Title & meta description',
			'seo_r1_d'            => 'Aanwezig, binnen lengte',
			'seo_r2_l'            => 'Open Graph tags',
			'seo_r2_d'            => 'Compleet',
			'seo_r3_l'            => 'Afbeeldingen zonder alt',
			'seo_r3_d'            => '3 van 24',
			'seo_r4_l'            => 'H1 uniek per pagina',
			'seo_r4_d'            => 'Ja',
			'seo_r5_l'            => 'Core Web Vitals (LCP)',
			'seo_r5_d'            => '3.1s, te traag',
			'seo_r6_l'            => 'Structured data',
			'seo_r6_d'            => 'JSON-LD gevonden',
			'seo_r7_l'            => 'Interne links',
			'seo_r7_d'            => 'Weinig diepte-links',

			'title_tagline'      => 'Webdeveloper · Marketeer · Digital',
		),
		'en' => array(
			'skip'                => 'Skip to content',
			'home_aria'           => ', back to home',
			'nav_work'            => 'Work',
			'nav_platforms'       => 'Platforms',
			'nav_modules'         => 'Modules',
			'nav_faq'             => 'FAQ',
			'nav_contact'         => 'Contact',
			'kbd_label'           => 'quick nav',
			'kbd_aria'            => 'Open command palette',
			'lang_to'             => 'NL',
			'lang_aria'           => 'Schakel over naar Nederlands',
			'to_top'              => 'Back to top',
			'work_view_site'      => 'Visit the site',
			'module_view'         => 'Open',
			'module_cue'          => 'View +',
			'stack_aria'          => 'Stack',
			'stack_why_label'     => 'Why this matters',

			'palette_placeholder' => 'Jump to… (type to search)',
			'palette_search_aria' => 'Search for an action',
			'palette_aria'        => 'Command palette',
			'palette_empty'       => 'Nothing found.',
			'palette_nav'         => 'navigate',
			'palette_open'        => 'open',
			'palette_close'       => 'close',
			'pal_go'              => 'Go to %s',
			'pal_top'             => 'Back to top',
			'pal_hint_section'    => 'section',
			'pal_hint_external'   => 'external',

			'console_hi'          => 'Nice to meet you, developer',
			'console_sub'         => 'You did not end up here by accident. Drop me a line through the form below.',

			'form_name'           => 'Name',
			'form_email'          => 'Email',
			'form_type'           => 'Project type',
			'form_message'        => 'What can I help you with?',
			'form_submit'         => 'Send',
			'form_hp'             => 'Leave this empty',
			'form_ok'             => 'Message sent. I usually reply within a day.',
			'form_err'            => 'Something went wrong. Check your details and try again.',

			'demo_palette_btn'    => 'Open the command palette',
			'demo_palette_hint'   => 'This is the exact same component that sits in the nav.',
			'demo_cursor_move'    => 'Move your mouse here',
			'demo_cursor_target'  => 'hover me',
			'demo_report_hint'    => 'Sample output. The real tool crawls a given URL.',
			'seo_r1_l'            => 'Title & meta description',
			'seo_r1_d'            => 'Present, within length',
			'seo_r2_l'            => 'Open Graph tags',
			'seo_r2_d'            => 'Complete',
			'seo_r3_l'            => 'Images without alt',
			'seo_r3_d'            => '3 of 24',
			'seo_r4_l'            => 'H1 unique per page',
			'seo_r4_d'            => 'Yes',
			'seo_r5_l'            => 'Core Web Vitals (LCP)',
			'seo_r5_d'            => '3.1s, too slow',
			'seo_r6_l'            => 'Structured data',
			'seo_r6_d'            => 'JSON-LD found',
			'seo_r7_l'            => 'Internal links',
			'seo_r7_d'            => 'Few deep links',

			'title_tagline'      => 'Web developer · Marketer · Digital',
		),
	);

	return $s;
}

/**
 * Eén vaste tekst in de actieve taal (val terug op NL).
 */
function irian_str( $key ) {
	$all  = irian_strings();
	$lang = irian_lang();
	if ( isset( $all[ $lang ][ $key ] ) ) {
		return $all[ $lang ][ $key ];
	}
	return $all['nl'][ $key ] ?? '';
}

/**
 * De panel-data voor de render: Engels als dat gekozen is en er Engelse data
 * staat, anders het Nederlandse origineel.
 *
 * @param int $post_id Pagina-ID.
 */
function irian_panels_data( $post_id ) {
	if ( irian_is_en() ) {
		$en = get_post_meta( $post_id, '_irian_panels_en', true );
		if ( is_array( $en ) && ! empty( $en ) ) {
			return $en;
		}
	}
	$nl = get_post_meta( $post_id, '_irian_panels', true );
	return is_array( $nl ) ? $nl : array();
}

/**
 * <html lang="..."> meebewegen met de taalkeuze.
 */
add_filter(
	'language_attributes',
	static function ( $output ) {
		$lang = irian_is_en() ? 'en-US' : 'nl-NL';
		$attr = 'lang="' . esc_attr( $lang ) . '"';
		if ( function_exists( 'is_rtl' ) && is_rtl() ) {
			$attr .= ' dir="rtl"';
		}
		return $attr;
	}
);

/**
 * Tagline in de <title> vertalen.
 */
add_filter(
	'document_title_parts',
	static function ( $parts ) {
		if ( ! is_admin() && isset( $parts['tagline'] ) ) {
			$parts['tagline'] = irian_str( 'title_tagline' );
		}
		return $parts;
	}
);

/**
 * Scheidingsteken in de <title>: een middot in plaats van de WordPress-default
 * en-dash, gelijk aan de puntjes in de tagline zelf.
 */
add_filter(
	'document_title_separator',
	static function ( $sep ) {
		return is_admin() ? $sep : '·';
	}
);
