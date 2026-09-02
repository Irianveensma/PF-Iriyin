<?php
/**
 * Irian Portfolio - functions.php
 * Panels systeem is hier volledig inline gebouwd (geen aparte plugin-bestanden),
 * zodat alles via de Theme File Editor bewerkbaar is.
 *
 * @package IrianPortfolio
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/i18n.php';
require_once get_template_directory() . '/inc/skill-visuals.php';
require_once get_template_directory() . '/inc/module-demos.php';
require_once get_template_directory() . '/inc/contact-form.php';

/**
 * Theme setup.
 */
function irian_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	register_nav_menus( array(
		'primary' => 'Hoofdmenu',
	) );
}
add_action( 'after_setup_theme', 'irian_theme_setup' );

/**
 * Enqueue front-end styles and fonts.
 */
function irian_enqueue_assets() {
	wp_enqueue_style( 'irian-style', get_stylesheet_uri(), array(), '0.33.0' );
	wp_enqueue_style( 'irian-panels', get_template_directory_uri() . '/assets/panels.css', array(), '0.33.0' );
	wp_enqueue_style( 'irian-site', get_template_directory_uri() . '/assets/site.css', array( 'irian-panels' ), '0.33.0' );
	wp_enqueue_style(
		'irian-fonts',
		'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap',
		array(),
		null
	);
	wp_enqueue_script( 'irian-site', get_template_directory_uri() . '/assets/site.js', array(), '0.33.0', true );

	wp_localize_script(
		'irian-site',
		'irianI18n',
		array(
			'lang'        => irian_lang(),
			'consoleHi'   => irian_str( 'console_hi' ),
			'consoleSub'  => irian_str( 'console_sub' ),
			'empty'       => irian_str( 'palette_empty' ),
			'hintSection' => irian_str( 'pal_hint_section' ),
			'hintExternal' => irian_str( 'pal_hint_external' ),
			'goTo'        => irian_str( 'pal_go' ),
			'top'         => irian_str( 'pal_top' ),
			'navWork'     => irian_str( 'nav_work' ),
			'navPlatforms' => irian_str( 'nav_platforms' ),
			'navModules'  => irian_str( 'nav_modules' ),
			'navFaq'      => irian_str( 'nav_faq' ),
			'navContact'  => irian_str( 'nav_contact' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'irian_enqueue_assets' );

/* =========================================================================
 * IRIAN PANELS - eigen "flexible content"-achtig systeem, gratis, geen ACF.
 * Metabox + jQuery UI Sortable + WP media uploader.
 * ========================================================================= */

/* ---------- Field renderer helpers ---------- */

/**
 * De veld-prefix van de metabox die op dit moment rendert: 'panels' voor de
 * Nederlandse content, 'panels_en' voor de Engelse. De <script>-templates
 * onderaan de metabox houden het token __PFX__ vast zodat één set templates
 * allebei de metaboxes bedient.
 *
 * @param string|null $set Nieuwe prefix, of null om alleen te lezen.
 */
function irian_panels_active_prefix( $set = null ) {
	static $prefix = 'panels';
	if ( null !== $set ) {
		$prefix = $set;
	}
	return $prefix;
}

function irian_field_name( $index, $field_path ) {
	// $field_path komt binnen als "data[eyebrow]" of "data[items][0][name]".
	// Zet dat om naar geldige geneste form-notatie: data][eyebrow  ->
	// panels[0][data][eyebrow]. (Zonder deze omzetting parset PHP
	// "panels[0][data[eyebrow]]" fout en worden panels bij opslaan gewist.)
	$field_path = str_replace( '[', '][', str_replace( ']', '', $field_path ) );
	$name       = irian_panels_active_prefix() . "[{$index}][{$field_path}]";
	// data-name-tpl houdt de tokens vast (__PFX__ voor de metabox-prefix,
	// __INDEX__ voor de paneelpositie) zodat de admin-JS bij herschikken of
	// toevoegen de juiste veldnaam kan herbouwen, in beide metaboxes.
	$tpl        = "__PFX__[__INDEX__][{$field_path}]";
	return sprintf( 'name="%1$s" data-name-tpl="%2$s"', esc_attr( $name ), esc_attr( $tpl ) );
}

function irian_text_field( $label, $index, $field_path, $value = '', $type = 'text' ) {
	printf(
		'<label class="irian-field"><span>%1$s</span><input type="%2$s" %3$s value="%4$s"></label>',
		esc_html( $label ),
		esc_attr( $type ),
		irian_field_name( $index, $field_path ),
		esc_attr( $value )
	);
}

function irian_textarea_field( $label, $index, $field_path, $value = '', $rows = 3 ) {
	printf(
		'<label class="irian-field"><span>%1$s</span><textarea rows="%2$d" %3$s>%4$s</textarea></label>',
		esc_html( $label ),
		(int) $rows,
		irian_field_name( $index, $field_path ),
		esc_textarea( $value )
	);
}

function irian_checkbox_field( $label, $index, $field_path, $checked ) {
	// De hidden partner deelt naam en tpl met de checkbox zodat er altijd een
	// waarde (0 of 1) meekomt, ook in het EN-kanaal. Beide via irian_field_name()
	// zodat de prefix (__PFX__) mee-verspringt en de twee metaboxes niet botsen.
	$attrs = irian_field_name( $index, $field_path );
	printf(
		'<label class="irian-field irian-inline"><input type="hidden" %1$s value="0"><input type="checkbox" %1$s value="1" %2$s> %3$s</label>',
		$attrs, // phpcs:ignore WordPress.Security.EscapeOutput -- irian_field_name() escapes.
		checked( $checked, true, false ),
		esc_html( $label )
	);
}

function irian_image_field( $label, $index, $field_path, $attachment_id = '' ) {
	$url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	?>
	<div class="irian-field irian-image-field">
		<span><?php echo esc_html( $label ); ?></span>
		<div class="irian-image-preview" style="<?php echo $url ? '' : 'display:none;'; ?>">
			<img src="<?php echo esc_url( $url ); ?>" alt="">
		</div>
		<input type="hidden" class="irian-image-id" <?php echo irian_field_name( $index, $field_path ); ?> value="<?php echo esc_attr( $attachment_id ); ?>">
		<button type="button" class="button irian-select-image">Kies afbeelding</button>
		<button type="button" class="button-link irian-remove-image" style="<?php echo $url ? '' : 'display:none;'; ?>">Verwijderen</button>
	</div>
	<?php
}

function irian_render_panel_fields( $type, $index, $data ) {
	$data = is_array( $data ) ? $data : array();

	switch ( $type ) {

		case 'hero':
			irian_text_field( 'Eyebrow', $index, 'data[eyebrow]', $data['eyebrow'] ?? 'WEBDEVELOPER · MARKETEER · DIGITAL' );
			irian_text_field( 'Titel (eerste deel)', $index, 'data[title_before]', $data['title_before'] ?? 'Irian' );
			irian_text_field( 'Titel (accentwoord)', $index, 'data[title_accent]', $data['title_accent'] ?? 'Veensma' );
			irian_textarea_field( 'Subtekst', $index, 'data[subtitle]', $data['subtitle'] ?? '' );
			irian_image_field( 'Foto (portret, rechts in de hero)', $index, 'data[photo]', $data['photo'] ?? '' );
			irian_text_field( 'Primaire knop, tekst', $index, 'data[primary_label]', $data['primary_label'] ?? 'Bekijk werk' );
			irian_text_field( 'Primaire knop, link', $index, 'data[primary_url]', $data['primary_url'] ?? '#work' );
			irian_text_field( 'Secundaire knop, tekst', $index, 'data[secondary_label]', $data['secondary_label'] ?? 'Neem contact op' );
			irian_text_field( 'Secundaire knop, link', $index, 'data[secondary_url]', $data['secondary_url'] ?? '#contact' );
			break;

		case 'stack':
			irian_textarea_field(
				'Tags (één per regel). Klikbaar met uitleg: "Label :: wat het is" of '
				. '"Label :: wat het is :: waarom dit ertoe doet". Groep van sub-skills '
				. '(zoals Content Management Systems): een regel "Groepsnaam:" gevolgd door '
				. 'kind-regels die met "> " beginnen, zelfde "Label :: wat :: waarom"-notatie.',
				$index,
				'data[tags_raw]',
				irian_stack_tags_to_text( $data['tags'] ?? null ),
				14
			);
			break;

		case 'work_grid':
			irian_text_field( 'Sectie-label', $index, 'data[section_label]', $data['section_label'] ?? '01 / SELECTED WORK' );
			irian_text_field( 'Sectie-titel', $index, 'data[section_title]', $data['section_title'] ?? 'Geselecteerd werk' );
			irian_textarea_field( 'Sectie-intro', $index, 'data[section_intro]', $data['section_intro'] ?? '' );

			echo '<div class="irian-subrepeater" data-field="items">';
			echo '<span class="irian-subrepeater-label">Projecten <span class="irian-subrepeater-count"></span></span>';
			echo '<ul class="irian-subrepeater-list">';
			$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
			foreach ( $items as $item_index => $item ) {
				irian_render_work_item( $index, $item_index, $item );
			}
			echo '</ul>';
			echo '<button type="button" class="button irian-add-item">Project toevoegen</button>';
			echo '</div>';

			irian_textarea_field( 'Toelichting onder de grid', $index, 'data[note]', $data['note'] ?? '' );
			break;

		case 'lab_grid':
			irian_text_field( 'Sectie-label', $index, 'data[section_label]', $data['section_label'] ?? '03 / MODULES' );
			irian_text_field( 'Sectie-titel', $index, 'data[section_title]', $data['section_title'] ?? 'Modules' );
			irian_textarea_field( 'Sectie-intro', $index, 'data[section_intro]', $data['section_intro'] ?? '' );

			echo '<div class="irian-subrepeater" data-field="items">';
			echo '<span class="irian-subrepeater-label">Tiles <span class="irian-subrepeater-count"></span></span>';
			echo '<ul class="irian-subrepeater-list">';
			$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
			foreach ( $items as $item_index => $item ) {
				irian_render_lab_item( $index, $item_index, $item );
			}
			echo '</ul>';
			echo '<button type="button" class="button irian-add-item">Tile toevoegen</button>';
			echo '</div>';
			break;

		case 'projects':
			irian_text_field( 'Sectie-label', $index, 'data[section_label]', $data['section_label'] ?? '02 / PLATFORMS' );
			irian_text_field( 'Sectie-titel', $index, 'data[section_title]', $data['section_title'] ?? 'Grotere projecten' );
			irian_textarea_field( 'Sectie-intro', $index, 'data[section_intro]', $data['section_intro'] ?? '' );

			echo '<div class="irian-subrepeater" data-field="items">';
			echo '<span class="irian-subrepeater-label">Projecten <span class="irian-subrepeater-count"></span></span>';
			echo '<ul class="irian-subrepeater-list">';
			$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
			foreach ( $items as $item_index => $item ) {
				irian_render_project_item( $index, $item_index, $item );
			}
			echo '</ul>';
			echo '<button type="button" class="button irian-add-item">Project toevoegen</button>';
			echo '</div>';
			break;

		case 'faq':
			irian_text_field( 'Sectie-label', $index, 'data[section_label]', $data['section_label'] ?? '04 / FAQ' );
			irian_text_field( 'Sectie-titel', $index, 'data[section_title]', $data['section_title'] ?? 'Veelgestelde vragen' );
			irian_textarea_field( 'Sectie-intro', $index, 'data[section_intro]', $data['section_intro'] ?? '' );

			echo '<div class="irian-subrepeater" data-field="items">';
			echo '<span class="irian-subrepeater-label">Vragen <span class="irian-subrepeater-count"></span></span>';
			echo '<ul class="irian-subrepeater-list">';
			$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
			foreach ( $items as $item_index => $item ) {
				irian_render_faq_item( $index, $item_index, $item );
			}
			echo '</ul>';
			echo '<button type="button" class="button irian-add-item">Vraag toevoegen</button>';
			echo '</div>';
			break;

		case 'contact':
			irian_text_field( 'Sectie-label', $index, 'data[section_label]', $data['section_label'] ?? '05 / CONTACT' );
			irian_textarea_field( 'CTA-tekst', $index, 'data[cta_text]', $data['cta_text'] ?? '' );
			irian_text_field( 'E-mailadres (ontvanger van het formulier, niet zichtbaar op de site)', $index, 'data[email]', $data['email'] ?? '', 'email' );

			irian_checkbox_field( 'Toon een contactformulier', $index, 'data[show_form]', ! empty( $data['show_form'] ) );
			irian_textarea_field( 'Projecttypes (voor de dropdown, één per regel)', $index, 'data[project_types]', isset( $data['project_types'] ) && is_array( $data['project_types'] ) ? implode( "\n", $data['project_types'] ) : "Nieuwe website\nWebshop (Magento / WooCommerce)\nDoorontwikkeling bestaande site\nAI / automatisering\nIets anders", 5 );
			irian_textarea_field( 'Kleine tekst onder het formulier', $index, 'data[form_note]', $data['form_note'] ?? '' );
			break;
	}
}

function irian_render_faq_item( $panel_index, $item_index, $item = array() ) {
	$item = is_array( $item ) ? $item : array();
	$base = "data[items][{$item_index}]";
	?>
	<li class="irian-subitem">
		<button type="button" class="irian-remove-item button-link">Verwijderen</button>
		<?php
		irian_text_field( 'Vraag', $panel_index, "{$base}[question]", $item['question'] ?? '' );
		irian_textarea_field( 'Antwoord', $panel_index, "{$base}[answer]", $item['answer'] ?? '', 4 );
		?>
	</li>
	<?php
}

function irian_render_project_item( $panel_index, $item_index, $item = array() ) {
	$item = is_array( $item ) ? $item : array();
	$base = "data[items][{$item_index}]";
	?>
	<li class="irian-subitem">
		<button type="button" class="irian-remove-item button-link">Verwijderen</button>
		<?php
		irian_text_field( 'Naam', $panel_index, "{$base}[name]", $item['name'] ?? '' );
		irian_text_field( 'Eén-regel omschrijving', $panel_index, "{$base}[tagline]", $item['tagline'] ?? '' );
		irian_text_field( 'Tags (bv. AI · FROM SCRATCH · TEAM TOOL)', $panel_index, "{$base}[tags]", $item['tags'] ?? '' );
		irian_textarea_field( 'Beschrijving', $panel_index, "{$base}[description]", $item['description'] ?? '', 5 );
		irian_textarea_field( 'Functies / onderdelen (één per regel)', $panel_index, "{$base}[features_raw]", isset( $item['features'] ) && is_array( $item['features'] ) ? implode( "\n", $item['features'] ) : '', 6 );
		irian_textarea_field( 'Panelen / rollen als chips (één per regel, optioneel)', $panel_index, "{$base}[roles_raw]", isset( $item['roles'] ) && is_array( $item['roles'] ) ? implode( "\n", $item['roles'] ) : '', 4 );
		irian_text_field( 'Link (optioneel, "Bekijk"-knop)', $panel_index, "{$base}[url]", $item['url'] ?? '' );
		irian_image_field( 'Afbeelding (optioneel)', $panel_index, "{$base}[image]", $item['image'] ?? '' );
		?>
	</li>
	<?php
}

function irian_render_work_item( $panel_index, $item_index, $item = array() ) {
	$item = is_array( $item ) ? $item : array();
	$base = "data[items][{$item_index}]";
	?>
	<li class="irian-subitem">
		<button type="button" class="irian-remove-item button-link">Verwijderen</button>
		<?php
		irian_text_field( 'Naam', $panel_index, "{$base}[name]", $item['name'] ?? '' );
		irian_text_field( 'URL', $panel_index, "{$base}[url]", $item['url'] ?? '' );
		irian_image_field( 'Screenshot desktop (in de MacBook)', $panel_index, "{$base}[visual]", $item['visual'] ?? '' );
		irian_image_field( 'Screenshot mobiel (in de telefoon)', $panel_index, "{$base}[visual_mobile]", $item['visual_mobile'] ?? '' );
		irian_textarea_field( 'Tags (één per regel: Design, Development, Hosting, ...)', $panel_index, "{$base}[tags_raw]", isset( $item['tags'] ) && is_array( $item['tags'] ) ? implode( "\n", $item['tags'] ) : '', 3 );
		?>
	</li>
	<?php
}

function irian_render_lab_item( $panel_index, $item_index, $item = array() ) {
	$item = is_array( $item ) ? $item : array();
	$base = "data[items][{$item_index}]";
	?>
	<li class="irian-subitem">
		<button type="button" class="irian-remove-item button-link">Verwijderen</button>
		<?php
		irian_text_field( 'Titel', $panel_index, "{$base}[title]", $item['title'] ?? '' );
		irian_text_field( 'Tag (bv. AI · PROTOTYPE)', $panel_index, "{$base}[tag]", $item['tag'] ?? '' );
		irian_textarea_field( 'Uitleg (in het uitklap-paneel)', $panel_index, "{$base}[blurb]", $item['blurb'] ?? '', 3 );

		$ctype = $item['content_type'] ?? 'none';
		?>
		<label class="irian-field"><span>Wat toont de tegel?</span>
			<select <?php echo irian_field_name( $panel_index, "{$base}[content_type]" ); ?>>
				<?php foreach ( array( 'none' => 'Alleen tekst', 'demo' => 'Live demo', 'code' => 'Code-snippet', 'image' => 'Afbeelding' ) as $k => $lbl ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $ctype, $k ); ?>><?php echo esc_html( $lbl ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="irian-field"><span>Demo (als "Live demo" gekozen is)</span>
			<select <?php echo irian_field_name( $panel_index, "{$base}[demo]" ); ?>>
				<?php
				$demo = $item['demo'] ?? '';
				foreach ( array( '' => '(geen)', 'palette' => 'Command palette', 'cursor' => 'Custom cursor', 'seo-report' => 'SEO-audit uitvoer' ) as $k => $lbl ) :
					?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $demo, $k ); ?>><?php echo esc_html( $lbl ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<?php
		irian_textarea_field( 'Code (als "Code-snippet" gekozen is)', $panel_index, "{$base}[code]", $item['code'] ?? '', 6 );
		irian_text_field( 'Code-taal (bv. PHP, JavaScript)', $panel_index, "{$base}[code_lang]", $item['code_lang'] ?? '' );
		irian_image_field( 'Afbeelding (als "Afbeelding" gekozen is)', $panel_index, "{$base}[image]", $item['image'] ?? '' );
		irian_text_field( 'Link (optioneel, "Bekijk"-knop)', $panel_index, "{$base}[url]", $item['url'] ?? '' );
		?>
	</li>
	<?php
}

/* ---------- Meta box ---------- */

/**
 * De paneeltypes met hun labels voor de metabox-dropdown.
 */
function irian_panel_type_labels() {
	return array(
		'hero'      => 'Hero',
		'stack'     => 'Stack',
		'work_grid' => 'Work Grid',
		'projects'  => 'Projecten (grote builds)',
		'lab_grid'  => 'Lab Grid (Modules)',
		'faq'       => 'FAQ',
		'contact'   => 'Contact',
	);
}

/**
 * De "kanalen": elke metabox schrijft naar zijn eigen post meta met zijn eigen
 * form-prefix en nonce. NL is het origineel, EN is de optionele vertaling die
 * de NL-content vervangt op ?lang=en (zie irian_panels_data() in inc/i18n.php).
 */
function irian_panels_channels() {
	return array(
		'_irian_panels'    => array(
			'prefix'       => 'panels',
			'nonce_field'  => 'irian_panels_nonce',
			'nonce_action' => 'irian_panels_save',
		),
		'_irian_panels_en' => array(
			'prefix'       => 'panels_en',
			'nonce_field'  => 'irian_panels_en_nonce',
			'nonce_action' => 'irian_panels_en_save',
		),
	);
}

function irian_panels_add_meta_box() {
	add_meta_box(
		'irian_panels_box',
		'Homepage Panels (NL)',
		function ( $post ) {
			irian_panels_render_channel( $post, '_irian_panels' );
			irian_panels_render_templates();
		},
		'page',
		'normal',
		'high'
	);
	add_meta_box(
		'irian_panels_en_box',
		'Homepage Panels (EN) - vertaling',
		function ( $post ) {
			irian_panels_render_channel( $post, '_irian_panels_en' );
		},
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'irian_panels_add_meta_box' );

/**
 * Rendert één kanaal (NL of EN) van de panels-editor.
 *
 * @param WP_Post $post     De pagina.
 * @param string  $meta_key '_irian_panels' of '_irian_panels_en'.
 */
function irian_panels_render_channel( $post, $meta_key ) {
	$channels = irian_panels_channels();
	if ( ! isset( $channels[ $meta_key ] ) ) {
		return;
	}
	$prefix       = $channels[ $meta_key ]['prefix'];
	$is_en        = '_irian_panels_en' === $meta_key;
	$labels       = irian_panel_type_labels();

	irian_panels_active_prefix( $prefix );

	$panels = get_post_meta( $post->ID, $meta_key, true );
	$panels = is_array( $panels ) ? $panels : array();

	wp_nonce_field( $channels[ $meta_key ]['nonce_action'], $channels[ $meta_key ]['nonce_field'] );

	if ( $is_en ) {
		echo '<p class="irian-channel-note">Laat dit leeg om overal de Nederlandse content te tonen. Zodra hier panelen staan, vervangen die de Nederlandse versie op <code>?lang=en</code>. Houd dezelfde volgorde en types aan als bij NL.</p>';
	}
	?>
	<div class="irian-panels-wrap" data-prefix="<?php echo esc_attr( $prefix ); ?>">
		<ul class="irian-panels-list">
			<?php foreach ( $panels as $index => $panel ) : ?>
				<?php
				$type  = $panel['type'] ?? '';
				$label = $labels[ $type ] ?? $type;
				?>
				<li class="irian-panel" data-type="<?php echo esc_attr( $type ); ?>">
					<div class="irian-panel-header">
						<span class="irian-panel-handle" title="Sleep om te herschikken">⠿</span>
						<span class="irian-panel-title"><?php echo esc_html( $label ); ?></span>
						<button type="button" class="button-link irian-toggle-panel">In-/uitklappen</button>
						<button type="button" class="button-link irian-remove-panel">Verwijderen</button>
					</div>
					<div class="irian-panel-body">
						<input type="hidden" name="<?php echo esc_attr( "{$prefix}[{$index}][type]" ); ?>" data-name-tpl="__PFX__[__INDEX__][type]" value="<?php echo esc_attr( $type ); ?>">
						<?php irian_render_panel_fields( $type, $index, $panel['data'] ?? array() ); ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="irian-add-panel-row">
			<select class="irian-add-panel-type">
				<?php foreach ( $labels as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button button-primary irian-add-panel-btn">Panel toevoegen</button>
		</div>
	</div>
	<?php
}

/**
 * De <script type="text/html">-templates. Eén set voor beide metaboxes: ze
 * houden __PFX__ / __INDEX__ / __ITEM__ vast, de admin-JS vult die in met de
 * prefix van het kanaal waarin je een paneel toevoegt.
 */
function irian_panels_render_templates() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	// Templates bewaren de prefix als token in zowel name als data-name-tpl.
	irian_panels_active_prefix( '__PFX__' );
	$labels = irian_panel_type_labels();
	?>
	<?php foreach ( $labels as $type => $label ) : ?>
		<script type="text/html" id="tmpl-panel-<?php echo esc_attr( $type ); ?>">
			<li class="irian-panel" data-type="<?php echo esc_attr( $type ); ?>">
				<div class="irian-panel-header">
					<span class="irian-panel-handle" title="Sleep om te herschikken">⠿</span>
					<span class="irian-panel-title"><?php echo esc_html( $label ); ?></span>
					<button type="button" class="button-link irian-toggle-panel">In-/uitklappen</button>
					<button type="button" class="button-link irian-remove-panel">Verwijderen</button>
				</div>
				<div class="irian-panel-body">
					<input type="hidden" name="__PFX__[__INDEX__][type]" data-name-tpl="__PFX__[__INDEX__][type]" value="<?php echo esc_attr( $type ); ?>">
					<?php irian_render_panel_fields( $type, '__INDEX__', array() ); ?>
				</div>
			</li>
		</script>
	<?php endforeach; ?>

	<script type="text/html" id="tmpl-item-work_grid">
		<?php irian_render_work_item( '__INDEX__', '__ITEM__', array() ); ?>
	</script>
	<script type="text/html" id="tmpl-item-lab_grid">
		<?php irian_render_lab_item( '__INDEX__', '__ITEM__', array() ); ?>
	</script>
	<script type="text/html" id="tmpl-item-faq">
		<?php irian_render_faq_item( '__INDEX__', '__ITEM__', array() ); ?>
	</script>
	<script type="text/html" id="tmpl-item-projects">
		<?php irian_render_project_item( '__INDEX__', '__ITEM__', array() ); ?>
	</script>
	<?php
	irian_panels_active_prefix( 'panels' );
}

/* ---------- Save handler ---------- */

add_action( 'save_post_page', 'irian_panels_save', 10, 1 );

function irian_panels_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	foreach ( irian_panels_channels() as $meta_key => $channel ) {
		if ( ! isset( $_POST[ $channel['nonce_field'] ] ) || ! wp_verify_nonce( $_POST[ $channel['nonce_field'] ], $channel['nonce_action'] ) ) {
			continue;
		}

		$req = $channel['prefix'];
		$raw = isset( $_POST[ $req ] ) && is_array( $_POST[ $req ] ) ? wp_unslash( $_POST[ $req ] ) : array();

		$clean = array();
		foreach ( $raw as $panel ) {
			if ( ! isset( $panel['type'] ) ) {
				continue;
			}
			$type = sanitize_key( $panel['type'] );
			$data = isset( $panel['data'] ) && is_array( $panel['data'] ) ? $panel['data'] : array();

			$clean[] = array(
				'type' => $type,
				'data' => irian_sanitize_panel_data( $type, $data ),
			);
		}

		update_post_meta( $post_id, $meta_key, $clean );
	}
}

function irian_sanitize_panel_data( $type, $data ) {
	$out = array();

	switch ( $type ) {

		case 'hero':
			foreach ( array( 'eyebrow', 'title_before', 'title_accent', 'primary_label', 'primary_url', 'secondary_label', 'secondary_url' ) as $key ) {
				$out[ $key ] = isset( $data[ $key ] ) ? sanitize_text_field( $data[ $key ] ) : '';
			}
			$out['subtitle'] = isset( $data['subtitle'] ) ? sanitize_textarea_field( $data['subtitle'] ) : '';
			$out['photo']    = isset( $data['photo'] ) ? absint( $data['photo'] ) : 0;
			break;

		case 'stack':
			$out['tags'] = irian_sanitize_stack_tags( $data['tags_raw'] ?? '' );
			break;

		case 'work_grid':
			foreach ( array( 'section_label', 'section_title' ) as $key ) {
				$out[ $key ] = isset( $data[ $key ] ) ? sanitize_text_field( $data[ $key ] ) : '';
			}
			$out['section_intro'] = isset( $data['section_intro'] ) ? sanitize_textarea_field( $data['section_intro'] ) : '';
			$out['note']          = isset( $data['note'] ) ? sanitize_textarea_field( $data['note'] ) : '';

			$out['items'] = array();
			if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
				foreach ( $data['items'] as $item ) {
					$out['items'][] = array(
						'name'          => isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '',
						'url'           => isset( $item['url'] ) ? sanitize_text_field( $item['url'] ) : '',
						'visual'        => isset( $item['visual'] ) ? absint( $item['visual'] ) : 0,
						'visual_mobile' => isset( $item['visual_mobile'] ) ? absint( $item['visual_mobile'] ) : 0,
						'tags'          => irian_lines_to_array( $item['tags_raw'] ?? '' ),
					);
				}
			}
			break;

		case 'lab_grid':
			foreach ( array( 'section_label', 'section_title' ) as $key ) {
				$out[ $key ] = isset( $data[ $key ] ) ? sanitize_text_field( $data[ $key ] ) : '';
			}
			$out['section_intro'] = isset( $data['section_intro'] ) ? sanitize_textarea_field( $data['section_intro'] ) : '';

			$out['items'] = array();
			if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
				$allowed_ctype = array( 'none', 'demo', 'code', 'image' );
				$allowed_demo  = array( '', 'palette', 'cursor', 'seo-report' );
				foreach ( $data['items'] as $item ) {
					$out['items'][] = array(
						'title'        => isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '',
						'tag'          => isset( $item['tag'] ) ? sanitize_text_field( $item['tag'] ) : '',
						'blurb'        => isset( $item['blurb'] ) ? sanitize_textarea_field( $item['blurb'] ) : '',
						'content_type' => in_array( $item['content_type'] ?? 'none', $allowed_ctype, true ) ? $item['content_type'] : 'none',
						'demo'         => in_array( $item['demo'] ?? '', $allowed_demo, true ) ? $item['demo'] : '',
						// Code niet tag-strippen (anders sneuvelt <?php etc.); wél als
						// platte tekst renderen met esc_html() in de template.
						'code'         => isset( $item['code'] ) ? trim( wp_check_invalid_utf8( (string) $item['code'] ) ) : '',
						'code_lang'    => isset( $item['code_lang'] ) ? sanitize_text_field( $item['code_lang'] ) : '',
						'image'        => isset( $item['image'] ) ? absint( $item['image'] ) : 0,
						'url'          => isset( $item['url'] ) ? sanitize_text_field( $item['url'] ) : '',
					);
				}
			}
			break;

		case 'faq':
			foreach ( array( 'section_label', 'section_title' ) as $key ) {
				$out[ $key ] = isset( $data[ $key ] ) ? sanitize_text_field( $data[ $key ] ) : '';
			}
			$out['section_intro'] = isset( $data['section_intro'] ) ? sanitize_textarea_field( $data['section_intro'] ) : '';
			$out['items']         = array();
			if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
				foreach ( $data['items'] as $item ) {
					$q = isset( $item['question'] ) ? sanitize_text_field( $item['question'] ) : '';
					$a = isset( $item['answer'] ) ? sanitize_textarea_field( $item['answer'] ) : '';
					if ( '' === $q && '' === $a ) {
						continue;
					}
					$out['items'][] = array( 'question' => $q, 'answer' => $a );
				}
			}
			break;

		case 'projects':
			foreach ( array( 'section_label', 'section_title' ) as $key ) {
				$out[ $key ] = isset( $data[ $key ] ) ? sanitize_text_field( $data[ $key ] ) : '';
			}
			$out['section_intro'] = isset( $data['section_intro'] ) ? sanitize_textarea_field( $data['section_intro'] ) : '';

			$out['items'] = array();
			if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
				foreach ( $data['items'] as $item ) {
					$name = isset( $item['name'] ) ? sanitize_text_field( $item['name'] ) : '';
					if ( '' === $name ) {
						continue;
					}
					$out['items'][] = array(
						'name'        => $name,
						'tagline'     => isset( $item['tagline'] ) ? sanitize_text_field( $item['tagline'] ) : '',
						'tags'        => isset( $item['tags'] ) ? sanitize_text_field( $item['tags'] ) : '',
						'description' => isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '',
						'features'    => irian_lines_to_array( $item['features_raw'] ?? '' ),
						'roles'       => irian_lines_to_array( $item['roles_raw'] ?? '' ),
						'url'         => isset( $item['url'] ) ? sanitize_text_field( $item['url'] ) : '',
						'image'       => isset( $item['image'] ) ? absint( $item['image'] ) : 0,
					);
				}
			}
			break;

		case 'contact':
			$out['section_label']  = isset( $data['section_label'] ) ? sanitize_text_field( $data['section_label'] ) : '';
			$out['cta_text']       = isset( $data['cta_text'] ) ? sanitize_textarea_field( $data['cta_text'] ) : '';
			$out['email']          = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
			$out['show_form']      = ! empty( $data['show_form'] ) ? 1 : 0;
			$out['project_types']  = irian_lines_to_array( $data['project_types'] ?? '' );
			$out['form_note']      = isset( $data['form_note'] ) ? sanitize_textarea_field( $data['form_note'] ) : '';
			break;
	}

	return $out;
}

