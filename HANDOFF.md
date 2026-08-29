# Handoff - Irian's portfoliowebsite (WordPress, lokaal via Local)

Laatste update: 2026-08-29 (sessie 3). Dit document vervangt de vorige handoff en
beschrijft de volledige stand van zaken. Sessie 2: de open follow-ups uit
sectie 7 afgewerkt (EN-metabox in wp-admin, contact-adres, title-separator,
em-dashes uit de dev-docs, git-repo), de Platforms-copy verrijkt, de
Konami-easter-egg volledig verwijderd, de "WordPress snippet library"-module
hernoemd naar "Eigen WordPress-toolkit", en de opdrachtgever-namen uit de git-
historie geschrobd. Zie sectie 5c. Sessie 3: git-remote gekoppeld en gepusht,
Platforms sectie-intro gecorrigeerd. Zie sectie 5d. Theme assets nu op `0.21.0`.

Geen em-dashes gebruiken. Nooit. (Harde eis van Irian, geldt overal: content,
code-commentaar, alles.)

---

## 1. Wat het is

Persoonlijke portfoliowebsite van Irian Veensma. Positionering: webdeveloper x
marketeer x digital, gevestigd in Friesland (het woord "Friesland" staat bewust
niet meer in de hero). De site zelf IS het portfolio: de techniek, de details en
de experimenten zijn het bewijs, niet een lijst klanten.

Slechts twee klantprojecten mogen met naam getoond worden:
- Pedicure Paulina (pedicure-paulina.nl) - volledige opdracht (design + dev + hosting)
- Sita Design (sitadesign.nl) - volledige opdracht

Ander klantwerk blijft verborgen. Twee grotere eigen/werk-builds mogen wel
verteld worden, in de Platforms-sectie (zie 5).

### Designrichting

- Donker, hoog contrast, metallic (chroom / gunmetal). GEEN neon-accenten.
- "Full black is out of the question" - gunmetal, niet dood zwart. Basis `#15171c`.
- 3D-relief (uitgefreesde "put") ALLEEN op: knoppen, de blokken van Work, de
  blokken van Modules en de kaarten van Platforms. De rest van de site is plat.
- Fonts: Space Grotesk (koppen), Inter (body), JetBrains Mono (code / labels).
- Schrijfstijl: nuchter, direct, geen corporate taal.

### Harde eisen van Irian (niet overschrijven)

- Geen em-dashes, nooit, nergens.
- Geen e-mailadres zichtbaar op de site (`hello@irianveensma.nl` is alleen de
  server-side ontvanger van het formulier).
- Donker palet behouden; de lichtere "plaat" achter secties is afgekeurd.
- 3D alleen op de vier bloktypes hierboven, niet site-breed.

---

## 2. Omgeving en hoe je dingen draait

Site draait in Local (localwp.com). Site "IriyinPort", domein `iriyinport.local`,
wp-admin login `dev` / `dev`. Statische voorpagina = pagina ID 9, template
`irian-portfolio-theme` / `page-home.php`.

### wp-cli / PHP (geen wp/php/mysql op PATH)

```
PHP="C:/Users/Irian Veensma/AppData/Roaming/Local/lightning-services/php-8.2.29+0/bin/win64/php.exe"
INI="C:/Users/Irian Veensma/AppData/Roaming/Local/run/poHbqraJn/conf/php/php.ini"
WPCLI="C:/Program Files (x86)/Local/resources/extraResources/bin/wp-cli/wp-cli.phar"
WP="C:/Users/Irian Veensma/Local Sites/iriyinport/app/public"
"$PHP" -c "$INI" -d error_reporting=0 -d display_errors=0 "$WPCLI" --path="$WP" <args>
```

Wrapper: `C:/Users/IRIANV~1/AppData/Local/Temp/claude/wp.sh` (scratchpad, kan
gewist zijn tussen sessies - opnieuw maken uit bovenstaande).

