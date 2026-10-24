# The Rightly-Guided Injīl

A child theme of Twenty Twenty-Five for [injil.help](https://injil.help): an answer to the Islamic Dilemma, with the four gospels and Paul of Tarsus examined and a gospel retold with the Qur'an as the measure.

## Install

1. Install and keep Twenty Twenty-Five (the parent theme).
2. Download this repository as a zip, or clone it into `wp-content/themes/injil-help`.
3. Activate **The Rightly-Guided Injīl** under Appearance → Themes.

The folder must be named `injil-help` so the text domain and pattern slugs resolve.

## Setup

See `readme.txt` for page slugs, the pillar page at `/the-islamic-dilemma/`, SEO, image credits and font licences.

## Updates

The theme header carries `GitHub Theme URI` and `Primary Branch: main`, so [Git Updater](https://git-updater.com) can update it from this repository. Bump `Version:` in `style.css` and `INJIL_HELP_VERSION` in `functions.php` together on every release, and tag the commit with the same number (e.g. `v2.4.0`).

## Structure

- `style.css`, `theme.json`, `functions.php`: header, tokens, setup
- `inc/options.php`: Appearance > Theme Options (tabbed: Colours, Branding, Sharing, SEO, Content)
- `inc/share.php`: share row on posts and pages, Web Share button
- `inc/starter-content.php`, `inc/starter-content-pages.php`: the page builder and the loader for the page data
- `inc/data/`: all site content as JSON. `pages/*.json` are the starter pages by part of the site (pillar, gospels, paul, quran, gospel, bonus, resources, site); `narrative.json` is The Narrative of Jesus, Son of Mary (six chapters, 27 sections, 235 notes); `gospel-islam.json` holds the section titles and the Introduction; `quran-jesus.json` the 145 Qur'anic verses in 45 passages; `thomas.json` the Gospel of Thomas sections
- `inc/gospel-islam.php`, `inc/quran-jesus.php`: build the gospel sections and the Qur'an page from that data
- `inc/seo.php`: titles, meta description, Open Graph, schema
- `parts/`, `templates/`, `patterns/`: block markup
- `assets/fonts/`: bundled fonts
- `assets/css/`, `assets/js/`: Theme Options page styles and script, the ornament layer (`ornament.css`, `ornament.js`) and the share row (`share.css`, `share.js`)
- `assets/icons/`: line icons used as CSS masks; `assets/icons/social/`: share icons from the Minimalist Social Icons Pack (GPL; LinkedIn by Font Awesome, CC BY 4.0)
- `languages/`: drop `injil-help-{locale}.mo` files here
- `assets/brandmark.svg`: brandmark (header, footer, fallback favicon)
- `assets/images/`: star pattern, divider and the default social image

## Documentation

`readme.txt` is the full manual: install, structure, patterns and block styles, fonts and licences, SEO, images, branding and favicon, colour customisation, release steps and changelog.

## Licence

GPL v2 or later. Bundled fonts keep their own licences (see `readme.txt`). Manuscript images load from Wikimedia Commons under the licences named in each caption.