function irian_lines_to_array( $raw ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
	$lines = array_map( 'sanitize_text_field', $lines );
	$lines = array_map( 'trim', $lines );
	return array_values( array_filter( $lines, fn( $line ) => $line !== '' ) );
}

/**
 * Stack-tags (array van ['label','note','why'?,'children'?] óf losse strings
 * van oude data) terug naar tekst voor de textarea. Zie irian_parse_stack_line()
 * voor de "Label :: wat :: waarom"-notatie en de groep-syntax ("Naam:" + "> "-regels).
 */
function irian_stack_tags_to_text( $tags ) {
	if ( ! is_array( $tags ) || empty( $tags ) ) {
		return "WordPress :: Mijn hoofdplatform.\nMagento :: Webshops op Magento 2.\nAI Development :: LLM's in echte producten.\nSEO :: Technische SEO en Core Web Vitals.\nHTML / CSS :: Semantische, toegankelijke markup.\nJavaScript :: Interactie en de details.\nPHP :: De taal onder WordPress en Magento.";
	}
	$lines = array();
	foreach ( $tags as $tag ) {
		if ( ! is_array( $tag ) ) {
			$lines[] = (string) $tag;
			continue;
		}
		$children = is_array( $tag['children'] ?? null ) ? $tag['children'] : array();
		if ( $children ) {
			$lines[] = ( $tag['label'] ?? '' ) . ':';
			foreach ( $children as $child ) {
				$lines[] = '> ' . irian_stack_line_to_text( $child );
			}
		} else {
			$lines[] = irian_stack_line_to_text( $tag );
		}
	}
	return implode( "\n", $lines );
}

