# Single source of truth

The fixed facts about The Injil theme. When another document, a comment or a pattern disagrees with this file, this file is right and the other is to be corrected. Keep it short and factual; explanations belong in `readme.txt`.

## Identity

| Fact | Value |
|---|---|
| Theme name | The Injil (the site title stays The Rightly-Guided Injīl) |
| Author | MENJ, https://menj.blog |
| Theme URI | https://github.com/menj/injil.help |
| Folder and text domain | `injil-help` |
| Parent theme | Twenty Twenty-Five (`twentytwentyfive`), must stay installed |
| Site | https://injil.help (The Rightly-Guided Injīl). The theme is custom-built for this one site and is not intended for use on any other. |
| Repository | https://github.com/menj/injil.help, `Primary Branch: main` (Git Updater). README.md, SSOT.md, CHANGELOG.md, UPGRADING.md and the dot-files are `export-ignore`: they stay in the repository and out of the release archive. No test files are kept in the repository; the release checks live outside it. |
| Version | `Version:` in `style.css` and `INJIL_HELP_VERSION` in `functions.php`, always equal; the release tag is `v` + that number |
| Requires | WordPress 6.6, PHP 7.4 |
| Licence | GPL v2 or later; fonts and images keep their own licences |

## Purpose and position

- The site answers the Islamic Dilemma: the claim that the Qur'an cannot affirm the earlier scripture and contradict it at once.
- The site's position: the Injīl is the revelation God gave to Jesus (5:46); the four gospels carry it and carry other things; the Qur'an is the measure (<em>muṣaddiq</em> and <em>muhaymin</em>, 5:48) that sorts the one from the other. Alteration of meaning and of text are each charged only where they can be shown.
- Every claim is labelled: scholarly consensus, minority view, or Islamic position. Gospel material is marked agrees / beyond the measure / unclear / conflicts / not needed.
- No placeholder text anywhere. Every page is complete when created.
- Laughter attributed to Jesus, docetism and mockery of the prophets in the Gnostic witnesses are set aside and the notes say so.
- No classical commentator names Simon of Cyrene as the substitute; the identification comes from Basilides through modern apologetics. Do not reintroduce the claim.
- No individual apologist is named on the secular-form page; the arguments are answered, not the people. The pillar page names the argument's historical proponents (Shamoun, Wood, Pfander, Ward) as its origin only. Pfander's Mīzān al-Ḥaqq is identified as the source of the Christian polemic and answered where it meets the site's premises on `/the-islamic-dilemma/balance-of-truth/`; it is quoted in short phrases only and never reproduced at length.
- The Gospel of Thomas in the Patterson and Meyer translation is reproduced with permission; Mattison's translations are public domain. Keep both credits on the sayings pages and the credits page.

## Pages

- 176 starter pages, created on activation from the JSON in `inc/data/` (page bodies in `pages/`, page patterns in `patterns/`), listed and restorable under Appearance › Theme Options › Content.
- Fixed slugs: `/the-islamic-dilemma/` (pillar), `/the-islamic-dilemma/secular-form/`, `/the-islamic-dilemma/balance-of-truth/`, `/method/`, `/corrections/`, `/answers/`, `/gospels/` (the part is named The Five Gospels: Mark, Matthew, Luke, John and Thomas; `/gospels/thomas/sayings/`, `/gospels/thomas/sayings-patterson-meyer/`, `/gospels/thomas/correlation/`, `/gospels/thomas/set-aside/`, `/gospels/discrepancies/{genealogies,nativity,last-supper,crucifixion,resurrection}/`), `/paul/`, `/quran/`, `/quran/passages/`, `/quran/commentaries/`, `/gospel-according-to-islam/` (Introduction at `/introduction/`, the 27 sections under the word slugs of `section_slugs` in `gospel-islam.json`, from `/family-of-amran-zacharias-and-john/` to `/empty-tomb-reports-and-return/`, `/how-composed/`), `/bonus/epistle/`, `/resources/` (`timeline`, `glossary`, `sources`, `manuscripts`, `borrowing`, `objections`, `responses`), `/about-the-author/`, `/contact/`, `/credits/`, `/terms-of-use/`, `/privacy-policy/`, `/sitemap/`.
- The gospel: Ahmad Shafaat's text (1985) in six chapters over 27 sections, with the Qur'anic passages, the Gospel of Thomas, the Infancy Gospel of James (sections 1, 2, 5) and the Infancy Gospel of Thomas (sections 4, 14), the three witnesses to the substitution (section 26) and the classical commentaries woven in, in that order.
- The Qur'anic passages: 13 surahs, 45 passages, 145 verses (`inc/data/quran-jesus.json`).
- The commentaries: eight works on 3:33-64, 4:155-159, 5:110-120, 19:16-37, 21:91, 23:50, 43:57-65, 57:27, 61:6; keys `tabari`, `baghawi`, `qurtubi`, `ibn-katheer`, `tanwir`, `jalalayn`, `aldur-almanthoor`, `saadi`; group slugs `family-of-imran`, `neither-killed-nor-crucified`, `the-table-and-the-questioning`, `birth-of-jesus`, `she-who-guarded-her-chastity`, `refuge-on-high-ground`, `son-of-mary-as-an-example`, `compassion-mercy-and-monasticism`, `good-news-of-ahmad`; later parts of a work are `{work}-part-two` siblings; 92 pages under `/quran/commentaries/`; folded into gospel sections 1, 2, 3, 7, 10, 13, 14, 16, 21, 22, 24, 25, 26, 27 (`injil_help_tafsir_section_map()`). Scope is the verses about Jesus, Mary, Zachariah and John only; a chunk that runs past them is cut at the commentator's heading in the build.

