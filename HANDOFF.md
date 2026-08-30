# Handoff - Irian's portfoliowebsite (WordPress, lokaal via Local)

Laatste update: 2026-08-30 (sessie 4). Dit document vervangt de vorige handoff en
beschrijft de volledige stand van zaken. Sessie 2: de open follow-ups uit
sectie 7 afgewerkt (EN-metabox in wp-admin, contact-adres, title-separator,
em-dashes uit de dev-docs, git-repo), de Platforms-copy verrijkt, de
Konami-easter-egg volledig verwijderd, de "WordPress snippet library"-module
hernoemd naar "Eigen WordPress-toolkit", en de opdrachtgever-namen uit de git-
historie geschrobd. Zie sectie 5c. Sessie 3: git-remote gekoppeld en gepusht,
Platforms sectie-intro gecorrigeerd, twee Modules-titels in lekentaal herschreven
("Custom cursor interactions" -> "Eigen cursor-effect", "Command palette
component" -> "Snelmenu (⌘K)"), "AI content generator" herschreven zodat
'ie niet meer overlapt met Prompt Studio in Platforms, en de work-screenshots
(Pedicure Paulina, Sita Design) opnieuw vastgelegd (banner weg, mobile
overflow-fix). Zie sectie 5d. Sessie 4: elke Stack-tegel toont nu ook "waarom
dit ertoe doet" naast de bestaande uitleg, en WordPress + Magento zijn
samengevoegd tot één tegel "Content Management Systems" met WordPress/
Magento 2 als sub-skills, aangevuld met een derde sub-skill "Headless CMS"
(eigen AI-portalen, losgetrokken van WordPress/Magento, verwijst naar de
Nieuws Website) inclusief een eigen icoon. Zie sectie 5e. Theme assets nu op
`0.23.0`.

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

Screenshots 19-22 zijn met Edge headless gemaakt (zie 5d voor de sessie-3-fix:
banner uit de pp-shots, mobile-overflow gefixt, mobile nu 600x860 i.p.v.
400x860). Irian kan alsnog mooiere uploaden via Media Library en de `visual` /
`visual_mobile` velden van de work-items aanpassen als hij wil.

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
commit hiervoor nodig. De JSON-content-bronnen (`panels.json` / `panels-en.json`
in de scratchpad, zie sectie 8) zijn hierna ook bijgewerkt (regel 89 in beide),
dus een her-import overschrijft de fix niet meer. Beide JSON's blijven geldig
(`json_decode` gecheckt).

Geverifieerd via curl op NL en EN: HTTP 200, nieuwe tekst zichtbaar, debug.log
leeg.

### Modules-titels in lekentaal (vervolg op de snippet-library fix uit 5c)

De twee overige technische titels uit sectie 7 zijn ook herschreven, zelfde
aanpak als "Eigen WordPress-toolkit": titel simpeler, blurb en code-demo
ongewijzigd.

- **Item 2 (cursor-demo):** "Custom cursor interactions" -> "Eigen
  cursor-effect" (EN: "Custom cursor interactions" -> "Custom cursor effect").
- **Item 4 (⌘K-demo):** "Command palette component" -> "Snelmenu (⌘K)" (EN:
  "Command palette component" -> "Quick menu (⌘K)").
- Item 3 ("SEO audit tool") was al begrijpelijk genoeg, niet aangepast.

Aangepast via `update_post_meta()` op `_irian_panels[4].data.items[2/4].title`
en `_irian_panels_en[4].data.items[2/4].title` (paneel-index 4 = `lab_grid`),
plus dezelfde twee titels in `panels.json` / `panels-en.json` (regel 158/172 in
beide). Geverifieerd op de live pagina (`.ipb-lab-tile-title`): alle 5 tegels
kloppen in NL en EN, HTTP 200, debug.log leeg, beide JSON's nog geldig.

Interne/technische termen ("Command palette", "Custom cursor" in code-comments,
de admin-dropdown en aria-labels) zijn expres niet aangepast - die zijn niet
voor bezoekers, en "command palette" is de gangbare technische naam voor dat
component.

### Work-screenshots opnieuw vastgelegd

Twee losse problemen gevonden en opgelost in de Edge-headless-screenshotmethode
(zie sectie 2):

**1. Pedicure Paulina toonde een tijdelijke banner.** `pp-desktop.png` en
`pp-mobile.png` (media 19/20) waren gemaakt terwijl de live site een
"Tijdelijk geen nieuwe klanten"-melding toonde - niet representatief voor een
portfolio-screenshot. Opgelost door de nav en de hero apart te vangen (ruime
`--window-size`, pixel-scan met GD naar de kleurgrens van het bannerblok) en
zonder de banier-band aan elkaar te plakken met `imagecopy()`. Geen zichtbare
naad; achtergrondkleur van nav en pagina lopen bijna gelijk.

**2. Mobile screenshots (alle drie) waren aan de rechterkant afgesneden**, ook
op onze eigen site. Bleek geen paginabug: de eerdere `--window-size=400,...`
capture rendert wel op de juiste breedte, maar bij smalle telefoonbreedtes
(~400-450px) past nav-content (taalpil + `⌘K`-hint, of het logo van de
klantsites) niet zonder te overlopen buiten het scherm - vermoedelijk een
site-brede responsive-hiaat onder ~500px die niet eerder getest is (de
overflow-check in sectie 10 ging alleen over de Platforms-sectie op 375px, niet
de volledige nav). Pragmatische fix voor de screenshots: vangen op 600px
breedte in plaats van 400px - nog steeds de mobiele (gestapelde/hamburger)
layout, maar breed genoeg om niets af te snijden. Niet erg zichtbaar in het
eindresultaat, want `.ipb-device--phone__screen img` toont de foto via
`object-fit: cover` op een klein kader (17% van de kaartbreedte).

**Los aandachtspunt (niet opgelost, buiten scope van deze taak):** de eigen
nav lijkt onder ~500px breed al over te lopen in headless. Nog niet bevestigd
of dat ook op een echt smalle telefoon (bv. 375px) gebeurt of alleen een
headless-artefact is - de moeite waard om ooit met een echt toestel of de
in-app browser-pane te checken.

Media vervangen (bestand overschreven + `wp_generate_attachment_metadata()`
opnieuw gedraaid zodat alle WP-formaten meekomen):
- Media 19 (`pp-desktop.png`) - banner weg, 1440x1000 ongewijzigd.
- Media 20 (`pp-mobile.png`) - banner weg + overflow-fix, nu 600x860 (was
  400x860).
- Media 22 (`sd-mobile.png`) - alleen overflow-fix, nu 600x860 (was 400x860).
- Media 21 (`sd-desktop.png`, Sita Design) - ongewijzigd, was al goed.

Geverifieerd: HTTP 200, debug.log leeg, de nieuwe screenshots kloppen in de
MacBook- en telefoon-mockups op de live Work-sectie (curl + Edge-headless
full-page check).

### "AI content generator" overlapte met Prompt Studio

Irian merkte op dat de Modules-tegel "AI content generator" hetzelfde verhaal
lijkt te vertellen als Prompt Studio in Platforms (zie 5a): allebei "AI
genereert content voor je". Vergelijking gemaakt (tagline/tags/features van
Prompt Studio naast titel/blurb van de tegel) en aan Irian voorgelegd. Verschil
in scope is er wel (Prompt Studio = grote interne teamtool met accounts,
kostenbewaking, beeldbank; de tegel = één los code-snippet dat de Claude API
aanroept), maar de framing als "generator" maakte dat onduidelijk.

