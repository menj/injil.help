# Upgrading the theme

Where the theme should go next: improvements to what exists, features and content worth adding, and the work that would make it fool-proof to run. Items are grouped by priority. Each names the files it touches so it can be picked up cold. The notes on updating an existing site are at the end.

## Priority 1: make it fool-proof

**Bring the checks into the repository.** The checks run during development (PHP lint, JSON validity, the page and link check, the block-balance render of every page, the structured-data harness) live outside the repository. Put them under `tools/` with one command (`php tools/check.php`) and run them in a GitHub Actions workflow on every push and pull request, so a broken data file or a dangling link cannot be merged. Add a JSON Schema for each data file under `inc/data/schema/` and validate against it.

**Fail loudly on bad data.** `injil_help_pages_file()` and `injil_help_data_file()` return an empty array when a file is missing or malformed, which silently produces a site with pages missing. Keep the safe return, but record the failure and show an admin notice naming the file, and add a Site Health test ("The Rightly-Guided Injīl data files") that reports every file's status.

**A Doctor tab in Theme Options.** One screen that lists: starter pages missing or duplicated; pages edited since the theme wrote them; pages above a size threshold; data files that fail validation; the parent theme's presence and version; fonts missing from `assets/fonts/`; whether a page cache and an SEO plugin are active; PHP `max_input_vars` and `post_max_size` against what the largest page needs. Each line with a one-click fix where one exists.

**Guard the parent theme.** If Twenty Twenty-Five is missing or below the tested version, show an admin notice and skip the parts that depend on it instead of failing. Pin the tested parent version in `SSOT.md` and `style.css`.

**Preview before rewriting.** The Content tab rewrites changed pages on the first admin load after an update. Add a dry run that lists which pages will change and which are kept as edited, with a diff link, before anything is written; and an option to keep one revision for rewrites so an owner can compare and revert from the editor.

**Export and import edits.** Let an owner export the pages they edited (and their Theme Options) as JSON and import them on a fresh install, so a rebuild from scratch loses nothing.

**WP-CLI commands.** `wp injil pages create`, `wp injil pages update [--all]`, `wp injil check`, `wp injil doctor`, so the whole population and every check can run headlessly on staging and in deployment scripts.

**Integrity manifest.** Ship a checksum manifest of `inc/data/` and the fonts; the Doctor tab verifies it, so a partial upload or a corrupted file is caught before visitors see it.

**Page size.** The gospel sections that carry the folded commentaries are large (section 2 is about 560 KB of HTML). Three options, in order of preference:
1. Load each fold's body on demand: the page ships the `<details>` with its summary and a verse range; the body is fetched from a REST endpoint (`/wp-json/injil/v1/commentary/{work}/{surah}/{from}-{to}`) when the fold is opened, from the same JSON data, and cached. The page stays small and nothing is lost.
2. Move the commentaries of a section to a child page (`/gospel-according-to-islam/2/commentaries/`) linked from the section.
3. Keep the pages as they are and require a page cache.
Whichever is chosen, document the server settings a site needs (`max_input_vars` 5000 or more if the sections are to be saved in the editor; a page cache).

**Accessibility pass.** Audit the fold and FAQ blocks for keyboard use and screen-reader announcement; check colour contrast of every tint against the palette; confirm the heading order inside folds; test the share row and the copy-link feedback with a screen reader; keep the reduced-motion behaviour.

**Performance pass.** Subset and preload the two primary faces (Sabon, EB Garamond) and load the display and script faces only where used; `font-display: swap` on every face; passive scroll listeners in `ornament.js`; measure Core Web Vitals on the front page, a section index, a gospel section and a commentary page, and record the numbers in `SSOT.md` so regressions show.

## Priority 2: features

**Verse lookup.** A small box ("Enter a verse, for example 4:157") that jumps to the passage on the Qur'an page, the gospel section that carries it and each commentary on it. Every commentary heading already names its verse; add stable `id` anchors (`#4-157`) so the links are exact.

**Arabic beside the English.** The Arabic of the commentaries is public domain and already in hand (about 200,000 words). Ship it as separate JSON files loaded on demand and show it paragraph by paragraph behind a toggle, in Amiri, with the Qur'anic lemmas in KFGQPC Hafs. The same for the gospel's Qur'anic passages.

**Parallel view.** For a verse, show the Qur'an, the gospel section and the commentaries side by side; for a gospel section, the synoptic parallels (Matthew, Mark, Luke, John, Thomas) in columns. The data for both exists.

**Glossary tooltips.** The glossary is data; mark its terms in the text on first use per page and show the definition on hover and focus.

**Dark scheme.** The palette is tokens, so a second scheme is a second set of values plus a switch in Theme Options › Colours and a system-preference default. Check the manuscript plates and the lattice against the dark base.

**Print and export.** A print stylesheet (no ornament, notes at the end, folds opened); an EPUB and PDF of The Gospel According to Islam built from `narrative.json`; "Copy citation" on every page.

