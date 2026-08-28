<?php
/**
 * Irian Fields: veld-typen. Welke er zijn, hoe je hun waarde-invoer rendert,
 * en hoe je een ingezonden waarde opschoont.
 *
 * De velddefinitie-editor (labels/namen/instellingen per veld) zit in
 * class-irf-admin.php; dit bestand gaat puur over de waardes op een post.
 *
 * @package IrianFields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IRF_Fields {

	/**
	 * Alle beschikbare veldtypen => label (voor de dropdown in de groep-editor).
	 */
	public static function types() {
		return array(
			'tab'              => '- Tab / sectie -',
			'message'          => 'Bericht (alleen tekst)',
			'text'             => 'Tekst',
			'textarea'         => 'Tekstvak',
			'number'           => 'Getal',
			'email'            => 'E-mail',
			'url'              => 'URL',
			'wysiwyg'          => 'WYSIWYG-editor',
			'image'            => 'Afbeelding',
			'file'             => 'Bestand',
			'select'           => 'Keuzelijst',
			'checkbox'         => 'Checkboxes (meerdere)',
			'radio'            => 'Radio (één)',
			'true_false'       => 'Ja / nee',
			'color'            => 'Kleur',
			'date'             => 'Datum',
			'link'             => 'Link (url + tekst)',
			'group'            => 'Groep (subvelden, enkel)',
			'repeater'         => 'Repeater (rijen subvelden)',
			'flexible_content' => 'Flexible content (blokken)',
		);
	}

	/**
	 * Containers bevatten subvelden en renderen recursief.
	 */
	public static function is_container( $type ) {
		return in_array( $type, array( 'group', 'repeater', 'flexible_content' ), true );
	}

	/**
	 * Typen zonder opgeslagen waarde.
	 */
	public static function is_presentational( $type ) {
		return in_array( $type, array( 'tab', 'message' ), true );
	}

	/* =====================================================================
	 * WAARDE-INVOER RENDEREN
	 * ===================================================================== */

	/**
	 * @param array  $field  Velddefinitie.
	 * @param string $name   Volledige HTML name, bv. "irf[titel]" of
	 *                       "irf[slides][__i__][heading]" in een JS-template.
	 * @param mixed  $value  Huidige waarde.
	 */
	public static function render_input( array $field, $name, $value ) {
		$type = irf_arr( $field, 'type', 'text' );
		$id   = 'irf-' . md5( $name );

		if ( self::is_presentational( $type ) ) {
			self::render_presentational( $field );
			return;
		}

		echo '<div class="irf-field irf-field--' . esc_attr( $type ) . '" data-type="' . esc_attr( $type ) . '">';

		if ( self::is_container( $type ) ) {
			printf(
				'<p class="irf-field__label irf-field__label--container">%s%s</p>',
				esc_html( irf_arr( $field, 'label', irf_arr( $field, 'name' ) ) ),
				irf_arr( $field, 'required' ) ? ' <span class="irf-req">*</span>' : ''
			);
		} else {
			printf(
				'<label class="irf-field__label" for="%s">%s%s</label>',
				esc_attr( $id ),
				esc_html( irf_arr( $field, 'label', irf_arr( $field, 'name' ) ) ),
				irf_arr( $field, 'required' ) ? ' <span class="irf-req">*</span>' : ''
			);
		}
		if ( '' !== (string) irf_arr( $field, 'instructions' ) ) {
			echo '<p class="irf-field__instructions">' . esc_html( $field['instructions'] ) . '</p>';
		}

		switch ( $type ) {

			case 'text':
			case 'email':
			case 'url':
			case 'number':
			case 'date':
			case 'color':
				$input_type = array(
					'text'   => 'text',
					'email'  => 'email',
					'url'    => 'url',
					'number' => 'number',
					'date'   => 'date',
					'color'  => 'color',
				)[ $type ];
				printf(
					'<input type="%s" id="%s" name="%s" value="%s" class="irf-input widefat"%s>',
					esc_attr( $input_type ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( is_scalar( $value ) ? $value : '' ),
					irf_arr( $field, 'required' ) ? ' required' : ''
				);
				break;

			case 'textarea':
				printf(
					'<textarea id="%s" name="%s" rows="%d" class="irf-input widefat">%s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					(int) ( irf_arr( $field, 'rows', 4 ) ?: 4 ),
					esc_textarea( is_scalar( $value ) ? $value : '' )
				);
				break;

			case 'wysiwyg':
				printf(
					'<textarea id="%s" name="%s" rows="8" class="irf-input widefat irf-wysiwyg">%s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_textarea( is_scalar( $value ) ? $value : '' )
				);
				break;

			case 'true_false':
				printf(
					'<label class="irf-toggle"><input type="hidden" name="%1$s" value="0"><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>',
					esc_attr( $name ),
					checked( (string) $value, '1', false ),
					esc_html( irf_arr( $field, 'message', 'Ja' ) )
				);
				break;

			case 'select':
			case 'radio':
			case 'checkbox':
				self::render_choice_input( $type, $field, $name, $value, $id );
				break;

			case 'image':
			case 'file':
				self::render_media_input( $type, $name, $value, $id );
				break;

			case 'link':
				$value = is_array( $value ) ? $value : array();
				printf(
					'<div class="irf-link"><input type="url" name="%1$s[url]" value="%2$s" placeholder="https://…" class="irf-input widefat">'
					. '<input type="text" name="%1$s[text]" value="%3$s" placeholder="Linktekst" class="irf-input widefat">'
					. '<label class="irf-inline"><input type="hidden" name="%1$s[target]" value="0"><input type="checkbox" name="%1$s[target]" value="_blank" %4$s> In nieuw tabblad</label></div>',
					esc_attr( $name ),
					esc_attr( irf_arr( $value, 'url' ) ),
					esc_attr( irf_arr( $value, 'text' ) ),
					checked( irf_arr( $value, 'target' ), '_blank', false )
				);
				break;

			case 'group':
				self::render_subfields( irf_arr( $field, 'sub_fields', array() ), $name, is_array( $value ) ? $value : array() );
				break;

			case 'repeater':
				self::render_repeater( $field, $name, is_array( $value ) ? $value : array() );
				break;

			case 'flexible_content':
				self::render_flexible( $field, $name, is_array( $value ) ? $value : array() );
				break;
		}

		echo '</div>';
	}

	protected static function render_presentational( array $field ) {
		$type = irf_arr( $field, 'type' );
		if ( 'tab' === $type ) {
			echo '<h3 class="irf-tab-sep">' . esc_html( irf_arr( $field, 'label' ) ) . '</h3>';
		} else {
			echo '<div class="irf-message">';
			echo wp_kses_post( wpautop( irf_arr( $field, 'instructions', irf_arr( $field, 'label' ) ) ) );
			echo '</div>';
		}
	}

	protected static function render_choice_input( $type, $field, $name, $value, $id ) {
		$choices  = irf_parse_choices( irf_arr( $field, 'choices', '' ) );
		$selected = (array) ( is_array( $value ) ? $value : ( '' === $value ? array() : array( $value ) ) );

		if ( 'select' === $type ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" class="irf-input widefat">';
			if ( ! irf_arr( $field, 'required' ) ) {
				echo '<option value="">- kies -</option>';
			}
			foreach ( $choices as $val => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( in_array( (string) $val, array_map( 'strval', $selected ), true ), true, false ), esc_html( $label ) );
			}
			echo '</select>';
			return;
		}

		$input   = 'checkbox' === $type ? 'checkbox' : 'radio';
		$namebkt = 'checkbox' === $type ? $name . '[]' : $name;
		echo '<div class="irf-choices">';
		if ( 'checkbox' === $type ) {
			echo '<input type="hidden" name="' . esc_attr( $name ) . '[__none]" value="1">';
		}
		foreach ( $choices as $val => $label ) {
			printf(
				'<label class="irf-choice"><input type="%s" name="%s" value="%s" %s> %s</label>',
				esc_attr( $input ),
				esc_attr( $namebkt ),
				esc_attr( $val ),
				checked( in_array( (string) $val, array_map( 'strval', $selected ), true ), true, false ),
				esc_html( $label )
			);
		}
		echo '</div>';
	}

	protected static function render_media_input( $type, $name, $value, $id ) {
		$att_id = absint( is_array( $value ) ? irf_arr( $value, 'id' ) : $value );
		$preview = '';
		if ( $att_id ) {
			$preview = 'image' === $type
				? wp_get_attachment_image( $att_id, 'medium', false, array( 'class' => 'irf-media-img' ) )
				: '<span class="irf-media-name">' . esc_html( get_the_title( $att_id ) ) . '</span>';
		}
		printf(
			'<div class="irf-media" data-media-type="%s">'
			. '<input type="hidden" class="irf-media-id" name="%s" value="%s">'
			. '<div class="irf-media-preview" %s>%s</div>'
			. '<button type="button" class="button irf-media-pick">%s kiezen</button> '
			. '<button type="button" class="button-link irf-media-clear" %s>Verwijderen</button>'
			. '</div>',
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $att_id ?: '' ),
			$att_id ? '' : 'hidden',
			$preview, // reeds ge-escaped door WP-functies hierboven
			'image' === $type ? 'Afbeelding' : 'Bestand',
			$att_id ? '' : 'hidden'
		);
	}

	/* ---------- Containers ---------- */

	/**
	 * Render een setje subvelden voor één (sub)waarde-array. Gebruikt door group,
	 * en per rij door repeater/flexible.
	 */
	public static function render_subfields( $sub_fields, $base_name, $values ) {
		foreach ( (array) $sub_fields as $sub ) {
			$sname = irf_arr( $sub, 'name' );
			if ( '' === $sname && ! self::is_presentational( irf_arr( $sub, 'type' ) ) ) {
				continue;
			}
			$child_name  = $base_name . '[' . $sname . ']';
			$child_value = self::is_presentational( irf_arr( $sub, 'type' ) ) ? null : irf_arr( $values, $sname, null );
			self::render_input( $sub, $child_name, $child_value );
		}
	}

	protected static function render_repeater( $field, $name, $rows ) {
		$sub_fields   = irf_arr( $field, 'sub_fields', array() );
		$button_label = irf_arr( $field, 'button_label', 'Rij toevoegen' );

		echo '<div class="irf-repeater" data-name="' . esc_attr( $name ) . '">';
		echo '<div class="irf-repeater-rows">';
		foreach ( array_values( (array) $rows ) as $i => $row ) {
			self::render_repeater_row( $sub_fields, $name, $i, is_array( $row ) ? $row : array() );
		}
		echo '</div>';

		// JS-template voor een lege rij.
		echo '<script type="text/html" class="irf-repeater-tpl">';
		self::render_repeater_row( $sub_fields, $name, '__i__', array() );
		echo '</script>';

		echo '<button type="button" class="button irf-repeater-add">' . esc_html( $button_label ) . '</button>';
		echo '</div>';
	}

	protected static function render_repeater_row( $sub_fields, $name, $i, $row ) {
		echo '<div class="irf-row">';
		echo '<div class="irf-row-handle" title="Sleep om te herschikken">⠿</div>';
		echo '<button type="button" class="button-link irf-row-remove" aria-label="Rij verwijderen">×</button>';
		echo '<div class="irf-row-body">';
		self::render_subfields( $sub_fields, $name . '[' . $i . ']', $row );
		echo '</div></div>';
	}

	protected static function render_flexible( $field, $name, $rows ) {
		$layouts = irf_arr( $field, 'layouts', array() );
		$by_name = array();
		foreach ( (array) $layouts as $l ) {
			$by_name[ irf_arr( $l, 'name' ) ] = $l;
		}

		echo '<div class="irf-flex" data-name="' . esc_attr( $name ) . '">';
		echo '<div class="irf-flex-rows">';
		foreach ( array_values( (array) $rows ) as $i => $row ) {
			$lname = irf_arr( $row, '__layout', '' );
			if ( ! isset( $by_name[ $lname ] ) ) {
				continue;
			}
			self::render_flex_row( $by_name[ $lname ], $name, $i, is_array( $row ) ? $row : array() );
		}
		echo '</div>';

		// Eén JS-template per layout.
		foreach ( (array) $layouts as $l ) {
			echo '<script type="text/html" class="irf-flex-tpl" data-layout="' . esc_attr( irf_arr( $l, 'name' ) ) . '">';
			self::render_flex_row( $l, $name, '__i__', array() );
			echo '</script>';
		}

		echo '<div class="irf-flex-add-wrap">';
		foreach ( (array) $layouts as $l ) {
			echo '<button type="button" class="button irf-flex-add" data-layout="' . esc_attr( irf_arr( $l, 'name' ) ) . '">+ ' . esc_html( irf_arr( $l, 'label', irf_arr( $l, 'name' ) ) ) . '</button> ';
		}
		echo '</div></div>';
	}

	protected static function render_flex_row( $layout, $name, $i, $row ) {
		$lname = irf_arr( $layout, 'name' );
		echo '<div class="irf-row irf-flex-row" data-layout="' . esc_attr( $lname ) . '">';
		echo '<div class="irf-row-handle" title="Sleep om te herschikken">⠿</div>';
		echo '<span class="irf-flex-badge">' . esc_html( irf_arr( $layout, 'label', $lname ) ) . '</span>';
		echo '<button type="button" class="button-link irf-row-remove" aria-label="Blok verwijderen">×</button>';
		echo '<input type="hidden" name="' . esc_attr( $name . '[' . $i . '][__layout]' ) . '" value="' . esc_attr( $lname ) . '">';
		echo '<div class="irf-row-body">';
		self::render_subfields( irf_arr( $layout, 'sub_fields', array() ), $name . '[' . $i . ']', $row );
		echo '</div></div>';
	}

	/* =====================================================================
	 * WAARDE OPSCHONEN (recursief)
	 * ===================================================================== */

	public static function sanitize( array $field, $raw ) {
		$type = irf_arr( $field, 'type', 'text' );

		switch ( $type ) {
			case 'tab':
			case 'message':
				return null;

			case 'text':
			case 'color':
			case 'date':
				return sanitize_text_field( (string) ( is_scalar( $raw ) ? $raw : '' ) );

			case 'textarea':
				return sanitize_textarea_field( (string) ( is_scalar( $raw ) ? $raw : '' ) );

			case 'wysiwyg':
				return wp_kses_post( (string) ( is_scalar( $raw ) ? $raw : '' ) );

			case 'email':
				return sanitize_email( (string) $raw );

			case 'url':
				return esc_url_raw( (string) $raw );

			case 'number':
				return is_numeric( $raw ) ? $raw + 0 : '';

			case 'true_false':
				return ( '1' === (string) ( is_array( $raw ) ? end( $raw ) : $raw ) ) ? 1 : 0;

			case 'select':
			case 'radio':
				return sanitize_text_field( (string) ( is_array( $raw ) ? reset( $raw ) : $raw ) );

			case 'checkbox':
				$raw = is_array( $raw ) ? $raw : array();
				unset( $raw['__none'] );
				return array_values( array_map( 'sanitize_text_field', $raw ) );

			case 'image':
			case 'file':
				return absint( is_array( $raw ) ? irf_arr( $raw, 'id' ) : $raw );

			case 'link':
				$raw = is_array( $raw ) ? $raw : array();
				return array(
					'url'    => esc_url_raw( irf_arr( $raw, 'url' ) ),
					'text'   => sanitize_text_field( irf_arr( $raw, 'text' ) ),
					'target' => ( '_blank' === irf_arr( $raw, 'target' ) ) ? '_blank' : '',
				);

			case 'group':
				return self::sanitize_subfields( irf_arr( $field, 'sub_fields', array() ), is_array( $raw ) ? $raw : array() );

			case 'repeater':
				$out = array();
				foreach ( (array) $raw as $key => $row ) {
					if ( '__i__' === $key || ! is_array( $row ) ) {
						continue;
					}
					$out[] = self::sanitize_subfields( irf_arr( $field, 'sub_fields', array() ), $row );
				}
				return $out;

			case 'flexible_content':
				$layouts = array();
				foreach ( (array) irf_arr( $field, 'layouts', array() ) as $l ) {
					$layouts[ irf_arr( $l, 'name' ) ] = irf_arr( $l, 'sub_fields', array() );
				}
				$out = array();
				foreach ( (array) $raw as $key => $row ) {
					if ( '__i__' === $key || ! is_array( $row ) ) {
						continue;
					}
					$lname = irf_arr( $row, '__layout' );
					if ( ! isset( $layouts[ $lname ] ) ) {
						continue;
					}
					$clean             = self::sanitize_subfields( $layouts[ $lname ], $row );
					$clean['__layout'] = sanitize_key( $lname );
					$out[]             = $clean;
				}
				return $out;

			default:
				return sanitize_text_field( (string) ( is_scalar( $raw ) ? $raw : '' ) );
		}
	}

	public static function sanitize_subfields( $sub_fields, $raw ) {
		$out = array();
		foreach ( (array) $sub_fields as $sub ) {
			if ( self::is_presentational( irf_arr( $sub, 'type' ) ) ) {
				continue;
			}
			$sname = irf_arr( $sub, 'name' );
			if ( '' === $sname ) {
				continue;
			}
			$out[ $sname ] = self::sanitize( $sub, irf_arr( $raw, $sname, null ) );
		}
		return $out;
	}
}
