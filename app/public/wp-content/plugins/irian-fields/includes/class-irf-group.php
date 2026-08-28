<?php
/**
 * Irian Fields: de "veldgroep". Custom post type, inlezen van definities, en
 * bepalen of een groep op een bepaald scherm/post hoort te verschijnen.
 *
 * @package IrianFields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IRF_Group {

	const CPT = 'irf_group';

	protected static $instance;
	protected $cache = array();

	public static function instance() {
		return self::$instance ??= new self();
	}

	protected function __construct() {
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'cleanup_values_on_delete' ) );
	}

	/**
	 * Verwijder je een veldgroep, ruim dan de opgeslagen waardes van die groep
	 * op alle posts op (anders blijven er wees-meta-rijen achter).
	 */
	public static function cleanup_values_on_delete( $post_id ) {
		if ( self::CPT !== get_post_type( $post_id ) ) {
			return;
		}
		global $wpdb;
		$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_irf_values_' . (int) $post_id ) );
	}

	public static function register_cpt() {
		register_post_type(
			self::CPT,
			array(
				'labels'          => array(
					'name'          => 'Veldgroepen',
					'singular_name' => 'Veldgroep',
					'add_new'       => 'Nieuwe veldgroep',
					'add_new_item'  => 'Nieuwe veldgroep',
					'edit_item'     => 'Veldgroep bewerken',
					'menu_name'     => 'Irian Fields',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-list-view',
				'menu_position'   => 58,
				'capability_type' => 'page',
				'map_meta_cap'    => true,
				'hierarchical'    => false,
				'supports'        => array( 'title' ),
				'show_in_rest'    => false,
			)
		);
	}

	/* =====================================================================
	 * DEFINITIES INLEZEN
	 * ===================================================================== */

	/**
	 * Alle gepubliceerde veldgroepen, elk als:
	 *   [ 'id'=>int, 'title'=>string, 'fields'=>array, 'location'=>array, 'settings'=>array ]
	 */
	public function all() {
		if ( ! empty( $this->cache ) ) {
			return $this->cache;
		}

		$posts = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => 'publish',
				'numberposts'    => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'suppress_filters' => true,
			)
		);

		foreach ( $posts as $p ) {
			$this->cache[ $p->ID ] = array(
				'id'       => $p->ID,
				'title'    => $p->post_title,
				'fields'   => (array) get_post_meta( $p->ID, '_irf_fields', true ),
				'location' => (array) get_post_meta( $p->ID, '_irf_location', true ),
				'settings' => (array) get_post_meta( $p->ID, '_irf_settings', true ),
			);
		}

		return $this->cache;
	}

	public function get( $group_id ) {
		$all = $this->all();
		return $all[ $group_id ] ?? null;
	}

	/**
	 * Welke groepen horen bij deze post? (locatie-regels, allemaal waar)
	 *
	 * @param WP_Post|int $post
	 * @return array lijst van groep-arrays
	 */
	public function for_post( $post ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return array();
		}
		$out = array();
		foreach ( $this->all() as $group ) {
			if ( self::location_matches( $group['location'], $post ) ) {
				$out[] = $group;
			}
		}
		return $out;
	}

	/**
	 * Bouw een index veldnaam => groep-id, zodat de template-API snel weet
	 * welke groep een veld bezit. (Laatste groep wint bij dubbele namen.)
	 */
	public function field_index() {
		$index = array();
		foreach ( $this->all() as $group ) {
			foreach ( self::collect_top_names( $group['fields'] ) as $name ) {
				$index[ $name ] = $group['id'];
			}
		}
		return $index;
	}

	protected static function collect_top_names( $fields ) {
		$names = array();
		foreach ( (array) $fields as $f ) {
			if ( IRF_Fields::is_presentational( irf_arr( $f, 'type' ) ) ) {
				continue;
			}
			if ( '' !== (string) irf_arr( $f, 'name' ) ) {
				$names[] = $f['name'];
			}
		}
		return $names;
	}

	/* =====================================================================
	 * LOCATIE-REGELS
	 * ===================================================================== */

	/**
	 * Beschikbare parameters voor locatie-regels => label.
	 */
	public static function location_params() {
		return array(
			'post_type'     => 'Posttype',
			'page_template' => 'Paginasjabloon',
			'post'          => 'Specifieke post/pagina (ID)',
			'post_status'   => 'Poststatus',
		);
	}

	/**
	 * @param array   $rules  lijst van [ 'param'=>, 'operator'=>('=='|'!='), 'value'=> ]
	 * @param WP_Post $post
	 */
	public static function location_matches( $rules, $post ) {
		$rules = array_filter( (array) $rules, static fn( $r ) => is_array( $r ) && ! empty( $r['param'] ) );
		if ( empty( $rules ) ) {
			return false; // geen regels = nergens tonen (voorkomt "overal" per ongeluk)
		}

		foreach ( $rules as $rule ) {
			$op    = ( '!=' === irf_arr( $rule, 'operator' ) ) ? '!=' : '==';
			$value = (string) irf_arr( $rule, 'value' );
			$fact  = self::location_fact( $rule['param'], $post );

			$hit = is_array( $fact ) ? in_array( $value, array_map( 'strval', $fact ), true ) : ( (string) $fact === $value );

			if ( '==' === $op && ! $hit ) {
				return false;
			}
			if ( '!=' === $op && $hit ) {
				return false;
			}
		}
		return true;
	}

	protected static function location_fact( $param, $post ) {
		switch ( $param ) {
			case 'post_type':
				return $post->post_type;
			case 'post':
				return (string) $post->ID;
			case 'post_status':
				return $post->post_status;
			case 'page_template':
				$tpl = get_page_template_slug( $post->ID );
				return $tpl ?: 'default';
			default:
				return '';
		}
	}

	/**
	 * Mogelijke waardes voor een param (voor de dropdown in de editor).
	 */
	public static function location_value_options( $param ) {
		switch ( $param ) {
			case 'post_type':
				$out = array();
				foreach ( get_post_types( array( 'show_ui' => true ), 'objects' ) as $pt ) {
					if ( self::CPT === $pt->name ) {
						continue;
					}
					$out[ $pt->name ] = $pt->label;
				}
				return $out;

			case 'page_template':
				$out = array( 'default' => 'Standaardsjabloon' );
				foreach ( (array) wp_get_theme()->get_page_templates( null, 'page' ) as $file => $label ) {
					$out[ $file ] = $label;
				}
				return $out;

			case 'post_status':
				return array(
					'publish' => 'Gepubliceerd',
					'draft'   => 'Concept',
					'private' => 'Privé',
					'pending' => 'Wacht op review',
				);

			default:
				return array(); // 'post' => vrij ID-tekstveld
		}
	}
}
