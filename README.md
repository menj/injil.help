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
| Version | `Version:` in `style.css` = `INJIL_HELP_VERSION` in `functions.php` (3.35.0) |
| PHP in `inc/` | options, social, progress, unlist, unlist-admin, search-permalinks, starter-content, journal, starter-content-pages, gospel-islam, quran-jesus, tafsir, images, seo, schema, schema-content, schema-extend, rank-math, share, contextual-links, login, security, security-detector, error-pages, error-page-html, article (26 files) |
| Data | `inc/data/` 2.6 MB of JSON: 182 starter pages, the 8 Journal posts (`posts.json`), the gospel narrative, the Qur'anic passages, Thomas (woven sections and two full texts), the infancy gospels, the substitution witnesses, eight commentaries (315,000 words) |
| Front-end assets | `style.css` 32 KB; `assets/css/ornament.css` 12 KB, `share.css` 3 KB, `bible.css` 3 KB, `login.css` 7 KB, `journal.css` 2 KB, `responsive.css` 2 KB (phone header, touch targets, focus ring, loaded on every view); `assets/js/ornament.js` 3 KB, `share.js` 2 KB; all plain, no build step |
| Fonts | 16 MB in `assets/fonts/`, 18 font families with 29 `fontFace` entries in `theme.json` |
| Admin | one tabbed page, Appearance › Theme Options, option `injil_help_options` |
| Updates | Git Updater via the `GitHub Theme URI` and `Primary Branch: main` headers; tag `vX.Y.Z` |

## Requirements and hosting

- WordPress 6.6 or later, PHP 7.4 or later (tested on 8.x). Twenty Twenty-Five installed; it may be inactive but must not be deleted.
- Pretty permalinks. The theme sets `/%postname%/` on activation if the structure is empty.
- Memory: the starter-content run that creates or rewrites 182 pages in one request works within WordPress's default 40 MB; on hosts with a lower `memory_limit` raise it to 128 MB for the first admin load after activation or an update.
- `max_input_vars` and `post_max_size`: the largest starter pages (gospel sections 1, 2 and 27, 370 to 560 KB of block HTML) exceed PHP's default `post_max_size` of 8 MB only on hosts that set it lower; saving them in the block editor needs `max_input_vars` of at least 5000. Reading them needs nothing.
- Page cache: strongly advised. The large pages are generated once and served from the database; a full-page cache (host-level, or any cache plugin) keeps them off PHP entirely. An object cache helps the options page but is not required.
- Database: the 182 pages add about 6 MB to `wp_posts`; revisions are suppressed for theme-written pages.
- Outbound requests: none at runtime. Manuscript images are loaded by the visitor's browser from `upload.wikimedia.org`; nothing is fetched server-side.
- No build tools, Composer or npm. Upload the folder and activate.

## Install and update

