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

		// Magento - een grote webshop-pagina: productfoto + titel + koopknop.
		'magento' => '
			<rect x="16" y="16" width="228" height="118" rx="10"/>
			<line x1="16" y1="40" x2="244" y2="40"/>
			<path class="accent" d="M207 23h2l3 3"/>
			<path class="accent" d="M212 26h14l-2 8h-10z"/>
			<circle class="accent" cx="216" cy="35" r="1.1"/><circle class="accent" cx="222" cy="35" r="1.1"/>
			<rect x="30" y="52" width="92" height="70" rx="6"/>
			<circle cx="48" cy="66" r="4"/>
			<path d="M38 108l14-16 11 10 8-8 13 14"/>
			<rect class="accent" x="136" y="58" width="100" height="14" rx="3"/>
			<line x1="136" y1="86" x2="224" y2="86"/>
			<line x1="136" y1="98" x2="196" y2="98"/>
			<rect class="accent" x="136" y="110" width="70" height="20" rx="10"/>',

		// Headless CMS - content-API in het midden, losgetrokken van een vaste
		// front-end: dezelfde content stroomt naar meerdere onafhankelijke schermen.
		'headless-cms' => '
			<rect x="20" y="40" width="68" height="70" rx="10"/>
			<path class="accent" d="M40 62l-9 13 9 13"/>
			<path class="accent" d="M68 62l9 13-9 13"/>
			<circle cx="54" cy="75" r="3"/>
			<circle cx="88" cy="58" r="2.4"/><circle cx="88" cy="92" r="2.4"/>
			<path d="M88 58L146 46"/>
			<path d="M88 92L170 118"/>
			<rect x="146" y="24" width="80" height="54" rx="6"/>
			<rect class="accent" x="154" y="32" width="64" height="32" rx="2"/>
			<line x1="186" y1="78" x2="186" y2="88"/>
			<line x1="170" y1="88" x2="202" y2="88"/>
			<rect x="170" y="96" width="32" height="52" rx="6"/>
			<rect class="accent" x="176" y="102" width="20" height="36" rx="2"/>
			<circle cx="186" cy="142" r="1.6"/>',

		// AI Development - prompt in, AI-antwoord (met avatar) terug, sparkles
		// voor de magie ertussen.
		'ai-development' => '
			<rect x="18" y="24" width="150" height="34" rx="17"/>
			<line x1="34" y1="41" x2="112" y2="41"/>
			<line class="accent" x1="120" y1="33" x2="120" y2="49"/>
			<circle class="accent" cx="150" cy="41" r="9"/>
			<path class="accent" d="M145 41h7M148 37l4 4-4 4"/>
			<line class="accent" x1="206" y1="16" x2="206" y2="32"/>
			<line class="accent" x1="198" y1="24" x2="214" y2="24"/>
			<line x1="228" y1="48" x2="228" y2="58"/>
			<line x1="223" y1="53" x2="233" y2="53"/>
			<rect x="54" y="84" width="166" height="42" rx="18"/>
			<line x1="40" y1="88" x2="40" y2="95"/>
			<circle class="accent" cx="40" cy="86" r="1.8"/>
			<rect x="28" y="95" width="24" height="20" rx="6"/>
			<circle class="accent" cx="35" cy="105" r="1.6"/><circle class="accent" cx="45" cy="105" r="1.6"/>
			<line x1="34" y1="111" x2="46" y2="111"/>
			<line x1="64" y1="99" x2="180" y2="99"/>
			<line x1="64" y1="111" x2="150" y2="111"/>',

		// SEO - checklist: kleine vinkjes naast tekstregels (sluit aan bij de
		// SEO-auditrapport-module).
		'seo' => '
			<rect x="16" y="16" width="228" height="118" rx="10"/>
			<path d="M33 40l3 4l8-8"/>
			<line x1="54" y1="40" x2="190" y2="40"/>
			<path class="accent" d="M33 64l3 4l8-8"/>
			<line class="accent" x1="54" y1="64" x2="210" y2="64"/>
			<path d="M33 88l3 4l8-8"/>
			<line x1="54" y1="88" x2="170" y2="88"/>
			<path d="M33 112l3 4l8-8"/>
			<line x1="54" y1="112" x2="200" y2="112"/>',

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

	// Alias: "Magento 2" moet dezelfde tekening tonen als "Magento".
	$map['magento-2'] = $map['magento'];

	$slug = irian_skill_slug( $label );
	if ( ! isset( $map[ $slug ] ) ) {
		return '';
	}
	return $open . preg_replace( '/\s+/', ' ', trim( $map[ $slug ] ) ) . $close;
}