/**
 * Eén tag/kind (['label','note'?,'why'?]) naar "Label :: wat :: waarom".
 */
function irian_stack_line_to_text( $entry ) {
	$label = $entry['label'] ?? '';
	$note  = $entry['note'] ?? '';
	$why   = $entry['why'] ?? '';
	if ( '' !== $why ) {
		return "{$label} :: {$note} :: {$why}";
	}
	if ( '' !== $note ) {
		return "{$label} :: {$note}";
	}
	return $label;
}

/**
 * Parseert één "Label :: wat :: waarom"-regel (2 of 3 delen, laatste optioneel)
 * terug naar ['label','note','why'?]. Retourneert null bij een lege/ongeldige regel.
 */
function irian_parse_stack_line( $line ) {
	$parts = array_map( 'trim', explode( '::', trim( $line ), 3 ) );
	if ( '' === $parts[0] ) {
		return null;
	}
	$entry = array(
		'label' => sanitize_text_field( $parts[0] ),
		'note'  => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '',
	);
	if ( isset( $parts[2] ) && '' !== $parts[2] ) {
		$entry['why'] = sanitize_text_field( $parts[2] );
	}
	return $entry;
}

/**
 * Parseert de volledige Stack-textarea (irian_stack_tags_to_text()'s notatie)
 * terug naar de tags-array die het front-end (panel-stack.php) verwacht.
 * Zie de veld-help bij de 'stack'-case in irian_render_panel_fields() voor de
 * geldende notatie.
 */