1. Install Twenty Twenty-Five. Clone or unzip this repository to `wp-content/themes/injil-help` (the folder name must be `injil-help`).
2. Activate "The Injil". `after_switch_theme` creates every page, sets permalinks, bins the untouched sample post, page and comment and replaces the default tagline.
3. Updates: Git Updater reads the header and offers the release whose tag matches `Version:`. The archive GitHub builds for it leaves out the development files marked `export-ignore` in `.gitattributes` (README.md, SSOT.md, CHANGELOG.md, UPGRADING.md and the two dot-files), so a site updated that way carries only what WordPress reads: the code, the assets, the content, readme.txt and the screenshot. The nine self-tests in `tests/` stay in the repository and out of the archive (`export-ignore`); the page, link and render scripts described under [Development workflow](#development-workflow) still live outside it. On the first admin screen load after the update, `admin_init` rewrites the starter pages whose text changed and leaves edited ones alone (see [Starter content lifecycle](#starter-content-lifecycle)). Take a database backup before updating.
4. Release procedure: bump both version strings together, add the entry to `CHANGELOG.md` and to the Changelog section of `readme.txt`, note anything an existing site must do in `UPGRADING.md`, merge to `main`, tag `vX.Y.Z`.

## Architecture

```
injil-help/
├─ style.css              theme header; tokens (--ih-*); every component style
├─ theme.json             palette, spacing, 18 font families (29 fontFace entries), custom templates
├─ functions.php          version constant, requires, enqueues, pattern category, block styles
├─ inc/
│  ├─ options.php         Theme Options page, defaults, sanitiser, body classes, colour CSS
│  ├─ starter-content.php markup parser, page writer, populate(), activation and update hooks
│  ├─ images.php          Commons and Pexels plates in page content; featured images sideloaded a few pages per admin load; plates served from the Media Library; the image sitemap
│  ├─ starter-content-pages.php  the page list, merged from the JSON files and the builders
│  ├─ gospel-islam.php    gospel sections from narrative.json + thomas + substitution + commentaries
│  ├─ quran-jesus.php     the Qur'anic passages page from quran-jesus.json
│  ├─ tafsir.php          the commentaries: works, verse groups, section map, pages, fold blocks
│  ├─ seo.php             document title, meta description, canonical, Open Graph; no author archives or users sitemap
│  ├─ schema.php          the JSON-LD graph
│  ├─ schema-content.php  the graph's content and page-element nodes: policies, elements, claims, objections, corrections, glosses, manuscripts, translations, subjects
│  ├─ login.php           the log-in screen in the site's dress
│  ├─ schema-extend.php   further graph nodes for the page elements
│  ├─ rank-math.php       Rank Math: graph merge, breadcrumbs, home description
│  ├─ share.php           share row
│  ├─ journal.php         the Journal: posts at /journal/<slug>/ from posts.json
│  ├─ article.php         a post's parts: meta row, In short, contents, Sources block
│  ├─ toc.php             the [auto_toc] contents list and the automatic list on long pages
│  ├─ contextual-links.php links from known phrases in the text (shared map in data/network-links.json)
│  ├─ social.php          profile links in the footer, shortcode and pattern
│  ├─ progress.php        reading progress bar
│  ├─ unlist.php, unlist-admin.php  unlisted posts and pages
│  ├─ search-permalinks.php  /search/{term}/ addresses
│  ├─ security.php        Security tab: locks, spam gate, 410 Gone, the two .htaccess sections, development-file clean-up
│  ├─ security-detector.php  gambling spam score, plain PHP
│  ├─ error-pages.php     wp-content drop-ins and static error pages, the Error pages section
│  ├─ error-page-html.php the error pages as plain HTML, no WordPress function
│  └─ data/               all content, JSON only (pages/, the 34 block patterns in patterns/, tafsir/ and the data files)
├─ templates/, parts/     block templates and the header and footer parts
├─ assets/css|js|fonts|icons|images
├─ errors/                static 403, 500, 502, 503, 504 pages for a site at the domain root
├─ bin/                   build-error-pages.php (command line only)
├─ tests/                 eleven command-line self-tests (export-ignore; refused by .htaccess)
├─ .htaccess              refuses .git*, .claude, tests, bin and *.md in the theme folder
└─ languages/             .mo files
```

Everything the theme writes to the database is JSON in `inc/data/`: the page definitions, the page bodies, the 17 page patterns, the texts they draw on, and the words the PHP builders add (`text.json`, `commentaries.json`), the claim labels, "Notes", "Back to the text" and the activation tagline among them. The front-page sections and the editor's building blocks are JSON patterns in the same folder. The PHP only arranges the data. No `.html`, `.txt` or PHP file holds content, and none may be added for that purpose; only the admin screens and notices keep their words in PHP, for translation. The front end has no framework: two small scripts add to pages that already work without them.

## Boot sequence

`functions.php` defines `INJIL_HELP_VERSION` and loads the files of `inc/` through `injil_help_load_theme_files()`: it checks that every file named in `injil_help_theme_files()` is there, those the others pull in included, then requires the fifteen it loads itself (starter-content pulls in starter-content-pages, which pulls in gospel-islam, tafsir and quran-jesus). A missing or half-written file is logged and answered with a 503 page (`injil_help_serve_incomplete()`), so a theme caught half way through an FTP upload never ends in a fatal error. Then it registers:

- `wp_enqueue_scripts`: `style.css`; `ornament.css` unless Ornament is Off; `ornament.js` (deferred, footer) when the reveal animation or parallax is on. `share.css` and `share.js` are enqueued by `share.php` only on pages that show the row. `bible.css` is enqueued on a singular post whose content contains the `ih-bible` block, which is every gospel section.
- `after_setup_theme`: text domain from `languages/`; `style.css` as editor style.
- `init`: pattern category `injil-help` and the 34 JSON patterns of `inc/data/patterns/`, registered with `register_block_pattern()`; the block style variations listed under [Block styles](#block-styles-and-css).
- `inc/favicon.php` hooks `wp_head`, `login_head` and `admin_head`: a custom favicon URL prints that one link; in brandmark mode (forced, or no Site Icon) the theme prints `assets/images/favicon.svg`, `favicon-32.png`, `apple-touch-icon.png`, `site.webmanifest` and `theme-color` #1f5c4a; with a Site Icon, WordPress prints its own. `/favicon.ico` reaches WordPress when no such file sits in the site root, and `injil_help_serve_favicon()` answers it on `template_redirect` (priority 0, so HEAD is covered) and `do_faviconico`: the icon with `Cache-Control: public, max-age=86400` and an ETag (304 on a match) in brandmark mode, a 302 to a custom URL, or WordPress's redirect to the Site Icon. The manifest is static, with relative icon paths, so it works in a subfolder. The theme `.htaccess` refuses Markdown, `.git*`, `.claude`, `tests` and `bin` only, so every icon file is served. On a site in a subfolder, such as `example.com/blog/`, browsers still ask for `example.com/favicon.ico`, which WordPress never sees: put a copy of `assets/images/favicon.ico` at the domain root there. The head tags cover the pages either way. If a Site Icon is set, put that icon there as favicon.ico instead.

`options.php` adds `enqueue_block_assets` (palette overrides in the editor), `body_class` (`ih-orn`, `ih-px-strong|gentle|off`), and filters on `injil_help_seo_defaults`, `injil_help_default_og_image` and `injil_help_output_schema` that apply the option values. `seo.php` filters `document_title_parts` and prints meta and Open Graph at `wp_head`. `schema.php` prints the graph at `wp_head` priority 5. `share.php` filters `the_content` at priority 20.

## Content as data

| File | Holds | Shape |
|---|---|---|
| `pages/pillar.json`, `gospels.json`, `paul.json`, `quran.json`, `gospel.json`, `bonus.json`, `resources.json`, `site.json` | the starter pages, one file per part, in site order | array of `{path, title, excerpt, pattern? \| body?, body_file?, template?, related?, former?}`; `body_file` names a file in `inc/data/` whose list of lines is appended to `body`; `related` lists the paths linked in the page's "Read next" row; `former` lists the paths the page had in earlier versions, which `populate()` renames in place |
| `narrative.json` | The Narrative of Jesus, Son of Mary | `{chapters: [{number, title, sections: [{number, lines[]}]}], notes: {n: text}}`; `[^n]` note markers, `[v]` verse breaks and `[qS:A]` Qur'anic verse references in lines |
| `gospel-islam.json` | section titles, section URL slugs, Introduction | `{section_titles: {n: title}, section_slugs: {n: slug}, front: {introduction: {title, paras[]}}}`; the slug is the section's URL segment (`/gospel-according-to-islam/john-baptist/`), never its number |
| `related.json` | the curated map of related pages behind the "Read next" row | `{path: [path, …]}`, merged with what the builders set (`injil_help_related_map()`) |
| `quran-jesus.json` | the Qur'anic passages | `{surahs: {n: name}, passages: [{surah, verses_range, heading, verses: {n: text}}]}` |
| `thomas.json`, `early-writings.json`, `infancy.json`, `substitution.json` | woven passages (Thomas, the early writings, the infancy gospels, the substitution witnesses, merged in that order) | `{section: [markup lines]}` |
| `thomas-mattison.json`, `thomas-patterson-meyer.json` | the full text of the Gospel of Thomas in two translations | `[markup lines]`, loaded through `body_file` |
| `patterns/{name}.json` | all 34 block patterns: the 17 that supply whole pages (the pillar, the measure, the five section indexes, the FAQs, how the gospel was composed, the corrections log, the five discrepancy tables, the manuscripts table and the sayings-order test), the 8 front-page sections, the 404 page and the 7 building blocks for the editor | `{title, slug, description, categories, keywords?, content[]}`; `content` is the block markup, one line per item, joined with newlines by `injil_help_pattern_file()`, which also refuses a name that is not a plain slug and a file that is malformed |
| `text.json` | the words the PHP builders put on pages: the commentaries index with its FAQs, the group and part pages, the colophon and the folds (`commentaries`); the running heads, navigation, titles and excerpts of the gospel sections (`gospel`); the title, introduction and source note of The Qur'an on Jesus (`quran_passages`); the "Read next" label (`related`) | `{group: {key: string \| [markup lines]}}`; format strings keep their `sprintf` placeholders; read with `injil_help_text( $group, $key )`. Without this file or `commentaries.json` the page list is empty, so nothing is published with gaps |
| `commentaries.json` | the eight commentaries and the nine verse groups | `{works: {key: {short, title, author, died, about, source}}, groups: {slug: {former, surah, from, to, name, title, intro}}}`, in display order |
| `images.json` | up to four images per page that has any, from Commons or Pexels | `{path: {file, alt, caption, credit, more: [{file, alt, caption, credit}]}}`, and for Pexels also `{source: "pexels", url, page}` with `file` set to `pexels-{id}`; `inc/images.php` puts the first plate near the top, spreads the `more` plates through the text, and sideloads each file into the Media Library as AVIF (WebP under 1000 pixels wide), the first as the featured image |
| `tafsir/{work}.json` | one commentary | `{work, entries: [{surah, from, to, arabic_words, english_words, paragraphs[]}]}` |

`injil_help_starter_pages()` (static-cached per request) concatenates: pillar, gospels, paul, quran, the Qur'an page built by `injil_help_quran_page()`, the commentary pages from `injil_help_tafsir_pages()`, gospel, the 28 gospel pages from `injil_help_gospel_islam_pages()`, bonus, resources, site. Parents precede children in that order, which `populate()` relies on to resolve `post_parent`.

### URLs and internal links

Every URL is made of words, as the Google SEO Starter Guide asks: no page has a number for a URL segment. The gospel sections take their slugs from `section_slugs` in `gospel-islam.json`, the commentary groups from the keys of `injil_help_tafsir_groups()`, and later parts of a long commentary are `{work}-part-two` siblings. A page that changes URL keeps its `former` paths in its definition; on the first admin load `injil_help_adopt_former_page()` finds the page under a former path (or under the `2-2` slug WordPress gave it before 3.5.1), renames it and re-parents it, keeping the ID, the edits and the featured image. Copies left under a former path or under a `-2` slug are binned when unedited and made drafts when edited. `injil_help_redirect_former()` sends each former path, its `-2`/`-3` copies and any address below it to the present URL with a 301. While the present page is missing, a draft or private, a request that would be a 404 goes with a 302 to the nearest published page above it (`injil_help_published_ancestor()`); the map is built from the `former` lists and cached per theme version (transient `injil_help_former_map`).

Every article ends with a "Read next" row (`injil_help_related_blocks()`): the pages in its `related` list, which is the union of what its builder sets (a gospel section names the commentary groups it rests on; a commentary page names the gospel sections, the Qur'an page and the passages page the verses belong with, the group, the index and its other parts) and the curated map in `inc/data/related.json`. Section indexes that already end in their own "Where this leads" aside get no second row. The folds under a gospel section link to every group page the commentary belongs to, and a group page links every part of a work, so no page depends on the menus alone to be reached.

A missing or malformed file yields an empty list from `injil_help_pages_file()` or `injil_help_data_file()`, and null from `injil_help_pattern_file()`, which makes that page "failed". An empty list stops `populate()` before it writes anything; a failed page leaves the version unstamped. Either way the run is retried on the next admin screen load. Validate every file with `php -r` or `python -m json.tool` before committing (see [Development workflow](#development-workflow)).

## The compact markup

A `body` is a list of lines. `injil_help_markup_to_blocks()` turns them into block HTML with these rules, in order of precedence:

| Line | Becomes |
|---|---|
| `- text` | list item; consecutive items form one `core/list`. A leading `[agree]`, `[beyond]`, `[unclear]`, `[conflict]` or `[notneeded]` adds class `ih-s ih-s-{key}` and a badge |
| `? Question` + next line | `core/details` with `is-style-faq`, the next line as its paragraph |
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

1. Takes a lock with `add_option('injil_help_populating')` (atomic insert); a lock older than two minutes is treated as stale.
2. Loads the page list; an empty list exits without writing or stamping.
3. Suppresses revisions (`wp_revisions_to_keep` → 0) for the run.
4. For each definition, in order: finds the page by path (`get_page_by_path`, trashed pages ignored); builds content (`injil_help_page_content()`: the pattern's content, or the parsed body); slashes it; inserts it if missing; if present and `$overwrite`, rewrites it only when the stored content differs and the page is not edited (or `$force`); sets `post_parent` from the path; stores `md5` of the written content in post meta `_injil_help_hash`.
5. Sets pretty permalinks if the structure is empty and flushes rules only in that case.
6. Stamps `injil_help_populated` with the version, only when no page failed, and releases the lock.

`injil_help_page_is_edited()` compares the stored hash with the current content; a mismatch means the owner edited the page. Entry points:

- `after_switch_theme`: first activation only (no stamp yet): removes sample content, then `populate()`.
- `admin_init`, for a user who can `edit_theme_options`: when there is no stamp (the first run did not finish) the background job creates what is missing; when the stamp is older than `INJIL_HELP_VERSION` it rewrites what changed, keeping edited pages.
- `admin_post_injil_help_populate` (capability and nonce checked): `populate($overwrite, $overwrite)` from the Content tab; "Create missing" passes nothing, "Update all" passes `overwrite=1`.


**Writing in steps (3.6.5).** `injil_help_populate_page()` writes one page and returns `created`, `updated`, `unchanged`, `kept` or `failed`. `injil_help_populate_step( $overwrite, $force, $offset, $seconds, $tally )` writes pages from `$offset` until all are done or `$seconds` have passed, takes the `injil_help_populating` lock (stale after two minutes; a held lock returns `busy`), and on the last page runs `injil_help_populate_finish()` (permalinks, rewrite rules, the version stamp). Activation and theme updates start a background job (`injil_help_populate_job_start()`, option `injil_help_populate_job`) that `admin_init` advances by about twenty seconds per admin screen load (not on admin-ajax); `admin_notices` shows its progress and, from the transient `injil_help_populate_report`, its result once. The Content tab's buttons call `wp_ajax_injil_help_populate_batch` (nonce `injil_help_populate`, twelve seconds a request) from `assets/js/admin.js`, which shows the progress in `#ih-populate-status` and then loads `injil_help_populate_redirect()`, whose `ih_done` and `ih_*` counts print the result at the top of the screen through `injil_help_populate_summary()`. Without JavaScript the `admin-post.php?action=injil_help_populate` link does the whole run in one request and redirects to the same result.
## The classical commentaries

Three tables drive it: `injil_help_tafsir_works()` (key, titles, author, death, note, source) and `injil_help_tafsir_groups()` (nine verse groups with slug, surah, range, title, intro), both read from `inc/data/commentaries.json`, and `injil_help_tafsir_section_map()` in `inc/tafsir.php` (which verse ranges each gospel section carries). From the data it builds:

- the index `/quran/commentaries/` (works, passages, how to read, six FAQs);
- one page per group `/quran/commentaries/{group}/` (the group slug is words only: `family-of-imran`, `neither-killed-nor-crucified`, `birth-of-jesus` …; the verse numbers are in the title) listing the works with word counts, the parts of each, and the gospel sections the verses stand in;
- one page per work and group `/quran/commentaries/{group}/{work}/`, split into parts at about 14,000 English words, never splitting an entry; a later part is a sibling page named `{work}-part-two`, `{work}-part-three` … (`injil_help_tafsir_part_path()`); each with the work's colophon, prev/next navigation and a "Read next" row naming the other parts, the group, the gospel sections and the Qur'an page the verses belong with;
- `injil_help_tafsir_section_lines( $n )`: for a gospel section, a `## From the commentaries on …` heading and one `[details]` fold per work containing every entry whose range touches the section's ranges (an entry that straddles two sections appears in both), which `injil_help_gospel_islam_pages()` appends after the Thomas and substitution passages.

Word counts come from `injil_help_tafsir_words()` (tags and footnotes stripped). Adding a work is one entry under `works` in `inc/data/commentaries.json` plus its JSON file; the pages, the index and the folds follow. Entries are sorted by surah and first verse on load and cached per request.

The pipeline that produced the data (fetch the Arabic from tafsir.app, greattafsirs.com or shamela.ws; chunk per verse range; translate; assemble and validate into `inc/data/tafsir/{work}.json`) ran outside the repository. Its validation rules are the ones to keep for any new work: every expected chunk present; no `[[ … ]]` editorial apparatus; only `<strong>` and `<em>` inline; balanced `{{fn:}}`; English at least 1.1 times the Arabic word count; chunks that run past the Jesus verses cut at the commentator's own heading.

## Theme Options

Appearance › Theme Options (`inc/options.php`), one option array `injil_help_options`, registered with `register_setting` and `injil_help_sanitize_options()` (hex colours via `sanitize_hex_color`, enumerations whitelisted, text fields sanitised, URLs escaped). Tabs and keys:

| Tab | Keys |
|---|---|
| Colours | `colors` (nine palette slugs → hex; empty keeps theme.json) |
| Branding | `login_design` 0\|1 and `login_message` (the log-in screen, `inc/login.php`); `toc_auto` 0\|1 (default 0: the contents list on pages with 4 or more level-two headings); `content_width` reading\|wide\|full (default reading since 3.32.0: the 720px column of theme.json under the 1240px header, footer and grids; wide is the 1240px of the header and footer; full fills the page between the gutters; running text stops at `--ih-measure`, 46rem, in every case; a site saved on full moves to reading once, option `injil_help_width_312`; body class `ih-width-{value}`; at full and wide, prose pages flow in two columns from 1280px with headings, tables, plates, folds and asides spanning both, while pages carrying `.ih-bible` keep the gospel's own columns), `ornament` rich\|subtle\|off, `animate` 0\|1, `parallax` strong\|gentle\|off, `footer_mark`, `favicon_force`, `favicon_url` |
| Sharing | `share`, `share_types` (post, page), `share_networks` (ordered subset of the network keys), `share_label`; `social_profiles` (network key to profile URL) and `social_footer` 0\|1 for the profile icons (3.21.0) |
| Reading | `progress` 0\|1, `progress_types` (public post types), `progress_position` top\|bottom, `progress_height` 1-12, `progress_color`, `progress_track` (hex, empty follows the palette) (3.21.0) |
| SEO | `schema` auto\|on\|off, `home_title`, `home_description`, `pillar_title`, `og_image`, `author_name`, `author_url`, `same_as` (one URL per line), `org_logo` |
| Contextual links | `ctx_own`, `ctx_sister` 0\|1 (links to this site and to the other sites); `ctx_own_max`, `ctx_sister_max` (caps per page, 4 and 3); `ctx_extra` (one `phrase \| address` per line, wins over the shared map); `ctx_exclude` (pages that get no links) |
| Security | `block_app_passwords`, `block_xmlrpc`, `block_rest_writes`, `spam_gate`, `spam_gone`, each 0\|1, default 1 (see [Security](#security)); changed only by a user with `manage_options`. Below the form: the spam scan, clean-up buttons, reports and the security log |
| Content | `gospel_layout` columns\|single\|plain (the Bible layout of the gospel sections); `remote_images` 0\|1, default 0 (3.20.0: a plate with no Media Library copy is hidden unless it is 1; filter `injil_help_remote_images`); lists every starter page as present (linked, green dot) or missing (gold dot) and runs the two populate actions |

`injil_help_get_option( $key )` merges the stored array over `injil_help_option_defaults()`. `injil_help_color_css()` emits the overrides as `:root` custom properties on the front end and in the editor (`enqueue_block_assets`). The page's own CSS and JS are `assets/css/admin.css` and `assets/js/admin.js` (a WAI-ARIA tablist with arrow keys, Home and End and the selection carried through the save; live slider values; colour pickers), loaded only on that screen's hook suffix. Since 3.20.0 the screen is built of cards by the `injil_help_ui_*()` helpers: a switch per boolean, selector cards for enumerations, range sliders, inputs, and a usage note per tab; the admin CSS reads the palette tokens so it follows the Colours tab.

## Templates, parts and patterns

- `templates/`: `front-page.html`, `page.html`, `page-section.html` ("Section index"), `page-wide.html` ("Wide page (tables)"), `page-journal.html` ("Journal", the list of posts on `/journal/`), `single.html` (a Journal post), `archive.html`, `home.html`, `index.html`, `search.html`, `404.html`. The three custom templates are declared in `theme.json` `customTemplates`.
- The Journal (`inc/journal.php`): posts at `/journal/<slug>/` through a rewrite rule and the `post_link` filter; their text in `inc/data/posts.json`; `injil_help_journal_seed()` writes them once per version with each entry's fixed `post_date`, publishing a past date and scheduling a future one, and finds them again by the `_injil_help_post` meta so a re-run writes nothing. The list, archive and single views load `assets/css/journal.css`. `tests/journal-test.php` checks the entries and the seeding.
- `parts/header.html`, `parts/footer.html`: navigation, brandmark, Qur'anic gloss (class `ih-gloss ih-ar ih-quran`, `dir="rtl" lang="ar"`).
- `inc/data/patterns/` (JSON, category `injil-help`, registered on `init`), the only patterns the theme has. The front-page sections `hero`, `home-dilemma`, `home-gospels`, `home-paul`, `home-quran`, `home-gospel`, `home-answers`, `home-resources`, which `templates/front-page.html` renders by slug in that order. The order is a story, recorded in `SSOT.md`: the claim, the argument (Part I), the evidence in the books and in Paul of Tarsus (Parts II and III), the Qur'an as the measure over both (Part IV), what the measure keeps (Part V), the questions a reader brings (Part VI), and the sources and corrections log to test it all by (Part VII). The building blocks `parts-grid`, `quran-verse-index`, `answer-capsule`, `claim-labels`, `gospel-verse`, `objection-reply`, `faq`. And the page patterns: `islamic-dilemma-page`, `criterion-statement`, `section-gospels`, `section-paul`, `section-quran`, `section-gospel`, `section-resources`, `answers-page`, `how-composed`, `corrections-log`, `discrepancy-genealogies`, `discrepancy-nativity`, `discrepancy-last-supper`, `discrepancy-crucifixion`, `discrepancy-resurrection`, `thomas-correlation`, `manuscripts-table`. A page definition names one by file name and `injil_help_page_content()` reads it with `injil_help_pattern_file()`, so populating never depends on the pattern registry. Every new pattern is added here, as JSON.

## Block styles and CSS

Registered in `functions.php`, styled in `style.css`:

| Block | Styles |
|---|---|
| `core/paragraph` | `label-consensus`, `label-minority`, `label-islamic`, `label-quran`, `label-nt`, `label-editorial` |
| `core/group` | `answer-capsule`, `gospel-verse`, `objection-reply`, `criterion`, `answer-item`, `read-first` |
| `core/table` | `discrepancy` |
| `core/separator` | `ornament` |
| `core/details` | `faq`; `fold` is applied by the parser and styled, not registered as a picker style |

`style.css` opens with the tokens: radii `--ih-r-sm|md|lg`, font roles `--ih-serif`, `--ih-sans`, `--ih-display`, `--ih-greek`, `--ih-quran`. Colours are always `var(--wp--preset--color--{slug})`; nothing is hard-coded. Component classes are prefixed `ih-` (`ih-section-head`, `ih-entry`, `ih-k`, `ih-eyebrow`, `ih-numeral`, `ih-plate`, `ih-fnref`, `ih-footnotes`, `ih-s`, `ih-badge`, `ih-share*`). Language-scoped fonts come from `:lang()` selectors.

`.ih-lanah` is the calligraphic لعنة الله set after the name of Paul of Tarsus: an empty span with `role="img"` and an `aria-label`, drawn from `assets/images/lanat-allah.svg` (Dubidam Arabic Regular outlines) as a CSS mask in the rubric red. It is placed by hand and kept to the Epistle's autograph line and the verdict on The false apostles.

## Fonts

All faces are bundled in `assets/fonts/` and declared as `fontFace` in `theme.json`, so they appear in the editor's picker and are served from the theme with no third-party requests. Slugs and roles: `sabon` body, `garamond` headings, `kagnue` and `kagnue-outline` display (site title, cover title, section index and home section titles, large numerals), `evangelion` labels and navigation, `sbl-greek`, `sbl-hebrew`, `sbl-biblit` biblical languages, `hafs` Qur'anic text only (class `ih-quran`), `amiri` other Arabic (`ih-ar`, `:lang(ar)`), `naskh` hadith (`ih-hadith`), `estrangelo` Syriac (`:lang(syc)`), `cuneiform` (`:lang(akk)`), `special-elite` corrections log (`ih-record`), `dubidam`, `arslan`, `ferments`, `angel` available. Licences: Sabon Next LT and Kagnue are commercial faces licensed to the site owner; Angel keeps its README; KFGQPC HAFS Uthmanic Script (King Fahd Glorious Quran Printing Complex) is free to use, copy and distribute but may not be modified; by the owner's decision of 2026-10-26 it is served as a WOFF2 conversion, `KFGQPC-Hafs-Uthmanic.woff2`, beside its licence `KFGQPC-Hafs-Uthmanic-LICENSE.txt` (see SSOT.md): never subset it or convert it again; the rest are OFL or free. Kagnue lacks the dotted transliteration letters, so it is kept off headings that may carry them.

### The Bible layout

`assets/css/bible.css` styles the gospel sections: `.ih-running-head` above the text; `.ih-bible` (the group block) with `.ih-argument` (italic, spanning both columns), `.ih-chapter-num` (the section numeral in the display face, floated at the start of the text), `h3` sub-headings in small capitals and `.ih-v` verse numbers (`.ih-vq` for the Qur'an's own); `.ih-apparatus` for the notes under a rule in small type. Two columns with a rule apply at 900px and above under `body.ih-gospel-columns`; `ih-gospel-single` keeps one column; `ih-gospel-plain` hides the running head, the numeral and the verse numbers. The body class comes from the `gospel_layout` option; the markup never changes, so switching the option is instant and reversible. Print always sets two columns.

## Ornament and parallax

`assets/css/ornament.css` draws the star lattice, section banners with watermark icons, card icons, framed plates and the footer pattern, all as CSS masks over `assets/icons/*.svg` and `assets/images/pattern-star.svg`, so every decoration takes its colour from the palette. It loads only when Ornament is not Off; `body.ih-orn` scopes it.

`assets/js/ornament.js` (3 KB, no dependencies) exits at once under `prefers-reduced-motion`. Reveal: an `IntersectionObserver` adds `is-in` to cards, answers and plates (`html.ih-js .ih-reveal`). Parallax: when `body.ih-px-strong` or `.ih-px-gentle` is present it sets `--ih-sy` (scroll offset) on the root and `--ih-p` (progress through the viewport, −1 to 1) on each layered element, throttled with `requestAnimationFrame` and skipped for elements far off screen; `ornament.css` turns these into `transform`s at per-layer rates multiplied by `--ih-px` (1 or 0.45). Only transforms change, so there is no layout work during scroll.

## SEO and structured data

`inc/seo.php`: `injil_help_seo_defaults()` (filterable) supplies the home title, home description and pillar title. `injil_help_document_title()` builds every title tag as "page part | site name" under 60 characters: the page part is the short title in `inc/data/seo-titles.json` for that path, else the page title, fitted by `injil_help_title_fit()` to the room the site name leaves. It answers `pre_get_document_title`, and Rank Math's, Yoast's and SEOPress's title filters at priority 99. `wp_head` prints `meta description` (the excerpt or the first paragraph, trimmed to 160 characters at a sentence end by `injil_help_trim_description()`), Open Graph and Twitter card tags, and the featured image or the default social image (`assets/images/og-default.jpg`, filter `injil_help_default_og_image`). `injil_help_is_pillar()` identifies `/islamic-dilemma/`. `injil_help_breadcrumb_html()` prepends the visible breadcrumb row (Home, the parents, the page) to the content of every page and post below the front page at `the_content` priority 5; search result pages carry `noindex` through `wp_robots`. `injil_help_archive_canonical()` gives the canonical of every view that is not singular (front page, terms, post types, dates, posts page, with `/page/N/`); core prints the singular one. The users sitemap is removed through `wp_sitemaps_add_provider`, and `is_author()` redirects to `/about/` with a 301 at `template_redirect` priority 1.

`inc/images.php` also filters `render_block_core/image`: `injil_help_localise_img()` points a plate at its Media Library copy (found by `_injil_help_commons`) with width, height and the class `wp-image-{id}`, so `wp_filter_content_tags()` adds srcset and lazy loading; a plate with no copy gets `loading="lazy"` and `decoding="async"`, the cover image excepted. `/image-sitemap.xml` is served at `template_redirect` priority 0 and named in `robots.txt`, unless Rank Math's sitemap module is on.

`inc/schema.php`: `injil_help_schema_enabled()` honours the SEO option and switches off when Yoast, Rank Math or SEOPress is active (filter `injil_help_output_schema`). `injil_help_schema_graph()` builds one `@graph` at `wp_head` priority 5 from every schema.org type the content supports, whatever a search engine shows: Organization (logo, sameAs, ContactPoint), Person (author from the SEO tab or the post author), WebSite (SearchAction; about ʿĪsā ibn Maryam, the Qur'an and the Islamic Dilemma), Person and Book nodes for those two subjects with Wikidata identifiers, an ItemList of SiteNavigationElement from the header, the page node typed by `injil_help_schema_page_type()` (CollectionPage with an ItemList when the page has three or more entries or is the sitemap, ProfilePage + AboutPage for the About page, ContactPage, SearchResultsPage, else WebPage; every page carries a SpeakableSpecification), BreadcrumbList from ancestors, Article + ScholarlyArticle or BlogPosting when `injil_help_schema_is_article()` says so (word count, dates, section, images, `citation` parsed from the Sources list as CreativeWork or ScholarlyArticle-in-Periodical), Book + Chapter for the gospel sections, Book (Arabic, with its author) + CreativeWork (the English rendering, `translationOfWork`) for each commentary page, DefinedTermSet + DefinedTerm for the glossary, Table for every comparison table, Dataset for the sayings-order test, Event for every dated timeline entry, Quotation for every quote block (at most 40), FAQPage from `is-style-faq` details and `answer-item` groups, ImageObject for every figure (licence read from the caption, creator from "Photo: Name", acquireLicensePage from the Commons link). Patterns are expanded with `injil_help_expand_patterns()` so the collectors see the real blocks. Filter the finished array with `injil_help_schema_graph`.

## Rank Math

`inc/rank-math.php`. With Rank Math active and the SEO option on Automatic (`injil_help_rank_math_mode()`), the theme's own head output and graph are off and the theme works inside the plugin: `injil_help_rank_math_json_ld()` on `rank_math/json_ld` (priority 50) merges into Rank Math's data every node of `injil_help_schema_graph()` that Rank Math does not make, skips the theme's Organization, WebSite, page node, BreadcrumbList and Article (the author Person is kept, since Rank Math's publisher may be an Organization), copies the Article's `citation`, `about`, `speakable` and extra `isPartOf` onto Rank Math's Article, and repoints every reference to those (`injil_help_rank_math_repoint()`) to Rank Math's publisher, WebPage and Article. A FAQPage is added only when Rank Math has none. `add_theme_support( 'rank-math-breadcrumbs' )` is declared; while Rank Math's breadcrumbs are on, the `injil_help_breadcrumb_html` filter replaces the theme's trail with `rank_math_get_breadcrumbs()` wrapped in the theme's `.ih-breadcrumb` nav. `rank_math/frontend/description` supplies the theme's home description when Rank Math's is empty. "Always on" prints the theme's head and graph beside the plugin's; "Always off" prints nothing.

## Share row

`inc/toc.php` (3.31.0) adds the `[auto_toc]` shortcode (attributes `tags`, default `h2,h3`, and `title`, default Contents; an empty title shows none). The shortcode leaves a comment marker, because WordPress expands shortcodes at `the_content` priority 11 and a filter that looks for the shortcode after that finds none. The filter at priority 13, after the Journal's heading ids (12) and before the contextual links (15), reads the finished headings, gives each heading of a listed level that lacks an id one made from its text, and replaces the marker with a `nav.ih-toc` holding a nested `ol`. An id the author gave is kept, and a made id never repeats any id on the page. Only the opening tag of a heading without an id changes; the text is not parsed or rewritten as a whole. Outside the main text of a page (an excerpt, a feed, a widget, REST) the marker is removed and nothing else changes. With `toc_auto` on (Branding > Layout), a page with 4 or more level-two headings (filter `injil_help_toc_min`) gets the list after the breadcrumb row; a page with its own `[auto_toc]` keeps its own. `assets/css/toc.css` loads only on pages that carry a list. The page markup accepts a `[toc]` line, which becomes a shortcode block; the Glossary and Objections pages use it. The shortcode interface follows Ikram Hawramani's MIT-licensed *WordPress Table-of-Contents Shortcode Done Properly* (2025); the code is the theme's own.

`inc/article.php` (3.25.7) builds the parts of a Journal post: `[injil_help_article_meta]` (reading time and the updated date, in the template's meta row), ids on the level-two headings (`the_content` priority 12), and at priority 16, after the contextual links and before the share row, "In short" from `_injil_help_points`, the contents list from three headings, and the `.ih-sources` frame around the Sources list and the Notes. Styles in `assets/css/article.css`. `injil_help_redirect_former()` in `inc/starter-content.php` keys its cached map by `injil_help_data_fingerprint()` and resolves old commentary addresses through `injil_help_former_commentary_target()`; `tests/former-redirect-test.php` checks the address shapes from the November 2026 log.

`inc/share.php` appends the row to `the_content` (priority 20) on singular posts and pages of the chosen types, not on the front page, and not when the filter runs for an excerpt (`doing_filter('get_the_excerpt')`) or for a post that is not the queried one. Networks are a filterable table of `{key: [name, endpoint with {url} {title} {image}]}`; icons are masks over `assets/icons/social/{key}.svg`, coloured by the palette. `assets/js/share.js` reveals the Copy link button when `navigator.clipboard` is available in a secure context (with "Copied" and failure feedback) and the More… button when `navigator.share` exists.

## Built-in plugin features

Since 3.21.0 the theme does the work of four plugins. Each of the last three turns itself off while its plugin is active.

- Profile links, `inc/social.php`. `injil_help_social_markup()` prints a `nav.ih-social` of links, each with an `aria-label` and an inline SVG from `assets/icons/social/{key}.svg` marked `aria-hidden` and `focusable="false"`, filled with `currentColor`. The footer gets it from a `render_block_core/group` filter after `.ih-legal`; the shortcode `[injil_help_social]` and the pattern `injil-help/profile-links` put it elsewhere. `assets/css/social.css` loads only when the row prints.
- Reading progress bar, `inc/progress.php`. On singular views of the chosen types (never the front page) `wp_body_open` prints `div.ih-progress` with `aria-hidden` and `hidden`; `assets/js/progress.js` unhides it and sets `--ih-read` from a passive scroll listener through `requestAnimationFrame`. It is decorative because the scroll bar carries the same information and a live `progressbar` would be announced on every scroll. Above 600 pixels a top bar sits under the admin bar; below, the admin bar (z-index 99999) covers it until it scrolls away.
- Unlisted posts, `inc/unlist.php` and `inc/unlist-admin.php`, after Unlist Posts & Pages 1.2.1: `posts_where` (not singular, not an admin screen, not an AJAX call from this admin), `get_next_post_where` and `get_previous_post_where`, `wp_robots` (and Yoast's robots emptied), `rank_math/frontend/robots`, `wp_list_pages_excludes`, `get_pages`, `wp_sitemaps_posts_query_args`, `rank_math/sitemap/entry`, `rank_math/sitemap/posts_to_exclude`, the image sitemap, and the "Read next" row through `render_block_core/group`. Admin: the side box, the column, Quick Edit (`assets/js/unlist-admin.js`), Bulk Edit, the post state and the Unlisted view. The truth is the post meta `_injil_help_unlisted`; `injil_help_unlist_rebuild()` writes the option `injil_help_unlisted` from it and mirrors it to `unlist_posts`, and `injil_help_unlist_sync()` takes over a change the plugin made to that list. `injil_help_unlisted_live_ids()` reads whichever list is live.
- Search addresses, `inc/search-permalinks.php`, after Pretty Search Permalinks 1.3: the base from `injil_help_search_base` on `init`; a field under Settings › Permalinks › Optional saved with the screen's `update-permalink` nonce; a 301 at `template_redirect` from `?s=` to `/{base}/{term}/` with `/page/N/` and other arguments kept, none for an empty term or a feed. `injil_help_search_pretty_url()` builds the address. Core already has the rules for the base; they are flushed once per change of `INJIL_HELP_RULES`.

`php tests/toc-test.php` checks the contents list: the shortcode marker, ids that are kept and never repeated, nesting that stays balanced when levels jump or fall, the automatic list, and a marker that never reaches an excerpt.

`php tests/plugin-features-test.php` checks the unlisted filtering and the search addresses.

`php tests/schema-render-test.php` checks the structured data against malformed blocks: it reproduces core's fatal error with a block that has more `null` placeholders than inner blocks, then checks that `injil_help_schema_render()` and `injil_help_expand_patterns()` keep every inner block, the front-page template with a pattern missing, the term links and the 503 guard in `functions.php`. `php tests/hardening-test.php` checks the account-name guards, the fall-back of former addresses, the privacy page, the clean-up of development files and the `.htaccess`.

`php tests/login-test.php` checks the log-in screen (`inc/login.php`): the Branding switch, the step-aside while a log-in plugin is active, the body classes, the note above the form and every title tag.

## Hooks and filters

| Filter | Purpose |
|---|---|
| `injil_help_seo_defaults( array )` | home title, home description, pillar title |
| `injil_help_default_og_image( string )` | default social image URL |
| `injil_help_output_schema( bool )` | turn the structured data on or off |
| `injil_help_schema_graph( array )` | edit the graph before output |
| `injil_help_share_networks( array )` | add, remove or reorder networks |
| `injil_help_social_networks( array )` | the networks a profile link can point to |
| `injil_help_unlist_enabled( bool )` | turn unlisting off |
| `injil_help_search_permalinks_enabled( bool )` | turn the search addresses off |
| `injil_help_search_reserved_bases( array )` | words the search base may not take |
| `injil_help_breadcrumb_trail( array )`, `injil_help_breadcrumb_html( string )` | edit the breadcrumb trail, or its markup |
| `injil_help_contextual_links_enabled( bool, WP_Post )`, `injil_help_contextual_links_map( array )` | switch contextual links off for one page; change the shared phrase map |
| `injil_help_remote_images( bool )` | serve plates that have no Media Library copy from Commons and Pexels |
| `injil_help_login_plugin_active( bool )` | whether a log-in plugin owns the log-in screen |
| `injil_help_sec_rest_allow( array )` | REST route prefixes that may write with non-cookie authentication |
| `injil_help_owner_digests( array )` | add SHA-1 digests of names the structured data must keep out of everything but the author node (`inc/schema-extend.php`); the built-in ones always stay |
| `unlist_posts_enabled( bool )` | the plugin's own switch, honoured as well |

Constants, each optional, set in `wp-config.php`:

| Constant | Effect |
|---|---|
| `INJIL_HELP_SECURITY_LOCK` | `false` lifts the three locks (XML-RPC, application passwords, REST writes) without changing the options |
| `INJIL_HELP_ALLOW_XMLRPC` | `true` keeps XML-RPC on for a client other than Jetpack, and leaves the xmlrpc.php lines out of `.htaccess` |
| `INJIL_HELP_ALLOW_CORE_FILES` | `true` takes "The Injil: deny direct core files" out of `.htaccess` |
| `INJIL_HELP_KEEP_DEV_FILES` | `true` keeps `.git`, `.claude`, `tests` and the notes on a production site |
| `INJIL_HELP_SPAM_THRESHOLD` | spam score at which a post is held (default 3) |
| `INJIL_HELP_SCHEMA_AUTHOR_NAME`, `INJIL_HELP_SCHEMA_AUTHOR_URL` | the schema author, defined in `inc/schema.php` unless set first (`SSOT.md`, Structured data and SEO) |

Actions used: `after_switch_theme`, `admin_init`, `admin_post_injil_help_populate`, `admin_menu`, `admin_enqueue_scripts`, `enqueue_block_assets`, `wp_enqueue_scripts`, `wp_head`, `init`, `after_setup_theme`. Options written: `injil_help_options`, `injil_help_populated`, `injil_help_populating`. Post meta: `_injil_help_hash`.

## Performance

- No render-blocking scripts: both front-end scripts are deferred and tiny; CSS totals under 50 KB uncompressed.
- Fonts are the heaviest asset class (16 MB on disk, but a page loads only the faces its text uses, by `font-family` fallback). Preloading `SabonNextLT.ttf` and `EBGaramond-Variable.ttf` is worthwhile; subsetting is on the roadmap.
- The commentary pages and the gospel sections 1, 2, 3, 14 and 27 are the only pages above 100 KB of HTML. Serve them through a page cache. The fold blocks keep them collapsed, so first paint is unaffected.
- `injil_help_starter_pages()` is built once per request and only in admin (Content tab, populate) or when the schema needs an entry count; it is not on the front-end hot path.

## Security

- The options page requires `edit_theme_options`; saving goes through the Settings API with a sanitiser; the populate actions check capability and a nonce (`injil_help_populate`).
- Content written to the database is kept only as JSON in `inc/data/`; no `.html` or `.txt` file is read for it. `injil_help_pattern_file()` opens a file only when the name is a plain slug (`[a-z0-9-]`), and a malformed file or a content line that is not a string makes the page fail rather than publish half-made.
- All output is escaped at the point of printing; page content written by the theme passes through `wp_kses_post` in the parser; data files are static and shipped with the theme, never fetched.
- No remote code, no eval, no inline scripts; the two scripts and three stylesheets are files, so a Content-Security-Policy with `script-src 'self'` works. `style-src` needs `'unsafe-inline'` only for the palette override block that WordPress prints as an inline style; put it in a file if your policy forbids that.
- Share links open third-party sites with `rel="noopener noreferrer nofollow"`.
- Account names stay private to visitors who are not logged in: the REST routes `/wp/v2/users` and `/wp/v2/users/<id>` are removed for them (`rest_endpoints`), `?author=N` goes to `/about/` at `parse_request` before `redirect_canonical()` can name the slug, and oEmbed answers carry no `author_name` or `author_url`. Author archives already go to `/about/`.
- The theme's `.htaccess` refuses `.git*`, `.claude`, `tests`, `bin` and `*.md` inside the theme folder on Apache. Nginx needs the rule in the server block: `location ~* /wp-content/themes/injil[.-]help[^/]*/((\.git[^/]*|\.claude|tests|bin)(/|$)|.*\.md$) { return 403; }` (the folder is `injil-help` in a release archive and `injil.help` in a copy of the repository). The `[^/]*` also covers the `-new` and `-old` folders of an FTP update, so add the rule before the first one. On a production site `injil_help_dev_cleanup()` deletes the entries of `injil_help_dev_entries()` (what `.gitattributes` leaves out of the release archive) on the next admin screen opened by a user who can update themes (on a network, a network administrator). It skips a site whose `wp_get_environment_type()` is not `production`, a local host name or private address, and a site with `INJIL_HELP_KEEP_DEV_FILES` set to true. It removes links without following them and stays inside the theme folder.
- Every PHP file in `tests/` and `bin/` answers a web request with a 404 before it runs anything.
- WordPress's own files (from 3.25.5). On Apache the theme keeps a second marked section in the site's `.htaccess`, `# BEGIN The Injil: deny direct core files`, written with the xmlrpc.php section by `injil_help_sec_sync_htaccess()` and taken out on a theme switch. It answers 403 for the PHP files in `wp-includes`, `wp-settings.php`, `wp-config-sample.php`, `readme.html`, `wp-admin/install.php`, `wp-admin/setup-config.php` and, while XML-RPC is off, `xmlrpc.php`. Bots ask for these by address, and each request for a `wp-includes` file ended in a fatal error in the log. TinyMCE's loader under `wp-includes/js/tinymce/` and `ms-files.php`, which a network's rules hand uploads to, stay open. The check runs on admin pages at most once an hour, and at once when what the sections should hold has changed (Jetpack switched on, a constant added, the XML-RPC switch moved); a failed write is tried again on the next admin page. With WordPress in its own subfolder the rules carry that folder (`injil_help_sec_core_prefix()`); with WordPress outside the `.htaccess` folder no section is written. `define( 'INJIL_HELP_ALLOW_CORE_FILES', true );` in wp-config.php takes it out. Where the theme cannot write `.htaccess`, the Security tab shows the lines to paste after the WordPress section (`injil_help_sec_htaccess_snippet()`). At the domain root they read:

  ```
  # BEGIN Block xmlrpc.php
  <Files xmlrpc.php>
  <IfModule mod_authz_core.c>
  Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
  Order Deny,Allow
  Deny from all
  </IfModule>
  </Files>
  # END Block xmlrpc.php
  # BEGIN The Injil: deny direct core files
  <IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteRule ^wp-includes/(?!js/tinymce/|ms-files\.php$).+\.php$ - [F,L]
  RewriteRule ^(wp-settings|wp-config-sample)\.php$ - [F,L]
  RewriteRule ^readme\.html$ - [F,L]
  RewriteRule ^wp-admin/(install|setup-config)\.php$ - [F,L]
  RewriteRule ^xmlrpc\.php$ - [F,L]
  </IfModule>
  <IfModule !mod_rewrite.c>
  <IfModule mod_alias.c>
  RedirectMatch 403 "^/wp-includes/(?!js/tinymce/|ms-files\.php$).+\.php$"
  RedirectMatch 403 "^/(wp-settings|wp-config-sample)\.php$"
  RedirectMatch 403 "^/readme\.html$"
  RedirectMatch 403 "^/wp-admin/(install|setup-config)\.php$"
  RedirectMatch 403 "^/xmlrpc\.php$"
  </IfModule>
  </IfModule>
  # END The Injil: deny direct core files
  ```

  The `RedirectMatch` lines refuse the same files on an Apache without mod_rewrite. They match the whole URL path, so they carry WordPress's URL path (`injil_help_sec_core_base()`, from `site_url()`), such as `^/blog/readme\.html$`. While XML-RPC stays on, both `xmlrpc.php` lines and the "Block xmlrpc.php" section are left out.

  On Nginx the same rules go in the server block: `location ~ ^/wp-includes/(?!js/tinymce/|ms-files\.php$).+\.php$ { return 403; }` and `location ~ ^/(wp-settings\.php|wp-config-sample\.php|readme\.html|wp-admin/(install|setup-config)\.php)$ { return 403; }`, and, only while XML-RPC is off (no Jetpack, no `INJIL_HELP_ALLOW_XMLRPC`), `location = /xmlrpc.php { return 403; }`. A WordPress in a subfolder puts its URL path in front, as in `^/blog/wp-includes/`. `php tests/live-logs-test.php` checks the switch, the Jetpack and constant cases, the lines, the subfolder prefix and the writing and removal of both sections.
- The installer lock. The site was taken over through an open `wp-admin/setup-config.php` while WordPress core sat on the server without `wp-config.php`. In that state the installer answers anyone, and the first visitor to reach it can connect the site to a database he controls. The theme cannot help then, because WordPress never loads it. Two things do. Never upload WordPress core to a live folder without its `wp-config.php` (upload `wp-config.php` first, or install in a folder the web server does not serve). And once the site is installed, put this rule in the server configuration, or at the top of the site's `.htaccess` above every `# BEGIN` section, where no plugin or theme rewrites it:

  ```
  # Installer lock: the site is installed. Keep this above every BEGIN section.
  <IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteRule ^(.+/)?wp-admin/(install|setup-config)\.php$ - [F,L]
  </IfModule>
  ```

  In an Apache virtual host the same lock is `<LocationMatch "^/(.+/)?wp-admin/(install|setup-config)\.php$"> Require all denied </LocationMatch>`; on Nginx it is `location ~ ^/(.+/)?wp-admin/(install|setup-config)\.php$ { return 403; }`. The `(.+/)?` lets the same rule cover WordPress in a subfolder of the `.htaccess` folder, such as `wp/wp-admin/setup-config.php`. Upgrades are untouched: they run through `wp-admin/upgrade.php`. Take the rule out for the length of a fresh install and put it back after.
- Application passwords are blocked by default: `wp_is_application_passwords_available` returns false while the `block_app_passwords` option is set (its default), so no client can log in through the REST API or XML-RPC with a per-application password. Untick the block under Theme Options › Security for a client that needs them.
- `inc/security.php` (from 3.13.0, ported from the Abrahamic theme 2.87.0): XML-RPC answers 403 (`block_xmlrpc`; counted per day in `injil_help_security_counts`; on Apache a "Block xmlrpc.php" marker section in .htaccess, shared with the sister themes). With it off, `xmlrpc_enabled` is false, `xmlrpc_methods` is empty (the pingback methods with the rest), the `X-Pingback` header and the RSD link go and pings close. From 3.25.5 it stays on while the Jetpack plugin is active, or with `define( 'INJIL_HELP_ALLOW_XMLRPC', true );` in wp-config.php (`injil_help_sec_xmlrpc_allowed()`), and the .htaccess section is then taken out; a password login through XML-RPC is refused whatever the switch; logged-in REST writes need a cookie session with a valid `wp_rest` nonce (`block_rest_writes`; filter `injil_help_sec_rest_allow` lists route prefixes exempt from it); failed logins are counted by account name (`injil_help_security_failed`); the spam gate (`spam_gate`, `wp_insert_post_data`) holds gambling spam and anything published or scheduled through XML-RPC, a header login or WP-CLI as Pending (private posts are only logged), posts and pages only, starter pages (`_injil_help_hash`, or the theme's own page writing) exempt; trashed spam answers 410 (`spam_gone`). The detector is `inc/security-detector.php`, plain PHP; `php tests/security-detector-test.php` checks it against every string in `inc/data/` and the attack samples. `INJIL_HELP_SECURITY_LOCK` false in wp-config.php turns the three locks off. The theme registers no REST routes and no front-end forms, so nothing of its own writes through the REST API.

## Error pages

WordPress reads three files from `wp-content` when it cannot build a page. `inc/error-pages.php` writes them when the theme is activated (`after_switch_theme`), again on an administrator's next admin page when a file is missing or outdated (`admin_init`, `edit_theme_options`), and at once when an administrator presses **Install error pages** at the foot of Theme Options (`admin_post_injil_help_install_error_pages`, nonce and `manage_options`). When another theme is activated (`switch_theme`), it deletes the files that carry its marker, except on a network: `injil_help_error_remove()` returns at once under `is_multisite()`, because every site shares `wp-content` and another site may still run the theme. The table beside the button shows each file as not installed, installed, outdated, left behind by another theme or another file left alone.

| File | Shown when | Status sent |
|---|---|---|
| `maintenance.php` | WordPress is updating | 503, `Retry-After: 600` |
| `php-error.php` | a fatal PHP error | 500 |
| `db-error.php` | the database cannot be reached | 503, `Retry-After: 600` |

Each file starts with the marker `The Injil error page drop-in`. The theme writes a missing file, one with that marker, or a copy another theme left behind (marked "Written by the ... theme; safe to delete"). It leaves any other file alone. The pages come from `inc/error-page-html.php` with their words in `text.json` (`errors`). They hold their style inline, call no WordPress function and touch no database, so they work with the theme half uploaded. Each sends `noindex` and `no-store`.

Every page carries the brandmark as its tab icon, as a `data:` address built from the inline mark, so it needs no path.

The web server answers some errors before WordPress starts. The theme writes static 403, 500, 502, 503 and 504 pages for those into `wp-content/injil-help-errors/`, at the same times as the drop-ins and with the same marker. Each copy carries the site's name and its real home address, so the home link works on a site in a subfolder. The theme removes them when another theme is activated, so take the `ErrorDocument` lines out of `.htaccess` at the same time; until you do, Apache answers those errors with its own plain message. On a network the files stay, and only the main site writes them, since every site shares `wp-content` and one `.htaccess`. Point the server at them with the lines from the Error pages section, which are built from the real address of `wp-content`. At the domain root they read:

```
ErrorDocument 403 /wp-content/injil-help-errors/403.html
ErrorDocument 500 /wp-content/injil-help-errors/500.html
ErrorDocument 502 /wp-content/injil-help-errors/502.html
ErrorDocument 503 /wp-content/injil-help-errors/503.html
ErrorDocument 504 /wp-content/injil-help-errors/504.html
```

`errors/` in the theme keeps the copies for a site at the domain root, rebuilt with `php bin/build-error-pages.php` (`--check` fails on a stale page).

## Development workflow

1. Edit content in `inc/data/` (JSON only, patterns included), behaviour in `inc/`, styles in `style.css` or `assets/css/`. Never put article text in PHP.
2. Checks before committing (all run without WordPress):
   - `for f in $(git ls-files '*.php'); do php -l $f; done`
   - JSON validity: `for f in $(git ls-files 'inc/data/*.json' 'inc/data/**/*.json'); do python3 -m json.tool $f > /dev/null || echo $f; done`
   - Page list and links: a script that stubs `get_stylesheet_directory()` and the handful of WordPress functions the loaders call, requires `inc/starter-content-pages.php`, calls `injil_help_starter_pages()`, and asserts no duplicate paths, every child has a parent, and every `href="/…/"` in a body or pattern points at a starter path.
   - Render: with the same stubs plus WordPress's block parser classes, run every definition through `injil_help_page_content()`, `parse_blocks()` it, and assert every `wp:details` opens and closes and no `null` block names appear where a block was intended.
   - Structured data: with the stubs, call `injil_help_schema_graph()` for one page of each type and check every `@id` reference resolves.
   - The self-tests in the repository, each a plain PHP script with WordPress stubbed: `for t in tests/*.php; do php "$t" || exit 1; done`, and `php bin/build-error-pages.php --check`, which fails on a stale static error page.
   The page, render and structured-data scripts lived in a scratch directory during development; `UPGRADING.md` asks for them to be brought into the repository and run in CI.
3. Commit messages describe the change; no tool or model names in any artefact.
4. Release as in [Install and update](#install-and-update).

## Troubleshooting

- **Pages missing after activation.** Open Appearance › Theme Options › Content and press "Create missing pages". If the list itself is empty, a data file failed to parse: validate the JSON.
- **An update did not change a page.** The page was edited after the theme wrote it (shown as "edited, kept on update"). Use "Update all pages from the theme" to replace it, or re-apply your edit after.
- **Populate seems stuck.** The lock option `injil_help_populating` is treated as stale after two minutes; reload.
- **Saving a gospel section fails in the editor.** Raise `max_input_vars` (5000) and `post_max_size`; or edit the data file and rewrite the page.
- **No structured data in the source.** An SEO plugin is active (the theme defers to it), or the SEO tab is set to Off.
- **Fonts do not load.** Check the host serves `.ttf` and `.otf` with a font MIME type and that `assets/fonts/` uploaded completely (16 MB).
- **Share row on the front page.** It is excluded by design; elsewhere check Theme Options › Sharing and the post type.

## Licence

GPL v3 or later for the theme code and the site's own text, including the English renderings of the commentaries; the full text is in `LICENSE`. Fonts, the gospel base text, the Qur'an translation, the Nag Hammadi translations and the manuscript images keep their own licences, named in `readme.txt` and on the Credits page.
