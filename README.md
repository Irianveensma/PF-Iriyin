# IriyinPort

Persoonlijke portfoliowebsite van Irian Veensma. WordPress, lokaal ontwikkeld
via [Local](https://localwp.com/). Positionering: webdeveloper x marketeer x
digital. De site zelf is het portfolio.

Geen em-dashes. Nergens, ook niet in code-commentaar of docs.

## Wat er in versiebeheer staat

Alleen de zelfgeschreven code. WordPress core, thema's en plugins van derden,
uploads, cache en de Local-config blijven erbuiten (zie `.gitignore`).

| Pad | Wat |
|---|---|
| `app/public/wp-content/themes/irian-portfolio-theme/` | Het actieve thema. Panelen-systeem, NL/EN laag, contactformulier, command palette. Zie de README in die map. |
| `app/public/wp-content/plugins/irian-fields/` | Eigen, gratis ACF-kloon (repeater, flexible content, herbruikbare veldgroepen). Nog niet gebruikt door de voorpagina. |
| `app/public/wp-content/mu-plugins/irian-local-editor-fix.php` | Laat de wp-admin File Editor edits bewaren op Local (loopback-fix). Draait alleen op `wp_get_environment_type() === 'local'`. |

## Lokale omgeving

- Local site "IriyinPort", domein `iriyinport.local`, wp-admin `dev` / `dev`.
- Statische voorpagina = pagina ID 9, sjabloon `page-home.php`.
- Panel-content: post meta `_irian_panels` (NL) en `_irian_panels_en` (EN) op
  pagina 9. JSON-bronnen plus import-scripts staan buiten de repo.

`HANDOFF.md` beschrijft de volledige stand van zaken en hoe je wp-cli, PHP en
screenshots draait in deze omgeving.
