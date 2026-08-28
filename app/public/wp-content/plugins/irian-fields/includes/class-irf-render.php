<?php
/**
 * Irian Fields: toon de veldgroepen als meta boxes op de bijpassende
 * bewerkschermen en sla de waardes op.
 *
 * Waardes staan per groep in post meta `_irf_values_{group_id}` (geneste array).
 * HTML-namen: irf_g[{group_id}][veldnaam]…  (per groep genamespaced tegen botsingen)
 *
 * @package IrianFields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IRF_Render {

	protected static $instance;

	public static function instance() {
		return self::$instance ??= new self();
	}

	protected function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function add_meta_boxes( $post_type, $post ) {
		if ( ! $post instanceof WP_Post ) {
			return;
		}
		foreach ( IRF_Group::instance()->for_post( $post ) as $group ) {
			$context  = ( 'side' === irf_arr( $group['settings'], 'position' ) ) ? 'side' : 'normal';
			$priority = ( 'high' === irf_arr( $group['settings'], 'priority' ) ) ? 'high' : 'default';

			add_meta_box(
				'irf_group_' . $group['id'],
				$group['title'] ?: 'Velden',
				array( $this, 'render_meta_box' ),
				$post_type,
				$context,
				$priority,
				array( 'group' => $group )
			);
		}
	}

	public function render_meta_box( $post, $box ) {
		$group  = $box['args']['group'];
		$gid    = $group['id'];
		$values = (array) get_post_meta( $post->ID, '_irf_values_' . $gid, true );

		wp_nonce_field( 'irf_save_' . $gid, 'irf_nonce_' . $gid );

		echo '<div class="irf-metabox">';
		foreach ( (array) $group['fields'] as $field ) {
			if ( IRF_Fields::is_presentational( irf_arr( $field, 'type' ) ) ) {
				IRF_Fields::render_input( $field, '', null );
				continue;
			}
			$name = 'irf_g[' . $gid . '][' . irf_arr( $field, 'name' ) . ']';
			IRF_Fields::render_input( $field, $name, irf_arr( $values, irf_arr( $field, 'name' ), null ) );
		}
		echo '</div>';
	}

	public function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$submitted = isset( $_POST['irf_g'] ) && is_array( $_POST['irf_g'] ) ? wp_unslash( $_POST['irf_g'] ) : array();

		foreach ( IRF_Group::instance()->for_post( $post ) as $group ) {
			$gid = $group['id'];

			// Alleen opslaan als de nonce van déze groep is meegestuurd
			// (voorkomt wissen bij quick-edit / REST / andere save-paden).
			if ( empty( $_POST[ 'irf_nonce_' . $gid ] ) || ! wp_verify_nonce( $_POST[ 'irf_nonce_' . $gid ], 'irf_save_' . $gid ) ) {
				continue;
			}

			$raw   = isset( $submitted[ $gid ] ) && is_array( $submitted[ $gid ] ) ? $submitted[ $gid ] : array();
			$clean = array();

			foreach ( (array) $group['fields'] as $field ) {
				if ( IRF_Fields::is_presentational( irf_arr( $field, 'type' ) ) ) {
					continue;
				}
				$fname           = irf_arr( $field, 'name' );
				$clean[ $fname ] = IRF_Fields::sanitize( $field, irf_arr( $raw, $fname, null ) );
			}

			update_post_meta( $post_id, '_irf_values_' . $gid, $clean );
		}
	}

	public function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_editor();
		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_style( 'irf-admin', IRF_URL . 'assets/admin.css', array(), IRF_VERSION );
		wp_enqueue_script( 'irf-admin', IRF_URL . 'assets/admin.js', array( 'jquery', 'jquery-ui-sortable' ), IRF_VERSION, true );
	}
}
