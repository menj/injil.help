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
| Repository | https://github.com/menj/injil.help, `Primary Branch: main` (Git Updater) |
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
- No individual apologist is named on the secular-form page; the arguments are answered, not the people. The pillar page names the argument's historical proponents (Shamoun, Wood, Pfander, Ward) as its origin only.
- The Gospel of Thomas in the Patterson and Meyer translation is reproduced with permission; Mattison's translations are public domain. Keep both credits on the sayings pages and the credits page.

## Pages

- 175 starter pages, created on activation from `inc/data/` and `patterns/`, listed and restorable under Appearance › Theme Options › Content.
- Fixed slugs: `/the-islamic-dilemma/` (pillar), `/the-islamic-dilemma/secular-form/`, `/method/`, `/corrections/`, `/answers/`, `/gospels/` (the part is named The Five Gospels: Mark, Matthew, Luke, John and Thomas; `/gospels/thomas/sayings/`, `/gospels/thomas/sayings-patterson-meyer/`, `/gospels/thomas/correlation/`, `/gospels/thomas/set-aside/`, `/gospels/discrepancies/{genealogies,nativity,last-supper,crucifixion,resurrection}/`), `/paul/`, `/quran/`, `/quran/passages/`, `/quran/commentaries/`, `/gospel-according-to-islam/` (Introduction at `/introduction/`, sections `/1/` to `/27/`, `/how-composed/`), `/bonus/epistle/`, `/resources/` (`timeline`, `glossary`, `sources`, `manuscripts`, `borrowing`, `objections`, `responses`), `/about-the-author/`, `/contact/`, `/credits/`, `/terms-of-use/`, `/privacy-policy/`, `/sitemap/`.
- The gospel: Ahmad Shafaat's text (1985) in six chapters over 27 sections, with the Qur'anic passages, the Gospel of Thomas, the Infancy Gospel of James (sections 1, 2, 5) and the Infancy Gospel of Thomas (sections 4, 14), the three witnesses to the substitution (section 26) and the classical commentaries woven in, in that order.
- The Qur'anic passages: 13 surahs, 45 passages, 145 verses (`inc/data/quran-jesus.json`).
- The commentaries: eight works on 3:33-64, 4:155-159, 5:110-120, 19:16-37, 21:91, 23:50, 43:57-65, 57:27, 61:6; keys `tabari`, `baghawi`, `qurtubi`, `ibn-katheer`, `tanwir`, `jalalayn`, `aldur-almanthoor`, `saadi`; 92 pages under `/quran/commentaries/`; folded into gospel sections 1, 2, 3, 7, 10, 13, 14, 16, 21, 22, 24, 25, 26, 27 (`injil_help_tafsir_section_map()`). Scope is the verses about Jesus, Mary, Zachariah and John only; a chunk that runs past them is cut at the commentator's heading in the build.

## Data

- All content is JSON under `inc/data/`: `pages/{pillar,gospels,paul,quran,gospel,bonus,resources,site}.json`, `narrative.json` (6 chapters, 27 sections, 237 notes; `[v]` verse breaks and `[qS:A]` references in the lines), `gospel-islam.json`, `quran-jesus.json`, `thomas.json`, `infancy.json`, `substitution.json`, `thomas-mattison.json`, `thomas-patterson-meyer.json`, `tafsir/{work}.json`.
- A page entry has `path`, `title`, `excerpt`, and `pattern` or `body`; optional `template`; optional `body_file`, a file in `inc/data/` whose lines are appended to `body` (the two full texts of Thomas).
- Compact markup of a `body`: `## ` and `### ` headings; `- ` list items, optionally opening with `[agree]`, `[beyond]`, `[unclear]`, `[conflict]` or `[notneeded]`; `> ` quotation; `@consensus`, `@minority`, `@islamic` labels; `{{fn:…}}` inline footnote (numbered per page, listed under "Notes"); `[page-list]`; `? Question` followed by its answer paragraph (FAQ entry, collected into FAQPage); `[details] Title` … `[/details]` (fold block); `[v]` verse break and `[qS:A]` Qur'anic verse reference; `[bible]` … `[/bible]` (the gospel text block); `[notes]` (print the notes so far as the apparatus); `[p.classes] text` (paragraph with classes). Inline HTML inside lines is limited to what `wp_kses_post` allows; the commentaries use only `<strong>` and `<em>`.
- Loaders: `injil_help_starter_pages()`, `injil_help_pages_file()`, `injil_help_data_file()`, `injil_help_narrative_data()`, `injil_help_gospel_islam_data()`, `injil_help_gospel_woven_sections()`, `injil_help_gospel_islam_pages()`, `injil_help_quran_data()`, `injil_help_quran_page()`, `injil_help_tafsir_data()`, `injil_help_tafsir_pages()`, `injil_help_tafsir_section_lines()`.
- Parser: `injil_help_markup_to_blocks()` in `inc/starter-content.php`.

## Starter content behaviour

- `injil_help_populate( $overwrite = false, $force = false )`. On activation: create missing pages, set pretty permalinks, bin the untouched sample post, page and comment, replace the default tagline.
- After an update (once per version, first admin load): rewrite pages whose text changed, skip pages the owner edited (post meta `_injil_help_hash` differs from the stored content), keep no revisions, slash data, flush rewrite rules only when permalinks were set. "Update all pages from the theme" rewrites everything, edited pages included.
- Options written: `injil_help_populated` (version stamped only after a non-empty page list), `injil_help_populating` (lock, stale after a minute), `injil_help_options`.

## Theme Options (`injil_help_options`)