## Data

- All content is JSON under `inc/data/`: `pages/{pillar,gospels,paul,quran,gospel,bonus,resources,site}.json`, `narrative.json` (6 chapters, 27 sections, 237 notes; `[v]` verse breaks and `[qS:A]` references in the lines), `gospel-islam.json`, `quran-jesus.json`, `thomas.json`, `infancy.json`, `substitution.json`, `thomas-mattison.json`, `thomas-patterson-meyer.json`, `images.json`, `related.json`, `tafsir/{work}.json`.
- A page entry has `path`, `title`, `excerpt`, and `pattern` or `body`; optional `template`; optional `body_file`, a file in `inc/data/` whose lines are appended to `body` (the two full texts of Thomas); optional `related`, the paths of its "Read next" row (merged with `related.json`); optional `former`, the paths it had in earlier versions, renamed in place by `populate()`.
- URLs are words only. No page has a number for a URL segment; a new page gets a slug of its title's words, and a page that moves keeps its old path in `former`. Every article carries a "Read next" row; a new page is added to `related.json` and linked from at least two existing pages.
- Compact markup of a `body`: `## ` and `### ` headings; `- ` list items, optionally opening with `[agree]`, `[beyond]`, `[unclear]`, `[conflict]` or `[notneeded]`; `> ` quotation; `@consensus`, `@minority`, `@islamic` labels; `{{fn:…}}` inline footnote (numbered per page, listed under "Notes"); `[page-list]`; `? Question` followed by its answer paragraph (FAQ entry, collected into FAQPage); `[details] Title` … `[/details]` (fold block); `[v]` verse break and `[qS:A]` Qur'anic verse reference; `[bible]` … `[/bible]` (the gospel text block); `[notes]` (print the notes so far as the apparatus); `[p.classes] text` (paragraph with classes). Inline HTML inside lines is limited to what `wp_kses_post` allows; the commentaries use only `<strong>` and `<em>`.
- Images: `injil_help_images()`, `injil_help_add_page_image()`, `injil_help_feature_page()` in `inc/images.php`; every image is a Wikimedia Commons file, public domain or CC0, named in `images.json` with alt, caption and credit; check the file exists before adding it.
- Loaders: `injil_help_starter_pages()`, `injil_help_pages_file()`, `injil_help_data_file()`, `injil_help_narrative_data()`, `injil_help_gospel_islam_data()`, `injil_help_gospel_woven_sections()`, `injil_help_gospel_islam_pages()`, `injil_help_quran_data()`, `injil_help_quran_page()`, `injil_help_tafsir_data()`, `injil_help_tafsir_pages()`, `injil_help_tafsir_section_lines()`.
- Parser: `injil_help_markup_to_blocks()` in `inc/starter-content.php`.

## Starter content behaviour

