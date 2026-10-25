# The Injil: technical reference

A block child theme of Twenty Twenty-Five built for one site, The Rightly-Guided Injīl at [injil.help](https://injil.help), and not intended for any other: its pages, data, fonts and licences belong to that site. This file is for theme developers, web developers and hosts: how the theme is built, how it boots, where every piece of behaviour lives, what the server needs, and how to work on it. The general-audience manual is `readme.txt`; the fixed facts are in `SSOT.md`; the history is `CHANGELOG.md`; the roadmap is `UPGRADING.md`.

## Contents

1. [At a glance](#at-a-glance)
2. [Requirements and hosting](#requirements-and-hosting)
3. [Install and update](#install-and-update)
4. [Architecture](#architecture)
5. [Boot sequence](#boot-sequence)
6. [Content as data](#content-as-data)
7. [The compact markup](#the-compact-markup)
8. [Starter content lifecycle](#starter-content-lifecycle)
9. [The classical commentaries](#the-classical-commentaries)
10. [Theme Options](#theme-options)
11. [Templates, parts and patterns](#templates-parts-and-patterns)
12. [Block styles and CSS](#block-styles-and-css)
13. [Fonts](#fonts)
14. [Ornament and parallax](#ornament-and-parallax)
15. [SEO and structured data](#seo-and-structured-data)
16. [Share row](#share-row)
17. [Hooks and filters](#hooks-and-filters)
18. [Performance](#performance)
19. [Security](#security)
20. [Development workflow](#development-workflow)
21. [Troubleshooting](#troubleshooting)
22. [Licence](#licence)

## At a glance

| | |
|---|---|
| Type | Block theme (theme.json v3), child of `twentytwentyfive` |
| Folder, text domain, pattern prefix | `injil-help` |
| Version | `Version:` in `style.css` = `INJIL_HELP_VERSION` in `functions.php` (3.6.0) |
| PHP in `inc/` | options, starter-content, starter-content-pages, gospel-islam, quran-jesus, tafsir, seo, schema, share |
| Data | `inc/data/` 2.6 MB of JSON: 176 starter pages, the gospel narrative, the Qur'anic passages, Thomas (woven sections and two full texts), the infancy gospels, the substitution witnesses, eight commentaries (315,000 words) |
| Front-end assets | `style.css` 32 KB; `assets/css/ornament.css` 12 KB, `share.css` 3 KB, `bible.css` 3 KB; `assets/js/ornament.js` 3 KB, `share.js` 2 KB; all plain, no build step |
| Fonts | 16 MB in `assets/fonts/`, 18 `fontFace` entries in `theme.json` |
| Admin | one tabbed page, Appearance › Theme Options, option `injil_help_options` |
| Updates | Git Updater via the `GitHub Theme URI` and `Primary Branch: main` headers; tag `vX.Y.Z` |

## Requirements and hosting

- WordPress 6.6 or later, PHP 7.4 or later (tested on 8.x). Twenty Twenty-Five installed; it may be inactive but must not be deleted.
- Pretty permalinks. The theme sets `/%postname%/` on activation if the structure is empty.
- Memory: the starter-content run that creates or rewrites 176 pages in one request works within WordPress's default 40 MB; on hosts with a lower `memory_limit` raise it to 128 MB for the first admin load after activation or an update.
- `max_input_vars` and `post_max_size`: the largest starter pages (gospel sections 1, 2 and 27, 370 to 560 KB of block HTML) exceed PHP's default `post_max_size` of 8 MB only on hosts that set it lower; saving them in the block editor needs `max_input_vars` of at least 5000. Reading them needs nothing.
- Page cache: strongly advised. The large pages are generated once and served from the database; a full-page cache (host-level, or any cache plugin) keeps them off PHP entirely. An object cache helps the options page but is not required.
- Database: the 176 pages add about 6 MB to `wp_posts`; revisions are suppressed for theme-written pages.
- Outbound requests: none at runtime. Manuscript images are loaded by the visitor's browser from `upload.wikimedia.org`; nothing is fetched server-side.
- No build tools, Composer or npm. Upload the folder and activate.

## Install and update

1. Install Twenty Twenty-Five. Clone or unzip this repository to `wp-content/themes/injil-help` (the folder name must be `injil-help`).
2. Activate "The Injil". `after_switch_theme` creates every page, sets permalinks, bins the untouched sample post, page and comment and replaces the default tagline.
3. Updates: Git Updater reads the header and offers the release whose tag matches `Version:`. On the first admin screen load after the update, `admin_init` rewrites the starter pages whose text changed and leaves edited ones alone (see [Starter content lifecycle](#starter-content-lifecycle)). Take a database backup before updating.
4. Release procedure: bump both version strings together, add the entry to `CHANGELOG.md` and to the Changelog section of `readme.txt`, note anything an existing site must do in `UPGRADING.md`, merge to `main`, tag `vX.Y.Z`.

## Architecture

```
injil-help/
├─ style.css              theme header; tokens (--ih-*); every component style
├─ theme.json             palette, spacing, 18 fontFace entries, custom templates
├─ functions.php          version constant, requires, enqueues, pattern category, block styles, favicon
├─ inc/
│  ├─ options.php         Theme Options page, defaults, sanitiser, body classes, colour CSS
│  ├─ starter-content.php markup parser, page writer, populate(), activation and update hooks
│  ├─ images.php          Commons plates in page content; featured images sideloaded a few pages per admin load
│  ├─ starter-content-pages.php  the page list, merged from the JSON files and the builders
│  ├─ gospel-islam.php    gospel sections from narrative.json + thomas + substitution + commentaries
│  ├─ quran-jesus.php     the Qur'anic passages page from quran-jesus.json
│  ├─ tafsir.php          the commentaries: works, verse groups, section map, pages, fold blocks
│  ├─ seo.php             document title, meta description, Open Graph
│  ├─ schema.php          the JSON-LD graph
│  ├─ share.php           share row
│  └─ data/               all content (JSON)
├─ templates/, parts/     block templates and the header and footer parts
├─ patterns/              26 PHP block patterns (front page, indexes, pillar, building blocks)
├─ assets/css|js|fonts|icons|images
└─ languages/             .mo files
```

Everything a visitor reads is either a block pattern in `patterns/` or JSON in `inc/data/`. PHP never holds article text. The front end has no framework: two small scripts enhance pages that already work without them.

## Boot sequence

`functions.php` defines `INJIL_HELP_VERSION`, requires the five `inc/` files (options, starter-content, seo, schema, share; starter-content pulls in starter-content-pages, which pulls in gospel-islam, tafsir and quran-jesus), then registers:

- `wp_enqueue_scripts`: `style.css`; `ornament.css` unless Ornament is Off; `ornament.js` (deferred, footer) when the reveal animation or parallax is on. `share.css` and `share.js` are enqueued by `share.php` only on pages that show the row. `bible.css` is enqueued on a singular post whose content contains the `ih-bible` block, which is every gospel section.
- `after_setup_theme`: text domain from `languages/`; `style.css` as editor style.
- `init`: pattern category `injil-help`; the block style variations listed under [Block styles](#block-styles-and-css).
- `wp_head`: favicon link (custom URL, or the SVG brandmark when forced or when no Site Icon is set).

`options.php` adds `enqueue_block_assets` (palette overrides in the editor), `body_class` (`ih-orn`, `ih-px-strong|gentle|off`), and filters on `injil_help_seo_defaults`, `injil_help_default_og_image` and `injil_help_output_schema` that apply the option values. `seo.php` filters `document_title_parts` and prints meta and Open Graph at `wp_head`. `schema.php` prints the graph at `wp_head` priority 5. `share.php` filters `the_content` at priority 20.

## Content as data

| File | Holds | Shape |
|---|---|---|
| `pages/pillar.json`, `gospels.json`, `paul.json`, `quran.json`, `gospel.json`, `bonus.json`, `resources.json`, `site.json` | the starter pages, one file per part, in site order | array of `{path, title, excerpt, pattern? \| body?, body_file?, template?, related?, former?}`; `body_file` names a file in `inc/data/` whose list of lines is appended to `body`; `related` lists the paths linked in the page's "Read next" row; `former` lists the paths the page had in earlier versions, which `populate()` renames in place |
| `narrative.json` | The Narrative of Jesus, Son of Mary | `{chapters: [{number, title, sections: [{number, lines[]}]}], notes: {n: text}}`; `[^n]` note markers, `[v]` verse breaks and `[qS:A]` Qur'anic verse references in lines |
| `gospel-islam.json` | section titles, section URL slugs, Introduction | `{section_titles: {n: title}, section_slugs: {n: slug}, front: {introduction: {title, paras[]}}}`; the slug is the section's URL segment (`/gospel-according-to-islam/john-the-baptist/`), never its number |
| `related.json` | the curated map of related pages behind the "Read next" row | `{path: [path, …]}`, merged with what the builders set (`injil_help_related_map()`) |
| `quran-jesus.json` | the Qur'anic passages | `{surahs: {n: name}, passages: [{surah, verses_range, heading, verses: {n: text}}]}` |
| `thomas.json`, `infancy.json`, `substitution.json` | woven passages (Thomas, the infancy gospels, the substitution witnesses, merged in that order) | `{section: [markup lines]}` |
| `thomas-mattison.json`, `thomas-patterson-meyer.json` | the full text of the Gospel of Thomas in two translations | `[markup lines]`, loaded through `body_file` |
| `images.json` | one Commons image per page that has one | `{path: {file, alt, caption, credit}}`; `inc/images.php` puts the plate into the content and sideloads the file as the featured image |
| `tafsir/{work}.json` | one commentary | `{work, entries: [{surah, from, to, arabic_words, english_words, paragraphs[]}]}` |

`injil_help_starter_pages()` (static-cached per request) concatenates: pillar, gospels, paul, quran, the Qur'an page built by `injil_help_quran_page()`, the commentary pages from `injil_help_tafsir_pages()`, gospel, the 28 gospel pages from `injil_help_gospel_islam_pages()`, bonus, resources, site. Parents precede children in that order, which `populate()` relies on to resolve `post_parent`.

### URLs and internal links

Every URL is made of words, as the Google SEO Starter Guide asks: no page has a number for a URL segment. The gospel sections take their slugs from `section_slugs` in `gospel-islam.json`, the commentary groups from the keys of `injil_help_tafsir_groups()`, and later parts of a long commentary are `{work}-part-two` siblings. A page that changes URL keeps its `former` paths in its definition; on the first admin load `injil_help_adopt_former_page()` finds the page under a former path (or under the `2-2` slug WordPress gave it before 3.5.1), renames it and re-parents it, keeping the ID, the edits and the featured image. The old URLs are not redirected.

Every article ends with a "Read next" row (`injil_help_related_blocks()`): the pages in its `related` list, which is the union of what its builder sets (a gospel section names the commentary groups it rests on; a commentary page names the gospel sections, the Qur'an page and the passages page the verses belong with, the group, the index and its other parts) and the curated map in `inc/data/related.json`. Section indexes that already end in their own "Where this leads" aside get no second row. The folds under a gospel section link to every group page the commentary belongs to, and a group page links every part of a work, so no page depends on the menus alone to be reached.

A missing or malformed file yields an empty list from `injil_help_pages_file()` or `injil_help_data_file()`; `populate()` then bails without stamping the version so the run is retried. Validate every file with `php -r` or `python -m json.tool` before committing (see [Development workflow](#development-workflow)).

## The compact markup

A `body` is a list of lines. `injil_help_markup_to_blocks()` turns them into block HTML with these rules, in order of precedence:

| Line | Becomes |
|---|---|
| `- text` | list item; consecutive items form one `core/list`. A leading `[agree]`, `[beyond]`, `[unclear]`, `[conflict]` or `[notneeded]` adds class `ih-s ih-s-{key}` and a badge |
| `? Question` + next line | `core/details` with `is-style-faq`, the next line as its paragraph; collected into FAQPage |
| `[details] Title` … `[/details]` | `core/details` with `is-style-fold` wrapping every block in between |
| `### text`, `## text` | `core/heading` level 3 or 2 |
| `> text` | `core/quote` with one paragraph |
| `@consensus`, `@minority`, `@islamic` | label paragraph `is-style-label-{key}` |
| `[page-list]` | `core/page-list` |
| anything else | `core/paragraph` |

Inline: `{{fn:note}}` is replaced by a numbered superscript link and the note is appended under a "Notes" heading as an ordered list with back-links; numbering is per page in order of appearance. Inline HTML passes through `wp_kses_post`. Lines are trimmed; blank lines are ignored.

Five tokens serve the Bible layout of the gospel sections, emitted by `injil_help_gospel_section_head()` and `injil_help_gospel_islam_pages()` from the markers in `narrative.json`:

- `[v]` opens a verse: `<span class="ih-v" id="vN">N</span>`, numbered from 1 per page. The markers are data, seeded from Shafaat's 1985 verse division; move, add or remove them and the numbering follows.
- `[qS:A]` marks a Qur'anic verse: `<span class="ih-v ih-vq" id="qS-A">S:A</span>`.
- `[bible]` … `[/bible]` wraps the text in `<div class="wp-block-group ih-bible">`, the block the layout styles.
- `[notes]` prints the footnotes collected so far as `ol.ih-footnotes.ih-apparatus` at that point, numbered on; the notes of material after it (Thomas, the infancy gospels, the commentaries) go under the "Notes" heading at the end as before, with `start` set so the numbers continue.
- `[p.classes] text` is a paragraph with those classes; the section head uses `ih-running-head`, `ih-argument` and `ih-chapter-num`.

## Starter content lifecycle

`injil_help_populate( $overwrite = false, $force = false )` in `inc/starter-content.php`:

1. Takes a lock with `add_option('injil_help_populating')` (atomic insert); a lock older than 60 seconds is treated as stale.
2. Loads the page list; an empty list exits without writing or stamping.
3. Suppresses revisions (`wp_revisions_to_keep` → 0) for the run.
4. For each definition, in order: finds the page by path (`get_page_by_path`, trashed pages ignored); builds content (`injil_help_page_content()`: the pattern's content, or the parsed body); slashes it; inserts it if missing; if present and `$overwrite`, rewrites it only when the stored content differs and the page is not edited (or `$force`); sets `post_parent` from the path; stores `md5` of the written content in post meta `_injil_help_hash`.
5. Sets pretty permalinks if the structure is empty and flushes rules only in that case.
6. Stamps `injil_help_populated` with the version and releases the lock.

`injil_help_page_is_edited()` compares the stored hash with the current content; a mismatch means the owner edited the page. Entry points:

- `after_switch_theme`: first activation only (no stamp yet): removes sample content, then `populate()`.
- `admin_init`: when the stamp is older than `INJIL_HELP_VERSION` and the user can `edit_theme_options`: `populate(true)`.
- `admin_post_injil_help_populate` (capability and nonce checked): `populate($overwrite, $overwrite)` from the Content tab; "Create missing" passes nothing, "Update all" passes `overwrite=1`.

## The classical commentaries

`inc/tafsir.php` holds three tables: `injil_help_tafsir_works()` (key, titles, author, death, note, source), `injil_help_tafsir_groups()` (nine verse groups with slug, surah, range, title, intro) and `injil_help_tafsir_section_map()` (which verse ranges each gospel section carries). From the data it builds:

- the index `/quran/commentaries/` (works, passages, how to read, six FAQs);
- one page per group `/quran/commentaries/{group}/` (the group slug is words only: `family-of-imran`, `neither-killed-nor-crucified`, `birth-of-jesus` …; the verse numbers are in the title) listing the works with word counts, the parts of each, and the gospel sections the verses stand in;
- one page per work and group `/quran/commentaries/{group}/{work}/`, split into parts at about 14,000 English words, never splitting an entry; a later part is a sibling page named `{work}-part-two`, `{work}-part-three` … (`injil_help_tafsir_part_path()`); each with the work's colophon, prev/next navigation and a "Read next" row naming the other parts, the group, the gospel sections and the Qur'an page the verses belong with;
- `injil_help_tafsir_section_lines( $n )`: for a gospel section, a `## From the commentaries on …` heading and one `[details]` fold per work containing every entry whose range touches the section's ranges (an entry that straddles two sections appears in both), which `injil_help_gospel_islam_pages()` appends after the Thomas and substitution passages.

Word counts come from `injil_help_tafsir_words()` (tags and footnotes stripped). Adding a work is one entry in `injil_help_tafsir_works()` plus its JSON file; the pages, the index and the folds follow. Entries are sorted by surah and first verse on load and cached per request.

The pipeline that produced the data (fetch the Arabic from tafsir.app, greattafsirs.com or shamela.ws; chunk per verse range; translate; assemble and validate into `inc/data/tafsir/{work}.json`) ran outside the repository. Its validation rules are the ones to keep for any new work: every expected chunk present; no `[[ … ]]` editorial apparatus; only `<strong>` and `<em>` inline; balanced `{{fn:}}`; English at least 1.1 times the Arabic word count; chunks that run past the Jesus verses cut at the commentator's own heading.

## Theme Options

Appearance › Theme Options (`inc/options.php`), one option array `injil_help_options`, registered with `register_setting` and `injil_help_sanitize_options()` (hex colours via `sanitize_hex_color`, enumerations whitelisted, text fields sanitised, URLs escaped). Tabs and keys:

| Tab | Keys |
|---|---|
| Colours | `colors` (nine palette slugs → hex; empty keeps theme.json) |
| Branding | `ornament` rich\|subtle\|off, `animate` 0\|1, `parallax` strong\|gentle\|off, `footer_mark`, `favicon_force`, `favicon_url` |
| Sharing | `share`, `share_types` (post, page), `share_networks` (ordered subset of the network keys), `share_label` |
| SEO | `schema` auto\|on\|off, `home_title`, `home_description`, `pillar_title`, `og_image`, `author_name`, `author_url`, `same_as` (one URL per line), `org_logo` |
| Security | `block_app_passwords` 0\|1 (default 1: application passwords, and with them REST API and XML-RPC log-ins with a per-application password, are refused site-wide until the block is unticked) |
| Content | `gospel_layout` columns\|single\|plain (the Bible layout of the gospel sections); lists every starter page with status (missing, written, edited) and runs the two populate actions |

`injil_help_get_option( $key )` merges the stored array over `injil_help_option_defaults()`. `injil_help_color_css()` emits the overrides as `:root` custom properties on the front end and in the editor (`enqueue_block_assets`). The page's own CSS and JS are `assets/css/admin.css` and `assets/js/admin.js` (tabs with remembered selection, colour pickers); the admin CSS reads the palette tokens so it follows the Colours tab.

## Templates, parts and patterns

- `templates/`: `front-page.html`, `page.html`, `page-section.html` ("Section index"), `page-wide.html` ("Wide page (tables)"), `single.html`, `archive.html`, `home.html`, `index.html`, `search.html`, `404.html`. The two custom templates are declared in `theme.json` `customTemplates` for pages.
- `parts/header.html`, `parts/footer.html`: navigation, brandmark, Qur'anic gloss (class `ih-gloss ih-ar ih-quran`, `dir="rtl" lang="ar"`).
- `patterns/` (category `injil-help`): `hero`, `home-dilemma`, `home-gospels`, `home-paul`, `home-quran`, `home-gospel`, `home-resources`, `home-answers`, `parts-grid`, `islamic-dilemma-page`, `criterion-statement`, `section-gospels`, `section-paul`, `section-quran`, `section-gospel`, `section-resources`, `answers-page`, `how-composed`, `corrections-log`, `discrepancy-genealogies`, `discrepancy-nativity`, `discrepancy-last-supper`, `discrepancy-crucifixion`, `discrepancy-resurrection`, `thomas-correlation`, `manuscripts-table`, `quran-verse-index`, `answer-capsule`, `claim-labels`, `gospel-verse`, `objection-reply`, `faq`. A page definition names a pattern by slug (without the prefix) and `injil_help_page_content()` fetches its content from the registry.

## Block styles and CSS

Registered in `functions.php`, styled in `style.css`:

| Block | Styles |
|---|---|
| `core/paragraph` | `label-consensus`, `label-minority`, `label-islamic`, `label-quran`, `label-nt`, `label-editorial` |
| `core/group` | `answer-capsule`, `gospel-verse`, `objection-reply`, `criterion`, `answer-item` (FAQPage), `read-first` |
| `core/table` | `discrepancy` |
| `core/separator` | `ornament` |
| `core/details` | `faq` (FAQPage); `fold` is applied by the parser and styled, not registered as a picker style |

`style.css` opens with the tokens: radii `--ih-r-sm|md|lg`, font roles `--ih-serif`, `--ih-sans`, `--ih-display`, `--ih-greek`, `--ih-quran`. Colours are always `var(--wp--preset--color--{slug})`; nothing is hard-coded. Component classes are prefixed `ih-` (`ih-section-head`, `ih-entry`, `ih-k`, `ih-eyebrow`, `ih-numeral`, `ih-plate`, `ih-fnref`, `ih-footnotes`, `ih-s`, `ih-badge`, `ih-share*`). Language-scoped fonts come from `:lang()` selectors.

## Fonts

All faces are bundled in `assets/fonts/` and declared as `fontFace` in `theme.json`, so they appear in the editor's picker and are served from the theme with no third-party requests. Slugs and roles: `sabon` body, `garamond` headings, `kagnue` and `kagnue-outline` display (site title, cover title, section index and home section titles, large numerals), `evangelion` labels and navigation, `sbl-greek`, `sbl-hebrew`, `sbl-biblit` biblical languages, `hafs` Qur'anic text only (class `ih-quran`), `amiri` other Arabic (`ih-ar`, `:lang(ar)`), `naskh` hadith (`ih-hadith`), `estrangelo` Syriac (`:lang(syc)`), `cuneiform` (`:lang(akk)`), `special-elite` corrections log (`ih-record`), `dubidam`, `arslan`, `ferments`, `angel` available. Licences: Sabon Next LT and Kagnue are commercial faces licensed to the site owner; Angel keeps its README; the rest are OFL or free. Kagnue lacks the dotted transliteration letters, so it is kept off headings that may carry them.

### The Bible layout

`assets/css/bible.css` styles the gospel sections: `.ih-running-head` above the text; `.ih-bible` (the group block) with `.ih-argument` (italic, spanning both columns), `.ih-chapter-num` (the section numeral in the display face, floated at the start of the text), `h3` sub-headings in small capitals and `.ih-v` verse numbers (`.ih-vq` for the Qur'an's own); `.ih-apparatus` for the notes under a rule in small type. Two columns with a rule apply at 900px and above under `body.ih-gospel-columns`; `ih-gospel-single` keeps one column; `ih-gospel-plain` hides the running head, the numeral and the verse numbers. The body class comes from the `gospel_layout` option; the markup never changes, so switching the option is instant and reversible. Print always sets two columns.

## Ornament and parallax

`assets/css/ornament.css` draws the star lattice, section banners with watermark icons, card icons, framed plates and the footer pattern, all as CSS masks over `assets/icons/*.svg` and `assets/images/pattern-star.svg`, so every decoration takes its colour from the palette. It loads only when Ornament is not Off; `body.ih-orn` scopes it.

`assets/js/ornament.js` (3 KB, no dependencies) exits at once under `prefers-reduced-motion`. Reveal: an `IntersectionObserver` adds `is-in` to cards, answers and plates (`html.ih-js .ih-reveal`). Parallax: when `body.ih-px-strong` or `.ih-px-gentle` is present it sets `--ih-sy` (scroll offset) on the root and `--ih-p` (progress through the viewport, −1 to 1) on each layered element, throttled with `requestAnimationFrame` and skipped for elements far off screen; `ornament.css` turns these into `transform`s at per-layer rates multiplied by `--ih-px` (1 or 0.45). Only transforms change, so there is no layout work during scroll.

## SEO and structured data

`inc/seo.php`: `injil_help_seo_defaults()` (filterable) supplies the home title, home description and pillar title; `document_title_parts` applies them; `wp_head` prints `meta description`, Open Graph and Twitter card tags with the page excerpt or the first paragraph, and the featured image or the default social image (`assets/images/og-default.jpg`, filter `injil_help_default_og_image`). `injil_help_is_pillar()` identifies `/the-islamic-dilemma/`.

`inc/schema.php`: `injil_help_schema_enabled()` honours the SEO option and switches off when Yoast, Rank Math or SEOPress is active (filter `injil_help_output_schema`). `injil_help_schema_graph()` builds one `@graph` at `wp_head` priority 5: Organization (logo, sameAs), Person (author from the SEO tab or the post author), WebSite (+ SearchAction on the front page), the page node typed by `injil_help_schema_page_type()` (CollectionPage with an ItemList when the page has three or more entries, ProfilePage for the About page, ContactPage, SearchResultsPage, else WebPage), BreadcrumbList from ancestors, Article or BlogPosting when `injil_help_schema_is_article()` says so (word count, dates, section, images), Book for the gospel sections, ImageObject for every figure (licence read from the caption, creator from "Photo: Name", acquireLicensePage from the Commons link), FAQPage from `is-style-faq` details and `answer-item` groups. Patterns are expanded with `injil_help_expand_patterns()` so FAQ and image collection sees the real blocks. Filter the finished array with `injil_help_schema_graph`.

## Share row

`inc/share.php` appends the row to `the_content` (priority 20) on singular posts and pages of the chosen types, not on the front page, and not when the filter runs for an excerpt (`doing_filter('get_the_excerpt')`) or for a post that is not the queried one. Networks are a filterable table of `{key: [name, endpoint with {url} {title} {image}]}`; icons are masks over `assets/icons/social/{key}.svg`, coloured by the palette. `assets/js/share.js` reveals the Copy link button when `navigator.clipboard` is available in a secure context (with "Copied" and failure feedback) and the More… button when `navigator.share` exists.

## Hooks and filters

| Filter | Purpose |
|---|---|
| `injil_help_seo_defaults( array )` | home title, home description, pillar title |
| `injil_help_default_og_image( string )` | default social image URL |
| `injil_help_output_schema( bool )` | turn the structured data on or off |
| `injil_help_schema_graph( array )` | edit the graph before output |
| `injil_help_share_networks( array )` | add, remove or reorder networks |

Actions used: `after_switch_theme`, `admin_init`, `admin_post_injil_help_populate`, `admin_menu`, `admin_enqueue_scripts`, `enqueue_block_assets`, `wp_enqueue_scripts`, `wp_head`, `init`, `after_setup_theme`. Options written: `injil_help_options`, `injil_help_populated`, `injil_help_populating`. Post meta: `_injil_help_hash`.

## Performance

- No render-blocking scripts: both front-end scripts are deferred and tiny; CSS totals under 50 KB uncompressed.
- Fonts are the heaviest asset class (16 MB on disk, but a page loads only the faces its text uses, by `font-family` fallback). Preloading `SabonNextLT.ttf` and `EBGaramond-Variable.ttf` is worthwhile; subsetting is on the roadmap.
- The commentary pages and the gospel sections 1, 2, 3, 14 and 27 are the only pages above 100 KB of HTML. Serve them through a page cache. The fold blocks keep them collapsed, so first paint is unaffected.
- `injil_help_starter_pages()` is built once per request and only in admin (Content tab, populate) or when the schema needs an entry count; it is not on the front-end hot path.

## Security

- The options page requires `edit_theme_options`; saving goes through the Settings API with a sanitiser; the populate actions check capability and a nonce (`injil_help_populate`).
- All output is escaped at the point of printing; page content written by the theme passes through `wp_kses_post` in the parser; data files are static and shipped with the theme, never fetched.
- No remote code, no eval, no inline scripts; the two scripts and three stylesheets are files, so a Content-Security-Policy with `script-src 'self'` works. `style-src` needs `'unsafe-inline'` only for the palette override block that WordPress prints as an inline style; put it in a file if your policy forbids that.
- Share links open third-party sites with `rel="noopener noreferrer nofollow"`.
- Application passwords are blocked by default: `wp_is_application_passwords_available` returns false while the `block_app_passwords` option is set (its default), so no client can log in through the REST API or XML-RPC with a per-application password. Untick the block under Theme Options › Security for a client that needs them.

## Development workflow

1. Edit content in `inc/data/` or `patterns/`, behaviour in `inc/`, styles in `style.css` or `assets/css/`. Never put article text in PHP.
2. Checks before committing (all run without WordPress):
   - `for f in $(git ls-files '*.php'); do php -l $f; done`
   - JSON validity: `for f in $(git ls-files 'inc/data/*.json' 'inc/data/**/*.json'); do python3 -m json.tool $f > /dev/null || echo $f; done`
   - Page list and links: a script that stubs `get_stylesheet_directory()` and the handful of WordPress functions the loaders call, requires `inc/starter-content-pages.php`, calls `injil_help_starter_pages()`, and asserts no duplicate paths, every child has a parent, and every `href="/…/"` in a body or pattern points at a starter path.
   - Render: with the same stubs plus WordPress's block parser classes, run every definition through `injil_help_page_content()`, `parse_blocks()` it, and assert every `wp:details` opens and closes and no `null` block names appear where a block was intended.
   - Structured data: with the stubs, call `injil_help_schema_graph()` for one page of each type and check every `@id` reference resolves.
   These harnesses lived in a scratch directory during development; `UPGRADING.md` asks for them to be brought into `tools/` and run in CI.
3. Commit messages describe the change; no tool or model names in any artefact.
4. Release as in [Install and update](#install-and-update).

## Troubleshooting

- **Pages missing after activation.** Open Appearance › Theme Options › Content and press "Create missing pages". If the list itself is empty, a data file failed to parse: validate the JSON.
- **An update did not change a page.** The page was edited after the theme wrote it (shown as "edited, kept on update"). Use "Update all pages from the theme" to replace it, or re-apply your edit after.
- **Populate seems stuck.** The lock option `injil_help_populating` expires after a minute; reload.
- **Saving a gospel section fails in the editor.** Raise `max_input_vars` (5000) and `post_max_size`; or edit the data file and rewrite the page.
- **No structured data in the source.** An SEO plugin is active (the theme defers to it), or the SEO tab is set to Off.
- **Fonts do not load.** Check the host serves `.ttf` and `.otf` with a font MIME type and that `assets/fonts/` uploaded completely (16 MB).
- **Share row on the front page.** It is excluded by design; elsewhere check Theme Options › Sharing and the post type.

## Licence

GPL v2 or later for the theme code and the site's own text, including the English renderings of the commentaries. Fonts, the gospel base text, the Qur'an translation, the Nag Hammadi translations and the manuscript images keep their own licences, named in `readme.txt` and on the Credits page.
