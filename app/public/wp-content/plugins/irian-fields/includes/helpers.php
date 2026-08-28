<?php
/**
 * Irian Fields: losse helpers.
 *
 * @package IrianFields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Genereer een stabiele, unieke veld-key.
 *
 * @return string bv. "field_9a1c2b7f4e"
 */
function irf_generate_key( $prefix = 'field_' ) {
	return $prefix . substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 10 );
}

/**
 * Maak van een label een geldige meta-key (veldnaam).
 */
function irf_sanitize_field_name( $name ) {
	$name = remove_accents( (string) $name );
	$name = strtolower( $name );
	$name = preg_replace( '/[^a-z0-9_]+/', '_', $name );
	$name = trim( $name, '_' );
	return $name;
}

/**
 * Parse een "waarde : Label"-lijst (één per regel) naar een assoc array.
 * Regels zonder ":" gebruiken de regel zelf als key én label.
 *
 * @return array<string,string>
 */
function irf_parse_choices( $raw ) {
	if ( is_array( $raw ) ) {
		return $raw; // al geparsed (opgeslagen als assoc array)
	}
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( false !== strpos( $line, ':' ) ) {
			list( $val, $label ) = array_map( 'trim', explode( ':', $line, 2 ) );
		} else {
			$val   = $line;
			$label = $line;
		}
		if ( '' !== $val ) {
			$out[ $val ] = $label;
		}
	}
	return $out;
}

/**
 * Zet een assoc choices-array terug om naar tekst voor de textarea.
 */
function irf_choices_to_text( $choices ) {
	if ( ! is_array( $choices ) || empty( $choices ) ) {
		return is_string( $choices ) ? $choices : '';
	}
	$lines = array();
	foreach ( $choices as $val => $label ) {
		if ( is_array( $label ) ) {
			$label = '';
		}
		$lines[] = ( (string) $val === (string) $label ) ? $val : "$val : $label";
	}
	return implode( "\n", $lines );
}

/**
 * Splits een tekstveld met één item per regel naar een schone array.
 */
function irf_lines_to_array( $raw ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
	$lines = array_map( 'trim', $lines );
	return array_values( array_filter( $lines, static fn( $l ) => '' !== $l ) );
}

/**
 * Veilige array-get, puntnotatie niet nodig: gewoon key + default.
 */
function irf_arr( $array, $key, $default = '' ) {
	return ( is_array( $array ) && array_key_exists( $key, $array ) ) ? $array[ $key ] : $default;
}