- Site moet draaien in Local (nginx :10004, mysql :10005, Mailpit :10000).
- `poHbqraJn` is het Local site-id.
- `php_imagick.dll` startup warning is onschuldig.
- `wp db query` / `wp db export` werken niet (geen mysql-client op PATH). Voor SQL
  `wp eval` met `$wpdb`, of zet
  `AppData/Roaming/Local/lightning-services/mysql-8.4.0+2/bin/win64/bin` op PATH.
- `wp config set/delete` werkt voor WP_DEBUG togglen.
- **Theme-bestanden altijd op schijf bewerken.** De in-browser Theme File Editor
  draait terug op Local (loopback-scrape faalt). De mu-plugin hieronder verhelpt
  dat grotendeels, maar op schijf bewerken is de veilige route.

### Screenshots

`save_to_disk`, mshots en thum.io werken NIET in deze omgeving. Wel werkend:
Edge headless CLI. Output-pad moet een echt Windows-pad zijn (backslashes),
niet een forward-slash pad, anders "Access is denied".

```
OUT='C:\Users\IRIANV~1\AppData\Local\Temp\claude\scratchpad\shot.png'
"/c/Program Files (x86)/Microsoft/Edge/Application/msedge.exe" --headless=new \
  --disable-gpu --hide-scrollbars --force-device-scale-factor=1 \
  --virtual-time-budget=9000 --run-all-compositor-stages-before-draw \
  --window-size=1400,7800 --screenshot="$OUT" "http://iriyinport.local/"
```

`--virtual-time-budget` is essentieel zodat de reveal-animaties gedraaid hebben.
Beeld = window-size x device-scale-factor. Croppen kan met GD via de bundled PHP
(`imagecreatefrompng` / `imagecrop`). Meerdere Edge-processen tegelijk botsen op
de user-data-dir; draai ze na elkaar.

Voor interactie-checks werkt de in-app browser pane (`mcp__Claude_Browser__*`).
De Chrome-pane composit soms niet; val dan terug op Edge + `javascript_tool` /
`get_page_text` / curl.

---

## 3. Thema-architectuur

`app/public/wp-content/themes/irian-portfolio-theme/` - actief thema. Geen build
tools. ACF-plugin staat uit, classic-editor aan.

### Panels-systeem (eigen "flexible content", geen ACF)

Volledig inline in `functions.php` gebouwd (metabox + jQuery UI Sortable + wp.media)
zodat alles via de Theme File Editor bewerkbaar zou zijn. Data staat als array in
post meta `_irian_panels` op pagina 9. `page-home.php` loopt de panels af en doet
per stuk `get_template_part( 'template-parts/panel', $type, $data )`.

Paneeltypes (in deze volgorde op de voorpagina):
`hero`, `stack`, `work_grid`, `projects`, `lab_grid`, `faq`, `contact`.

Elk type heeft: een `case` in `irian_render_panel_fields()` (metabox-velden), een
`case` in `irian_sanitize_panel_data()` (opslaan), en `template-parts/panel-<type>.php`
(front-end). Subrepeaters (work-items, lab-items, faq-items, project-items) hebben
een `irian_render_*_item()` functie plus een `<script type="text/html" id="tmpl-item-<type>">`.

**Bekende valkuil (opgelost, niet opnieuw introduceren):** `irian_field_name()`
moet `data[items][0][name]` omzetten naar `panels[0][data][items][0][name]`. Deed
dat eerst fout (`panels[0][data[items]...]`) waardoor `_irian_panels` bij de
eerste "Update" gewist werd. Fix staat in de functie:
`str_replace( '[', '][', str_replace( ']', '', $field_path ) )`.

### irian-fields plugin

`wp-content/plugins/irian-fields/` - eigen ACF-kloon (CPT `irf_group`, alle
veldtypes incl. repeater / flexible_content / group, location rules, template-API
`irf_get_field` / `irf_have_rows` / `irf_get_sub_field`). Waarden in
`_irf_values_{group_id}`. End-to-end getest. Zie de plugin-README.
**De voorpagina gebruikt dit NIET** - die draait op het `_irian_panels` systeem.
Optionele opschoning ooit: `page-home.php` herbouwen op een irian-fields
flexible_content group.

### mu-plugin loopback-fix