- `injil_help_populate( $overwrite = false, $force = false )`. On activation: create missing pages, set pretty permalinks, bin the untouched sample post, page and comment, replace the default tagline.
- After an update (once per version, first admin load): rewrite pages whose text changed, skip pages the owner edited (post meta `_injil_help_hash` differs from the stored content, or `_injil_help_head` from the title and excerpt), keep no revisions, slash data, flush rewrite rules only when permalinks were set. "Update all pages from the theme" rewrites everything, edited pages included.
- Options written: `injil_help_populated` (version stamped only after a non-empty page list), `injil_help_populating` (lock, stale after a minute), `injil_help_options`.

## Theme Options (`injil_help_options`)

Tabs: Colours, Branding, Sharing, Contextual links, SEO, Security, Content. Defaults: `colors` {}, `footer_mark` 1, `ornament` rich, `animate` 1, `parallax` strong, `gospel_layout` columns, `content_width` full (full and wide set prose pages in two columns from 1280px; pages with `.ih-bible` are excluded through `:has()`), `block_app_passwords` 1, `favicon_force` 0, `favicon_url` '', `share` 1, `share_networks` x, facebook, whatsapp, telegram, reddit, linkedin, bluesky, threads, `share_types` post, page, `share_label` '', `schema` auto, `home_title` '', `home_description` '', `pillar_title` '', `og_image` '', `author_name` '', `author_url` '', `same_as` menj.org and apostleofdoom.org, `org_logo` '', `ctx_own` 1, `ctx_sister` 1, `ctx_own_max` 4 (0-15), `ctx_sister_max` 3 (0-10), `ctx_extra` '', `ctx_exclude` ''.

## Filters

- Hardening set by the theme: `wp_is_application_passwords_available` returns false while `block_app_passwords` is set (functions.php), which it is by default, so application passwords are off site-wide until the block is unticked under Theme Options › Security.

`injil_help_seo_defaults`, `injil_help_default_og_image`, `injil_help_output_schema`, `injil_help_schema_graph`, `injil_help_share_networks`, `injil_help_contextual_links_map` (the decoded map), `injil_help_contextual_links_enabled` (bool, `$post`).

## Design tokens

