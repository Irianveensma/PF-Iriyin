# Irian Portfolio theme

Persoonlijk portfoliothema voor Irian Veensma. Donker, hoog contrast, metallic
(gunmetal, geen neon). De site zelf is het portfolio: de techniek en de details
zijn het bewijs.

Volledig zelfstandig: geen ACF, geen build-tools. Het panelen-systeem (de
"Homepage Panels" metaboxen) zit inline in `functions.php`.

Geen em-dashes. Nergens, ook niet in code-commentaar of docs.

## Installatie in Local

1. Zet deze map in `app/public/wp-content/themes/`.
2. Activeer via Weergave > Thema's (opnieuw activeren forceert een verse
   `functions.php`).
3. Maak of open de pagina "Home", kies sjabloon "Home (Panels)", en zet 'm als
   statische voorpagina via Instellingen > Lezen.
4. Vul de panelen in via de "Homepage Panels (NL)" box onder de editor. Sleep
   aan het greep-icoon om te herschikken, klik Bijwerken om op te slaan.

## Panelen-systeem

Eigen flexible-content-achtige oplossing. Data staat als array in post meta
`_irian_panels` (NL) en `_irian_panels_en` (EN) op de voorpagina.

Paneeltypes, in volgorde op de voorpagina: `hero`, `stack`, `work_grid`,
`projects`, `lab_grid`, `faq`, `contact`.

Elk type heeft drie plekken in `functions.php`:

- een `case` in `irian_render_panel_fields()` (de metabox-velden)
- een `case` in `irian_sanitize_panel_data()` (opslaan)
- `template-parts/panel-<type>.php` (front-end)

Subrepeaters (work-items, project-items, lab-items, faq-items) hebben een
`irian_render_*_item()` functie plus een `<script type="text/html">` template.

### Twee metaboxen (NL en EN)

`functions.php` registreert de panels-editor twee keer: "Homepage Panels (NL)"
schrijft naar `_irian_panels`, "Homepage Panels (EN)" naar `_irian_panels_en`.
Laat de EN-box leeg om overal de NL-content te tonen; zodra er EN-panelen staan,
vervangen die de NL-versie op `?lang=en` (zie `inc/i18n.php`,
`irian_panels_data()`).

Beide boxen delen een set `<script type="text/html">` templates. De templates
houden het token `__PFX__` vast voor de form-prefix (`panels` of `panels_en`);
de admin-JS vult dat in op basis van `data-prefix` op de wrapper. `__INDEX__` en
`__ITEM__` werken hetzelfde voor paneel- en itemposities.

**Valkuil (opgelost, niet opnieuw introduceren):** `irian_field_name()` zet
`data[items][0][name]` om naar `panels[0][data][items][0][name]` via
`str_replace( '[', '][', str_replace( ']', '', $path ) )`. Zonder die omzetting
parset PHP de naam fout en worden de panelen bij de eerste "Bijwerken" gewist.
Alle velden lopen via `irian_field_name()` of `irian_checkbox_field()`, nooit via
een hardgecodeerde `panels[...]` string, anders botsen de twee metaboxen.

### EN-content bewerken zonder wp-admin

De "Homepage Panels (EN)" box werkt, maar je kunt de EN-panelen ook via JSON
importeren. Bron: `panels-en.json` (NL: `panels.json`) in de scratchpad, plus
`import-panels-en.php` / `import-panels.php`. Kern:
`update_post_meta( 9, '_irian_panels_en', json_decode( ..., true ) )`.

## Bestandsoverzicht

- `functions.php` - theme setup, front-end enqueue, `wp_localize_script`
  (`irianI18n`), en het complete panelen-systeem (field renderers, twee
  metaboxen, save-handler, inline admin-JS/CSS)
- `inc/i18n.php` - NL/EN laag (`irian_lang`, `irian_str`, `irian_panels_data`,
  filters voor `<html lang>`, `<title>`-tagline en het `·`-scheidingsteken)
- `inc/skill-visuals.php` - `irian_skill_visual()`, inline SVG per skill
- `inc/module-demos.php` - `irian_module_demo()`, palette/cursor/seo-report
- `inc/contact-form.php` - formulier, handler via `admin-post.php`, honeypot +
  tijd-check + `wp_mail()`, server-side ontvanger uit de contact-panel
- `header.php` - nav (logo, links, taalpill, kbd-hint), `<html lang>`, favicon
- `footer.php` - footer + command-palette markup
- `page-home.php` - leest `irian_panels_data()` en rendert de panelen
- `template-parts/panel-*.php` - front-end template per paneeltype
- `assets/panels.css` - front-end panel-styling + `:root` tokens
- `assets/site.css` - nav, footer, command palette, `.ipb-nav-tools`, `.ipb-lang`
- `assets/site.js` - command palette, verborgen console-bericht,
  stack/modules interacties, `irianI18n`

## Sectie-ankers

`#work` (work_grid), `#platforms` (projects), `#lab` (lab_grid), `#faq` (faq),
`#contact` (contact), `#stack` (stack). Eén paneel per anker per pagina.

## Easter eggs

- Cmd+K / Ctrl+K: command palette. De knop in de nav opent 'm ook. Esc sluit,
  pijltjes plus Enter navigeren.
- `console.log`: verborgen bericht voor wie de DevTools opent.