`wp-content/mu-plugins/irian-local-editor-fix.php` - beantwoordt WP's interne
loopback-scrape via `pre_http_request` zodat de wp-admin File Editor niet meer
terugdraait op Local. Gated op `wp_get_environment_type() === 'local'`.

---

## 4. Media

| ID | Inhoud |
|----|--------|
| 18 | Foto van Irian (hero, rechts) |
| 19 | Pedicure Paulina, desktop-screenshot |
| 20 | Pedicure Paulina, mobiel-screenshot |
| 21 | Sita Design, desktop-screenshot |
| 22 | Sita Design, mobiel-screenshot |
| 23 | IRIYIN favicon (ook site_icon) |

Screenshots 19-22 zijn first-pass (Edge headless). Irian kan mooiere uploaden via
Media Library en de `visual` / `visual_mobile` velden van de work-items aanpassen.
De pp-desktop shot toont de "geen nieuwe klanten"-balk van de echte site.

---

## 5. Wat er in dit gesprek is gebouwd

### 5a. Platforms-sectie (nieuw paneeltype `projects`)

Tussen Work en Modules. Sectie-id `#platforms`, nav-link "Platforms", command-
palette-actie. Gestapelde `.ipb-project-card`'s met dezelfde uitgefreesde
behandeling als de Work-blokken.

Panel-velden per project: `name`, `tagline` (eenregelig), `tags` (losse string,
bv. "AI · FROM SCRATCH · TEAM TOOL"), `description` (textarea, wpautop), `features`
(array, een per regel, bullets met `+`), `roles` (array, chips), `url` (optioneel,
"Bekijk de site" link), `image` (optioneel).

Bestanden: `case 'projects'` in `irian_render_panel_fields()` +
`irian_sanitize_panel_data()`, `irian_render_project_item()`,
`#tmpl-item-projects`, `template-parts/panel-projects.php`, CSS-blok
`.ipb-project-*` in `assets/panels.css`.

Twee items:
- **Prompt Studio** - interne AI-werkomgeving voor Irians marketingteam, van
  scratch met AI gebouwd. Features als bullets: afbeeldingen/video's genereren,
  captions + social posts met AI, achtergronden weghalen, brand-voice bots,
  interne teamchat, beeldbank. Geen URL (intern).
- **Nieuws Website** - geanonimiseerd (een bestaande nieuws-/vaksite van een
  opdrachtgever; Irian wil de echte naam en URL nergens tonen). Vier panelen als
  role-chips: Admin / Redactie / Partner / Voorkant. Geen URL. In het Engels heet
  dit "News Website".

Sectielabels hernummerd: Work 01, Platforms 02, Modules 03, FAQ 04, Contact 05.

### 5b. NL/EN taalknop

Pill rechts in de nav: toont "EN" op de NL-site, "NL" op de EN-site. Klik gaat
naar `?lang=en` respectievelijk `?lang=nl`.

Mechaniek in `inc/i18n.php`:
- `irian_lang()` - `$_GET['lang']` (in {nl,en}) wint, anders cookie `irian_lang`,
  anders 'nl'. `irian_is_en()` als snelle check.
- `irian_lang_persist()` op `init` - zet het cookie (1 jaar) zodra `?lang=` meekomt,
  zodat een kale reload of de form-redirect de keuze niet verliest.