Tabs: Colours, Branding, Sharing, SEO, Content. Defaults: `colors` {}, `footer_mark` 1, `ornament` rich, `animate` 1, `parallax` strong, `gospel_layout` columns, `favicon_force` 0, `favicon_url` '', `share` 1, `share_networks` x, facebook, whatsapp, telegram, reddit, linkedin, bluesky, threads, `share_types` post, page, `share_label` '', `schema` auto, `home_title` '', `home_description` '', `pillar_title` '', `og_image` '', `author_name` '', `author_url` '', `same_as` menj.org and apostleofdoom.org, `org_logo` ''.

## Filters

`injil_help_seo_defaults`, `injil_help_default_og_image`, `injil_help_output_schema`, `injil_help_schema_graph`, `injil_help_share_networks`.

## Design tokens

- Palette slugs (theme.json): `base` #f6f1e6, `contrast` #2a2420, `accent-1` #1f5c4a, `accent-2` #9b2c2c, `accent-3` #c39a3f, `accent-4` #2d4a7a, `accent-5` #ece3d1, `accent-6` #d8cbb2, `muted` #6b6158; tints `green-deep`, `green-tint`, `red-tint`, `lapis-tint`, `gold-tint`. Never hard-code a colour in CSS; read the token.
- Font slugs: `sabon` (body), `garamond` (headings), `kagnue` and `kagnue-outline` (display: site title, cover title, section index and home section titles, large numerals), `evangelion` (labels, kickers, navigation), `sbl-greek`, `sbl-hebrew`, `sbl-biblit`, `hafs` (Qur'anic text only, class `ih-quran`), `amiri` (other Arabic, class `ih-ar` or `lang="ar"`), `naskh` (hadith, `ih-hadith`), `dubidam`, `arslan`, `estrangelo` (Syriac), `cuneiform`, `special-elite` (corrections log, `ih-record`), `ferments`, `angel`.
- Rule: KFGQPC Hafs is never set on anything that is not the Qur'an. Kagnue is not used for titles that may carry dotted transliteration letters.
- Font roles are variables at the top of `style.css`: `--ih-serif`, `--ih-sans`, `--ih-display`, `--ih-greek`, `--ih-quran`; radii `--ih-r-sm`, `--ih-r-md`, `--ih-r-lg`.
- Block styles: `is-style-faq`, `is-style-fold`, `is-style-label-consensus|minority|islamic`, answer capsule, gospel verse, objection and reply, criterion box, discrepancy table, ornament separator.
- Ornament: `assets/css/ornament.css`, `assets/js/ornament.js`; body classes `ih-orn`, `ih-px-strong|gentle|off`; CSS variables `--ih-sy` (scroll) and `--ih-p` (element progress); everything a transform; reduced motion respected.
- Assets live in their own directories: `assets/css/`, `assets/js/`, `assets/fonts/`, `assets/icons/` (+ `social/`), `assets/images/`. No inline scripts or styles in patterns beyond block attributes.

## Structured data and SEO

- One JSON-LD `@graph` per page from `inc/schema.php` at `wp_head` priority 5: Organization, Person, WebSite (+ SearchAction on the front page), page node (WebPage, CollectionPage with ItemList when a page has three or more entries, ProfilePage, ContactPage, SearchResultsPage), BreadcrumbList, Article or BlogPosting, Book for the gospel sections, ImageObject with licence metadata, FAQPage from `is-style-faq` details.
- Off automatically when Yoast, Rank Math or SEOPress is active.
- Six FAQs on every main page, written as search queries.
- Default social image: `assets/images/og-default.jpg`.

## Sharing

`inc/share.php` adds the row after the content on singular posts and pages (not the front page) via `the_content` at priority 20, guarded against excerpts; networks x, facebook, whatsapp, telegram, reddit, linkedin, bluesky, threads, pinterest, line; Copy link and Web Share buttons appear only with JavaScript. Icons are CSS masks in `assets/icons/social/`.

## Credits that must stay

Shafaat (1985) for the gospel text; Alham Ahmed and The Clear Quran for the passages; Lambdin for Thomas; Bullard and Gibbons, Brashler and Bullard for the Nag Hammadi texts; Irenaeus and Arendzen (Catholic Encyclopedia, 1907) for Basilides; al-Fārūqī (1983) for the ornament principle; the Minimalist Social Icons Pack (GPL) and Font Awesome (LinkedIn, CC BY 4.0); Wikimedia Commons for the manuscript images with each caption's licence; tafsir.app, greattafsirs.com and shamela.ws for the Arabic of the commentaries, whose English renderings are the site's own and unchecked by a second translator.

## Rules for contributors

- Shafaat's 1985 text is the starting point of the gospel, not its rule. Where it or his notes depart from the Qur'an as the measure they are changed, and every departure is listed under "Where this gospel departs from Shafaat" on the composition page. The end of the story follows 4:157-158 and 3:55: not killed, not crucified, raised to God, another seen in his likeness and no name given for him.
- The verse division of the gospel is the `[v]` markers in `narrative.json`, numbered at build time. Move, add or remove them with the text; never hard-code a verse number.
- Content changes go in `inc/data/` or `patterns/`, never in PHP strings.
- JS and CSS are separate files in `assets/js/` and `assets/css/`.
- The options page stays tabbed; a new setting joins an existing tab or gets its own.
- British spelling in site text; no contractions; no em dashes; Qur'anic quotations in bold inside the commentaries.
- Release: bump both version strings, update `CHANGELOG.md` and the readme changelog, add an `UPGRADING.md` note if an existing site must act, merge to `main`, tag `vX.Y.Z`.
- Run before pushing: `php -l` on every PHP file, JSON validity on every data file, the page link check and the block-balance render check (the scratch harnesses used in development live outside the repository; rebuild them from `inc/starter-content-pages.php` and `inc/schema.php` if needed).