Gekozen aanpak: titel en blurb herschreven zodat het duidelijk een klein,
losstaand code-voorbeeld is, geen tool:

- NL titel: "AI content generator" -> "AI-snippet: productteksten". Blurb
  vooraan aangevuld met "Eén losse code-snippet, geen team-tool: ...".
- EN titel: "AI content generator" -> "AI snippet: product copy". Blurb:
  "One standalone code snippet, not a team tool: ...".

Tag (`AI · PROTOTYPE`) en het code-voorbeeld zelf ongewijzigd. Aangepast via
`update_post_meta()` op item 0 van hetzelfde `lab_grid`-paneel (NL + EN) en in
`panels.json` / `panels-en.json`. Geverifieerd: `.ipb-lab-tile-title` toont de
nieuwe tekst in beide talen, HTTP 200, debug.log leeg, JSON's nog geldig.

---

## 5e. Sessie 4 (2026-08-30) - Stack: "waarom"-uitleg + CMS-tags samengevoegd

### "Waarom dit ertoe doet" toegevoegd aan elke Stack-tegel

Elke klikbare Stack-tag (WordPress, Magento, AI Development, SEO, HTML/CSS,
JavaScript, PHP) toonde tot nu toe alleen een "note": wat de skill is. Irian
wilde daarnaast, in hetzelfde paneel, een uitleg waarom die skill ertoe doet
voor een bezoeker/opdrachtgever.

- **Data-vorm uitgebreid:** elke tag krijgt een optioneel derde veld `why`
  naast `label`/`note` (zie `template-parts/panel-stack.php` bovenaan).
