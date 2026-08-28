<?php
/**
 * Plugin Name:       Irian Fields
 * Plugin URI:        https://irianveensma.nl
 * Description:        Eigen, gratis custom-fields systeem: herbruikbare veldgroepen die je per posttype/pagina/sjabloon toewijst. Vervangt ACF voor de dingen die in de gratis ACF achter PRO zaten (repeater, flexible content, opties). Beheer via Weergave, menu "Irian Fields".
 * Version:           0.1.0
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            Irian Veensma
 * Text Domain:       irian-fields
 *
 * ---------------------------------------------------------------------------
 * OPZET (kort)
 * ---------------------------------------------------------------------------
 * - Een "veldgroep" is een custom post type `irf_group`.
 *     • post_title              = label van de groep
 *     • post meta `_irf_fields`   = array met velddefinities
 *     • post meta `_irf_location` = array met locatie-regels (allemaal waar = tonen)
 *     • post meta `_irf_settings` = plek (normal/side), stijl, menu-volgorde
 * - Op elk bewerkscherm dat aan de locatie-regels voldoet, rendert de plugin
 *   één meta box per groep. Opslaan gaat naar post meta
 *   `_irf_values_{group_id}` als geneste array.
 * - Front-end / templates: gebruik de `irf_*` helpers uit includes/api.php
 *   (vergelijkbaar met ACF: irf_get_field, irf_have_rows, irf_get_sub_field, …).
 *
 * Alle PHP-bestanden zijn los en klein gehouden, zodat ze via de wp-admin
 * Plugin File Editor te bewerken zijn. Op deze Local-omgeving zorgt de
 * mu-plugin `irian-local-editor-fix.php` ervoor dat die edits blijven staan.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IRF_VERSION', '0.1.0' );
define( 'IRF_FILE', __FILE__ );
define( 'IRF_DIR', plugin_dir_path( __FILE__ ) );
define( 'IRF_URL', plugin_dir_url( __FILE__ ) );

require_once IRF_DIR . 'includes/helpers.php';
require_once IRF_DIR . 'includes/class-irf-fields.php';
require_once IRF_DIR . 'includes/class-irf-group.php';
require_once IRF_DIR . 'includes/class-irf-admin.php';
require_once IRF_DIR . 'includes/class-irf-render.php';
require_once IRF_DIR . 'includes/api.php';

/**
 * Kick-off.
 */
function irf_boot() {
	IRF_Group::instance();
	IRF_Admin::instance();
	IRF_Render::instance();
}
add_action( 'plugins_loaded', 'irf_boot' );

/**
 * Zorg dat de rewrite-regels van het CPT kloppen na (de)activatie.
 */
function irf_activate() {
	IRF_Group::register_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'irf_activate' );

function irf_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'irf_deactivate' );
