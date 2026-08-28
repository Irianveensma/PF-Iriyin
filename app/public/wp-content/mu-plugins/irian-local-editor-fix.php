<?php
/**
 * Plugin Name: Irian Local editor fix
 * Description: Laat de wp-admin Plugin/Theme File Editor PHP-wijzigingen bewaren op deze Local-omgeving. Na een edit doet WordPress een "loopback" HTTP-request naar zichzelf om te checken of de site nog werkt; op Local (maar 2 PHP-workers) loopt die vaak vast, waarna WP de wijziging terugdraait met "Unable to communicate back with site to check for fatal errors". Deze mu-plugin beantwoordt die interne scrape-request zelf.
 * Author: Irian Veensma
 *
 * Draait ALLEEN als wp_get_environment_type() === 'local'. Op productie doet dit
 * bestand niets (en het hoort daar ook niet mee te gaan).
 *
 * Kanttekening: hiermee vervalt de automatische "white screen"-bescherming van
 * de file editor. Schrijf je een PHP fatal error, dan wordt het bestand NIET
 * teruggedraaid; je ziet de fout en past 'm zelf aan. Op dev is dat prima.
 *
 * Debuggen: definieer IRF_EDITOR_FIX_DEBUG = true in wp-config.php om te loggen
 * (vereist WP_DEBUG_LOG).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'wp_get_environment_type' ) && 'local' !== wp_get_environment_type() ) {
	return;
}

/**
 * Vang de interne loopback-scrape af en geef meteen het "alles ok"-antwoord
 * terug dat wp_edit_theme_plugin_file() verwacht, zonder tweede PHP-worker.
 */
add_filter(
	'pre_http_request',
	function ( $pre, $args, $url ) {
		if ( empty( $url ) || false === strpos( (string) $url, 'wp_scrape_key' ) ) {
			return $pre;
		}

		$parts = wp_parse_url( $url );
		if ( empty( $parts['query'] ) ) {
			return $pre;
		}
		parse_str( $parts['query'], $q );
		if ( empty( $q['wp_scrape_key'] ) || empty( $q['wp_scrape_nonce'] ) ) {
			return $pre;
		}

		$key  = preg_replace( '/[^a-f0-9]/', '', (string) $q['wp_scrape_key'] );
		$body = "\n###### wp_scraping_result_start:$key ######\n"
			. wp_json_encode( true )
			. "\n###### wp_scraping_result_end:$key ######\n";

		if ( defined( 'IRF_EDITOR_FIX_DEBUG' ) && IRF_EDITOR_FIX_DEBUG ) {
			error_log( 'irf-editor-fix: loopback-scrape beantwoord voor key ' . $key );
		}

		return array(
			'headers'       => array(),
			'body'          => $body,
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
			'filename'      => null,
			'http_response' => null,
		);
	},
	10,
	3
);