- **Template:** als `why` niet leeg is, verschijnt onder de bestaande note een
  apart bloktje met een vast label ("Waarom dit ertoe doet" / "Why this
  matters", nieuwe i18n-key `stack_why_label` in `inc/i18n.php`) en de
  why-tekst.
- **CSS:** `.ipb-stack-panel__why` (`assets/panels.css`), scheidingslijntje
  boven de why-tekst, iets kleiner lettertype dan de note.
- Content (7x NL + 7x EN) ingevuld via `update_post_meta()` op
  `_irian_panels[1].data.tags[*].why` / `_irian_panels_en[1].data.tags[*].why`
  (paneel-index 1 = `stack`), en gemirrored in `panels.json` / `panels-en.json`
  (scratchpad content-bronnen, zie sectie 8).
- Magento's `why`-tekst is op verzoek herschreven van een generieke
  "waarom dit ertoe doet" naar een keuzegerichte framing ("waarom je voor een
  Magento-shop zou kiezen"): wanneer WooCommerce tekortschiet, niet in
  algemene termen.

### WordPress + Magento samengevoegd tot "Content Management Systems"

Twee losse tegels ("WordPress", "Magento") werden op verzoek samengevoegd tot
één tegel "Content Management Systems", met WordPress en Magento 2 als
sub-skills in hetzelfde paneel (Stack ging van 7 naar 6 tegels).

- **Template ondersteunt nu een `children`-array** per tag (naast het
  bestaande `note`/`why`-paar): een tag met `children` toont meerdere
  sub-skills (elk met eigen icoon, label, note en why) in één paneel in plaats
  van één losse note. Geëxtraheerd naar een kleine `$render_entry`-closure in
  `panel-stack.php` zodat de visual+label+note+why-opmaak niet dubbel
  hoeft (herbruikt voor zowel een los item als elk kind).
- **CSS:** `.ipb-stack-panel--group` / `.ipb-stack-panel__item` (`panels.css`)
  - stapelt de sub-skills verticaal met een scheidingslijn, elk sub-item
  behoudt de bestaande 260px-visual + tekst grid-layout, met een mobiele
  breakpoint (620px) net als de rest van het paneel-systeem.
- **Iconen:** `inc/skill-visuals.php` had losse SVG's per skill-slug
  ("wordpress", "magento"); het kind heet nu "Magento 2" (nieuwe slug
  "magento-2"), dus een alias toegevoegd (`$map['magento-2'] = $map['magento'];`)
  zodat hetzelfde icoon blijft resolven.
- **Content:** WordPress-note aangevuld met "10+ jaar ervaring", Magento
  2-note met "ongeveer een jaar ervaring" (ervaringsniveaus die Irian gaf).
  De `why`-teksten van beide skills zijn ongewijzigd overgenomen als
  kind-`why`. Aangepast via `update_post_meta()` op
  `_irian_panels[1].data.tags` / `_irian_panels_en[1].data.tags` (WordPress- en
  Magento-tag verwijderd, één samengevoegde tag met `children` ervoor in de
  plaats), en gemirrored in `panels.json` / `panels-en.json`.
- Asset-versie (CSS/JS cache-buster in `functions.php`) opgehoogd van
  `0.21.0` naar `0.22.0` voor deze twee front-end wijzigingen samen.

Geverifieerd: beide JSON-bronnen nog geldig (`json_decode`), curl op NL en EN
geeft HTTP 200 en toont de nieuwe why-blokken en de samengevoegde CMS-tegel,
debug.log leeg. Visueel gecontroleerd via de browser: klik op "Content
Management Systems" toont WordPress en Magento 2 correct gestapeld, elk met
eigen icoon en why-tekst.

### Derde CMS-kind toegevoegd: "Headless CMS"

Irian wilde daarnaast, los van WordPress/Magento, ook noemen dat hij met AI
een eigen headless portaal bouwt (volledig losgetrokken van een bestaand CMS)
- met de Nieuws Website (zie Platforms/5a) als voorbeeld. Toegevoegd als derde
`children`-item onder de "Content Management Systems"-tag (naast WordPress en
Magento 2), zelfde note/why-opbouw. In eerste instantie zonder eigen icoon
(geen entry in `inc/skill-visuals.php` voor "headless-cms"): de tekst viel
zonder icoon terug op een lege linkerkolom - later in dezelfde sessie alsnog
een icoon toegevoegd, zie onder.

**Layout-bug ontdekt en gefixt tijdens het testen:** zonder icoon viel de
tekst (`.ipb-stack-panel__body`) in de eerste (icoon-)kolom van het
`260px + 1fr`-grid in plaats van de tweede, omdat een CSS-grid een los kind
altijd in de eerste vrije track plaatst. Trof zowel deze nieuwe group-variant
als in theorie elke toekomstige los-staande tag zonder icoon. Gefixt met een
expliciete `grid-column: 2` op `.ipb-stack-panel__body` in `panels.css` (en
teruggezet naar `auto` in de mobiele 620px-breakpoint, anders zou de vaste
kolom-2-plaatsing op een 1-koloms mobiel grid een extra, ongewenste kolom
forceren). Asset-versie opgehoogd naar `0.23.0` voor deze CSS-fix.

Content toegevoegd via `update_post_meta()` op
`_irian_panels[1].data.tags[0].children` /
`_irian_panels_en[1].data.tags[0].children` (de CMS-tag, index 0 van
`stack`), en gemirrored in `panels.json` / `panels-en.json`. Geverifieerd:
beide JSON's geldig, curl op NL/EN toont de nieuwe tekst, debug.log leeg, en
in de browser lijnt "Headless CMS" nu netjes uit onder WordPress/Magento 2
(na de grid-fix).

### Eigen icoon voor Headless CMS

Irian vroeg alsnog om voor alle drie de CMS-sub-skills een eigen, verschillend
plaatje (WordPress en Magento 2 hadden er al een, Headless CMS nog niet).
Nieuwe SVG toegevoegd aan de `$map` in `inc/skill-visuals.php` onder key
`headless-cms` (zelfde line-art-stijl: `currentColor`-lijnen, `class="accent"`
voor het uitgelichte element, 260x150-viewBox): een `< >`-content/API-kern
die met twee lijnen naar een los beeldscherm en een telefoon wijst - visueel
het idee "één content-bron, meerdere onafhankelijke front-ends", in lijn met
de note-tekst. Geen aparte aanpassing nodig aan `panel-stack.php` of de
grid-fix van hierboven: die pakken de nieuwe visual automatisch op zodra
`irian_skill_visual( 'Headless CMS' )` iets teruggeeft in plaats van `''`.

Geverifieerd: `php -l` op het bestand, HTTP 200, debug.log leeg, en visueel
in de browser klopt het icoon en staat het net als bij WordPress/Magento 2
netjes in de linkerkolom.

### Magento-icoon: winkelwagentje herontworpen

Irian gaf aan dat het karretje in het Magento-icoon "scuffed" was: het oude
accent-path (`M214 24h6l3 12h12l-2 8h-11l1 4h9`) plus een los cirkeltje was
geen herkenbare vorm, eerder een willekeurig lijntje. Vervangen door een
duidelijk winkelwagentje opgebouwd uit drie simpele vormen (zelfde plek,
rechtsboven in de titelbalk van het icoon): een handvat-hoekje, een
trapezium-mandje (breder vanboven, smaller vanonder, zoals een echt
winkelwagentje) en twee wieltjes als kleine cirkels eronder. Alleen
`inc/skill-visuals.php` aangepast (de `'magento'`-entry in de `$map`), geen
wijziging aan `panel-stack.php` of CSS nodig. Geverifieerd: `php -l`, HTTP
200, debug.log leeg, visueel in de browser (ingezoomd) - het karretje is nu
in één oogopslag herkenbaar.

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
sectie-intro gecorrigeerd, Modules-titels "Custom cursor interactions" en
"Command palette component" in lekentaal herschreven, "AI content generator"
herschreven om overlap met Prompt Studio weg te nemen, work-screenshots
opnieuw vastgelegd (banner weg, mobile-overflow-bug in de vangmethode
gefixt).

Nog open:

- **Voorpagina draait op `_irian_panels`, niet op irian-fields.** Irian koos
  bewust: laten staan. Werkt en is battle-tested; herbouw = risico zonder
  zichtbare winst.
- **Platforms-items hebben geen `image`.** Optioneel: screenshots/mockups van
  Prompt Studio en de Nieuws Website toevoegen via Media Library + het
  `image`-veld per project-item.
- **Mogelijk responsive-hiaat in de eigen nav onder ~500px breed** (zie 5d) -
  ontdekt via headless screenshots, nog niet bevestigd op een echt smal
  toestel. Even checken.
- Mooiere work-project-screenshots uploaden via Media Library (`visual` /
  `visual_mobile` per work-item) als Irian nog beter bronmateriaal heeft dan
  de Edge-headless-vangst.

---

## 8. Bestandsoverzicht (thema)

```
functions.php            panels-systeem: twee metaboxen (NL + EN) via
                         irian_panels_channels(), render/sanitize/save,
                         irian_field_name()/__PFX__, irian_checkbox_field(),
                         enqueue, wp_localize_script irianI18n, asset-versie 0.23.0
inc/i18n.php              NL/EN laag (irian_lang, irian_str, irian_panels_data,
                         filters: <html lang>, title-tagline, title-separator ·)
inc/skill-visuals.php     irian_skill_visual() - inline SVG per skill (+ alias
                         magento-2 -> magento)
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