function irian_sanitize_stack_tags( $raw ) {
	$tags        = array();
	$group_index = null;
	foreach ( irian_lines_to_array( $raw ) as $line ) {
		if ( '>' === substr( $line, 0, 1 ) ) {
			if ( null === $group_index ) {
				continue; // geen actieve groep, losse '>'-regel negeren.
			}
			$child = irian_parse_stack_line( substr( $line, 1 ) );
			if ( null !== $child ) {
				$tags[ $group_index ]['children'][] = $child;
			}
			continue;
		}

		if ( false === strpos( $line, '::' ) && ':' === substr( $line, -1 ) ) {
			$label = trim( substr( $line, 0, -1 ) );
			if ( '' === $label ) {
				continue;
			}
			$tags[] = array(
				'label'    => sanitize_text_field( $label ),
				'note'     => '',
				'children' => array(),
			);
			$group_index = count( $tags ) - 1;
			continue;
		}

		$tag = irian_parse_stack_line( $line );
		if ( null !== $tag ) {
			$tags[] = $tag;
		}
		$group_index = null;
	}
	return $tags;
}

/* ---------- Admin assets (inline, no separate files needed) ---------- */

function irian_panels_enqueue_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	global $post;
	if ( ! $post || 'page' !== $post->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery' );

	$inline_js = <<<'JS'
( function ( $ ) {
	'use strict';

	function resolveName( tpl, prefix, indexValue, itemValue ) {
		var name = String( tpl )
			.replace( /__PFX__/g, prefix )
			.replace( /__INDEX__/g, indexValue );
		if ( typeof itemValue !== 'undefined' ) {
			name = name.replace( /__ITEM__/g, itemValue );
		}
		return name;
	}

	function fillTemplate( tplHtml, prefix, indexValue, itemValue ) {
		var html = tplHtml
			.replace( /__PFX__/g, prefix )
			.replace( /__INDEX__/g, indexValue );
		if ( typeof itemValue !== 'undefined' ) {
			html = html.replace( /__ITEM__/g, itemValue );
		}
		return $( html );
	}

	function renumberTopLevel( $panel, prefix, panelIdx ) {
		$panel
			.children( '.irian-panel-body' )
			.find( '[data-name-tpl]' )
			.filter( function () {
				return $( this ).closest( '.irian-subrepeater' ).length === 0;
			} )
			.each( function () {
				$( this ).attr( 'name', resolveName( $( this ).data( 'name-tpl' ), prefix, panelIdx ) );
			} );
	}

	function renumberFields( $scope, prefix, indexValue, itemValue ) {
		$scope.find( '[data-name-tpl]' ).each( function () {
			$( this ).attr( 'name', resolveName( $( this ).data( 'name-tpl' ), prefix, indexValue, itemValue ) );
		} );
	}

	function renumberAll() {
		$( '.irian-panels-wrap' ).each( function () {
			var $wrap = $( this );
			var prefix = String( $wrap.data( 'prefix' ) );
			$wrap.children( '.irian-panels-list' ).children( 'li.irian-panel' ).each( function ( panelIdx ) {
				var $panel = $( this );
				renumberTopLevel( $panel, prefix, panelIdx );
				$panel.find( '.irian-subrepeater' ).each( function () {
					$( this )
						.find( '> .irian-subrepeater-list > li.irian-subitem' )
						.each( function ( itemIdx ) {
							renumberFields( $( this ), prefix, panelIdx, itemIdx );
						} );
				} );
			} );
		} );
		updateSubrepeaterCounts();
	}

	function updateSubrepeaterCounts() {
		$( '.irian-subrepeater' ).each( function () {
			var count = $( this ).find( '> .irian-subrepeater-list > li.irian-subitem' ).length;
			$( this ).find( '> .irian-subrepeater-label > .irian-subrepeater-count' ).text( count );
		} );
	}

	$( function () {
		function ensureSortable( $el, opts ) {
			if ( $el.data( 'ui-sortable' ) ) {
				$el.sortable( 'refresh' );
			} else {
				$el.sortable( opts || { axis: 'y', update: renumberAll } );
			}
		}

		$( '.irian-panels-wrap' ).each( function () {
			var $wrap = $( this );
			$wrap.children( '.irian-panels-list' ).sortable( { handle: '.irian-panel-handle', axis: 'y', update: renumberAll } );
			$wrap.find( '.irian-subrepeater-list' ).each( function () { ensureSortable( $( this ) ); } );
		} );

		updateSubrepeaterCounts();

		$( document ).on( 'click', '.irian-toggle-panel', function () {
			$( this ).closest( '.irian-panel' ).children( '.irian-panel-body' ).slideToggle( 150 );
		} );

		$( document ).on( 'click', '.irian-remove-panel', function () {
			if ( ! window.confirm( 'Dit paneel verwijderen?' ) ) { return; }
			$( this ).closest( '.irian-panel' ).remove();
			renumberAll();
		} );

		$( document ).on( 'click', '.irian-add-panel-btn', function () {
			var $wrap = $( this ).closest( '.irian-panels-wrap' );
			var $list = $wrap.children( '.irian-panels-list' );
			var prefix = String( $wrap.data( 'prefix' ) );
			var type = $wrap.find( '.irian-add-panel-type' ).val();
			var tplHtml = $( '#tmpl-panel-' + type ).html();
			if ( ! tplHtml ) { return; }
			var nextIndex = $list.children( '.irian-panel' ).length;
			var $li = fillTemplate( tplHtml, prefix, nextIndex );
			$list.append( $li );
			$list.sortable( 'refresh' );
			$li.find( '.irian-subrepeater-list' ).each( function () { ensureSortable( $( this ) ); } );
			initMediaButtons( $li );
		} );

		$( document ).on( 'click', '.irian-add-item', function () {
			var $wrap = $( this ).closest( '.irian-panels-wrap' );
			var $repeater = $( this ).closest( '.irian-subrepeater' );
			var $panel = $( this ).closest( '.irian-panel' );
			var prefix = String( $wrap.data( 'prefix' ) );
			var panelIndex = $wrap.children( '.irian-panels-list' ).children( '.irian-panel' ).index( $panel );
			var type = $panel.data( 'type' );
			var tplHtml = $( '#tmpl-item-' + type ).html();
			if ( ! tplHtml ) { return; }
			var $itemsList = $repeater.children( '.irian-subrepeater-list' );
			var nextItemIndex = $itemsList.children( '.irian-subitem' ).length;
			var $li = fillTemplate( tplHtml, prefix, panelIndex, nextItemIndex );
			$itemsList.append( $li );
			ensureSortable( $itemsList );
			initMediaButtons( $li );
			updateSubrepeaterCounts();
		} );

		$( document ).on( 'click', '.irian-remove-item', function () {
			$( this ).closest( '.irian-subitem' ).remove();
			renumberAll();
		} );

		function initMediaButtons( $scope ) {
			$scope.find( '.irian-select-image' ).on( 'click', function ( e ) {
				e.preventDefault();
				var $field = $( this ).closest( '.irian-image-field' );
				var frame = wp.media( { title: 'Kies een afbeelding', multiple: false } );
				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					$field.find( '.irian-image-id' ).val( attachment.id );
					$field.find( '.irian-image-preview img' ).attr( 'src', attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url );
					$field.find( '.irian-image-preview' ).show();
					$field.find( '.irian-remove-image' ).show();
				} );
				frame.open();
			} );

			$scope.find( '.irian-remove-image' ).on( 'click', function ( e ) {
				e.preventDefault();
				var $field = $( this ).closest( '.irian-image-field' );
				$field.find( '.irian-image-id' ).val( '' );
				$field.find( '.irian-image-preview' ).hide();
				$( this ).hide();
			} );
		}
		initMediaButtons( $( document ) );

		$( '#post' ).on( 'submit', renumberAll );
	} );
} )( jQuery );
JS;

	wp_add_inline_script( 'jquery', $inline_js );

	$inline_css = <<<'CSS'
