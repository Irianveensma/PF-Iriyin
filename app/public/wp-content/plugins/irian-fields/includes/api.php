<?php
/**
 * Irian Fields: template-API. Losjes gemodelleerd naar ACF, met prefix `irf_`.
 *
 *   irf_get_field( 'subtitel' );
 *   the: echo esc_html( irf_get_field( 'subtitel' ) );
 *
 *   if ( irf_have_rows( 'slides' ) ) {
 *       while ( irf_have_rows( 'slides' ) ) { irf_the_row();
 *           echo esc_html( irf_get_sub_field( 'heading' ) );
 *       }
 *   }
 *
 *   Flexible content:
 *   foreach ( irf_get_field( 'blokken' ) as $blok ) {
 *       if ( 'tekst' === $blok['__layout'] ) { ... $blok['body'] ... }
 *   }
 *
 * @package IrianFields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interne rij-stack voor irf_have_rows()/irf_the_row().
 */
global $irf_row_stack;
$irf_row_stack = array();

/**
 * Haal een topniveau-veldwaarde op voor een post.
 *
 * @param string          $name
 * @param int|WP_Post|null $post_id  Standaard: huidige post.
 */
function irf_get_field( $name, $post_id = null ) {
	$post_id = $post_id ? ( $post_id instanceof WP_Post ? $post_id->ID : (int) $post_id ) : get_the_ID();
	if ( ! $post_id ) {
		return null;
	}

	$index = IRF_Group::instance()->field_index();
	if ( ! isset( $index[ $name ] ) ) {
		return null;
	}

	$values = (array) get_post_meta( $post_id, '_irf_values_' . $index[ $name ], true );
	return $values[ $name ] ?? null;
}

/**
 * Echo-variant met standaard-escaping. Voor rijke HTML (wysiwyg) gebruik je
 * beter irf_get_field() + eigen output.
 */
function irf_the_field( $name, $post_id = null ) {
	$v = irf_get_field( $name, $post_id );
	if ( is_scalar( $v ) ) {
		echo esc_html( $v );
	}
}

/**
 * Alle waardes van één groep (of van elke groep) voor een post.
 */
function irf_get_fields( $post_id = null ) {
	$post_id = $post_id ? ( $post_id instanceof WP_Post ? $post_id->ID : (int) $post_id ) : get_the_ID();
	$out     = array();
	foreach ( IRF_Group::instance()->all() as $group ) {
		$out += (array) get_post_meta( $post_id, '_irf_values_' . $group['id'], true );
	}
	return $out;
}

/* ---------------------------------------------------------------------------
 * Repeater-loop
 * ------------------------------------------------------------------------- */

/**
 * @param string $selector  Topniveau: veldnaam. Genest: gebruik binnen een
 *                           lopende rij gewoon de subveldnaam.
 */
function irf_have_rows( $selector, $post_id = null ) {
	global $irf_row_stack;

	$top = end( $irf_row_stack );

	if ( $top && $top['selector'] === $selector ) {
		// Bestaande loop: is er nog een rij?
		if ( $top['i'] + 1 < count( $top['rows'] ) ) {
			return true;
		}
		array_pop( $irf_row_stack );
		return false;
	}

	// Nieuwe loop opzetten.
	if ( $top ) {
		$rows = $top['rows'][ $top['i'] ][ $selector ] ?? null;
	} else {
		$rows = irf_get_field( $selector, $post_id );
	}

	if ( ! is_array( $rows ) || empty( $rows ) ) {
		return false;
	}

	$irf_row_stack[] = array(
		'selector' => $selector,
		'rows'     => array_values( $rows ),
		'i'        => -1,
	);
	return true;
}

function irf_the_row() {
	global $irf_row_stack;
	$idx = count( $irf_row_stack ) - 1;
	if ( $idx < 0 ) {
		return array();
	}
	$irf_row_stack[ $idx ]['i']++;
	return irf_get_row();
}

function irf_get_row() {
	global $irf_row_stack;
	$top = end( $irf_row_stack );
	if ( ! $top || $top['i'] < 0 ) {
		return array();
	}
	return $top['rows'][ $top['i'] ];
}

/**
 * Huidige layout-naam binnen een flexible-content loop.
 */
function irf_get_row_layout() {
	$row = irf_get_row();
	return $row['__layout'] ?? '';
}

function irf_get_sub_field( $name ) {
	$row = irf_get_row();
	return $row[ $name ] ?? null;
}

function irf_the_sub_field( $name ) {
	$v = irf_get_sub_field( $name );
	if ( is_scalar( $v ) ) {
		echo esc_html( $v );
	}
}

/* ---------------------------------------------------------------------------
 * Handige "opgemaakte waarde"-helpers
 * ------------------------------------------------------------------------- */

/**
 * Image-veld => <img> tag (of '' als leeg).
 */
function irf_get_image( $value, $size = 'large', $attr = array() ) {
	$id = is_array( $value ) ? ( $value['id'] ?? 0 ) : (int) $value;
	return $id ? wp_get_attachment_image( $id, $size, false, $attr ) : '';
}

function irf_get_image_url( $value, $size = 'large' ) {
	$id = is_array( $value ) ? ( $value['id'] ?? 0 ) : (int) $value;
	return $id ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

/**
 * Link-veld => <a> tag.
 */
function irf_get_link( $value, $fallback_text = '' ) {
	if ( ! is_array( $value ) || empty( $value['url'] ) ) {
		return '';
	}
	return sprintf(
		'<a href="%s"%s>%s</a>',
		esc_url( $value['url'] ),
		( '_blank' === ( $value['target'] ?? '' ) ) ? ' target="_blank" rel="noopener"' : '',
		esc_html( $value['text'] ?: ( $fallback_text ?: $value['url'] ) )
	);
}
