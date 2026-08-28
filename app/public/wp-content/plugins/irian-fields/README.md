# Irian Fields

Eigen, gratis custom-fields systeem voor WordPress. Vult het gat dat de gratis
ACF laat vallen: **repeater**, **flexible content** en **herbruikbare veldgroepen
per posttype/sjabloon**, zonder ACF PRO, zonder build-tools, alles in gewone
PHP + een beetje jQuery (uit WP core).

Losstaand van het thema: veldgroepen en hun waardes blijven bestaan als je een
ander thema activeert.

---

## Snel starten

1. **Plugins → Irian Fields → Nieuwe veldgroep.**
2. Geef de groep een titel (bv. "Pagina, losse velden").
3. **Velden**: klik *+ Veld toevoegen*, kies een type, geef een label
   (de veldnaam/meta-key wordt automatisch afgeleid, die gebruik je straks in
   de template).
4. **Locatie**: stel in waar de groep verschijnt. Alle regels moeten kloppen,
   bijv. `Posttype is gelijk aan Pagina`.
5. **Publiceren.**
6. Open een pagina die aan de locatie voldoet. Onder de editor staat nu een
   meta box met jouw velden. Vul in, **Bijwerken**.
7. Toon de waardes in je thema met de `irf_*` helpers (zie onder).

> Na het wijzigen van locatie-parameters even **Bijwerken** klikken zodat de
> keuzelijst voor de waarde ververst.

---

## Veldtypen

| Type | Opslag (`irf_get_field`) |
|---|---|
| Tekst / Tekstvak / Getal / E-mail / URL / Datum / Kleur | string |
| WYSIWYG-editor | HTML-string (`wp_kses_post`) |
| Ja / nee | `1` of `0` |
| Keuzelijst / Radio | gekozen waarde (string) |
| Checkboxes | array van waardes |
| Afbeelding / Bestand | attachment-ID (int), via `irf_get_image()` / `irf_get_image_url()` |
| Link | `['url' => , 'text' => , 'target' => ]`, via `irf_get_link()` |
| Groep | associatieve array van subvelden |
| Repeater | lijst van rijen (elke rij = array subvelden) |
| Flexible content | lijst van blokken; elk blok heeft `__layout` = layoutnaam |
| Tab / Bericht | geen waarde, puur presentatie in de editor |

Subvelden (in groep/repeater/flexible layout) mogen zelf **geen** container zijn:
één niveau diep. Voor de meeste gevallen ruim voldoende; diepere nesting is
ook in ACF een bron van ellende.

---

## Template-API

```php
// Enkel veld
echo esc_html( irf_get_field( 'ondertitel' ) );

// WYSIWYG (niet nog eens escapen)
echo irf_get_field( 'intro' );

// Afbeelding
echo irf_get_image( irf_get_field( 'header_beeld' ), 'large' );
$url = irf_get_image_url( irf_get_field( 'header_beeld' ), 'full' );

// Link
echo irf_get_link( irf_get_field( 'cta' ) );

// Repeater
if ( irf_have_rows( 'kenmerken' ) ) {
    echo '<ul>';
    while ( irf_have_rows( 'kenmerken' ) ) { irf_the_row();
        printf( '<li>%s</li>', esc_html( irf_get_sub_field( 'naam' ) ) );
    }
    echo '</ul>';
}

// Flexible content
foreach ( (array) irf_get_field( 'blokken' ) as $blok ) {
    if ( 'citaat' === $blok['__layout'] ) {
        printf( '<blockquote>%s<cite>%s</cite></blockquote>',
            esc_html( $blok['quote'] ), esc_html( $blok['bron'] ) );
    }
}

// Andere post dan de huidige
irf_get_field( 'ondertitel', 42 );
```

Beschikbaar: `irf_get_field`, `irf_the_field`, `irf_get_fields`,
`irf_have_rows`, `irf_the_row`, `irf_get_row`, `irf_get_row_layout`,
`irf_get_sub_field`, `irf_the_sub_field`, `irf_get_image`, `irf_get_image_url`,
`irf_get_link`.

---

## Waar staat wat

| Bestand | Rol |
|---|---|
| `irian-fields.php` | bootstrap, constants, requires |
| `includes/helpers.php` | losse hulpfuncties (keys, choices-parsing, slugs) |
| `includes/class-irf-fields.php` | veldtypen: invoer renderen + waarde opschonen (recursief) |
| `includes/class-irf-group.php` | CPT `irf_group`, definities inlezen, locatie-matching |
| `includes/class-irf-admin.php` | de veldgroep-editor (velden + locatie + weergave) |
| `includes/class-irf-render.php` | meta boxes tonen op bewerkschermen + opslaan |
| `includes/api.php` | de `irf_*` template-helpers |
| `assets/*.css` / `assets/*.js` | admin-styling en -gedrag (repeater, media, wysiwyg) |

Opslag:
- **Definities**: post meta op de `irf_group`-post: `_irf_fields`,
  `_irf_location`, `_irf_settings`.
- **Waardes**: post meta op de doel-post: `_irf_values_{groep_id}`, als één
  geneste array per groep.

Bestanden zijn klein en los gehouden zodat ze via de wp-admin **Plugin File
Editor** te bewerken zijn. Op deze Local-omgeving houdt de mu-plugin
`wp-content/mu-plugins/irian-local-editor-fix.php` die edits vast (WordPress'
loopback-check faalt hier anders en draait wijzigingen terug).

---

## Bekende grenzen (v0.1.0)

- Locatie-regels zijn alleen **EN** (geen OF-groepen zoals ACF).
- Subvelden: één niveau diep.
- Geen conditionele logica ("toon veld X als Y = ...").
- Geen import/export van veldgroepen (het zijn posts, `wp export` werkt wel).
- Waardes zijn niet als losse meta-keys queryable; gebruik `irf_get_field()`.
