# The Rightly-Guided Injīl

A child theme of Twenty Twenty-Five for [injil.help](https://injil.help): an answer to the Islamic Dilemma, with the four gospels and Paul of Tarsus examined and a gospel retold with the Qur'an as the measure.

## Install

1. Install and keep Twenty Twenty-Five (the parent theme).
2. Download this repository as a zip, or clone it into `wp-content/themes/injil-help`.
3. Activate **The Rightly-Guided Injīl** under Appearance → Themes.

The folder must be named `injil-help` so the text domain and pattern slugs resolve.

## Setup

Activation creates the whole site: 166 pages, pretty permalinks, the sample post and page removed. See `readme.txt` for the page list, the pillar page at `/the-islamic-dilemma/`, Theme Options, SEO, image credits and font licences.

## Content

- The Islamic Dilemma answered (`/the-islamic-dilemma/`), with its secular restatement and the historical-critical reply answered point by point (`/the-islamic-dilemma/secular-form/`), and the measure the site reads by (`/method/`).
- The four gospels, the Gospel of Thomas, the synoptic problem and the discrepancy tables; six pages on Paul of Tarsus; six pages on Jesus in the Qur'an and the compilation of passages.
- The classical commentaries on Jesus (`/quran/commentaries/`): al-Ṭabarī, al-Baghawī, al-Qurṭubī, Ibn Kathīr, Tanwīr al-Miqbās, al-Jalālayn, al-Durr al-Manthūr and al-Saʿdī on 3:33-64, 4:155-159, 5:110-120, 19:16-37, 21:91, 23:50, 43:57-65, 57:27 and 61:6, translated in full (about 315,000 words), on 92 pages and folded into the gospel sections where the verses fall.
- The Gospel According to Islam: the Introduction and 27 sections of Ahmad Shafaat's text with the Qur'anic passages, the Gospel of Thomas, the early witnesses to the substitution and the commentaries woven in; how it was composed.
- Resources: timeline, glossary, source library, objections and replies, responses to named articles; FAQs on every main page.

All of it is data in `inc/data/` (JSON) and `patterns/`; the page bodies use a compact markup documented in `readme.txt`.

## Updates

The theme header carries `GitHub Theme URI` and `Primary Branch: main`, so [Git Updater](https://git-updater.com) can update it from this repository. Bump `Version:` in `style.css` and `INJIL_HELP_VERSION` in `functions.php` together on every release, and tag the commit with the same number (e.g. `v3.4.0`).

## Structure

- `style.css`, `theme.json`, `functions.php`: header, tokens, setup
- `inc/options.php`: Appearance > Theme Options (tabbed: Colours, Branding, Sharing, SEO, Content)
- `inc/share.php`: share row on posts and pages, Web Share button
- `inc/starter-content.php`, `inc/starter-content-pages.php`: the page builder and the loader for the page data
- `inc/data/`: all site content as JSON. `pages/*.json` are the starter pages by part of the site (pillar, gospels, paul, quran, gospel, bonus, resources, site); `narrative.json` is The Narrative of Jesus, Son of Mary (six chapters, 27 sections, 235 notes); `gospel-islam.json` holds the section titles and the Introduction; `quran-jesus.json` the 145 Qur'anic verses in 45 passages; `thomas.json` the Gospel of Thomas sections
- `inc/data/tafsir/*.json`, `inc/tafsir.php`: the classical commentaries (al-Ṭabarī, al-Baghawī, al-Qurṭubī, Ibn Kathīr, Tanwīr al-Miqbās, al-Jalālayn, al-Durr al-Manthūr, al-Saʿdī) on the verses about Jesus, Mary, Zachariah and John, translated in full; builds `/quran/commentaries/` and the "From the commentaries" blocks folded into the gospel sections
- `inc/gospel-islam.php`, `inc/quran-jesus.php`: build the gospel sections and the Qur'an page from that data
- `inc/seo.php`: titles, meta description, Open Graph
- `inc/schema.php`: the JSON-LD graph on every page (Organization, Person, WebSite, WebPage/CollectionPage/ProfilePage/ContactPage, BreadcrumbList, Article, FAQPage, ImageObject licence metadata, ItemList, Book)
- `parts/`, `templates/`, `patterns/`: block markup
- `assets/fonts/`: bundled fonts
- `assets/css/`, `assets/js/`: Theme Options page styles and script, the ornament layer (`ornament.css`, `ornament.js`) and the share row (`share.css`, `share.js`)
- `assets/icons/`: line icons used as CSS masks; `assets/icons/social/`: share icons from the Minimalist Social Icons Pack (GPL; LinkedIn by Font Awesome, CC BY 4.0)
- `languages/`: drop `injil-help-{locale}.mo` files here
- `assets/brandmark.svg`: brandmark (header, footer, fallback favicon)
- `assets/images/`: star pattern, divider and the default social image

## Documentation

- `readme.txt`: the user-facing manual that WordPress and Git Updater read: install, starter content, data and markup, patterns and block styles, fonts and licences, SEO and structured data, images, branding, Theme Options, ornament, colours, release steps and the changelog.
- `SSOT.md`: the single source of truth: every fixed fact about the theme (slugs, data files, options, tokens, filters, rules). When documents disagree, this one is right.
- `CHANGELOG.md`: the full version history.
- `UPGRADING.md`: how to take the theme further: improvements, features and content to add, and the work that would make it fool-proof to run; with notes on updating an existing site.

## Licence

GPL v2 or later. Bundled fonts keep their own licences (see `readme.txt`). Manuscript images load from Wikimedia Commons under the licences named in each caption.
