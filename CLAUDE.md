# IriyinPort - Irian's portfoliowebsite (WordPress, Local + productie iriyin.nl)

**De volledige stand van zaken staat in `HANDOFF.md` in deze map.** Lees dat
eerst. Dit bestand is alleen de wegwijzer + de harde regels.

## Wat het is

Persoonlijk portfolio van Irian Veensma (webdeveloper x marketeer x digital).
De site zelf IS het portfolio. Slechts twee klantprojecten mogen met naam:
Pedicure Paulina en Sita Design. Eigen thema `irian-portfolio-theme` (geen ACF;
eigen "panels"-systeem in `functions.php`, data in post meta `_irian_panels` /
`_irian_panels_en` op pagina 9).

- **Lokaal**: Local-site "IriyinPort", `iriyinport.local`, wp-admin `dev`/`dev`.
- **Productie**: `iriyin.nl`. Deploy = verse thema-zip + "vervang geinstalleerd
  door geupload" in wp-admin (zie HANDOFF 5h). Content (post meta) deployt NIET
  mee - dat is per omgeving.
- Thema-assets versie zit hardgecodeerd in `irian_enqueue_assets()`
  (`functions.php`); bump 'm bij elke CSS/JS-wijziging.

## Harde regels (niet overschrijven)

- **Geen em-dashes. Nooit. Nergens.** Content, code-commentaar, commits, alles.
  Gebruik ` - ` of een puntkomma.
- **Geen e-mailadres zichtbaar** op de site. `irianveensma@gmail.com` is alleen
  de server-side ontvanger van het contactformulier.
- **Donker/metallic** als basis (`--ipb-base #15171c`), geen lichtere "plaat"
  achter secties. Sinds sessie 6 is **één gedempte warme accentkleur**
  (`--ipb-accent #c8894f`, amber, geen neon) wel toegestaan, spaarzaam.
- **Knoppen plat** (primair = accent, secundair = outline). Geen glossy chroom.
- **Nooit** een gekleurde accent-`border-left` op kaarten/panelen ("oogt heel
  AI-achtig").
- Algemeen: keuzes die "AI-gegenereerd" aanvoelen vermijden.
- Fonts: Space Grotesk (koppen), Inter (body + labels), JetBrains Mono (alleen
  code, `⌘K`, tech-tags, URL's, footer).

## Omgeving

- wp-cli / PHP-paden: zie `HANDOFF.md` sectie 2, of de memory-notitie
  `local-wpcli-access.md`.
- Theme-bestanden altijd op schijf bewerken (in-browser editor draait terug).
- Screenshots: `save_to_disk` / mshots werken niet; Edge headless CLI wel.
- Git: repo in deze projectroot, branch `master`, geen remote. Commit alleen
  op verzoek. Commits sinds sessie 5j staan nog niet gepusht/gedeployed.

## Persistente memory

`~/.claude/projects/C--Users-Irian-Veensma-Local-Sites-iriyinport/memory/`
(`portfolio-project-state.md`, `local-wpcli-access.md`,
`figma-feedback-2026-08-27.md`). Laadt alleen automatisch als Claude Code
vanuit deze projectmap wordt gestart.
