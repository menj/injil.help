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
2. Move the commentaries of a section to a child page (`/gospel-according-to-islam/annunciation-and-birth-of-jesus/commentaries/`) linked from the section.
3. Keep the pages as they are and require a page cache.
Whichever is chosen, document the server settings a site needs (`max_input_vars` 5000 or more if the sections are to be saved in the editor; a page cache).

**Accessibility pass.** Audit the fold and FAQ blocks for keyboard use and screen-reader announcement; check colour contrast of every tint against the palette; confirm the heading order inside folds; test the share row and the copy-link feedback with a screen reader; keep the reduced-motion behaviour.

**Performance pass.** Subset and preload the two primary faces (Sabon, EB Garamond) and load the display and script faces only where used; `font-display: swap` on every face; passive scroll listeners in `ornament.js`; measure Core Web Vitals on the front page, a section index, a gospel section and a commentary page, and record the numbers in `SSOT.md` so regressions show.

## Priority 2: features

**Verse lookup.** A small box ("Enter a verse, for example 4:157") that jumps to the passage on the Qur'an page, the gospel section that carries it and each commentary on it. Every commentary heading already names its verse; add stable `id` anchors (`#4-157`) so the links are exact.

**Arabic beside the English.** The Arabic of the commentaries is public domain and already in hand (about 200,000 words). Ship it as separate JSON files loaded on demand and show it paragraph by paragraph behind a toggle, in Amiri, with the Qur'anic lemmas in KFGQPC Hafs. The same for the gospel's Qur'anic passages.

**Red-letter edition.** Mark the words of Jesus in the narrative data with a span token (`[r]` … `[/r]`) and set them in rubric, as many printed Bibles do, with a switch beside the layout option; the parser already carries the verse and reference tokens the feature would sit beside.

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

**More apocryphal gospels.** The Infancy Gospel of James and the Infancy Gospel of Thomas are woven in since 3.5.0 (sections 1, 2, 4, 5, 14) and the borrowing charge is answered. Still to do: the Arabic Infancy Gospel (the cradle speech), the Gospel of the Hebrews and the Ebionites, the Diatessaron, each read against the measure with a set-aside page; and a set-aside page for the infancy gospels' rejected items, which are at present only described in the section notes.

**Paul of Tarsus.** A table of Acts against the letters on every point where they differ; Marcion and the first canon; the Jerusalem church after James.

**The five gospels.** A harmony table mapping each section of The Gospel According to Islam to its source verses in the four gospels and Thomas, generated from the notes; a manuscripts page for the canonical gospels with the plates already used, to stand beside the extracanonical table added in 3.5.0; rerun the sayings-order correlation from Koester's list with the figures in the repository, since the page reports the note's figures and says so.

**Responses.** Keep answering named articles and videos on the responses page as they appear, in the same form (claim stated as made, then answered), and grow the FAQs from search queries that reach the site.

**Timeline and glossary.** Make the timeline navigable by period and link each entry to its page; link every glossary term to the pages that use it.

**Images.** The featured images are now sideloaded into the Media Library; serve the in-content plates from the same attachments instead of from Commons, keeping each caption's credit and licence; give the remaining article pages their images (the good news of Aḥmad, the borrowing page, the composition page); add the Birmingham and Sanaa folios at full resolution with zoom.

## Updating an existing site

Git Updater reads the theme header and offers the release whose tag matches `Version:`. After the update, the first load of any admin screen rewrites the starter pages whose text changed, keeps pages you edited (marked "edited, kept on update" on the Content tab), keeps no revisions, and flushes rewrite rules only if permalinks had to be set. "Update all pages from the theme" replaces every page, edited ones included, after a confirmation. Take a database backup before updating; open the Content tab once after.

- **3.6.1.** Every page is full width by default, and on screens from 1280px a page of running prose is set in two columns so the lines stay readable. To have the former single reading column back, choose it under Theme Options › Branding › Content width; nothing else changes and no page is rewritten.
- **3.6.0.** Application passwords are switched off by default; any client that logged in with one (a mobile app, a publishing tool) will stop working until you untick "Block application passwords" under Theme Options › Security. The 27 gospel sections, the nine commentary groups and the later parts of long commentaries change URL (words instead of numbers). The first admin load renames the existing pages in place: same IDs, edits and featured images; nothing is duplicated. The old URLs are not redirected, so update external links and resubmit the sitemap. Links inside pages you edited by hand still point at the numeric URLs and must be corrected by hand or with "Update all pages from the theme". One new page (Pfander's Balance of Truth); the Content tab lists 176. Every unedited page is rewritten once for its "Read next" row.
- **3.5.2.** Two more page images; nothing else changes. The two table pages are rewritten on the first admin load unless edited.
- **3.5.1.** The gospel sections become reachable: the first admin load renames each section's oldest copy from "N-2" back to "N", bins the duplicate copies that were never edited (they are in the bin, not deleted), rewrites the section with the current text, and flushes the rewrite rules. If a section still opens the index afterwards, save Settings › Permalinks once. Featured images arrive over the next few admin screen loads (six pages per load) and need outbound HTTP from the host to commons.wikimedia.org; a page whose fetch fails is retried after a day. Stylesheet fixes, the dilemma diagram on the front page and a prose pass; the front page, the composition page, the pillar page, the gospels index and the secular-form page are rewritten on the first admin load unless edited. Nothing else changes.
- **3.5.0.** Nine new pages (the two full texts of Thomas, the sayings-order test, four discrepancy tables, the manuscripts table and the borrowing page); the Content tab lists 175. The first part of the site is renamed The Five Gospels; if you created a category named The Four Gospels, rename it. Gospel sections 1, 2, 4, 5 and 14 gain the infancy-gospel items and are rewritten on the first admin load unless you edited them. The pillar page is extended, not replaced; an edited copy is kept and must be updated by hand or with "Update all pages from the theme". All 27 gospel sections are rewritten in the Bible layout (sections 25 to 27 in substance) and are replaced on the first admin load unless edited; verse anchors (`#v12` on a section page, `#q4-157` for a Qur'anic verse) resolve again; choose two columns, one column or plain under Theme Options › Content › Gospel text layout.
- **3.4.0.** 93 new pages (the commentaries and the secular-form page); the Content tab lists 166. Fourteen gospel sections carry the folded commentaries and are large (section 2 about 560 KB): use a page cache; raise `max_input_vars` and `post_max_size` if those sections are to be saved in the editor. Sections you edited before 3.4.0 do not receive the commentaries until rewritten. Kagnue now sets the section index and home section titles and the large numerals. Rolling back leaves the new pages in place; delete them from the Content tab.
- **3.3.0.** Content moved to JSON under `inc/data/`; nineteen article pages, the indexes, the composition page and the gospel sections rewritten; the hash check that protects edited pages begins here; the share row appears under every post and page (Theme Options › Sharing); the structured data is one graph per page and stops when an SEO plugin is active; Parallax depth added under Branding.
- **3.2.1.** First release to rewrite starter pages on update; edits were replaced in this version only.
- **3.2.0.** The gospel changed from numbered verses to the narrative in six chapters over the same 27 URLs; verse anchors no longer resolve; the per-chapter Qur'an appendices were removed in favour of `/quran/passages/`.
- **3.1.0.** The ornament layer, on by default; adjust under Branding › Ornament.
- **3.0.0.** Shafaat (1985) became the base text; 73 starter pages.
- **2.7.0.** Starter content and the Content tab begin; older sites must press "Create missing pages" once.