- Palette slugs (theme.json): `base` #f6f1e6, `contrast` #2a2420, `accent-1` #1f5c4a, `accent-2` #9b2c2c, `accent-3` #c39a3f, `accent-4` #2d4a7a, `accent-5` #ece3d1, `accent-6` #d8cbb2, `muted` #6b6158; tints `green-deep`, `green-tint`, `red-tint`, `lapis-tint`, `gold-tint`. Never hard-code a colour in CSS; read the token.
- Font slugs: `sabon` (body), `garamond` (headings), `kagnue` and `kagnue-outline` (display: site title, cover title, section index and home section titles, large numerals), `evangelion` (labels, kickers, navigation), `sbl-greek`, `sbl-hebrew`, `sbl-biblit`, `hafs` (Qur'anic text only, class `ih-quran`), `amiri` (other Arabic, class `ih-ar` or `lang="ar"`), `naskh` (hadith, `ih-hadith`), `dubidam`, `arslan`, `estrangelo` (Syriac), `cuneiform`, `special-elite` (corrections log, `ih-record`), `ferments`, `angel`.
- Rule: KFGQPC Hafs is never set on anything that is not the Qur'an, and the same face is the Qur'anic font of all seven themes of the network. Its licence forbids modification. The owner chose on 2026-10-26 to serve a WOFF2 conversion of the KFGQPC HAFS Uthmanic Script (107 KB, from 298 KB), accepting that the KFGQPC licence forbids modifying the font. The conversion is lossless for glyphs, cmap and OpenType layout; the digital signature (DSIG) is dropped. The file is `assets/fonts/KFGQPC-Hafs-Uthmanic.woff2`, never subset, beside `assets/fonts/KFGQPC-Hafs-Uthmanic-LICENSE.txt`. In this face a verse number is written as bare Arabic-Indic digits (٤٥), which the font sets inside the ayah ornament itself; do not add U+06DD (۝), which draws a second, empty ornament. Kagnue is not used for titles that may carry dotted transliteration letters.
- Font roles are variables at the top of `style.css`: `--ih-serif`, `--ih-sans`, `--ih-display`, `--ih-greek`, `--ih-quran`; radii `--ih-r-sm`, `--ih-r-md`, `--ih-r-lg`.
- Block styles: `is-style-faq`, `is-style-fold`, `is-style-label-consensus|minority|islamic`, answer capsule, gospel verse, objection and reply, criterion box, discrepancy table, ornament separator.
- Ornament: `assets/css/ornament.css`, `assets/js/ornament.js`; body classes `ih-orn`, `ih-px-strong|gentle|off`; CSS variables `--ih-sy` (scroll) and `--ih-p` (element progress); everything a transform; reduced motion respected.
- Assets live in their own directories: `assets/css/`, `assets/js/`, `assets/fonts/`, `assets/icons/` (+ `social/`), `assets/images/`. No inline scripts or styles in patterns beyond block attributes.

## Structured data and SEO

- One JSON-LD `@graph` per page from `inc/schema.php` at `wp_head` priority 5, carrying every schema.org type the content supports regardless of what any search engine displays: Organization (ContactPoint), Person, WebSite (SearchAction, about), Person and Book for ʿĪsā ibn Maryam and the Qur'an (Wikidata sameAs), SiteNavigationElement ItemList, page node (WebPage, CollectionPage + ItemList, ProfilePage + AboutPage, ContactPage, SearchResultsPage; SpeakableSpecification), BreadcrumbList, Article + ScholarlyArticle or BlogPosting with citation, Book + Chapter (the gospel), Book + CreativeWork translation (the commentaries), DefinedTermSet (glossary), Table, Dataset (correlation), Event (timeline), Quotation, FAQPage, ImageObject with licence metadata. Add a type whenever new content fits one.
- Pages are written in steps, never all in one request: the buttons in batches of about twelve seconds over admin-ajax, the automatic rewrite about twenty seconds per admin screen load; every run reports created, updated, up to date, kept and failed.
- With Rank Math active and the SEO option on Automatic, Rank Math owns the head and the one graph; the theme adds the nodes it does not make through `rank_math/json_ld`, repoints references to its own organisation, page and article to Rank Math's (its author Person stays its own node), and prints Rank Math's breadcrumb trail in its own row (`inc/rank-math.php`). Yoast and SEOPress: the theme steps aside.
- Card grids end on a full row: front-page sections in three fixed columns (six or nine cards), the FAQ band in four (eight cards). Prose is never set in columns; only the gospel text has its Bible columns.
- Every page below the front page opens with a visible breadcrumb row (`injil_help_breadcrumb_html()`) that matches the BreadcrumbList; meta descriptions are trimmed to 160 characters at a sentence end; search result pages are `noindex`; anchor text is descriptive (never "its own page", "here" or a bare URL); every URL is words only.
- The theme's own head output and graph are off when Yoast or SEOPress is active; with Rank Math they work inside the plugin as above.
- Six FAQs on every main page, written as search queries.
- Default social image: `assets/images/og-default.jpg`.

## Sharing

`inc/share.php` adds the row after the content on singular posts and pages (not the front page) via `the_content` at priority 20, guarded against excerpts; networks x, facebook, whatsapp, telegram, reddit, linkedin, bluesky, threads, pinterest, line; Copy link and Web Share buttons appear only with JavaScript. Icons are CSS masks in `assets/icons/social/`.

## Contextual links

`inc/contextual-links.php` links the first mention of a known phrase in the running text of a single post or page (`is_singular( array( 'post', 'page' ) )`, so never an attachment; not the front page) to the inner page that treats it, via `the_content` at priority 15: after `do_blocks`, `wpautop`, `wptexturize` and shortcodes, before the share row (20). The guard matches the queried post rather than `in_the_loop()`, as the breadcrumb and share row do. This site's key is `injil` (`INJIL_HELP_SITE_KEY`).

- The map is `inc/data/network-links.json`: `sites` (key to base URL) and `links` (`site`, `path`, `phrases`). It is shared: the same file, byte for byte, ships in all seven themes (abrahamic, bestofislam, injil.help, paulus, compelling-evidence, murtadd, lailatulqadar). Change it in all seven or in none. Each phrase belongs to one page across the network, and each path must exist on the site that serves it.
- Entries for this site link with `home_url( path )`, so staging links to staging; entries for a sister site link to its base URL. Lines in `ctx_extra` come first and win; a full address there must be on a host in `sites`.
- Matching is whole phrase, capitals aside, curly apostrophes and non-breaking spaces allowed. The phrases are split into patterns of 150, because one pattern of the whole map is more than PCRE compiles; matches are merged in text order, the longest first at one place.
- Never inside `a`, headings, `blockquote`, `q`, `figure`, `figcaption`, `cite`, `code`, `pre`, `sup`, `sub`, `nav`, `table`, form controls, `summary`, `aside`, `header`, `footer`, `lang` ar, he, el or syr, `dir="rtl"`, or the classes `ih-ar`, `ih-quran`, `ih-hadith`, `ih-verse`, `ih-v`, `ih-vq`, `ih-bible`, `ih-translit`, `ih-fnref`, `ih-footnotes`, `ih-foot-list`, `ih-apparatus`, `ih-lanah`, `ih-cite`, `ih-running-head`, `ih-chapter-head`, `ih-chapter-num`, `ih-eyebrow`, `ih-meta`, `ih-numeral`, `ih-mark`, `ih-breadcrumb`, `ih-share`, `ih-next`, `ih-entries-list`, `ih-answers-list`, `is-style-gospel-verse`.
- At most one new link in a `p` or `li`; each target once a page; never the page itself; a target the text already links to is skipped. Markup: `<a class="ih-ctx-link">` for this site, `<a class="ih-ctx-link ih-ctx-link--sister" rel="noopener">` for a sister site, same tab. The links take the theme's own link style; no CSS is added.

## Credits that must stay

Shafaat (1985) for the gospel text; Alham Ahmed and The Clear Quran for the passages; Lambdin for Thomas; Bullard and Gibbons, Brashler and Bullard for the Nag Hammadi texts; Irenaeus and Arendzen (Catholic Encyclopedia, 1907) for Basilides; al-Fārūqī (1983) for the ornament principle; the Minimalist Social Icons Pack (GPL) and Font Awesome (LinkedIn, CC BY 4.0); Wikimedia Commons for the manuscript images with each caption's licence; tafsir.app, greattafsirs.com and shamela.ws for the Arabic of the commentaries, whose English renderings are the site's own and unchecked by a second translator.

## Rules for contributors

- Shafaat's 1985 text is the starting point of the gospel, and the measure is its rule. Where it or his notes depart from the Qur'an as the measure they are changed, and every departure is listed under "Where this gospel departs from Shafaat" on the composition page. The end of the story follows 4:157-158 and 3:55: not killed, not crucified, raised to God, another seen in his likeness and no name given for him.
- Never give a page a numeric slug: WordPress renames it `N-2` and routes `/N/` to pagination, and the site's URLs are words only (3.6.0 removed the filter and rewrite rules that once allowed numbers). A page that changes URL keeps its old path in `former`, and `injil_help_adopt_former_page()` moves the existing page.
- The verse division of the gospel is the `[v]` markers in `narrative.json`, numbered at build time. Move, add or remove them with the text; never hard-code a verse number.
- Content intended for the database is stored only as `.json` in `inc/data/`; never as `.html`, `.txt` or PHP strings. This is a security requirement. The words the PHP builders add go in `inc/data/text.json`, the commentaries and verse groups in `inc/data/commentaries.json`. A pattern that supplies a page goes in `inc/data/patterns/`; `patterns/` holds only the front-page sections and the editor's building blocks.
- لعنة الله after Paul of Tarsus is a calligraphic mark (`.ih-lanah`), used only on the Epistle's autograph line and the verdict on The false apostles. Never in titles, excerpts, menus, the front page or structured data; any new use is a deliberate exception.
- JS and CSS are separate files in `assets/js/` and `assets/css/`.
- The options page stays tabbed; a new setting joins an existing tab or gets its own.
- British spelling in site text; no contractions; no em dashes; Qur'anic quotations in bold inside the commentaries.
- apostleofdoom.org (the case against Paul of Tarsus) and bestofislam.org (objections to Islam answered) are linked within the running text only, article by article, where the subject meets, as any source is cited: a sentence naming the article and the site. Never present them as companion or sister sites, never in the footer, the menus or a declared row, and never nofollow them.
- Paul is always "Paul of Tarsus" in the site's own text, including headings and link text; book titles, quotations and the glossary term "New perspective on Paul" keep their wording.
- Release: bump both version strings, update `CHANGELOG.md` and the readme changelog, add an `UPGRADING.md` note if an existing site must act, merge to `main`, tag `vX.Y.Z`.
- Run before pushing: `php -l` on every PHP file, JSON validity on every data file, the page link check and the block-balance render check (the scratch harnesses used in development live outside the repository; rebuild them from `inc/starter-content-pages.php` and `inc/schema.php` if needed).