- `irian_lang_switch_url()` - huidige URL met de taal omgezet.
- `irian_strings()` / `irian_str( $key )` - vaste thema-teksten per taal (nav,
  palette, formulier, footer, module-demo's, skip-link, aria-labels).
- `irian_panels_data( $post_id )` - geeft `_irian_panels_en` terug bij EN als die
  niet leeg is, anders `_irian_panels`. `page-home.php` gebruikt dit.
- Filters: `language_attributes` (`<html lang="en-US">` / `nl-NL`) en
  `document_title_parts` (tagline in de `<title>`).

Aangepast om via `irian_str()` te lopen: `header.php`, `footer.php`,
`template-parts/panel-work_grid.php`, `panel-projects.php`, `panel-lab_grid.php`,
`inc/contact-form.php`, `inc/module-demos.php`. `assets/site.js` krijgt zijn
palette-labels + console-tekst via `wp_localize_script( 'irian-site', 'irianI18n', ... )`
met NL-fallback als het object ontbreekt.

CSS: `.ipb-nav-tools` (flex-wrapper om taalpill + kbd-hint) en `.ipb-lang` in
`assets/site.css`. Taalpill blijft zichtbaar op mobiel (nav-links verdwijnen < 780px).

`inc/contact-form.php`: de redirect na verzenden krijgt `lang=en` mee bij EN.
`irian_contact_recipient()` leest het adres altijd uit de NL-panels - dus de
ontvanger klopt ook in EN-modus.

**Engelse content** staat in post meta `_irian_panels_en` op pagina 9. Sinds
sessie 2 (zie 5c) is die ook via wp-admin te bewerken: de tweede metabox
"Homepage Panels (EN) - vertaling". De JSON-import blijft werken. Bron:
`C:/Users/IRIANV~1/AppData/Local/Temp/claude/panels-en.json`. NL-bron:
`panels.json` in dezelfde map. Importeren:

```
bash wp.sh eval-file "C:/Users/IRIANV~1/AppData/Local/Temp/claude/import-panels.php"      # NL  -> _irian_panels
bash wp.sh eval-file "C:/Users/IRIANV~1/AppData/Local/Temp/claude/import-panels-en.php"   # EN  -> _irian_panels_en
```

(Beide import-scripts staan in de scratchpad. Recreeren: `update_post_meta( 9,
'_irian_panels' [of _en], json_decode( file_get_contents( '<pad>.json' ), true ) )`.)

Theme-assetversie staat nu op `0.21.0` (4 plekken in `functions.php`).

### Konami-easter-egg verwijderd

Irian wilde 'm eruit, allebei de varianten:
- Het verborgen globale toets-easter-egg in `site.js` (keydown-listener +
  `filter: invert()` op de body) is weg.
- De "Konami-code easter egg"-tegel in de Modules-sectie is weg (lab_grid-item
  uit `_irian_panels` + `_irian_panels_en` + de JSON-bronnen). Modules heeft nu
  5 tegels. `initKonamiDemo()`, `case 'konami'` in `module-demos.php`, de
  `demo_konami_*` strings, de `konami`-optie in de admin-dropdown +
  `$allowed_demo`, en het `.ipb-demo--konami` / `.ipb-keycap` CSS-blok zijn
  allemaal verwijderd.

Over: het `console.log`-easter-egg en de Cmd+K command palette.

---

## 5c. Sessie 2 (2026-08-28) - open follow-ups + Platforms-copy

### EN-content bewerkbaar in wp-admin

`functions.php` registreert de panels-editor nu twee keer:

- "Homepage Panels (NL)" -> post meta `_irian_panels`, form-prefix `panels`
- "Homepage Panels (EN) - vertaling" -> `_irian_panels_en`, prefix `panels_en`

Leeg laten = de NL-content wordt overal getoond. Zodra er EN-panelen staan
vervangen die de NL-versie op `?lang=en` (`irian_panels_data()`, ongewijzigd).

Mechaniek:
- `irian_panels_active_prefix( $set = null )` - static, houdt bij welke metabox
  nu rendert ('panels' / 'panels_en' / '__PFX__' voor de templates).
- `irian_field_name()` zet in `data-name-tpl` de tokens `__PFX__` (prefix) en
  `__INDEX__` (paneelpositie). De inline admin-JS lost die op per
  `.irian-panels-wrap[data-prefix]`. `renumberAll()` loopt over beide wrappers.
- Eén set `<script type="text/html">` templates voor beide metaboxen
  (`irian_panels_render_templates()`, `static $done`-guard). Templates dragen
  `__PFX__` ook in `name=`, de JS vult 'm in bij toevoegen.
- `irian_panels_channels()` + `irian_panels_save()` loopt over de twee kanalen
  en checkt per kanaal de eigen nonce (`irian_panels_nonce` /
  `irian_panels_en_nonce`). Kanaal zonder geldige nonce wordt overgeslagen.

**Valkuil (opgelost, niet opnieuw introduceren):** de contact-`show_form`
checkbox had een hardgecodeerde `name="panels[{$index}][data][show_form]"` in
plaats van via `irian_field_name()`. Met twee metaboxen botste dat: de EN-
checkbox schreef in NL's `panels[6]...` en zette bij opslaan NL's `show_form` op
0. Nu via nieuwe helper `irian_checkbox_field()` (hidden + checkbox delen naam en
tpl). **Regel: elk metabox-veld loopt via `irian_field_name()` of
`irian_checkbox_field()`, nooit via een handmatige `panels[...]` string.**

Getest via wp-admin: los bewerken van EN, sub-item toevoegen in EN, meermaals
opslaan (idempotent), geen kruisbesmetting met NL, en de eerdere data-wipe-bug
komt niet terug.

### Contact-ontvanger

Nu `irianveensma@gmail.com` (was `hello@irianveensma.nl`, mockup-adres). Gezet in
`_irian_panels` + `_irian_panels_en` (contact-panel) en in `panels.json` /
`panels-en.json`. `irian_contact_recipient()` leest 'm server-side uit de NL-
panels; het adres staat niet in de HTML.

### `<title>`-scheidingsteken

Nu `·` (middot, gelijk aan de tagline-puntjes) i.p.v. de WP-default en-dash.
Filter `document_title_separator` in `inc/i18n.php`.

### Em-dashes uit de dev-docs

Weg uit `README.md` (thema, herschreven want stale), `index.php`, `style.css`,
de mu-plugin, en de hele `irian-fields`-plugin (comments + de UI-strings
`- kies -` / `- Tab / sectie -` / lege-waarde `-`). Front-end/`functions.php`/
`page-home.php` waren al schoon.

### Platforms-copy verrijkt

Prompt Studio en Nieuws Website hebben rijkere tagline/description/features
gekregen (NL + EN, live meta + JSON-bronnen). **Blijft geanonimiseerd**: Irian
wil de echte opdrachtgevers en URL's nergens tonen. Op de site zelf:
- Prompt Studio = besloten AI-omgeving voor een horecagroothandel. Toegang per
  account voor geautoriseerd personeel + ketenpartners; elke generatie kost geld,
  dus bewust gebruik. Op de site: "collega's en ketenpartners", "een horecabranche".
- Nieuws Website = een bestaande vaknieuws-/kennissite van een opdrachtgever, met
  vier omgevingen (admin / redactie / partner / voorkant). Op de site: "een
  horecabranche", geen naam, geen URL.

### Modules: "WordPress snippet library" verduidelijkt

Titel was te vaag voor een leek. Nu **"Eigen WordPress-toolkit"** (EN: "My
WordPress toolkit") met een blurb in gewone taal: een eigen verzameling
herbruikbare stukjes WordPress-code, scheelt tijd en fouten. Code-voorbeeld
ongewijzigd. Gewijzigd in `_irian_panels` + `_irian_panels_en` + de JSON-bronnen.

### Git-repo

`git init` in de projectroot (`C:/Users/Irian Veensma/Local Sites/iriyinport`).
Whitelist-`.gitignore`: alleen `irian-portfolio-theme`, `irian-fields`,
`mu-plugins` + root `README.md` / `HANDOFF.md` / `.gitattributes` /`.gitignore`.
`.gitattributes` normaliseert regeleindes naar LF. Geen remote (push kan niet:
geen remote, geen `gh` CLI; Irian koos "lokaal laten").

De volledige historie is met `git filter-branch` herschreven om
opdrachtgever-namen (`Frituurwereld`, `VHC`) uit alle oude commit-diffs te halen,
ook uit `panel-projects.php`-comments. Alle commit-hashes zijn daardoor veranderd.
Huidige log (5 commits, branch `master`):

```
bd5242f  panel-projects.php: opdrachtgever-naam uit code-comments
352d042  HANDOFF: opdrachtgevers geanonimiseerd
76bc889  Konami-easter-egg volledig verwijderd
cee62fb  HANDOFF: sessie 2 gedocumenteerd
44328d1  Eerste commit: eigen thema, irian-fields plugin, mu-plugin
```

---

## 5d. Sessie 3 (2026-08-29) - remote gekoppeld, Platforms-intro gecorrigeerd

### Git-remote

`origin` gekoppeld aan `https://github.com/Irianveensma/PF-Iriyin.git` en de
lokale `master` (6 commits) gepusht (`-u`, dus tracked). `gh` CLI is niet
beschikbaar; gewoon `git push` via de credential-manager.

### Platforms sectie-intro gecorrigeerd

De intro zei nog "één voor mijn werk, één als eigen project" - klopte niet meer
sinds Nieuws Website ook als opdrachtgever-werk beschreven staat (zie 5a: geen
van beide items is nog een puur persoonlijk project). Aangepast naar een tekst
zonder de werk/prive-splitsing:

- NL: "Twee grotere dingen die ik gebouwd heb, allebei van de grond af opgezet,
  met AI door het hele proces heen."
- EN: "Two bigger things I built, both set up from scratch, with AI throughout
  the whole process."

Dit is content, geen code: aangepast via `update_post_meta()` direct op
`_irian_panels[3].data.section_intro` en `_irian_panels_en[3].data.section_intro`
(pagina 9, paneel-index 3 = het `projects`-paneeltype). Niet in de repo, dus geen
commit hiervoor nodig. **Let op:** de JSON-content-bronnen (`panels.json` /
`panels-en.json` in de scratchpad, zie sectie 8) zijn hier NIET in bijgewerkt -
als die ooit opnieuw geimporteerd worden overschrijven ze deze fix.

Geverifieerd via curl op NL en EN: HTTP 200, nieuwe tekst zichtbaar, debug.log
leeg.

---

## 6. Eerdere designronde (na Figma-feedback, 2026-08-27/28)

Volledige historie in `figma-feedback-2026-08-27.md` (memory). Kort:

- Name-hero met foto rechts. Gunmetal palet, donker. Logo in de nav
  (`assets/logo-mark.svg`) + favicon (`assets/favicon.svg`).
- Klikbare skill-tags met line-art per skill (`inc/skill-visuals.php`).
- Work: MacBook + telefoon device-mockups met echte screenshots.
- "Experiments" -> "Modules": elke tegel klikbaar -> paneel met echte inhoud
  (code-snippet / live demo / SEO-rapport). `inc/module-demos.php`, deeplink
  `?module=N`.
- FAQ-sectie (`faq` paneltype, native `<details>`-accordion).
- Contactformulier (`contact` paneltype: `show_form`, `project_types`, `form_note`).
  Handler `inc/contact-form.php` via `admin-post.php` + honeypot (`website`) +
  tijd-check (`irian_t`) + `wp_mail()`. Op Local -> Mailpit. Geen adres in de HTML.
- `box-sizing: border-box` globaal in `site.css` (inputs liepen anders over hun
  grid-kolom; naam/e-mail overlapten).

---

## 7. Open punten / mogelijke follow-ups

Afgehandeld in sessie 2 (zie 5c): EN-metabox in wp-admin, contact-adres ->
gmail, title-separator -> `·`, em-dashes uit de dev-docs, git-repo.

Afgehandeld in sessie 3 (zie 5d): git-remote gekoppeld en gepusht, Platforms
sectie-intro gecorrigeerd.

Nog open:

- **Voorpagina draait op `_irian_panels`, niet op irian-fields.** Irian koos
  bewust: laten staan. Werkt en is battle-tested; herbouw = risico zonder
  zichtbare winst.
- **Platforms-items hebben geen `image`.** Optioneel: screenshots/mockups van
  Prompt Studio en de Nieuws Website toevoegen via Media Library + het
  `image`-veld per project-item.
- Mooiere work-project-screenshots uploaden via Media Library (`visual` /
  `visual_mobile` per work-item).
- `panels.json` / `panels-en.json` (contentbronnen in scratchpad) hebben de
  oude Platforms-intro nog. Bij een volgende her-import (zie sectie 5b) eerst
  bijwerken, anders overschrijft de import de sessie-3-fix.

---

## 8. Bestandsoverzicht (thema)

```
functions.php            panels-systeem: twee metaboxen (NL + EN) via
                         irian_panels_channels(), render/sanitize/save,
                         irian_field_name()/__PFX__, irian_checkbox_field(),
                         enqueue, wp_localize_script irianI18n, asset-versie 0.21.0
inc/i18n.php              NL/EN laag (irian_lang, irian_str, irian_panels_data,
                         filters: <html lang>, title-tagline, title-separator ·)
inc/skill-visuals.php     irian_skill_visual() - inline SVG per skill
inc/module-demos.php      irian_module_demo() - palette/cursor/seo-report, via irian_str()
inc/contact-form.php      irian_contact_form() + irian_handle_contact() + recipient
header.php                nav (logo, links, taalpill, kbd-hint), <html lang>, favicon
footer.php                footer + command-palette markup
page-home.php             leest irian_panels_data() en rendert de panels
template-parts/
  panel-hero.php          hero, 2-koloms met foto
  panel-stack.php         klikbare skill-tags + panelen
  panel-work_grid.php     device-mockup kaarten
  panel-projects.php      Platforms-kaarten (NIEUW)
  panel-lab_grid.php      Modules-tegels + uitklap-panelen
  panel-faq.php           <details>-accordion
  panel-contact.php       CTA + formulier
assets/
  panels.css             front-end panel-styling + :root tokens + .ipb-project-*
  site.css               nav / footer / palette / .ipb-nav-tools / .ipb-lang
  site.js                ⌘K palette, console-easter-egg, stack/modules interacties, irianI18n
  logo-mark.svg, favicon.svg, favicon-*.png, logo-full.svg
README.md                thema-uitleg (dev-doc, herschreven sessie 2)
```

Buiten het thema:
```
wp-content/plugins/irian-fields/          eigen ACF-kloon
wp-content/mu-plugins/irian-local-editor-fix.php   loopback-fix voor Local
.gitignore / .gitattributes / README.md   projectroot, in git (sessie 2)
```

Contentbronnen (scratchpad, buiten het project):
```
C:/Users/IRIANV~1/AppData/Local/Temp/claude/panels.json         -> _irian_panels
C:/Users/IRIANV~1/AppData/Local/Temp/claude/panels-en.json      -> _irian_panels_en
C:/Users/IRIANV~1/AppData/Local/Temp/claude/import-panels.php
C:/Users/IRIANV~1/AppData/Local/Temp/claude/import-panels-en.php
C:/Users/IRIANV~1/AppData/Local/Temp/claude/wp.sh
```

---

## 9. Persistente memory (voor de volgende sessie)

`C:/Users/Irian Veensma/.claude/projects/C--Users-Irian-Veensma-Local-Sites-iriyinport/memory/`
- `MEMORY.md` - index
- `portfolio-project-state.md` - stand van zaken (bijgewerkt met Platforms + taalknop)
- `local-wpcli-access.md` - wp-cli / PHP / screenshots
- `figma-feedback-2026-08-27.md` - de 8 Figma-comments en hoe ze zijn opgelost

---

## 10. Verificatie bij oplevering

- PHP-lint schoon op alle aangeraakte bestanden.
- `node -c assets/site.js` schoon.
- `panels.json` en `panels-en.json` valide JSON, geimporteerd.
- NL en EN vergeleken via curl: nav, secties, formulier, palette-placeholder,
  module-code-commentaar, `<html lang>`, `<title>`.
- Cookie-persistentie getest (`?lang=en` -> kale `/` blijft EN; `?lang=nl`
  overschrijft).
- Taalknop heen-en-terug geklikt in de browser pane (NL -> EN -> NL).
- WP_DEBUG-pass (E_ALL) op NL en EN: geen notices/warnings. WP_DEBUG weer op false,
  toegevoegde `WP_DEBUG_DISPLAY` weer verwijderd.
- Geen em-dashes in thema-bestanden, `panels.json`, `panels-en.json`.
- Geen horizontale overflow op mobiel (375px) in de Platforms-sectie.
