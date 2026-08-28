<?php
/**
 * Irian Fields: de veldgroep-editor. Metaboxes op het `irf_group`-scherm om
 * velden te definiëren en locatie-regels in te stellen.
 *
 * Naamschema van de definitie-inputs (met __i__/__j__ placeholders die de JS
 * vervangt door de echte index vlak vóór submit):
 *   _irf_fields[__i__][label]
 *   _irf_fields[__i__][sub_fields][__j__][label]            (group / repeater)
 *   _irf_fields[__i__][layouts][__j__][sub_fields][__k__][label]   (flexible)
 *
 * @package IrianFields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IRF_Admin {

	protected static $instance;

	public static function instance() {
		return self::$instance ??= new self();
	}

	protected function __construct() {
		add_action( 'add_meta_boxes_' . IRF_Group::CPT, array( $this, 'meta_boxes' ) );
		add_action( 'save_post_' . IRF_Group::CPT, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'manage_' . IRF_Group::CPT . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . IRF_Group::CPT . '_posts_custom_column', array( $this, 'column' ), 10, 2 );
	}

	public function meta_boxes() {
		add_meta_box( 'irf_fields_box', 'Velden', array( $this, 'render_fields_box' ), IRF_Group::CPT, 'normal', 'high' );
		add_meta_box( 'irf_location_box', 'Locatie: waar verschijnt deze groep?', array( $this, 'render_location_box' ), IRF_Group::CPT, 'normal', 'default' );
		add_meta_box( 'irf_settings_box', 'Weergave', array( $this, 'render_settings_box' ), IRF_Group::CPT, 'side', 'default' );
	}

	/* =====================================================================
	 * VELDEN-BOX
	 * ===================================================================== */

	public function render_fields_box( $post ) {
		wp_nonce_field( 'irf_group_save', 'irf_group_nonce' );
		$fields = (array) get_post_meta( $post->ID, '_irf_fields', true );

		echo '<div class="irf-def" id="irf-def-root">';
		echo '<div class="irf-def-list" data-level="fields">';
		foreach ( array_values( $fields ) as $i => $field ) {
			$this->render_field_def( $field, "_irf_fields[$i]", 0 );
		}
		echo '</div>';
		echo '<button type="button" class="button button-primary irf-def-add" data-target="fields">+ Veld toevoegen</button>';
		echo '</div>';

		/*
		 * Platte templates (géén geneste <script>, die zou de browser bij de
		 * eerste </script> voortijdig sluiten). De JS vervangt __BASE__ door het
		 * juiste pad vlak vóór het invoegen.
		 */
		echo '<script type="text/html" id="irf-tpl-def">';
		$this->render_field_def( array(), '__BASE__', 0 );
		echo '</script>';

		echo '<script type="text/html" id="irf-tpl-subdef">';
		$this->render_field_def( array(), '__BASE__', 1 );
		echo '</script>';

		echo '<script type="text/html" id="irf-tpl-layout">';
		$this->render_layout_def( array(), '__BASE__' );
		echo '</script>';
	}

	/**
	 * Eén velddefinitie-rij. Geen <script> hierin; subvelden/layouts worden
	 * door de JS uit de platte templates opgebouwd.
	 *
	 * @param string $base  concreet pad ("_irf_fields[3]") of "__BASE__" in een template.
	 * @param int    $depth 0 = topniveau; 1 = subveld (geen verdere nesting).
	 */
	protected function render_field_def( $field, $base, $depth = 0 ) {
		$types = IRF_Fields::types();
		if ( $depth >= 1 ) {
			// Subvelden mogen zelf geen container zijn (één niveau diep).
			$types = array_diff_key( $types, array_flip( array( 'group', 'repeater', 'flexible_content' ) ) );
		}
		$type = irf_arr( $field, 'type', 'text' );

		echo '<div class="irf-def-row" data-depth="' . (int) $depth . '" data-base="' . esc_attr( $base ) . '">';
		echo '<div class="irf-def-row-head">';
		echo '<span class="irf-def-handle" title="Sleep">⠿</span>';
		echo '<span class="irf-def-summary">' . esc_html( irf_arr( $field, 'label', 'Nieuw veld' ) ) . '</span>';
		echo '<button type="button" class="button-link irf-def-toggle">tonen/verbergen</button>';
		echo '<button type="button" class="button-link irf-def-remove">verwijderen</button>';
		echo '</div>';

		echo '<div class="irf-def-row-body">';

		printf(
			'<input type="hidden" class="irf-def-key" name="%1$s[key]" value="%2$s">',
			esc_attr( $base ),
			esc_attr( irf_arr( $field, 'key' ) )
		);

		$this->def_text( $base, 'label', 'Label', irf_arr( $field, 'label' ), 'irf-def-label-src' );
		$this->def_text( $base, 'name', 'Veldnaam (meta key)', irf_arr( $field, 'name' ), 'irf-def-name-out' );

		echo '<label class="irf-def-field"><span>Type</span><select name="' . esc_attr( $base ) . '[type]" class="irf-def-type">';
		foreach ( $types as $val => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $type, $val, false ), esc_html( $label ) );
		}
		echo '</select></label>';

		$this->def_text( $base, 'instructions', 'Instructies', irf_arr( $field, 'instructions' ) );

		echo '<label class="irf-def-field irf-inline"><input type="hidden" name="' . esc_attr( $base ) . '[required]" value="0"><input type="checkbox" name="' . esc_attr( $base ) . '[required]" value="1" ' . checked( irf_arr( $field, 'required' ), 1, false ) . '> Verplicht</label>';

		echo '<div class="irf-def-extra" data-show-for="select,radio,checkbox">';
		$this->def_textarea( $base, 'choices', 'Keuzes (één per regel, "waarde : Label")', irf_choices_to_text( irf_arr( $field, 'choices' ) ) );
		echo '</div>';

		echo '<div class="irf-def-extra" data-show-for="textarea">';
		$this->def_text( $base, 'rows', 'Aantal regels', irf_arr( $field, 'rows', 4 ) );
		echo '</div>';

		echo '<div class="irf-def-extra" data-show-for="true_false">';
		$this->def_text( $base, 'message', 'Tekst naast de checkbox', irf_arr( $field, 'message' ) );
		echo '</div>';

		echo '<div class="irf-def-extra" data-show-for="repeater">';
		$this->def_text( $base, 'button_label', 'Knoptekst', irf_arr( $field, 'button_label', 'Rij toevoegen' ) );
		echo '</div>';

		if ( $depth < 1 ) {
			echo '<div class="irf-def-extra irf-def-subfields" data-show-for="group,repeater">';
			echo '<p class="irf-def-sublabel">Subvelden</p>';
			echo '<div class="irf-def-list" data-level="sub">';
			foreach ( array_values( (array) irf_arr( $field, 'sub_fields', array() ) ) as $j => $sf ) {
				$this->render_field_def( $sf, $base . "[sub_fields][$j]", 1 );
			}
			echo '</div>';
			echo '<button type="button" class="button irf-def-add" data-target="sub">+ Subveld</button>';
			echo '</div>';

			echo '<div class="irf-def-extra irf-def-layouts" data-show-for="flexible_content">';
			echo '<p class="irf-def-sublabel">Layouts (blokken)</p>';
			echo '<div class="irf-lay-list">';
			foreach ( array_values( (array) irf_arr( $field, 'layouts', array() ) ) as $j => $lay ) {
				$this->render_layout_def( $lay, $base . "[layouts][$j]" );
			}
			echo '</div>';
			echo '<button type="button" class="button irf-lay-add">+ Layout</button>';
			echo '</div>';
		}

		echo '</div></div>';
	}

	protected function render_layout_def( $lay, $base ) {
		echo '<div class="irf-lay-row" data-base="' . esc_attr( $base ) . '">';
		echo '<div class="irf-def-row-head"><span class="irf-def-handle">⠿</span><span class="irf-def-summary">' . esc_html( irf_arr( $lay, 'label', 'Layout' ) ) . '</span><button type="button" class="button-link irf-lay-remove">verwijderen</button></div>';
		echo '<div class="irf-lay-body">';
		$this->def_text( $base, 'label', 'Label', irf_arr( $lay, 'label' ), 'irf-def-label-src' );
		$this->def_text( $base, 'name', 'Naam (key)', irf_arr( $lay, 'name' ), 'irf-def-name-out' );
		echo '<p class="irf-def-sublabel">Subvelden van dit blok</p>';
		echo '<div class="irf-def-list" data-level="laysub">';
		foreach ( array_values( (array) irf_arr( $lay, 'sub_fields', array() ) ) as $k => $sf ) {
			$this->render_field_def( $sf, $base . "[sub_fields][$k]", 1 );
		}
		echo '</div>';
		echo '<button type="button" class="button irf-def-add" data-target="laysub">+ Subveld</button>';
		echo '</div></div>';
	}

	protected function def_text( $base, $key, $label, $value, $extra_class = '' ) {
		printf(
			'<label class="irf-def-field"><span>%s</span><input type="text" name="%s[%s]" value="%s" class="regular-text %s"></label>',
			esc_html( $label ),
			esc_attr( $base ),
			esc_attr( $key ),
			esc_attr( is_scalar( $value ) ? $value : '' ),
			esc_attr( $extra_class )
		);
	}

	protected function def_textarea( $base, $key, $label, $value ) {
		printf(
			'<label class="irf-def-field"><span>%s</span><textarea rows="4" name="%s[%s]" class="widefat">%s</textarea></label>',
			esc_html( $label ),
			esc_attr( $base ),
			esc_attr( $key ),
			esc_textarea( is_scalar( $value ) ? $value : '' )
		);
	}

	/* =====================================================================
	 * LOCATIE-BOX
	 * ===================================================================== */

	public function render_location_box( $post ) {
		$rules  = (array) get_post_meta( $post->ID, '_irf_location', true );
		$params = IRF_Group::location_params();

		echo '<p class="description">De groep verschijnt als <strong>alle</strong> regels hieronder kloppen.</p>';
		echo '<div class="irf-loc" id="irf-loc">';
		echo '<div class="irf-loc-list">';
		$rules = $rules ?: array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) );
		foreach ( array_values( $rules ) as $i => $rule ) {
			$this->render_loc_rule( $rule, $i, $params );
		}
		echo '</div>';

		echo '<script type="text/html" class="irf-loc-tpl">';
		$this->render_loc_rule( array(), '__i__', $params );
		echo '</script>';

		echo '<button type="button" class="button irf-loc-add">+ Regel</button>';
		echo '</div>';
	}

	protected function render_loc_rule( $rule, $i, $params ) {
		$param = irf_arr( $rule, 'param', 'post_type' );
		$op    = irf_arr( $rule, 'operator', '==' );
		$val   = irf_arr( $rule, 'value', '' );
		$b     = "_irf_location[$i]";

		echo '<div class="irf-loc-rule">';

		echo '<select data-tpl="' . esc_attr( $b ) . '[param]" name="' . esc_attr( $b ) . '[param]" class="irf-loc-param">';
		foreach ( $params as $pk => $pl ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $pk ), selected( $param, $pk, false ), esc_html( $pl ) );
		}
		echo '</select>';

		echo '<select data-tpl="' . esc_attr( $b ) . '[operator]" name="' . esc_attr( $b ) . '[operator]">';
		printf( '<option value="==" %s>is gelijk aan</option>', selected( $op, '==', false ) );
		printf( '<option value="!=" %s>is niet</option>', selected( $op, '!=', false ) );
		echo '</select>';

		$options = IRF_Group::location_value_options( $param );
		echo '<span class="irf-loc-value-wrap">';
		if ( $options ) {
			echo '<select data-tpl="' . esc_attr( $b ) . '[value]" name="' . esc_attr( $b ) . '[value]" class="irf-loc-value">';
			foreach ( $options as $ok => $ol ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $ok ), selected( (string) $val, (string) $ok, false ), esc_html( $ol ) );
			}
			echo '</select>';
		} else {
			printf( '<input type="text" data-tpl="%1$s[value]" name="%1$s[value]" value="%2$s" class="irf-loc-value" placeholder="ID">', esc_attr( $b ), esc_attr( $val ) );
		}
		echo '</span>';

		echo '<button type="button" class="button-link irf-loc-remove">×</button>';
		echo '</div>';
	}

	/* =====================================================================
	 * WEERGAVE-BOX
	 * ===================================================================== */

	public function render_settings_box( $post ) {
		$s = (array) get_post_meta( $post->ID, '_irf_settings', true );
		echo '<label class="irf-def-field"><span>Positie</span><select name="_irf_settings[position]">';
		printf( '<option value="normal" %s>Normaal (onder de inhoud)</option>', selected( irf_arr( $s, 'position', 'normal' ), 'normal', false ) );
		printf( '<option value="side" %s>Zijbalk</option>', selected( irf_arr( $s, 'position' ), 'side', false ) );
		echo '</select></label>';

		echo '<label class="irf-def-field"><span>Prioriteit</span><select name="_irf_settings[priority]">';
		printf( '<option value="default" %s>Normaal</option>', selected( irf_arr( $s, 'priority', 'default' ), 'default', false ) );
		printf( '<option value="high" %s>Hoog (bovenaan)</option>', selected( irf_arr( $s, 'priority' ), 'high', false ) );
		echo '</select></label>';

		echo '<p class="description">Menu-volgorde bepaal je met het veld <em>Volgorde</em> onder Pagina-attributen.</p>';
	}

	/* =====================================================================
	 * OPSLAAN
	 * ===================================================================== */

	public function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( empty( $_POST['irf_group_nonce'] ) || ! wp_verify_nonce( $_POST['irf_group_nonce'], 'irf_group_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw_fields = isset( $_POST['_irf_fields'] ) ? wp_unslash( $_POST['_irf_fields'] ) : array();
		update_post_meta( $post_id, '_irf_fields', $this->clean_fields( $raw_fields ) );

		$raw_loc = isset( $_POST['_irf_location'] ) ? wp_unslash( $_POST['_irf_location'] ) : array();
		update_post_meta( $post_id, '_irf_location', $this->clean_location( $raw_loc ) );

		$raw_s = isset( $_POST['_irf_settings'] ) ? wp_unslash( $_POST['_irf_settings'] ) : array();
		update_post_meta(
			$post_id,
			'_irf_settings',
			array(
				'position' => ( 'side' === irf_arr( $raw_s, 'position' ) ) ? 'side' : 'normal',
				'priority' => ( 'high' === irf_arr( $raw_s, 'priority' ) ) ? 'high' : 'default',
			)
		);

		IRF_Group::instance(); // cache wordt bij volgende request opnieuw gebouwd
	}

	protected function clean_fields( $raw ) {
		$out = array();
		foreach ( (array) $raw as $f ) {
			if ( ! is_array( $f ) ) {
				continue;
			}
			$type  = array_key_exists( $f['type'] ?? '', IRF_Fields::types() ) ? $f['type'] : 'text';
			$label = sanitize_text_field( irf_arr( $f, 'label' ) );
			$name  = irf_sanitize_field_name( irf_arr( $f, 'name' ) ?: $label );

			if ( '' === $label && IRF_Fields::is_presentational( $type ) === false && '' === $name ) {
				continue;
			}

			$clean = array(
				'key'          => sanitize_text_field( irf_arr( $f, 'key' ) ?: irf_generate_key() ),
				'label'        => $label,
				'name'         => $name,
				'type'         => $type,
				'instructions' => sanitize_text_field( irf_arr( $f, 'instructions' ) ),
				'required'     => ( '1' === (string) irf_arr( $f, 'required' ) ) ? 1 : 0,
			);

			if ( in_array( $type, array( 'select', 'radio', 'checkbox' ), true ) ) {
				$clean['choices'] = irf_parse_choices( irf_arr( $f, 'choices' ) );
			}
			if ( 'textarea' === $type ) {
				$clean['rows'] = max( 2, absint( irf_arr( $f, 'rows', 4 ) ) );
			}
			if ( 'true_false' === $type ) {
				$clean['message'] = sanitize_text_field( irf_arr( $f, 'message' ) );
			}
			if ( 'repeater' === $type ) {
				$clean['button_label'] = sanitize_text_field( irf_arr( $f, 'button_label' ) ?: 'Rij toevoegen' );
			}
			if ( in_array( $type, array( 'group', 'repeater' ), true ) ) {
				$clean['sub_fields'] = $this->clean_fields( irf_arr( $f, 'sub_fields', array() ) );
			}
			if ( 'flexible_content' === $type ) {
				$clean['layouts'] = array();
				foreach ( (array) irf_arr( $f, 'layouts', array() ) as $lay ) {
					$llabel = sanitize_text_field( irf_arr( $lay, 'label' ) );
					$lname  = irf_sanitize_field_name( irf_arr( $lay, 'name' ) ?: $llabel );
					if ( '' === $lname ) {
						continue;
					}
					$clean['layouts'][] = array(
						'label'      => $llabel ?: $lname,
						'name'       => $lname,
						'sub_fields' => $this->clean_fields( irf_arr( $lay, 'sub_fields', array() ) ),
					);
				}
			}

			$out[] = $clean;
		}
		return $out;
	}

	protected function clean_location( $raw ) {
		$out = array();
		foreach ( (array) $raw as $r ) {
			if ( ! is_array( $r ) || empty( $r['param'] ) ) {
				continue;
			}
			if ( ! array_key_exists( $r['param'], IRF_Group::location_params() ) ) {
				continue;
			}
			$out[] = array(
				'param'    => sanitize_key( $r['param'] ),
				'operator' => ( '!=' === irf_arr( $r, 'operator' ) ) ? '!=' : '==',
				'value'    => sanitize_text_field( irf_arr( $r, 'value' ) ),
			);
		}
		return $out;
	}

	/* =====================================================================
	 * LIJST-KOLOM + ASSETS
	 * ===================================================================== */

	public function columns( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['irf_location'] = 'Locatie';
				$new['irf_count']    = 'Velden';
			}
		}
		return $new;
	}

	public function column( $col, $post_id ) {
		if ( 'irf_count' === $col ) {
			echo (int) count( (array) get_post_meta( $post_id, '_irf_fields', true ) );
		}
		if ( 'irf_location' === $col ) {
			$rules  = (array) get_post_meta( $post_id, '_irf_location', true );
			$params = IRF_Group::location_params();
			$bits   = array();
			foreach ( $rules as $r ) {
				$bits[] = esc_html( ( $params[ $r['param'] ] ?? $r['param'] ) . ' ' . $r['operator'] . ' ' . $r['value'] );
			}
			echo $bits ? implode( '<br>', $bits ) : '-';
		}
	}

	public function enqueue( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || IRF_Group::CPT !== $screen->post_type ) {
			return;
		}
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		wp_enqueue_style( 'irf-group-admin', IRF_URL . 'assets/group-admin.css', array(), IRF_VERSION );
		wp_enqueue_script( 'irf-group-admin', IRF_URL . 'assets/group-admin.js', array( 'jquery', 'jquery-ui-sortable' ), IRF_VERSION, true );
	}
}
