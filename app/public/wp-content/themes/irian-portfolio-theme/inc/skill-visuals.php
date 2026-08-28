<?php
/**
 * Irian Portfolio - visuele beelden per skill.
 *
 * irian_skill_visual( 'WordPress' ) geeft een inline SVG-string terug (line-art,
 * metallic) die laat zien wat die skill is/kan. Onbekende labels -> ''.
 *
 * De SVG's gebruiken currentColor voor de lijnen; een element met class="accent"
 * krijgt via CSS een lichtere/chrome kleur.
 *
 * @package IrianPortfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normaliseer een skill-label naar een key.
 */
function irian_skill_slug( $label ) {
	$slug = strtolower( remove_accents( (string) $label ) );
	$slug = preg_replace( '/[^a-z0-9]+/', '-', $slug );
	return trim( $slug, '-' );
}

/**
 * @return string Inline SVG of lege string.
 */
function irian_skill_visual( $label ) {
	$open  = '<svg class="ipb-skill-svg" viewBox="0 0 260 150" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
	$close = '</svg>';

	$map = array(

		// WordPress - pagina's bouwen uit blokken in een browservenster.
		'wordpress' => '
			<rect x="16" y="16" width="228" height="118" rx="10"/>
			<line x1="16" y1="42" x2="244" y2="42"/>
			<circle cx="30" cy="29" r="2.4"/><circle cx="40" cy="29" r="2.4"/><circle cx="50" cy="29" r="2.4"/>
			<rect class="accent" x="34" y="56" width="126" height="16" rx="3"/>
			<line x1="34" y1="88" x2="150" y2="88"/>
			<line x1="34" y1="100" x2="128" y2="100"/>
			<line x1="34" y1="112" x2="140" y2="112"/>
			<rect x="180" y="56" width="46" height="46" rx="4"/>
			<path d="M180 92l12-13 9 8 6-6 9 9"/>
			<circle cx="192" cy="70" r="3.4"/>',

		// Magento - webshop: productkaarten + winkelwagen.
		'magento' => '
			<rect x="16" y="16" width="228" height="118" rx="10"/>
			<line x1="16" y1="40" x2="244" y2="40"/>
			<path class="accent" d="M214 24h6l3 12h12l-2 8h-11l1 4h9"/>
			<circle class="accent" cx="222" cy="31" r="1.6"/>
			<rect x="30" y="52" width="58" height="70" rx="4"/>
			<rect x="38" y="60" width="42" height="30" rx="2"/>
			<line x1="38" y1="100" x2="72" y2="100"/><line x1="38" y1="110" x2="62" y2="110"/>
			<rect x="101" y="52" width="58" height="70" rx="4"/>
			<rect x="109" y="60" width="42" height="30" rx="2"/>
			<line x1="109" y1="100" x2="143" y2="100"/><line x1="109" y1="110" x2="133" y2="110"/>
			<rect x="172" y="52" width="58" height="70" rx="4"/>
			<rect x="180" y="60" width="42" height="30" rx="2"/>
			<line x1="180" y1="100" x2="214" y2="100"/><line x1="180" y1="110" x2="204" y2="110"/>',

		// AI Development - prompt door een netwerk naar output.
		'ai-development' => '
			<circle cx="34" cy="75" r="11"/>
			<circle cx="128" cy="38" r="9"/><circle cx="128" cy="75" r="9"/><circle cx="128" cy="112" r="9"/>
			<circle class="accent" cx="224" cy="75" r="11"/>
			<path d="M45 75l74-33M45 75h74M45 75l74 33"/>
			<path d="M137 38l76 32M137 75h76M137 112l76-32"/>
			<path class="accent" d="M224 52v-9M224 107v9M241 75h9M198 75h9"/>',

		// SEO - zoekbalk + resultaten, bovenste stijgt.
		'seo' => '
			<rect x="16" y="16" width="228" height="26" rx="13"/>
			<circle cx="34" cy="29" r="6"/><line x1="38.5" y1="33.5" x2="44" y2="39"/>
			<path class="accent" d="M28 74l8-9 8 9"/>
			<line class="accent" x1="36" y1="65" x2="36" y2="86"/>
			<line class="accent" x1="58" y1="64" x2="150" y2="64"/>
			<line x1="58" y1="76" x2="120" y2="76"/><line x1="58" y1="85" x2="104" y2="85"/>
			<line x1="30" y1="104" x2="140" y2="104"/>
			<line x1="30" y1="115" x2="110" y2="115"/><line x1="30" y1="124" x2="96" y2="124"/>
			<path d="M198 120l12-12 12 12" class="accent"/>
			<line x1="210" y1="108" x2="210" y2="60" class="accent"/>
			<path d="M170 96h12v24h-12zM192 84h12v36h-12zM214 96h12v24h-12z"/>',

		// HTML / CSS - code-tags + een layout-wireframe.
		'html-css' => '
			<path d="M56 46L34 75l22 29"/>
			<path d="M84 42L70 108"/>
			<path d="M100 46l22 29-22 29"/>
			<rect x="146" y="26" width="98" height="100" rx="6"/>
			<rect class="accent" x="156" y="36" width="78" height="16" rx="2"/>
			<rect x="156" y="60" width="34" height="40" rx="2"/>
			<rect x="200" y="60" width="34" height="40" rx="2"/>
			<line x1="156" y1="112" x2="234" y2="112"/>',

		// JavaScript - interactie: cursor + klik-ripple + toggle.
		'javascript' => '
			<path d="M60 34c-10 0-14 6-14 16v6c0 6-4 9-10 9 6 0 10 3 10 9v6c0 10 4 16 14 16"/>
			<path d="M196 34c10 0 14 6 14 16v6c0 6 4 9 10 9-6 0-10 3-10 9v6c0 10-4 16-14 16"/>
			<path class="accent" d="M118 66l0 44 11-12 7 15 8-4-7-14 16-1z"/>
			<path d="M150 44a26 26 0 0 0-40 0" class="accent"/>
			<path d="M162 40a40 40 0 0 0-64 0" class="accent"/>
			<rect x="104" y="120" width="52" height="20" rx="10"/>
			<circle class="accent" cx="146" cy="130" r="6"/>',

		// PHP - server + database + code-brackets.
		'php' => '
			<path d="M30 96l-8-9 8-9" class="accent"/>
			<path d="M230 78l8 9-8 9" class="accent"/>
			<rect x="52" y="24" width="156" height="30" rx="5"/>
			<line x1="52" y1="39" x2="208" y2="39"/>
			<circle class="accent" cx="196" cy="31" r="2.6"/><circle cx="196" cy="47" r="2.6"/>
			<path d="M96 66c0 6 22 10 34 10s34-4 34-10"/>
			<path d="M96 66v42c0 6 22 10 34 10s34-4 34-10V66"/>
			<path d="M96 66c0-6 22-10 34-10s34 4 34 10"/>
			<path d="M96 87c0 6 22 10 34 10s34-4 34-10"/>',
	);

	$slug = irian_skill_slug( $label );
	if ( ! isset( $map[ $slug ] ) ) {
		return '';
	}
	return $open . preg_replace( '/\s+/', ' ', trim( $map[ $slug ] ) ) . $close;
}
