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
- `inc/options.php`: Appearance > Theme Options (tabbed: Colours, Branding, SEO)
- `inc/starter-content.php`, `inc/starter-content-pages.php`: pages created on activation
- `inc/seo.php`: titles, meta description, Open Graph, schema
- `parts/`, `templates/`, `patterns/`: block markup
- `assets/fonts/`: bundled fonts
- `assets/css/`, `assets/js/`: Theme Options page styles and script
- `assets/brandmark.svg`: brandmark (header, footer, fallback favicon)
- `assets/images/`: placeholder cover art

## Documentation

`readme.txt` is the full manual: install, structure, patterns and block styles, fonts and licences, SEO, images, branding and favicon, colour customisation, release steps and changelog.

## Licence

GPL v2 or later. Bundled fonts keep their own licences (see `readme.txt`). Manuscript images load from Wikimedia Commons under the licences named in each caption.