**Reading aids.** Remember the reader's place in the gospel and the commentaries (per-visitor, local storage only); "continue reading" on the front page; an estimated reading time on long pages; a table of contents for pages with many headings.

**Corrections feed.** An RSS feed of the corrections log and a "last corrected" date on every page that has an entry.

**Translations.** The interface strings are translatable, the content is not. Add language-keyed data directories (`inc/data/ms/`, `inc/data/ar/`) and a language setting, starting with Malay, so a whole site can be installed in another language from the same theme. An Arabic interface needs an RTL stylesheet.

**Theme Options › Commentaries.** Show or hide the folded commentaries in the gospel sections, choose which works appear and in what order, and whether folds open by default.

**Comments and questions.** A moderated question form on the FAQs page that feeds new FAQs; the responses page could take submissions of articles to answer.

## Priority 3: content

**More verses.** Commentaries on the remaining verses about Jesus and his family: 19:2-15 (Zachariah and John), 2:87 and 2:253, 3:65-80 (the dispute with the People of the Book), 4:171-172, 5:17, 5:72-78, 9:30-31, 33:7, 42:13, 61:14. The pipeline (fetch, chunk, translate, build) is documented in `SSOT.md` and the tools can be reused as they are.

**More commentators.** Earlier and other schools: Muqātil ibn Sulaymān (the oldest complete commentary), al-Māturīdī, al-Zamakhsharī, Ibn ʿAṭiyya, al-Rāzī, al-Bayḍāwī, al-Nasafī, Ibn ʿĀshūr; the Shia al-Ṭūsī and al-Ṭabrisī. All are available in public-domain Arabic from the same sources. Add each as a new key in `injil_help_tafsir_works()`; nothing else changes.

**A second translator.** The English renderings have not been checked by a second translator. Commission a reading of the crucifixion and raising passages first (4:155-159, 3:54-58, 5:116-120 in every work) and log corrections on the corrections page.

**Hadith.** A page of the Prophet's reports about Jesus from the Ṣaḥīḥ collections (his description, the descent, "the prophets are brothers") in Arabic (Noto Naskh) and English, measured and cross-linked to the commentaries that cite them.

**Infancy and apocryphal gospels.** The Infancy Gospel of Thomas and the Arabic Infancy Gospel (the clay birds, the cradle), the Gospel of the Hebrews and the Ebionites, the Diatessaron: each read against the measure as the Gospel of Thomas was, with a set-aside page.

**Paul of Tarsus.** A table of Acts against the letters on every point where they differ; Marcion and the first canon; the Jerusalem church after James.

**The four gospels.** A harmony table mapping each section of The Gospel According to Islam to its source verses in the four gospels and Thomas, generated from the notes; a page on the manuscripts themselves with the plates already used.

**Responses.** Keep answering named articles and videos on the responses page as they appear, in the same form (claim stated as made, then answered), and grow the FAQs from search queries that reach the site.

**Timeline and glossary.** Make the timeline navigable by period and link each entry to its page; link every glossary term to the pages that use it.

**Images.** Host the manuscript images in the Media Library instead of loading from Wikimedia Commons, keeping each caption's credit and licence; add the Birmingham and Sanaa folios at full resolution with zoom.

## Updating an existing site

Git Updater reads the theme header and offers the release whose tag matches `Version:`. After the update, the first load of any admin screen rewrites the starter pages whose text changed, keeps pages you edited (marked "edited, kept on update" on the Content tab), keeps no revisions, and flushes rewrite rules only if permalinks had to be set. "Update all pages from the theme" replaces every page, edited ones included, after a confirmation. Take a database backup before updating; open the Content tab once after.

- **3.4.0.** 93 new pages (the commentaries and the secular-form page); the Content tab lists 166. Fourteen gospel sections carry the folded commentaries and are large (section 2 about 560 KB): use a page cache; raise `max_input_vars` and `post_max_size` if those sections are to be saved in the editor. Sections you edited before 3.4.0 do not receive the commentaries until rewritten. Kagnue now sets the section index and home section titles and the large numerals. Rolling back leaves the new pages in place; delete them from the Content tab.
- **3.3.0.** Content moved to JSON under `inc/data/`; nineteen article pages, the indexes, the composition page and the gospel sections rewritten; the hash check that protects edited pages begins here; the share row appears under every post and page (Theme Options › Sharing); the structured data is one graph per page and stops when an SEO plugin is active; Parallax depth added under Branding.
- **3.2.1.** First release to rewrite starter pages on update; edits were replaced in this version only.
- **3.2.0.** The gospel changed from numbered verses to the narrative in six chapters over the same 27 URLs; verse anchors no longer resolve; the per-chapter Qur'an appendices were removed in favour of `/quran/passages/`.
- **3.1.0.** The ornament layer, on by default; adjust under Branding › Ornament.
- **3.0.0.** Shafaat (1985) became the base text; 73 starter pages.
- **2.7.0.** Starter content and the Content tab begin; older sites must press "Create missing pages" once.