/* Metaboxes-container is de volle editorbreedte (er is geen block-content,
 * alles loopt via panels) - zonder ruime max-width blijft dat een smalle
 * strook velden met een enorme lege vlakte ernaast op brede schermen. */
.irian-panels-wrap { max-width: 1200px; }
.irian-channel-note { max-width: 1200px; margin: 0 0 20px; padding: 12px 16px; background: #f0f6fc; border: 1px solid #c5dcf0; border-radius: 6px; color: #1d2327; line-height: 1.5; }
.irian-channel-note code { background: rgba( 0, 0, 0, 0.06 ); padding: 1px 5px; border-radius: 3px; }

.irian-panels-list { margin: 0 0 18px; padding: 0; list-style: none; }

.irian-panel { border: 1px solid #dcdcde; border-radius: 8px; background: #fff; margin-bottom: 18px; box-shadow: 0 1px 2px rgba( 0, 0, 0, 0.04 ); overflow: hidden; }
.irian-panel-header { display: flex; align-items: center; gap: 12px; padding: 14px 18px; background: #f6f7f7; border-bottom: 1px solid #dcdcde; }
.irian-panel-handle { cursor: grab; color: #8c8f94; font-size: 18px; line-height: 1; }
.irian-panel-handle:active { cursor: grabbing; }
.irian-panel-title { font-weight: 600; font-size: 14px; flex: 1; color: #1d2327; }
.irian-panel-header .button-link { font-size: 13px; padding: 4px 8px; border-radius: 4px; text-decoration: none; }
.irian-panel-header .button-link:hover { background: rgba( 0, 0, 0, 0.05 ); }
.irian-remove-panel { color: #b32d2e; }
.irian-remove-panel:hover { background: rgba( 179, 45, 46, 0.08 ) !important; }

.irian-panel-body { padding: 22px 22px 24px; }

.irian-field { display: block; margin-bottom: 22px; }
.irian-field:last-child { margin-bottom: 0; }
.irian-field span { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #1d2327; }
/* Inputs blijven leesbaar breed (niet uitrekken tot 1400px) ook al is de
 * wrap eromheen nu ruim - een regel van 1 meter breed leest niemand prettig. */
.irian-field input[type="text"], .irian-field input[type="url"], .irian-field input[type="email"], .irian-field textarea, .irian-field select { width: 100%; padding: 8px 10px; border-radius: 4px; }
.irian-field input[type="text"], .irian-field input[type="url"], .irian-field input[type="email"] { max-width: 640px; }
.irian-field select { max-width: 420px; }
.irian-field textarea { max-width: 820px; line-height: 1.5; }
.irian-field.irian-inline { display: flex; align-items: center; gap: 8px; }

.irian-image-preview img { max-width: 180px; height: auto; display: block; margin-bottom: 8px; border: 1px solid #dcdcde; border-radius: 4px; }

.irian-subrepeater { background: #f9f9fa; border: 1px solid #e2e2e3; border-radius: 8px; padding: 18px; margin: 24px 0; }
.irian-subrepeater-label { display: flex; align-items: center; gap: 8px; font-weight: 700; margin-bottom: 14px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; color: #50575e; }
.irian-subrepeater-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 10px; background: #dcdcde; color: #1d2327; font-weight: 600; font-size: 11px; text-transform: none; letter-spacing: normal; }
.irian-subrepeater-list { margin: 0 0 14px; padding: 0; list-style: none; }
.irian-subitem { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px 46px 6px 16px; margin-bottom: 12px; position: relative; }
.irian-subitem:last-child { margin-bottom: 0; }
.irian-subitem .irian-field { margin-bottom: 16px; }
.irian-subitem .irian-remove-item { position: absolute; top: 12px; right: 12px; color: #b32d2e; font-size: 12px; padding: 4px 6px; border-radius: 4px; }
.irian-subitem .irian-remove-item:hover { background: rgba( 179, 45, 46, 0.08 ); }

.irian-add-item { display: inline-flex; align-items: center; gap: 6px; }
.irian-add-item::before { content: "+"; font-weight: 700; }

.irian-add-panel-row { display: flex; gap: 10px; align-items: center; padding: 16px 18px; background: #f6f7f7; border: 1px dashed #a7aaad; border-radius: 8px; }
.irian-add-panel-type { min-width: 220px; }
.irian-add-panel-btn { display: inline-flex; align-items: center; gap: 6px; }
.irian-add-panel-btn::before { content: "+"; font-weight: 700; }
CSS;

	wp_register_style( 'irian-panels-admin-inline', false );
	wp_enqueue_style( 'irian-panels-admin-inline' );
	wp_add_inline_style( 'irian-panels-admin-inline', $inline_css );
}
add_action( 'admin_enqueue_scripts', 'irian_panels_enqueue_admin_assets' );
