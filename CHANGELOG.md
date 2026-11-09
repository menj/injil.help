# Changelog

All notable changes to The Injil theme. The `Changelog` section of `readme.txt` mirrors this file; `UPGRADING.md` says what each release means for an existing site. Versions follow `Version:` in `style.css` and the tag on `main`.

## 3.38.5

New material now supersedes old material. No feature removed and no option key or identifier renamed. The data file ships empty, so nothing changes until an address pair is added.

- Added: `inc/supersede.php` and `inc/data/supersedes.json`, a map of old address to new address for pages and Journal posts (`journal/<slug>`).
- Added: a superseded item answers with a 301 to its replacement (also from the Trash), joins the unlisted posts so lists, search, feeds and sitemaps leave it out, and is moved to the Trash by the update unless it was edited. A chain of replacements ends at the live item, and a cycle supersedes nothing.
- Added: a Journal entry may list `former` slugs. An unedited post under one is renamed in place and the old address redirects.
- Added: items the theme wrote that its data no longer holds move to the Trash when unedited, and are kept when edited. A post with no content hash counts as edited.
- Added: guards. Removals stop when the number of items falls more than 10 percent below the last good run, when the page or Journal data cannot be read, and when `INJIL_HELP_DISABLE_SUPERSEDE` is true in `wp-config.php`.
- Added: Theme Options, Content shows a dry run of the next update, a log of the last 100 changes (`injil_help_sync_log`) and, for an edited item, a comparison with the theme's copy. The update notice also reports what the retirement did.
- Added: `tests/supersede-test.php`, 29 checks.

## 3.38.4

Canon claims and three Journal posts.

- Changed: the glossary entry for the canon, the answer on how Christians receive the list of books and the Muratorian fragment line on the timeline (`inc/data/pages/resources.json`). The lists settled over the fourth and fifth centuries through teachers, bishops and regional councils, and the Muratorian date is stated as contested.
- Added: Journal posts `who-chose-four-gospels`, `did-nicaea-choose-books` and `syriac-bible-twenty-two-books`, with short titles in `inc/data/seo-titles.json`.
- Added: Samuel Davidson, The Canon of the Bible (1877), in the source library, the timeline sources and the responses sources; and under Credits in `readme.txt`.

## 3.38.3

Theme Options settings UI audit.

- Audited against the admin design brief: tabs (arrow keys, Home, End), cards, toggle switches, choice cards, live range values, colour cards, header and footer all pass at 782 and 375 pixels. No table layouts.
- Fixed: range sliders were 16 pixels tall (2.5.8); they are now at least 24. The five scroll areas on the Security tab take keyboard focus and have names (2.1.1).

## 3.38.2

WCAG 2.2 AA audit.

- Fixed: search and index result titles jumped from level one to level three (1.3.1, 2.4.6); they are now level two, sized as before. Their links keep a 24-pixel target (2.5.8).
- Fixed: a table that scrolls sideways could not be reached by keyboard (2.1.1). `inc/a11y.php` gives each core table figure `tabindex="0"`, `role="region"` and a numbered name.
- Added: `tests/a11y-axe.js`, an axe-core run at 1280 and 375 pixels; `RESOURCES.md`, the one record of copyright and asset licences.

## 3.38.1

Mobile-first audit on 9 November 2026, measured at 320, 360, 375, 390, 414, 480, 768, 1024 and 1280 pixels on 14 page types. No page scrolled sideways before or after. No feature removed, no class, id or hook renamed.

- Fixed: layout shift on the Bible pages, the wide-table pages and the Journal list (0.10 to 0.20 in the lab at 320 to 414 pixels). The theme now preloads the two faces the reading copy and the labels use, EB Garamond and Evangelion CPA, plus Kagnue on the Bible sections (the chapter numeral), from `functions.php`. The shift fell to between 0.00 and 0.002.
- Changed: the front-page section grid, the answer cards and the entry cards in `style.css` now start from one column and add columns with `min-width` queries (641 and 1200 pixels), in place of `max-width` queries that took columns away. The column count is identical at every width from 320 to 1280 pixels.
- Fixed: a caption with one very long word wraps inside its figure (`responsive.css`).
- Fixed: the footer brand mark has a 44-pixel tap area on phones and touch screens, and the log-in screen links, the "Remember me" label and the password eye have 44-pixel targets (`responsive.css`, `login.css`).


The dictionaries page.

- Added: `resources/dictionaries` ("Arabic dictionaries"), the 44 lexicons of arabiclexicon.hawramani.com that the website consults, in seven groups, each with author, work, one plain line and a link. Page count 182 to 183.
- Changed: the Resources index card and overview, and `related.json` for the glossary, sources and the new page.

## 3.37.0

Pexels client, built into the theme at the owner's request.

- Added: `inc/pexels.php`, a PHP port of the request in Pexels' official JavaScript client (MIT): search, curated, one photo and collections, a twelve-hour cache, a ten-minute flag and admin notice after a 401, and `injil_help_pexels_entry()` to build an `images.json` entry (key `pexels-{id}`, "Photo: name, Pexels License.").
- Added: the option `pexels_key` (Theme Options › Content › Images and privacy), the constant `INJIL_HELP_PEXELS_KEY` and the filter `injil_help_pexels_key`. No key is in the repository; with none, nothing is requested.
- Added: `tests/pexels-test.php`, 15 checks.
## 3.36.1

The Theme Options page uses the full width of the screen. No feature or setting removed.

- Changed: `assets/css/admin.css` drops the 1080px `max-width` on `.ih-options`, gives switch rows a flexible text column in place of the 1fr/1.6fr split, and loosens the width limits on the header text (70ch to 110ch) and the usage box (90ch to none). The rules at 782px and below are unchanged.

## 3.36.0

Pixabay images, approved by the owner on 7 November 2026.

- Added: 12 Pixabay photographs on nine pages (Torah in Islam, the raised Jesus, Aḥmad, Glossary, Timeline, Borrowing, Manuscripts). An entry has `"source": "pixabay"`, the 1280 px `url` and the `page`; the key is `pixabay-{id}`.
- Changed: `inc/images.php`, `inc/schema.php` and `inc/schema-extend.php` handle the source; the credit reads "Photo: name, Pixabay Content License." and the structured data names the licence. A Pixabay plate shows only from its Media Library copy, since the licence forbids hotlinking.
- Changed: `SSOT.md`, the credits page, the privacy policy and the readmes name Pixabay; the earlier ban is lifted.

## 3.35.0

Six more lexicons from arabiclexicon.hawramani.com, approved by the owner on 7 November 2026: al-Fīrūzābādī (already used), Ghulām Thaʿlab, Salmoné, Ḥamīd al-Dīn al-Farāhī, Ibn Abī Thābit and Ibn Fāris.

- Added: Ibn Fāris's *Maqāyīs al-Lugha* on Kalima (the word extends to a story and a whole ode, with 4:46), Tawaffā (to take all of it and leave nothing), al-Māʾida (two roots, motion and feeding) and al-Masīḥ (one side of the face wiped, whence the Dajjāl).
- Added: Ghulām Thaʿlab's *al-ʿAsharāt* on Injīl: the senses of *najl*, among them "child", and the view that Injīl is taken from all of them.
- Changed: `SSOT.md` adds Ghulām Thaʿlab, Salmoné, al-Farāhī, Ibn Abī Thābit and Ibn Fāris to the approved lexicons.

Salmoné, al-Farāhī and Ibn Abī Thābit are approved and not yet used: no Glossary term called for them.

## 3.34.0

The Glossary and two Qur'an pages draw on more of the classical dictionaries at arabiclexicon.hawramani.com. The owner approved the sources on 7 November 2026.

- Added: Abū Ḥayyān's *Tuḥfat al-Arīb* on Injīl (from *najl*, "origin"), Zabūr, al-Masīḥ (six views of its derivation), Rūḥ ("God gave him life"), al-Ḥawāriyyūn ("the chosen of the prophets"), Āya, Fiṭra.
- Added: al-Rāghib al-Iṣfahānī's *al-Mufradāt* on Zabūr, Kalima (the reasons ʿĪsā is called a word), Rūḥ (called a spirit for raising the dead), al-Ḥawāriyyūn (fullers, hunters, or purifiers of souls) and al-Māʾida (food or knowledge).
- Added: al-Zamakhsharī's *Asās al-Balāgha* on Muhaymin and Fiṭra ("every child is born on the *fiṭra*"), and al-Fīrūzābādī's *al-Qāmūs al-Muḥīṭ* on Tawaffā.
- Added: the page on the Messiah in Islam gives al-Rāghib's reasons for calling ʿĪsā a word and a spirit, and the table page gives his two views of what the disciples asked for. Both list him under Sources.
- Changed: `SSOT.md` records the approved list of Arabic lexicons and the limit on quoting them.

Each Arabic phrase was read in the entry it comes from.

## 3.33.0

More manuscript images, matched to the verses each page covers. The owner repeated the list of image sources on 7 November 2026.

- Added: 56 images from Wikimedia Commons, all public domain and all manuscripts: the early papyri of Matthew, Mark, Luke and John. A gospel section gets the leaves that carry its own verses, for example Papyrus 102 (Matthew 4:11-12) for the baptism and temptation, and Papyrus 52 (John 18:31-33) for the trial. The pages for Matthew and Luke take the rest of their papyri.
- Each caption gives the papyrus number, the library and the passage, and the alt text says it is a Greek papyrus fragment. No image repeats on another page, and no image already on a page changed.
- Result: 168 images on 70 pages; 22 pages have the full four, up from 7.
- Not changed: the rule in `SSOT.md` that every image is a manuscript or an inscription.

## 3.32.0

The layout reads at every width, and the Gospel According to Islam takes in six early writings.

- Changed: the reading column is the default content width, 720px of text under the 1240px header, footer, front page and grids. Full width had been the default since 3.6.1 and ran each line of text across the whole screen, about 1,870px on a 1,920px display. A site on that default moves to the reading column once (option `injil_help_width_312`); choosing full width again under Theme Options > Branding keeps it.
- Changed: in every width, running text stops at `--ih-measure`, 46rem: paragraphs, lists, headings, folds, answer capsules, asides and the intro of section heads. Grids, tables, plates, the hero and the dilemma diagram keep the full width. Checked at 390, 768, 1,024, 1,440 and 1,920px on twelve pages: nothing scrolls sideways.
- Added: sayings of Jesus from 1 Clement, the Epistle of Barnabas, the Gospel of Mary, the Epistle of the Apostles, Tatian's Diatessaron and the Didache, each tested against the Qur'an. The 21 that do not contradict it are woven into sections 8, 9, 13, 18, 21, 22 and 24 of the Gospel According to Islam with a colour mark and a note (`inc/data/early-writings.json`). The Didache appears in notes only.
- Added: `/gospel-according-to-islam/early-writings/`, which names the sources, the translations and the rule, and `/gospel-according-to-islam/early-writings/set-aside/`, which lists the 26 passages left out with the reason for each. They include the Epistle of the Apostles' prophecy of Paul (chapters 31 and 33), set aside because it has Jesus speak from heaven after God raised him (Qur'an 5:117).
- Changed: the translations are in the public domain: Roberts and Donaldson (1 Clement, Barnabas), M. R. James, 1924 (the Epistle of the Apostles), Mark Mattison (the Gospel of Mary), Hope Hogg (the Diatessaron) and M. B. Riddle (the Didache). The Lightfoot pages at earlychristianwritings.com refused automated requests, so the Ante-Nicene Fathers texts were used for 1 Clement and Barnabas.

## 3.31.0

A contents list for pages, from the plugin the owner supplied on 6 November 2026: Ikram Hawramani's *WordPress Table-of-Contents Shortcode Done Properly* (MIT licence, 2025). The theme takes its shortcode interface and writes its own code, because the plugin cannot work inside a theme as written.

- Added: the `[auto_toc]` shortcode (`inc/toc.php`) with `tags` and `title`, and `assets/css/toc.css`. It lists the headings of a page as a nested list and gives a heading without an id one made from its text.
- Added: Theme Options > Branding > Layout > "Add a contents list to long pages" (`toc_auto`, off by default). A page with 4 or more level-two headings gets the list after the breadcrumb row.
- Added: a `[toc]` line in the page markup. The Glossary and Objections pages use it.
- Added: `tests/toc-test.php`, 34 checks.
- Fixed in the port: the plugin looks for its shortcode at `the_content` priority 99, after WordPress has expanded it at 11, so it finds nothing. The theme leaves a marker at 11 and fills it at 13.
- Fixed in the port: the plugin gives every heading a new id, which would break the notes anchors and the footnote links. The theme keeps an id the author gave and never repeats one on the page.
- Fixed in the port: the plugin rewrites the whole text through `DOMDocument`. The theme changes only the opening tag of a heading that lacks an id. The nested list is also closed properly when the levels jump or fall, which the plugin's is not.
- Changed: `SSOT.md` credits the plugin.

## 3.30.0

The Aḥmad page now names the Companion and the chains behind the ḥadīth of the Prophet's five names, which 3.29.0 left unstated because the reference could not be checked then. The owner asked for both on 6 November 2026.

- Added: the Companion is Jubayr ibn Muṭʿim, and the ḥadīth is in al-Bukhārī (3532) and Muslim (2354) as well as Mālik's *Muwaṭṭaʾ*. The numbers are al-Rūdānī's, from *Jamʿ al-fawāʾid* no. 6358.
- Added: a short biography of Jubayr from Ibn ʿAbd al-Barr's *al-Istīʿāb*, with a link to his entry in the Hadith Transmitters Encyclopedia.
- Added: three chains found in Mālik's *Muwaṭṭaʾ*, Ibn Wahb's *Jāmiʿ* and Ibn al-Ghaṭrīf's *Juzʾ*, written one to a line in the form HadithGraph reads. Each runs through al-Zuhrī, Muḥammad ibn Jubayr and Jubayr ibn Muṭʿim.
- Changed: `SSOT.md` credits turath.io, whose full-text search was used to check the ḥadīth against the books. Nothing is quoted from it.

Each chain was read in the Arabic text of the book it names. The chains of al-Bukhārī and Muslim themselves were not read, so none is given for them.

## 3.29.0

Two reading aids from hawramani.com, approved by the owner on 6 November 2026: the Hadith Transmitters Encyclopedia (hadithtransmitters.hawramani.com) and HadithGraph (hawramani.com/hadithgraph/).

- Added: a Glossary entry for Isnād and Rijāl that explains the terms and links to both tools and to the instructions for HadithGraph.
- Added: the Aḥmad page says where a reader can look up each transmitter in the chain of the ḥadīth it cites, and draw the chains. Both tools are listed under Sources there.
- Changed: `SSOT.md` credits Ikram Hawramani's two tools as reading aids. The site quotes nothing from them.

The theme fetches nothing from either site. The links open only when a reader clicks them. The two tools are third-party services: the Encyclopedia is a search over biographical dictionaries, and HadithGraph runs in the reader's browser on chains the reader types in.

## 3.28.0

Injīl and Tawrāt, which Lane's lexicon does not treat, now rest on the other classical dictionaries at arabiclexicon.hawramani.com. The owner asked for a second source on 6 November 2026.

- Added: the Glossary entries for Injīl and Tawrāt report the classical views. Ibn Manẓūr calls Injīl a Hebrew or Syriac name and records the view that it is Arabic; al-Fayyūmī gives the derivation from *najaltuhu*. Abū Mūsā al-Madīnī reads Tawrāt as "brightness and light", and al-Rāghib al-Iṣfahānī as "the book they inherited from Mūsā".
- Added: the pages on the Injīl and the Torah in Islam carry the same, with Jawharī's definition of the Injīl, Ibn Manẓūr's note on the ḥadīth "whose chests are their gospels", and the lexicons under Sources.
- Changed: `SSOT.md` names the dictionaries and says the English renderings of their Arabic are the site's own.

The modern view stays beside them: Jeffery traces Injīl to the Greek *euangelion*, and Tawrāt to the Hebrew *torah*.

## 3.27.0

Lane's Arabic-English Lexicon now backs more of the Arabic on the site, under the owner's approval of 6 November 2026.

- Added: Lane glosses for Zabūr, Rūḥ, ʿAbd, Ahl al-Kitāb, Naṣārā, Tawaffā, al-Māʾida and Fiṭra in the Glossary, each a few quoted words with a link to the entry.
- Added: the page on the end of ʿĪsā's earthly mission cites Lane on *tawaffā* (death or sleep), and the table page gives the Arabic derivations of *māʾida* beside the Ethiopic loanword view. Both list the lexicon under Sources.
- Not added: Injīl, Tawrāt, Muhaymin, Āya, Shirk and Sūra. The Lane text on the Hawramani site has no usable entry for them.

## 3.26.0

The Glossary and the Aḥmad page cite Lane's Arabic-English Lexicon. The owner approved the source on 6 November 2026.

- Added: short glosses from Lane for al-Masīḥ, al-Ḥawāriyyūn, Taḥrīf and Aḥmad in the Glossary. Each quotes a few words and links to the entry on arabiclexicon.hawramani.com.
- Added: the Aḥmad page cites Lane for the root ḥ-m-d and lists the lexicon under Sources.
- Changed: `SSOT.md` names Lane and the Hawramani site among the credits and says how a gloss is written.

The theme fetches nothing from that site. The links open only when a reader clicks them.

## 3.25.10

The settings and readme speak of the other sites in plain words, and the docs name the subfolder favicon step. No feature or setting removed.

- Changed: the Contextual links tab, its help text and the code comments in `inc/contextual-links.php` and `inc/security.php` say "the other sites" and "the shared phrase map". The `ctx_sister` option keys and the `ih-ctx-link--sister` class keep their names.
- Changed: `readme.txt` and `README.md` say that a site in a subfolder needs a copy of `favicon.ico` at the domain root, because browsers ask for it there and WordPress never sees the request.

## 3.25.9

The browser icon set is complete. No feature or setting removed.

- Added: `assets/images/favicon.svg` (the brandmark without its metadata, 832 bytes), `favicon.ico` (16, 32 and 48px), `favicon-32.png`, `apple-touch-icon.png` (180px on the paper colour), `icon-192.png`, `icon-512.png`, `icon-maskable-512.png` and `site.webmanifest`, all rendered from `assets/brandmark.svg`.
- Added: `inc/favicon.php`. The brandmark mode prints the SVG, 32px, touch icon, manifest and `theme-color` (#1f5c4a) on the site, the log-in screen and the admin. A custom favicon URL still prints that one link, and a Site Icon still gets WordPress's own tags.
- Added: `/favicon.ico` answers with the icon in brandmark mode, with a day's cache and an ETag (304 while unchanged; HEAD gets the headers alone). With a custom favicon URL it redirects there; with a Site Icon, WordPress redirects to it.
- Added: the error pages and drop-ins carry the brandmark as their tab icon, as a `data:` address, so it shows on any path and in a subfolder install.
- Changed: the favicon code moves from `functions.php` to `inc/favicon.php`, and the SVG link names `assets/images/favicon.svg` in place of `assets/brandmark.svg`.

## 3.25.8

Documentation only.

- Changed: the FTP update steps in `readme.txt` and `UPGRADING.md` send the theme's `.htaccess` file first, so its private files stay closed to visitors while the rest uploads.
- Changed: the Nginx theme rule in `README.md` matches `injil[.-]help[^/]*`, so it also covers the `-new` and `-old` folders of an FTP update, and the FTP steps tell Nginx sites to add it first.
- Changed: `.claude/skills/theme-docs/SKILL.md` takes the refreshed standard.

## 3.25.7

The anatomy of a Journal post, and the old commentary addresses fixed. No option key, function or hook was renamed or removed.

- Fixed: the November 2026 log shows 304 requests from 1 November for commentary addresses of 3.5 and earlier, such as `/quran/commentaries/az-zukhruf-57-65/jalalayn/`, answering 404 while their pages answered 200. The FTP log shows why. The PHP went up at 10:57 and `inc/data/tafsir/` at 11:10. A visit in between built the former-address map without the commentary pages and cached it for a week under the version alone. Two changes fix it. The map's cache key now carries a fingerprint of the data files (`injil_help_data_fingerprint()`: names, sizes and times), so it is rebuilt when they land. And `injil_help_former_commentary_target()` resolves every commentary address of an earlier scheme from the groups and works in `inc/data/commentaries.json` alone: the old group slugs, with or without a work, a part number (`tabari/2`) or a WordPress copy (`2-2`). The answer is a 301 to the published page, or a 302 to the nearest published page above it until the page is published. All 187 addresses the log shows resolve, with the commentary texts present or absent. `tests/former-redirect-test.php` covers the shapes.
- Added: the post's excerpt under the title (`ih-dek`), and in the meta row the reading time and "Updated" with the date when the post changed a day or more after it was published (`[injil_help_article_meta]`, `inc/article.php`).
- Added: "In short", the key points from a new Key points box (`_injil_help_points`) in the answer capsule's style, under the breadcrumb row. The WebPage `speakable` adds `.ih-points` when it shows.
- Added: the contents, a list of the post's sections from three level-two headings up. Headings without an id get one from their text.
- Added: the Sources list and the Notes framed together as one block (`.ih-sources`). The headings and lists stay as they were, so the citations read from them are unchanged. A Sources item with no italic title is now cited by its whole line, with its address when it has one.
- The FAQ on a post is the theme's FAQ style, as on the pages; the FAQPage is built from those same blocks, so it appears only when the questions are on the page. No byline was added.
- Licence: the theme is GNU GPL version 3 or later (`style.css`, `readme.txt`), with the full text in a new `LICENSE` file. Fonts, texts and images keep their own licences.
- New files: `inc/article.php`, `assets/css/article.css`, `tests/former-redirect-test.php`, `LICENSE`.

## 3.25.6

Documentation release. No option key, function, hook or address changed.

- `readme.txt` gains an Updating section: Git Updater, the uploader's "Replace active with uploaded", and FTP (show hidden files, upload to a temporary folder and rename it into place, never upload WordPress without `wp-config.php`). Its settings list covers the eight tabs (Reading was missing, and the count said seven), the log-in screen on Branding, the profile links on Sharing and the Error pages section. Three questions are new: the development files, the Security locks and the error pages. Its public text no longer calls the linked sites sister sites or the themes a network of seven.
- `README.md` lists all 25 files in `inc/` in the file map, `errors/`, `bin/`, `tests/` and the theme `.htaccess`, the Contextual links tab and the log-in keys on Branding, every filter the theme provides and a table of `wp-config.php` constants. It no longer says the theme has no test files, and it gives the test commands.
- `SSOT.md` records the folder on the live server, every filter and constant, the development-file clean-up, the test commands in the push checklist and the rule against calling the linked sites a network. The populate lock is stale after two minutes, as the code has it; the file said one.
- `UPGRADING.md` sorts the roadmap into Next, Later and Ideas, each item with its reason and evidence, drops what shipped (plates served from the Media Library, the tests in the repository), and adds the general update steps.
- The 18 font families carry 29 `fontFace` entries; the README said 18 entries.
- This file gains a 3.9.1 entry.

## 3.25.5

The rest of the fixes from the live log of November 2026: XML-RPC with a way to keep it for Jetpack, a server block for WordPress's own files, and the installer lock in the notes. No option key, function or hook was renamed or removed.

- The log showed 586 POSTs to `xmlrpc.php`, password guesses, answered with 200. XML-RPC is off as before while "Turn off XML-RPC" is on: `xmlrpc_enabled` is false, `xmlrpc_methods` is empty (the pingback methods go with the rest), the `X-Pingback` header and the RSD link go, and `xmlrpc.php` answers 403. It now stays on while the Jetpack plugin is active, which needs it, or with `define( 'INJIL_HELP_ALLOW_XMLRPC', true );` (`injil_help_sec_xmlrpc_allowed()`, `injil_help_sec_xmlrpc_off()`). The "Block xmlrpc.php" section of `.htaccess` follows the same test. Password logins through XML-RPC stay refused while the XML-RPC or REST switch is on.
- A second marked section in the site's `.htaccess`, "The Injil: deny direct core files", answers 403 for the PHP files in `wp-includes` (TinyMCE's loader and `ms-files.php` excepted), `wp-settings.php`, `wp-config-sample.php`, `readme.html`, `wp-admin/install.php`, `wp-admin/setup-config.php` and, while XML-RPC is off, `xmlrpc.php` (`injil_help_sec_core_lines()`). It carries WordPress's folder when WordPress sits in a subfolder of the `.htaccess` folder, and is left out when WordPress sits outside it (`injil_help_sec_core_prefix()`). On an Apache without mod_rewrite, `RedirectMatch 403` lines refuse the same files by their URL path (`injil_help_sec_core_base()`). `define( 'INJIL_HELP_ALLOW_CORE_FILES', true );` takes it out. A theme switch removes it with the xmlrpc.php section.
- The hourly `.htaccess` check runs again at once when what the sections should hold has changed: Jetpack switched on or off, a constant added, the XML-RPC switch moved (`injil_help_sec_htaccess_state()`). A failed write, or a `.htaccess` the theme cannot write, reads as "could not write" and is tried again on the next admin screen.
- Where the theme cannot write `.htaccess`, the Security tab shows both sections to paste after the WordPress section (`injil_help_sec_htaccess_snippet()`). On Nginx it points to the server-block rules in the README instead. It also says when Jetpack or the constant keeps XML-RPC on, and names the core-file section and its constant.
- `UPGRADING.md` and the README carry the installer lock. The site was taken over through an open `setup-config.php` while WordPress core sat without `wp-config.php`, a state in which the theme never loads. The notes give the rule that refuses `setup-config.php` and `install.php` once the site is installed, for `.htaccess`, an Apache virtual host and Nginx, and say never to upload core without `wp-config.php`. The `.htaccess` rule also covers WordPress in a subfolder. The README also gives the Nginx form of the core-file rules, with `xmlrpc.php` in a rule of its own for sites that keep XML-RPC on.
- `tests/live-logs-test.php` checks the switch, the Jetpack and constant cases, the lines and which paths they refuse and leave open, the subfolder prefix, the writing, retry and removal of both sections, and the paste lines. The rules were also run on Apache 2.4, with and without mod_rewrite, at the domain root, in a subfolder site and with WordPress in its own folder.

## 3.25.4

A page no longer fails when the structured data meets a malformed block, and a theme caught half uploaded answers 503. No option key, function or hook was renamed or removed.

- The fatal error on the front page (29 October and 1 November 2026, "Attempt to assign property "parsed_block" on null" in `class-wp-block.php`) came from `injil_help_expand_patterns()`. It swapped each `core/pattern` block for the pattern's blocks and left `innerContent` alone. A pattern that was not registered, as while the theme was being uploaded or when a stored template names a pattern that has gone, left a `null` with no inner block, and core's `WP_Block::render()` stopped the request inside `wp_head`. Visitors got a page cut off after the head.
- `injil_help_expand_patterns()` now puts each expansion in the place of its placeholder, one `null` per block, and stops a pattern that names itself. `injil_help_schema_normalise_block()` gives any block exactly one `null` per inner block. `injil_help_schema_render()` renders through both and, if a render still throws, reads the block's own markup (`injil_help_schema_block_markup()`). Every `render_block()` call in `inc/schema.php` goes through it, and if building the graph throws, `injil_help_schema_graph_safe()` returns an empty graph: the `wp_head` printer sends the page without it, and the Rank Math merge sends Rank Math's nodes as they are.
- A block with more inner blocks than placeholders used to lose the extra ones from the FAQ answers and quotations. They are read now.
- `injil_help_term_link()` (`inc/seo.php`) calls `get_term_link()` only for a term whose taxonomy is registered. The graph called it on every archive, and on a post type archive core read `query_var` on false (`taxonomy.php` line 4751).
- `functions.php` checks every file in `inc/` before it reads one, loads each inside a try and checks the flag each sets on its last line, so a file cut short where it still parses is caught too (`injil_help_theme_files()`, `injil_help_load_theme_files()`). A missing or half-written file is logged and visitors get a 503 page with `Retry-After` (`injil_help_serve_incomplete()`: the static page in `wp-content/injil-help-errors/`, then `errors/503.html`, then a few lines of HTML). The login screen, cron, AJAX and the command line go on without the theme. On 25 October 2026 a missing `inc/options.php` was a fatal error.
- The REST users routes answer only logged-in users (`injil_help_sec_hide_user_routes()`). `?author=N` goes to the About page before WordPress can redirect it to the author's slug (`injil_help_sec_author_query()`), and oEmbed answers lose the author's name and link (`injil_help_sec_oembed_author()`).
- A former address whose target page is missing, a draft or private now goes for now (302) to the nearest published page above the target, such as the group or work page of a commentary (`injil_help_published_ancestor()`). The logs showed about 650 requests for `/quran/commentaries/al-muminun-50/` and `an-nisa-155-159/` answered 404.
- The privacy page WordPress drafts on install held the path `privacy-policy`, so the theme counted the page as written and `/privacy-policy/` answered 404. `injil_help_installer_draft()` lets the content sync fill it in and publish it, when it is the page named in `wp_page_for_privacy_policy` and nobody has edited it. A privacy draft the owner wrote or changed stays a draft.
- The theme ships an `.htaccess` that refuses `.git*`, `.claude`, `tests`, `bin` and the Markdown files on Apache. On a production site the development files the release archive leaves out are deleted from the theme folder on the next admin screen (`injil_help_dev_cleanup()`). A local, development or staging site keeps them, and so does a local host name, a private address or a site that defines `INJIL_HELP_KEEP_DEV_FILES`.
- Every PHP file in `tests/` and `bin/` answers a web request with a 404 and runs nothing.
- `tests/schema-render-test.php` reproduces the fatal error with a block that has more placeholders than inner blocks, and checks the opposite shape, the front-page template with a pattern missing, the fall-back render, the term links and the 503 guard. `tests/hardening-test.php` covers the account-name guards, the redirect fall-back, the privacy page, the clean-up and the `.htaccess`.

## 3.25.3

The front page follows one story from top to bottom. No section, pattern slug, option key, function or hook was renamed or removed.

- `templates/front-page.html` renders the FAQs (`home-answers`) before Resources (`home-resources`), so the reader meets the objections after the evidence and ends on the sources and the corrections log.
- The FAQs carry the label "Part VI · The questions" (it was "In brief") and Resources "Part VII · Test it yourself" (it was "Apparatus"). The section headings are unchanged.
- The intros of the Five Gospels, Paul of Tarsus, Jesus in the Qur'an, the Gospel According to Islam, the FAQs and Resources each open with a line that leads on from the section before.
- `SSOT.md` and `README.md` record the order and the job of each section.
- The README says `injil_help_error_remove()` leaves the error pages in place on a network, as the code already did.

## 3.25.2

The web server error pages work on a site in a subfolder. No option key, function or hook was renamed or removed.

- `injil_help_error_install()` also writes a copy of each static page into `wp-content/injil-help-errors/` with the site's name and home address (`injil_help_error_static_files()`, `injil_help_error_file_source()`). The automatic install, the admin check, the status table and `injil_help_error_remove()` cover these copies as they cover the drop-ins. `injil_help_error_htaccess_snippet()` points at that folder. `errors/` stays as the copy for a site at the domain root.
- `CLAUDE.md` and `FARUQI.md` moved into `.claude/`, which `.gitattributes` already kept out of the release archive. Their two root entries in `.gitattributes` are gone, and `SSOT.md` and `.claude/CLAUDE.md` name the new paths, the check for the banned word among them.
- `injil_help_error_install()` puts a new file in place with a hard link, which fails when a file has appeared since the check, and checks an existing file again just before the swap. A file someone else wrote in the meantime is never replaced.
- The README says to take the `ErrorDocument` lines out of `.htaccess` when switching theme. `SSOT.md` lists `tests/error-pages-test.php` among the tests kept in the repository.
- `tests/error-pages-test.php` covers the static copies, with the `.htaccess` lines under a subfolder.

## 3.25.1

The error pages install themselves. No option key, function or hook was renamed or removed.

- `injil_help_error_auto_install()` runs `injil_help_error_install()` on `after_switch_theme`. `injil_help_error_maybe_install()` runs it on `admin_init` for a user with `edit_theme_options` when a file is missing, is another theme's leftover, or was written for another version, site name or home address (option `injil_help_error_pages_fingerprint`). The Install error pages button stays.
- A drop-in another theme left behind, marked "Written by the ... theme; safe to delete", is replaced at install (`injil_help_error_is_other_theme()`, state `theme`). A file without such a mark is still never touched.
- `injil_help_error_remove()` on `switch_theme` deletes the drop-ins that carry the theme's marker, and only those.
- `errors/504.html` (gateway timeout) in the design of the 502 page, with its words in `text.json`, and an `ErrorDocument 504` line in the section and the README.
- `tests/error-pages-test.php` covers the automatic install, the replacement, the removal and the 504 page.

## 3.25.0

The owner is declared as the schema author, in the markup only. No option key, function or hook was renamed or removed; two constants were added.

- `INJIL_HELP_SCHEMA_AUTHOR_NAME` and `INJIL_HELP_SCHEMA_AUTHOR_URL` (`inc/schema.php`) give the author Person its name and url, `https://menj.bio`. The sameAs list keeps the Theme Options entries and always includes `https://menj.bio`.
- `injil_help_schema_unname()` leaves the author Person's name, url and sameAs alone and strips the name from every other node, as before.
- Every page node now names the author Person. With Rank Math, its own Person nodes are removed, references to them point at the theme's author, and its Article and WebPage take that author.
- `tests/schema-graph-test.php` requires one author Person with the name, url and sameAs, the author on every page and article node, and the name nowhere else.

## 3.24.0

New answers on the Islamic Dilemma pages and a corrected reading of *muṣaddiq*. No option key, function or hook was renamed or removed.

- `patterns/islamic-dilemma-page.json` and `patterns/answers-page.json` carry three new questions, each answered in under 60 words: "Is there a Christian dilemma?", "Can the Qur’an reinterpret the Bible without calling it altered?" and one on Qur’an 3:81. The FAQPage markup is built from the same blocks, so it picks them up.
- The secular-form page reads *muṣaddiq* as confirming, declaring true, after 3:50, which states confirming the Torah and making lawful as two acts. Matthew 5:17 is now cited as the Christian reading of "fulfil", set against it.
- "The biblical dilemma" gains a paragraph on Daniel 9 rereading Jeremiah's seventy years and on the Qumran commentary on Habakkuk, and a line that the test proves too much and proves nothing for the Qur’an.
- `/islamic-dilemma/torah-in-islam/` adds Tawrāt as law and instruction, with the oral Torah marked as a scholarly view, and Genesis 18 and Moses' hand (20:22; 27:12; 28:32) as retellings.
- The Organization and author Person always list `https://menj.bio` in `sameAs`; `tests/schema-graph-test.php` checks it.

## 3.23.0

Error pages for the moments WordPress cannot build a page, and a `noindex` on the 404 page. No option key, function or hook was renamed or removed.

- `inc/error-pages.php` writes `wp-content/maintenance.php`, `php-error.php` and `db-error.php` when an administrator presses Install error pages at the foot of Theme Options. The handler checks `manage_options` and a nonce. A file is written to a temporary name and then renamed, so WordPress never reads half of one. On a network only the main site writes.
- Each file carries the marker `The Injil error page drop-in` and its kind. The button writes a missing file or one with that marker and leaves every other file alone. The table beside it reads not installed, installed, outdated or another file left alone.
- `maintenance.php` sends 503 with `Retry-After: 600`. `php-error.php` sends 500 and says "Something went wrong". `db-error.php` sends 503 with `Retry-After: 600` and says "We cannot reach our database". All three send `X-Robots-Tag: noindex`, the robots meta tag and `Cache-Control: no-store`.
- `inc/error-page-html.php` builds the pages in the site's paper, green and gold, with the brandmark inline and a dark version for a dark system setting. The style is inline and no WordPress function runs, so a page shows with the theme half uploaded or the database down. The words are in `text.json` (`errors`). Titles keep the title rule.
- `errors/403.html`, `500.html`, `502.html` and `503.html` are static copies for the web server's `ErrorDocument` lines, built by `bin/build-error-pages.php`. The README lists the `.htaccess` lines.
- The 404 page sends `noindex, follow`.
- `tests/error-pages-test.php` checks each status, header and wording, runs each file without WordPress, checks the installer against foreign and outdated files, and compares `errors/` with the builder.

## 3.22.0

The log-in screen gets a switch, a note and a pass for WCAG 2.2 AA. No option key, function or hook was renamed or removed.

- Theme Options › Branding has a Log-in screen card. `login_design` (on by default) turns the theme's dress on wp-login.php on or off; `login_message` adds one line of plain text above the form, on the log-in screen only.
- While a plugin that styles the log-in screen is active (LoginPress, Custom Login, Login Logo, Branda, Admin Custom Login, Login Designer, Erident), the theme leaves the screen to it. The filter `injil_help_login_plugin_active` adds others. WPS Hide Login only moves the address, so the design follows it there. The title tag keeps the site's rule either way.
- The body carries `ih-login`, and `ih-login--interim` in the pop-up shown when a session expires. The pop-up drops the lattice and sets a tighter column.
- Focus rings and field edges use the green and the muted brown, which reach 3:1 against the ground; the gold stays as ornament. The links under the card, the checkbox and the show-password button are at least 24px. Phones get a card with less padding.
- The closures in `inc/login.php` are named functions now, so a child theme can unhook them.
- `tests/login-test.php` checks the switch, the plugin check, the classes, the note and every title.

## 3.21.2

Two faults in the search base, found by the review of the same code on the TBE theme. No option key, function or hook was renamed or removed.

- A post type registered on `init` after priority 1 could take the stored base. The late `init` hook switched to the fallback but flushed while `$wp_rewrite->search_base` still held the old base, so the rebuilt rules kept it. The hook now sets the base in force on every load, before any flush, so the rules and the search links agree.
- When a post type or taxonomy registers `search` itself, the fallback was reserved too. A refused base now falls back to the first free one of `search`, `find` and `search-results` (`injil_help_search_fallback_base()`), and the notice names the base used.
- The page check exempted `search`, so a page at `/search/` lost its child paths to the search rule. Every base is checked against the pages now, the fallback candidates included.
- When `search`, `find` and `search-results` are all taken, the fallback numbers the last (`search-results-2`, `search-results-3` and on) until one is free.
- `tests/plugin-features-test.php` checks all of these.

## 3.21.1

A review of the matching change on the TBE theme found two faults in the search base under Settings › Permalinks. No option key, function or hook was renamed or removed.

- The help text said the base takes letters, digits and hyphens only, but `sanitize_key()` kept underscores. `injil_help_search_clean_base()` now removes them, so `my_search` is stored as `mysearch`, and the help text says so.
- A base that WordPress keeps for itself (`feed`, `page`, `tag`, `author`, `wp-json` and the like) or the Journal's base would have clashed with those rewrite rules. So would the address of a page: a base of `gospels` would have turned `/gospels/mark/` into a search. Such a base is refused on save with a notice, and `search` is used. The list is in `injil_help_search_reserved_bases()`, with a filter of the same name.
- The reserved list also takes the live category and tag bases from Settings › Permalinks and the rewrite slug of every registered post type and taxonomy, through `injil_help_search_live_slugs()`.
- A base stored before it was refused left the saved rewrite rules on the old base. The option `injil_help_search_rules_base` records the base the rules were built with, and `init` flushes once when the base in force differs.
- An array posted for the field (`injil_help_search_base[]=x`) made `sanitize_key()` throw a TypeError on PHP 8. The value is checked with `is_string()` first.
- `tests/plugin-features-test.php` checks all of these.

## 3.21.0

The owner asked for four plugins to be built into the theme: the Minimalist Social Icons Pack, Reading Progress Bar, Unlist Posts & Pages and Pretty Search Permalinks. No option key, function, hook or class was renamed or removed.

- What existed already: the share row used ten icons from the Minimalist Social Icons Pack as CSS masks, and the SEO tab had the sameAs profile list for the graph. Both are kept as they were. The theme had no reading bar, no unlisting and no search rewrite.
- Profile links (`inc/social.php`, `assets/css/social.css`). Theme Options › Sharing has a new Profile links card with an address field for each of 13 networks and a switch, on by default, for the footer. The icons are inline SVG with `fill="currentColor"`, `aria-hidden` and `focusable="false"`; each link is named by its `aria-label` and is 44 pixels square. The row shows under the footer links, and anywhere through the `[injil_help_social]` shortcode or the new Profile links pattern (`inc/data/patterns/profile-links.json`). Six icons are new in `assets/icons/social/` (Instagram, YouTube, TikTok, Mastodon, GitHub, Substack), each stripped of its title and role. With no address set, nothing is printed and no stylesheet loads. Filter: `injil_help_social_networks`.
- Reading progress bar (`inc/progress.php`, `assets/css/progress.css`, `assets/js/progress.js`). A new Reading tab sets it on or off, the post types, the top or bottom edge, a height of 1 to 12 pixels and the colours of the bar and its track. It shows on single posts and pages, never on the front page, and the two files load only there. The script uses a passive scroll listener and one `requestAnimationFrame` per frame and moves the fill by a transform. Reduced motion drops the transition. Above 600 pixels a top bar starts under the admin bar; below that the admin bar scrolls away and covers the bar until it does. The bar is `aria-hidden`: the scroll bar already tells every reader where they are, and a live progressbar would be announced on every scroll. No bar prints while the plugin is active.
- Unlisted posts (`inc/unlist.php`, `inc/unlist-admin.php`, `assets/js/unlist-admin.js`). Each public post type gets an Unlist box in the editor sidebar, an Unlisted column, a Quick Edit checkbox, a Bulk Edit choice, the "Unlisted" post state and an "Unlisted (n)" view. An unlisted post opens at its address and is left out of the home page, archives, feeds, search, REST collections, Query Loops, the core sitemaps, Rank Math's sitemap, the image sitemap, `wp_list_pages()`, the page-list block, the previous and next links and the "Read next" row. Its head carries `noindex`, set through `rank_math/frontend/robots` when Rank Math writes the head. Admin screens, and AJAX calls that come from this site's admin, still see it, as in the plugin. Each post keeps its state in the post meta `_injil_help_unlisted`, and every save rebuilds the option `injil_help_unlisted` from that meta, so two saves at once cannot undo each other. The list is mirrored to the plugin's `unlist_posts`. When the plugin has changed that list (on a site that ran it, or after it was switched on again and used), the theme takes the change over on its next read. The image sitemap reads the plugin's list while the plugin is active. Filter: `injil_help_unlist_enabled`.
- Search addresses (`inc/search-permalinks.php`). `/?s=term` answers 301 to `/search/term/`, and `/?s=term&paged=2` to `/search/term/page/2/`; other query arguments are carried over, arrays such as `post_type[]` included, and an empty search is never redirected. The base is set under Settings › Permalinks › Optional, as the plugin does, in the option `injil_help_search_base`. `INJIL_HELP_RULES` goes to 5, so the rewrite rules are flushed once after this update and never on an ordinary load. Filter: `injil_help_search_permalinks_enabled`.
- Each of the three behaviour features steps aside while its plugin is active.
- `tests/plugin-features-test.php` checks the unlisted filtering and the search addresses.

## 3.20.2

The Schema Markup Validator run of 4 November flagged two faults in the graph. No option key, function, hook or @id was renamed or removed, and every policy address stays.

- The speakable `cssSelector` named `.entry-content > p` on every page. The front page is built from its template and has no `.entry-content`, and the answers page puts its paragraphs inside groups, so the selector matched nothing on either. The new `injil_help_schema_speakable_selectors()` gives `h1` on every page, `.ih-cover-lede` where the hero lede is, `.ih-intro` where a section intro is (front page excepted), and `.entry-content > p` only when a paragraph block sits at the top level of the post's content.
- The Organization carried `masthead` and `verificationFactCheckingPolicy`, which schema.org defines on NewsMediaOrganization only. The node is typed `["Organization", "NewsMediaOrganization"]` now, and both properties keep their addresses (/about/ and /method/).
- A version change rewrites the starter pages and the Journal posts the theme wrote. `injil_help_page_is_edited()` counted a post with no `_injil_help_hash` as unedited, so on the first admin load after an update both seeders overwrote any page or post the theme had never stamped, whatever the owner had put in it. A post with no stored hash counts as edited now, and both seeders keep it. "Update all pages from the theme" still writes such a page.
- The speakable classes are matched as whole tokens of the `class` attribute, so a paragraph with `ih-intro-extra` gives no `.ih-intro` selector.
- `tests/schema-graph-test.php` renders each starter page and the front page and checks each speakable selector against that markup, and a page with no SpeakableSpecification fails. It also fails a node that carries `masthead` or `verificationFactCheckingPolicy` without the NewsMediaOrganization type. Both checks fail on 3.20.1.
- `tests/journal-test.php` strips the hashes from two Journal posts and from a customised starter page, then runs the seeders: all 3 must come through untouched, and a forced page update must still write the page. These checks fail on 3.20.1.

## 3.20.1

A CodeAnt finding on the sister themes: Theme Options built every panel on every load. Here the Content tab ran `injil_help_starter_pages()` and one `injil_help_find_page()` lookup per starter page whatever tab was open. No option key, function or hook was renamed or removed.

- The starter page list and its two buttons move into `injil_help_render_starter_pages()`. The screen builds it only on `?tab=content`. On any other load the Content tab holds a placeholder with an "Open the Content tab" link, and `admin.js` fetches the list the first time the tab is shown, through the new `wp_ajax_injil_help_starter_pages` action. That action checks `edit_theme_options` and the `injil_help_starter_pages` nonce (localised as `injilHelpPopulate.pagesNonce`).
- The confirm prompt and the batched page writer bind through delegation on the screen, so the fetched buttons work as before.
- The placeholder gets `aria-busy` from `admin.js` when the fetch starts, so it is never left busy where the script or `fetch` is missing.
- The `?tab=` read checks `is_string()` first, so `?tab[]=x` cannot make `sanitize_key()` throw a TypeError on PHP 8.
- `admin.js` opens the tab named by `?tab=` when the address has no fragment.
- The settings fields stay in every panel, so a save from any tab keeps the others. The security reports were already fetched over admin-ajax and are unchanged. The server markup carries no `tabindex="-1"`; the script sets the roving tabindex.

## 3.20.0

The owner's upgrade rules of 4 November 2026, applied without removing a feature or renaming a public identifier. The theme folder keeps its name, `injil-help`.

- Theme Options is rebuilt to the settings design system of the brief. Cards replace every `form-table`. The tabs are a WAI-ARIA tablist: `role="tab"` links with `aria-selected`, a roving `tabindex`, arrow keys, Home and End, and no reload. They scroll sideways on a narrow screen. A header carries the icon, the title, a line of description and the version; a footer links to the theme details and Site Health.
- Every on-off setting is a switch (a checkbox with `role="switch"`), the ten networks and both post types included. Content width, ornament, parallax, the gospel layout and the SEO output are selector cards (radio inputs with an active border). The two contextual-link limits are range sliders whose value shows in an `<output>` as they move. The nine colour pickers sit in a responsive grid. Below 782 pixels rows stack, controls go full width and the cards fall to one column.
- Each tab opens with a note on how to use it.
- `assets/css/admin.css` and `assets/js/admin.js` are rewritten and still load only on `appearance_page_injil-help-options`. The script has no globals beyond the localised settings it already read. New helpers in `inc/options.php`: `injil_help_ui_row()`, `injil_help_ui_switch()`, `injil_help_ui_toggle()`, `injil_help_ui_cards()`, `injil_help_ui_range()`, `injil_help_ui_input()`, `injil_help_ui_card_open()`, `injil_help_ui_card_close()`, `injil_help_ui_usage()` and `injil_help_options_tabs()`. `injil_help_sec_field()` and `injil_help_sec_render_settings()` keep their signatures and print the card layout. Every existing option key, default and sanitiser is unchanged; one key is new, `remote_images` (below). As before, the Security switches still need `manage_options`.
- Each switch is named by its action ("Turn off XML-RPC", "Show share links on posts", "Show X in the share row"), with its explanation attached through `aria-describedby`.
- `injil_help_render_options()` checks `edit_theme_options` before it prints anything.
- Privacy: a plate whose only copy is on Wikimedia Commons or Pexels is left out of the page until the Media Library holds it. The new option `remote_images` (off by default; Theme Options › Content) and the filter `injil_help_remote_images` bring back loading from the source. `injil_help_pattern_images()` lists the four Commons plates of the patterns that `images.json` does not carry, and the admin-load copier fetches them too, on a budget of their own so a failing page plate cannot hold them back. A page with a featured image of its own still gets its plate copied. An option lock (`injil_help_image_lock`, stale after 5 minutes) stops two admin requests copying the same plate at once. The Privacy page says so.
- `title-tag` and `automatic-feed-links` are declared in `after_setup_theme`; core gave them to the block theme already.
- `readme.txt` gains Description, Features, Usage, FAQ, Privacy, Upgrade Notice and Copyright and licences sections. The last lists every bundled font, image and script with its licence and source.

## 3.19.0

A mobile and accessibility pass, made to two of the owner's briefs of 4 November 2026: the mobile-first theme audit and the WCAG 2.2 AA policy. Pages were rendered at 9 widths from 320 to 1280 pixels and checked with axe-core, the keyboard, text spacing and a 200% zoom. No page scrolled sideways before or after.

- `assets/css/responsive.css` is new and loads after the ornament on every view. Base rules serve the phone; the media queries say where each stops.
- The keyboard focus ring is two rings: a 2px outline in the ink colour on a 2px halo in the page colour, swapped on the green panels. One of the two stays visible whatever the Colours tab holds. The gold ring measured 2.3:1 against the parchment, under the 3:1 WCAG 1.4.11 asks for.
- At 320 and 360 pixels the menu button sat on top of the site title and hid "Injīl". The title now takes a second line there; from 600 pixels the header is as it was.
- Footer links, breadcrumbs, post categories, the previous and next links, page numbers and the share buttons are 44 pixels tall on phones and touch screens. Page numbers are 24 pixels wide on every screen (WCAG 2.5.8); the digits were 9 pixels wide and 13 apart. The icon-only share links are 44 pixels wide on phones as well as tall.
- The Greek glosses in the menu, the footer and the section heads carry `lang="el"`, and two Arabic words in running text carry `lang="ar"` (WCAG 3.1.2). The glosses in the patterns are wrapped in a span, so the paragraph blocks stay valid. The pages built from these patterns, and `/islamic-dilemma/what-is-injil/`, are rewritten once after the update; a page the owner edited is kept.
- The Copy link button announces "copied" or the failure through a polite live region (WCAG 4.1.3).
- The header menu's Journal item is named "Journal" in the structured data; it read "JournalΗΜΕΡΑ" since 3.18.0. `injil_help_schema_navigation()` now drops each gloss span before it strips the tags, in place of a list of known glosses, and `tests/schema-graph-test.php` checks every name.
- A long word in a plate caption breaks instead of spilling past the panel when the reader widens the text spacing (WCAG 1.4.12).

## 3.18.1

Every image the structured data refers to now has a node in the same graph.

- The cause: the page's `primaryImageOfPage` pointed at the featured image's uploaded address, while the theme made `ImageObject` nodes only from the figures in the body. When the two addresses differed, the reference pointed at nothing. On 3.13.1 they always differed, since the figures still loaded from Commons; on 3.18.0 they still could, for instance after a file was stored again as AVIF. `/islamic-dilemma/` and `/gospels/mark/` showed it.
- The featured image now gets its own `ImageObject`, with the caption, credit and licence of its `images.json` entry (found through the Commons file recorded on the attachment). A figure of the same file becomes that node, so the image is described once. The match is on the file (its Commons name, or the uploaded file name without its size suffix and extension), so a figure with a missing or different licence link still merges.
- Any reference to an image address that no node defines is given an `ImageObject`, in the theme's graph and in the Rank Math merge alike, where Rank Math's own `primaryImageOfPage` or Article `image` may point at an upload it does not define.
- `encodingFormat` follows the address first, so an AVIF or WebP copy is described as AVIF or WebP. `image/avif` is known.
- A cited journal article in a Sources list carries a `headline`.
- `tests/schema-graph-test.php` gives every page with an image a sideloaded featured image, half of them AVIF, and has Rank Math refer to it. It fails on any unresolved `@id`, on a referenced image without `contentUrl`, `creditText` or `license`, on any article node of any Article subtype, NewsArticle and TechArticle among them, nested or not, without a headline, and on a second ImageObject for the featured file when the figure's licence link is missing or changed.

## 3.18.0

The site has a Journal: dated posts beside the pages.

- The Journal page is at `/journal/`, a starter page with the new template `templates/page-journal.html`, which lists the posts newest first. The reading settings are left alone. The header menu and the footer's Reference list link to it.
- Posts live at `/journal/<slug>/` (`inc/journal.php`: a rewrite rule and the `post_link` filter). An old `/<slug>/` address redirects there through WordPress's canonical redirect. `INJIL_HELP_RULES` is 4, so the rules are flushed once after the update.
- `templates/single.html`, `archive.html` and `home.html` are restyled to match the theme. Their CSS is `assets/css/journal.css`, loaded on those views only.
- A post's breadcrumb reads Home, Journal, the post, in the visible row and in the `BreadcrumbList` alike (`injil_help_breadcrumb_parents()`). Each post is a `BlogPosting` in the page's one `@graph`, with its citations from the Sources list.
- The posts are JSON in `inc/data/posts.json`, in the same compact markup as the pages. `injil_help_journal_seed()` writes them once per version with the fixed `post_date` each entry carries. A past date is published at once; a date still to come is scheduled, and WordPress publishes it on the day. A second run writes nothing. A post you edit or bin is kept as it is.
- 8 posts: 3 in October, 1 on 15 November and 4 in December, from the woman taken in adultery to the peace of Qur'an 19:33. Their title tags are in `inc/data/seo-titles.json` under `journal/<slug>`.
- The WordPress sample post is still binned on activation.
- `tests/journal-test.php` checks every entry (a slug of words only, the title tag length, 700 to 1,100 words, notes and sources, links to pages that exist) and the seeding: the status from the date, the exact date, a second run that changes nothing, and kept edits. `tests/schema-graph-test.php` now also builds each post's graph.

## 3.17.0

Pages can now carry more than one image.

- An entry in `inc/data/images.json` may add `"more"`: up to three further images, each with `file`, `alt`, `caption` and `credit`. They are placed after paragraphs, evenly through the text and clear of the notes. The first image stays where it was.
- The images are manuscripts and inscriptions from Wikimedia Commons, as `SSOT.md` requires. 112 images now stand on 61 pages: the 27 gospel sections that had none are covered where Commons has a fitting manuscript, and 26 pages gained one to three more.
- A sideloaded image is stored in the Media Library as AVIF, or as WebP when it is under 1000 pixels wide, scaled to at most 1600 pixels. A server whose image library cannot write the format keeps the source format. Nothing is added to the theme: the files load from Commons until the admin pass copies them.
- The admin pass that gave pages their featured image now also copies the extra images, six images per admin load, and records failures for a day.
- The Qur'an translation, the base text and the fonts are unchanged.

## 3.16.0

- The Read next row carries the owner's pixel arrow after its label (`assets/images/arrow-next.webp`, in the ornament layer of `assets/css/ornament.css`, so Ornament off removes it). Each is animated WebP drawn at its own pixel size, for decoration only; the link text is unchanged. Readers who ask for reduced motion see a still first frame.

## 3.15.0

- The Islamic Dilemma page credits its source study by title only. The author's name no longer appears on the front end.

The structured data takes the shared model of the network's schema plan (3 November 2026). Every type the graph carried before stays. The owner keeps all schema.org markup, including the types Google does not show.

- The Qur'an verses shown in English (the `ih-vq` reference spans) are each a `Quotation` with its text and reference, under a `Chapter` for its surah, which is part of the Qur'an `Book` (now with `genre` Scripture). The article cites them.
- The two full texts of Thomas carry an `ItemList` of their sayings in order, each a `Quotation`. The timeline carries an `ItemList` of its events in order.
- Every glossary term has its own `@id` and `inDefinedTermSet`. A term whose entry reads "(also spelt Injil, Injeel)" lists those spellings as `alternateName`; such entries were missed before.
- Images take their credit, licence, licence page, format and photographer from `inc/data/images.json`, and their width and height when the page gives them.
- The subjects in `inc/data/schema-entities.json` are defined once per page with the `@id` home URL plus `#entity-` and the key, and referenced by that `@id`. The four canonical gospels are new entries; the comparison tables are `about` the gospels their columns name.
- The header links are parts of the page's `SiteNavigationElement`. The site links in the body (outside "Read next") are `significantLink`.
- Articles add `abstract` (the first paragraph), `dateCreated`, `copyrightYear`, `accessMode`, `accessModeSufficient` and `accessibilityFeature`.
- With Rank Math, these fill what its Article and WebPage leave empty, in its one graph.
- The calligraphic mark is removed from every value, and a paragraph that names the owner is never used as an abstract.
- `tests/schema-graph-test.php` builds all 181 starter pages and checks each graph, alone and merged with Rank Math: valid JSON, unique `@id`s, every reference resolved, the required properties present, and no mark, owner's name or banned word.
- An Author name in Theme Options that holds the owner's name gives way to the site name. A last pass over the theme's graph and the merged Rank Math graph removes the owner's name from every value, author nodes included. The theme matches the name by SHA-1 digest and never writes it.
- A saying of Thomas that runs over several paragraphs, Saying 3 among them, is now quoted whole.
- A Pexels image takes its format from its address, since its key in `images.json` has no extension.
- A caption that reads "Photograph by" names the photographer as `creator` and `copyrightHolder`, as a "Photo:" credit already did.
- The test puts a stand-in owner's name in the Author name and in Rank Math's author, and fails if any word of any string matches a withheld digest. It also fails on a second definition of a nested `@id`, excuses only the `@id`s of its stand-in Rank Math graph, and fails when the page, Quotation, DefinedTerm, Chapter or Event totals fall below those of this release.

The new code is `inc/schema-extend.php`. The credits page lists no image one by one, so it gets no image list; no manuscript has a page of its own, so there is no `ItemPage`.

## 3.14.1

Links to the sister sites follow their new addresses. On 3 November 2026 the owner ruled that no address on the network carries a verse number, a count or a year, and every site answers its old addresses with a 301.

- `inc/data/network-links.json` is the same file in all seven themes again. This site's copy already held the paths from 3.11.0, and the others now carry them too. It takes `/journal/prophet-like-moses/` on Abrahamic Religions, `/deuteronomy-prophecy-and-the-paraclete/`, `/crusades-facts/`, `/taqiyya-myths/` and `/islamophobia-facts/` on Best of Islam, `/articles/sword-verse-jizya/` on Compelling Evidence and `/rebuttals/quran-wife-beating-verse-in-context/` on murtadd.org.
- The front-page Qur'an section (`home-quran` pattern) links the Best of Islam article at `/deuteronomy-prophecy-and-the-paraclete/`.

No address on this site changes.

## 3.14.0

Google's SEO Starter Guide, applied where `SSOT.md` allows. The structured data types themselves are unchanged; the conflicts are listed for the owner below.

- Archives (terms, post types, dates, the posts page) print `<link rel="canonical">`, with `/page/N/` on paged views. Before, only the front page and single pages had one.
- The core users sitemap is off, and an author archive (`/author/…/` or `?author=`) answers 301 to `/about/`. Every page is published under the site's name, so the archive only repeated the site under the account's name.
- A plate (`.ih-plate`) whose image the theme has sideloaded into the Media Library is served from there, with its class `wp-image-{id}`. WordPress then adds width, height, srcset and lazy loading. Commons serves no size with its redirect, so the browser could not reserve the space. A plate with no local copy loads lazily and decodes asynchronously, except the front page cover image.
- An image sitemap at `/image-sitemap.xml` lists each page that has an image entry, with its featured image (or the source file when the sideload has not run). `robots.txt` names it. With Rank Math's sitemap on, Rank Math lists the images and the theme's sitemap stands down.
- The front page cover image is fetched at high priority, with no lazy loading and no async decoding, so it paints with the page. The image sitemap now opens with the front page and that cover: the Media Library copy once sideloaded, else the address in the hero pattern.
- The author Person carries a `url` when the Author URL option is empty: the About page. Google's Article guidance asks for one.
- All 181 starter pages were run through the title builder: every title is under 60 characters, none is cut and none repeats.

Left for the owner. Google's list of supported structured data leaves out several types that `SSOT.md` keeps: `FAQPage`, `SearchAction` on WebSite (the sitelinks search box was retired in November 2024), `SpeakableSpecification` on pages that are not news, `Event` for the timeline (Google reads Event as something people can attend), `Dataset` for the correlation page (no downloadable data), and the page-element nodes (`WPHeader`, `WPFooter`, `SiteNavigationElement`). They stay until the owner rules on them.

## 3.13.1

Fixes to the Security tab and the starter pages.

- The spam patterns carry the `i` flag, so upper-case Cyrillic is caught on hosts without mbstring.
- The spam gate exempts a post only when it carries `_injil_help_hash`, or when the theme itself is writing its pages. A matching title or slug grants nothing, so a deleted starter slug cannot shelter a new post. XML-RPC, REST without a cookie session and WP-CLI count as outside the editor; the theme's own page writing does not.
- Private posts stay private. Only Publish and Scheduled are held as Pending; a private post that reads as spam is logged as `flagged-private`.
- Trashed spam answers 410 at its query address, /?p=ID, as well as at its permalink.
- The daily tallies are folded with a delete that removes only the value read, so a late increment is kept for the next fold. The attack table holds 30 days, today included.
- Failed logins older than 24 hours drop out of the notice and the report at once, and a count restarts when its first failure is more than a day old.
- .htaccess is reported as written only when WordPress confirms the write.
- The reports on the Security tab load when the tab is opened, so the other tabs of Theme Options no longer pay for their queries. The trash button stays off for the whole of a scan.
- `tests/security-detector-test.php` fails when `inc/data/` or its page files are missing or unreadable, and checks upper-case text against the raw patterns.
- The former-URL map is cached by `INJIL_HELP_VERSION`. Its 301 goes out only to a published page. A `-N` copy without the theme's hash is treated as edited: it becomes a draft and never goes to the bin.

## 3.13.0

- The former-URL redirect sends its 301 only once the page stands at its present address. While an update is still moving pages, the slug-history handler sends a 302 instead, so no browser or search engine caches a permanent redirect to an address that would still answer 404.
- Refused XML-RPC requests and other security counters are now one atomic upsert on a small daily row, folded into the thirty-day counts once a day, instead of a rewrite of the whole counts option on every request.

The protection built for the Abrahamic theme (2.87.0) after the October 2026 attack on abrahamic-religions.com, ported here: password guessing through XML-RPC from 701 rotating addresses under forged "Jetpack by WordPress.com" user agents, a correct password found, then a casino spam post every 18 to 20 minutes through wp.newPost under the owner's own account.

- New `inc/security.php`, `inc/security-detector.php`, `assets/js/security.js` and `assets/css/security.css`. The application-password filter moves from `functions.php` into `inc/security.php`; its setting, `block_app_passwords`, and its default are unchanged.
- Theme Options › Security grows from one switch to five, all on by default: Turn off XML-RPC (`block_xmlrpc`: xmlrpc.php answers 403, counted per day rather than logged per request, and on Apache a "Block xmlrpc.php" section is kept in .htaccess), Block application passwords, REST API changes only from a logged-in browser (`block_rest_writes`: a logged-in write must come from a cookie session with a valid `wp_rest` nonce; anonymous requests, reads, the block editor and Rank Math are unchanged), Hold suspected gambling spam as Pending (`spam_gate`) and 410 Gone for removed spam (`spam_gone`).
- A password sent through XML-RPC is refused even when XML-RPC is switched back on, and an admin notice says the password is known. Failed logins are counted by account name, since the attack changed address on every try.
- The spam gate holds as Pending any post or page that reads as gambling spam, and anything published from outside the editor. The pages the theme writes are exempt: they carry `_injil_help_hash` (see 3.13.1).
- The Security tab carries a batch scan (100 at a time) with a move-to-Trash button (never a permanent delete), clean-up of empty spam categories on request, buttons to revoke all application passwords and log out all sessions, reports (administrators, attacks per day, accounts being guessed, posts per day, burst publishing, starter pages with gambling links) and the security log. Only administrators (`manage_options`) see or change any of it.
- `define( 'INJIL_HELP_SECURITY_LOCK', false );` in wp-config.php turns the XML-RPC, application-password and REST locks off without changing the options.
- `tests/security-detector-test.php` scores every page definition, pattern and string in `inc/data/` (15,961 items) at zero and flags all 52 attack samples. The bare word "bet" (the Hebrew letter), "slot", "spins" and "odds" no longer count, so Bethlehem, Bethel, the alphabet, "Mary spins purple thread" and the Bonus pages read clean.

- Every URL the site had before stays alive: a former path redirects to the present word-based URL with a 301. The map comes from the `former` lists the pages already carry, so it covers the 27 numbered gospel sections, the nine commentary groups with verse numbers in their slugs, every numbered commentary part and /about-the-author/. WordPress copies of these (/gospel-according-to-islam/1-2/, /quran/commentaries/al-imran-33-64/tabari/2-3/) and of a present path (/about-2/) redirect too, and so does any address below a former path.
- The update now clears copies in every case. Before, a `-2` copy survived when the page itself had already moved. Now unedited copies go to the bin and edited ones become drafts.
- The redirect for /about-the-author/ is folded into the general one.
- Scan of all 176 page paths: none carries a verse number, a numeric prefix or a `-N` suffix.

## 3.12.0

The log-in screen, and structured data for every element and piece of content that fits a schema.org type.

- The log-in screen wears the site's dress in place of the WordPress defaults: the brandmark and the site name in Kagnue, linking to the site; the gloss of the header, وَلۡيَحۡكُمۡ أَهۡلُ ٱلۡإِنجِيلِ (Q 5:47), in KFGQPC Hafs; the gold star lattice and the washes of the front-page hero; a card with Evangelion labels, a gold focus ring and a green pill button. `assets/css/login.css` reads only the theme.json tokens, which `inc/login.php` prints on that screen with the font faces and any colours set under Theme Options. The words are in `text.json` (`login`), and the title tag reads "Log in | The Rightly-Guided Injīl".
- `inc/schema-content.php` adds to the graph:
  - the editorial policies on the Organization (publishingPrinciples, verificationFactCheckingPolicy, correctionsPolicy, actionableFeedbackPolicy, masthead);
  - the page's elements: WPHeader, SiteNavigationElement and WPFooter on every page, mainContentOfPage, the notes and the share row as WebPageElements with their CSS selectors;
  - the share row's networks as ShareActions, and the "Read next" row as relatedLink;
  - 263 Claims on 43 pages, from the claim labels and the status badges;
  - each objection-and-reply block as a Question, the best Christian reply as suggestedAnswer, the response as acceptedAnswer;
  - the 32 claims on the responses page, each with the article it first appeared in;
  - the corrections log as 5 CorrectionComments, each also on the page it corrected;
  - the Qur'anic glosses as Quotations tied to their verse;
  - the 20 rows of the manuscripts table as Manuscripts;
  - the Thomas full texts (Mattison; Patterson and Meyer) and The Clear Quran (Mustafa Khattab) as translation CreativeWorks;
  - each part's subjects as about and mentions, from the new `inc/data/schema-entities.json`, with Wikidata sameAs only where the identity is certain (Paul of Tarsus, the Torah, the Bible, the New Testament, Islam, Christianity, Muḥammad, Maryam).
- The 404 page has a graph; the log-in screen has none, since WordPress marks it noindex.
- The 404 page is the site's own: a green hero with the gloss of Q 1:6, ٱهۡدِنَا ٱلصِّرَٰطَ ٱلۡمُسۡتَقِيمَ ("guide us on the straight path"), a large 404 in Kagnue, a search field, and the six parts of the website in a row-of-three grid of tinted, numbered cards. The page is the JSON pattern `inc/data/patterns/page-404.json`; `templates/404.html` only places it.
- Checked by running the graph for all 181 pages, the front page and a 404: 183 graphs, every node typed, none of the new nodes empty, the largest graph 31 KB.

## 3.11.1

Fixes from a review of the 3.9.0 to 3.11.0 code and an audit of all 181 pages against SSOT.md.

- The redirect from a moved page runs at `template_redirect` priority 9, before WordPress's `redirect_canonical()` guesses at a 404, so the old address always goes where `slug-history.json` says.
- The structured data took the journal of a quoted article from a point found with `mb_strpos()`, a character count, and cut there with `substr()`, which counts bytes. With curly quotes and transliterated names before it, the cut fell early, and a title with an italic word in it would have given that word as the journal. It now uses `strpos()`. No citation on the site was affected yet: 0 of 151 change.
- Theme Options, SEO: the home and pillar title fields say how many characters fit beside " | " and the site name, and that a longer title is cut.
- A comment in `functions.php` now says every pattern is JSON.
- `injil_help_title_room()` gives the characters left beside " | " and the site name, never below 0, and both the title tag and the Theme Options hint use it; a site name over 56 characters showed a negative number in the hint.
- Leave the gospel of Paul of Tarsus said "Paul’s formula" once; it now says "the formula of Paul of Tarsus", as SSOT.md asks. The rest of the audit found no breach: every page has inbound links, every key in images.json, seo-titles.json and related.json is a page, the laʿnat Allāh mark stands only in its 4 recorded places, and the em dashes left are in reproduced texts (The Clear Quran on /quran/passages/ and Shafaat's Introduction).

## 3.11.0

URL slugs drop the articles "the", "a" and "an": 32 of the 181 pages move. Every old address answers with a 301 to the new one.

- The pillar page moves from /the-islamic-dilemma/ to /islamic-dilemma/, and its 4 child pages with it; What is the Injīl? becomes /islamic-dilemma/what-is-injil/.
- /paul/leave-the-gospel-of-paul/ becomes /paul/leave-gospel-of-paul/.
- 2 commentary groups and their 16 work pages move: /quran/commentaries/table-and-questioning/ and /quran/commentaries/son-of-mary-as-example/.
- 8 sections of The Gospel According to Islam move, among them /gospel-according-to-islam/john-baptist/ and /gospel-according-to-islam/hour-and-promise-of-counsellor/.
- New data file `inc/data/slug-history.json` lists each old path with its new one. `injil_help_starter_pages()` adds the old paths to each page's `former`, so the update renames the existing pages in place (same IDs, edits and featured images), and `injil_help_redirect_moved_page()` sends a request for an old path to the new one with a 301. It replaces the single redirect for /about-the-author/, which is now one line of the same file. While the update is still moving pages, a renamed parent can stand at its new path before its renamed child does; until the child moves, the old address is sent with a 302 to wherever the page stands, and the 301 takes over once the rename is done.
- Every internal link, "Read next" row, image key, front-page pattern, header and footer link, and this site's 4 entries in `network-links.json` use the new paths. `INJIL_HELP_PILLAR_SLUG` is `islamic-dilemma`.
- SSOT.md: no slug contains "the", "a" or "an".

Title tags.

- Every title tag is "page part | site name", under 60 characters. With the site name The Rightly-Guided Injīl the page part has 32 characters. 132 of the 181 page titles are longer; each has a short title in the new `inc/data/seo-titles.json` ("What is the Injil (Injeel)?", "Questions in the temple", "al-Ṭabarī on 3:33-64, part 2"). The page's own heading is unchanged.
- `injil_help_document_title()` builds the title for every request, the front page, search and 404 included, and fits anything still too long at a colon or between words. It also answers the title filters of Rank Math, Yoast and SEOPress, so their titles follow the same rule.
- The default pillar title is "The Islamic Dilemma explained". All 181 titles are unique; the longest is 59 characters.
- SSOT.md and CLAUDE.md state the rule.

Images from Pexels.

- What is the Injīl?, the Torah in Islam, Why Islam?, Who you are as a servant of God and Leave the gospel of Paul of Tarsus each gain an image, chosen to keep the site's rule that its only images are texts: a medieval Latin manuscript, a Sefer Torah, a Qur'an manuscript open at al-Anbiyāʾ 21:105-112, an illuminated Arabic manuscript, and a Greek inscription in marble.
- `inc/images.php` takes an image from Pexels as well as Commons: an entry with `"source": "pexels"` gives the photo's `url` and `page`, the plate loads the photo from images.pexels.com and links its Pexels page, and the featured image is sideloaded from the same URL. `inc/schema.php` gives such an image the Pexels License URL; the "Photo: name" credit names its creator.
- SSOT.md, the credits page and the privacy policy name Pexels. Pixabay is not used, because its licence requires a copy on the site's own server and the plates load from the source.

Every content seed is JSON.

- The 15 PHP patterns, the 8 front-page sections and the 7 building blocks for the editor, are now JSON in `inc/data/patterns/`, with the same slugs and the same output. The `patterns/` folder is gone. functions.php already registers every JSON pattern there.
- The words the markup writes into pages (Scholarly consensus, Minority view, Islamic position, the 5 status badges, "Notes" and "Back to the text") and the tagline set on activation move from PHP strings to `text.json`, under `markup` and `site`. Every page renders byte for byte as before.


## 3.10.0

Three pages that speak to the Christian reader directly, in answer to the "Why Jesus?" and "identity in Christ" pages of the Islamic Dilemma website. Each is between 1,600 and 2,500 words.

- /why-islam/, "Why Islam? The message in one page": the one God, shirk as the sin not forgiven to one who dies on it (4:48), the Messiah's own words against the Trinity (Mark 10:18; 13:32; John 17:3), forgiveness before the cross (Mark 2:5; Matthew 9:13), the way back (5:74; 39:53), the testimony with the words on ʿĪsā from al-Bukhārī 3435, what changes in daily life, what the Qur'an says of Christians, and 6 FAQs.
- /paul/leave-the-gospel-of-paul/, "Leave the gospel of Paul of Tarsus": where his gospel came from, the 4 places it parts from the words of Jesus, the law, the death, the charge in Jerusalem (Acts 21:20-24), the disciples and James, his own method (1 Corinthians 9:20-22), what leaving him means in practice, and 5 FAQs.
- /servant-of-god/, "Who you are as a servant of God": what the Qur'an says the believer is, item by item, from 51:56 to 84:6, with steps to take tonight and 4 FAQs.
- The laʿnat Allāh mark after Paul of Tarsus, by the owner's decision, once on Leave the gospel of Paul of Tarsus and once on Why Islam?; SSOT.md records the exception.
- Linked from /quran/servant/, /paul/false-apostles/ and /paul/paul-against-jesus/; related.json gives each a "Read next" row. 181 pages.
- The background page job starts again from the first page when it was begun under another version. A job left unfinished by one update used to resume at its old offset in the new, longer page list, skip the pages added before that offset, and still record the new version as done.

The arguments of the Islamic Dilemma website (islamicdilemma.com) answered. The site is named; the person behind it is not. Its devotional pages are left alone.

- /resources/responses/ gains a fifth section with 11 claims and replies. Among them: "where is this separate book?" (Mark 1:14-15, where Jesus preaches the gospel before any of the four books exists); the "book in the hands of Christians in 700" (the Qur'an was revealed between 610 and 632); 29:46 and 5:48 read in full; how and when the text changed, from the Qur'an's 4 verbs and the bracketed passages in Nestle-Aland; and the claim that one lie weighs the same as murder, answered from John 19:11, Luke 12:47-48 and Luke 18:13-14. The page notes that the website's home page and its own Injil page give 2 different definitions of the Injil.
- The pillar FAQ gains 3 questions: 29:46, how revealed guidance could be altered (5:44, 15:9, Jeremiah 8:8), and the ways the Qur'an says it was altered.
- What is the Injīl? adds Mark 1:14-15 to "Is the Injīl the Bible?", and sets the seed of 48:29 beside Mark 4:26-29.
- The excerpt of the responses page, the Resources index and the source library count the website with the 4 articles.

## 3.9.1

Tagged on its own branch. Its change, the security counters as one atomic upsert on a small daily row, reached sites in 3.13.0 and is listed there.

## 3.9.0

Keyword work from the October 2026 keyword lists (the editorially refined list of 3,259 keywords and the superlist of 4,226). Each search intent has one owner page.

- New page /the-islamic-dilemma/what-is-the-injil/ ("What is the Injīl? The meaning of Injil and Injeel in Islam") owns "injil", "injeel", "what is the injil", "injeel meaning", "injil in English", "is the injil the bible" and "koran vs bible". It covers the meaning and spellings of the word, the 12 verses that name it, the Bible and the New Testament, the language Jesus spoke, how the Injīl was revealed, what happened to it, the Gospel of Barnabas, and the Qur'an and the Bible compared, with 10 FAQs.
- New page /the-islamic-dilemma/torah-in-islam/ ("The Torah in Islam: the Tawrāt and the Qur'an") owns the Torah keywords of the superlist: "islam torah", "taurat book", "quran vs torah", "torah in the quran", "do muslims read the torah". The 18 verses, the Pentateuch, a comparison table marked agrees, conflicts and unclear, and 7 FAQs.
- /quran/ hub: a new excerpt ("What does the Qur'an say about Jesus?") and 5 FAQs: the Muslim name for Jesus, how many times he is mentioned, which verses are about him, whether the Qur'an calls him a Muslim, and what it says about Jews and Christians (5:82 among them).
- /quran/servant/ gains 2 FAQs: "Does the Qur'an say Jesus is God?" and "Does the Qur'an call Jesus the word of God?".
- The glossary entries for Injīl and Tawrāt give the English spellings and link the new pages; the pillar FAQ and the /quran/ hub link What is the Injīl?; related.json gives both pages a "Read next" row.
- Home meta description names the Injil (Injeel) and what the Qur'an says about Jesus.
- The two parts of the Islamic Dilemma are its two claims throughout. The front-page diagram reads "Claim 1" and "Claim 2", "Both claims rest on one assumption"; the pillar and Pfander pages say "both claims" and "two-part form"; the diagram's CSS classes are now `ih-claim*`. The al-Jalālayn footnote on 3:50 glosses <em>ṣīṣiya</em> as a spur, spike or hard protrusion. SSOT.md bans the old term.
- The writing rules in `.claude/skills/anti-ai-writing-style/SKILL.md` are now the house style for every piece of English prose (SSOT.md, CLAUDE.md). Both stay out of the release archive.
- Facts checked before release: the counts of ʿĪsā (25), ibn Maryam (23), al-Masīḥ (11), al-Injīl (12 verses) and al-Tawrāh (18) against the Tanzil simple-clean text; the Ramaḍān report (Aḥmad, <em>Musnad</em> 16984, graded <em>ḥasan</em> by al-Albānī, its chain faulted by others); the Gospel of Barnabas jubilee passage; the 2012 Ankara manuscript reports; Rashi on Deuteronomy 18:15.
- Review fixes: the Torah page marks the seventh day as unclear, with Exodus 31:17 beside Genesis 2:2 and 50:38; the readme's install steps say the pages are written in 20-second steps over the first admin screen loads; the `\bhorn` check in SSOT.md and CLAUDE.md excludes the two files that state the rule; the style skill's audit script splits sentences on `?` and `!` and catches sentence-initial triads, and its one exception to the banned list is stated plainly.
- 178 pages. The version bump makes the update create the 2 new pages and rewrite the changed ones on existing sites unless the owner edited them.

## 3.8.2

- "About the author" is now "About", at /about/: the page describes the website and names no person. The old page is moved to the new address in place (its ID and any edits stay), and /about-the-author/ redirects to /about/ with a 301. The footer link, the related-pages map and the structured data (AboutPage) follow.
- The owner's name is removed from the site: the About page, the credits, the source library and the Islamic Dilemma page now cite the owner's works by title only. The structured data no longer falls back to the WordPress author's display name for the author node; it uses the name set in Theme Options, or the site name.
- The Contact page and the default profile links (sameAs) use menj.bio in place of menj.org.
- The About page no longer links Apostle of Doom, which SSOT.md forbids presenting as a sister site.

## 3.8.1

- Coverage check of the 55 starter page entries (TF-IDF over titles, headings and text): no two pages overlap strongly; the one subject gap is closed. /quran/raised/ ("The end of his earthly mission") never used the words of its title in its text; one sentence under "How Muslims have read it" now says that, on the majority reading, the raising to God ends his earthly mission and his return (4:159) is still to come. No other text changed.
- The version bump makes the update rewrite that page on existing sites unless the owner edited it.

## 3.8.0

- The Islamic Dilemma pillar page carries no "across the network" box: SSOT.md forbids presenting bestofislam.org and apostleofdoom.org as sister sites, so the box planned for 3.7.4 is removed before release.

Keyword ownership from the October 2026 keyword map: each primary keyword has one owner page on this site.

- The Islamic Dilemma pillar (/the-islamic-dilemma/) keeps "Islamic dilemma" as its focus keyword. Title tag (`pillar_title` default in `inc/seo.php`): "The Islamic Dilemma: What It Is and Why It Fails". Meta (excerpt) opens on "What is the Islamic Dilemma?". Its FAQ block gains "What is the Islamic Dilemma?" and "Is there a book on the Islamic Dilemma?", and "Is the Islamic Dilemma a good argument?" becomes "Is the Islamic Dilemma valid?".
- /quran/servant/ owns "messiah in islam" and "who is the messiah in islam": title "The Messiah in Islam: a servant and a messenger", a new excerpt, new H2s "Who is the Messiah in Islam?", "What “Messiah” means in the Qur’an" (al-Ṭabarī on 3:45, reports 7063-7066, checked on shamela.ws) and "Are Muslims waiting for the Messiah?" (al-Bukhārī 3448 and 3439, checked on shamela.ws), and six FAQs, among them "Do Muslims believe in Jesus?" and "Is Jesus the son of God in Islam?". The count of al-Masīḥ is corrected to 11 times in 9 verses (checked on api.quran.com). The son-of-God FAQ moves there from the /quran/ hub, which takes "Did Jesus have disciples in the Qur’an?" in its place. No new /quran/messiah/ page: the servant page was the close match.
- /paul/ hub owns "gospel of paul": title "Paul of Tarsus and his gospel", H1 the same (it was "Paul of Tarsus, the false apostle", which competed with /paul/false-apostles/), a new excerpt, an H2 "The gospel of Paul of Tarsus and the Injīl", and the FAQ "What is the gospel of Paul of Tarsus?" in place of "Did Paul of Tarsus and Jesus teach the same thing?", which moves to /paul/paul-against-jesus/.
- /paul/paul-against-jesus/ ("paul vs jesus"): excerpt opens "Paul of Tarsus vs Jesus"; three FAQs. Title and H1 unchanged.
- /paul/false-apostles/ ("was paul a false apostle"): title "The false apostles: was Paul of Tarsus one?", a new excerpt, three FAQs. The laʿnat Allāh mark stays where it was; none is added.
- /paul/acts-galatians/ ("how long was paul in arabia"): title "Acts against Galatians: Paul of Tarsus in Arabia and Jerusalem", a new excerpt, an H2 "Paul of Tarsus in Arabia" and three FAQs.
- Prose revised to the house style.
- The version bump makes the update rewrite these pages on existing sites unless the owner edited them.

## 3.7.4

- On-page SEO checked, nothing else changed: the keyword "the Islamic Dilemma" is already in the title tag ("The Islamic Dilemma, answered: Qur’an 5:47 and the Injīl"), the H1, the meta description (the page excerpt) and the first 100 words. No new article.

## 3.7.3

- Five content errors from the content review are corrected.
  - Ibn Isḥāq's 7 hours of death is the Christians' claim, which he reports (al-Ṭabarī on 3:55); the Islamic Dilemma page gave it as his own view. The sentence now says so.
  - "Taught to write" (3:48; 5:110): the page called the reading a slip and a misreading, but al-Ṭabarī, al-Jalālayn, al-Baghawī, al-Qurṭubī (from Ibn Jurayj) and Ibn Kathīr, in the site's own translations, all gloss <em>al-kitāb</em> there as writing. The page now concedes the reading and answers what the argument builds on it.
  - 46:30 was quoted as confirming "what came after Moses"; the verse calls the Qur'an "a Book sent down after Moses, confirming what was before it".
  - The al-Saʿdī commentary on 3:55 is retranslated from his Arabic (quran.com tafsir 91). The passage on the descent of Jesus and the quotation of 24:55 are not in his text and are removed; his remarks on God's exaltation, the restraint of the Children of Israel (5:110) and the Christians and then the Muslims prevailing are added.
  - The alt text of the Codex Sinaiticus image on the Mark page no longer says the codex has the shorter ending; it ends Mark at 16:8.

## 3.7.2

- The Qur'anic face is served as `assets/fonts/KFGQPC-Hafs-Uthmanic.woff2` (107 KB) in place of the original TTF (298 KB, removed). The owner chose on 2026-10-26 to serve a WOFF2 conversion of the KFGQPC HAFS Uthmanic Script (107 KB, from 298 KB), accepting that the KFGQPC licence forbids modifying the font. The conversion is lossless for glyphs, cmap and OpenType layout; the digital signature (DSIG) is dropped. Recorded in SSOT.md; the credits page says so.
- Contextual links run on posts and pages only (`is_singular( array( 'post', 'page' ) )`). Attachment pages and any other singular type are no longer linked.
- Saving Theme Options no longer fails when a Contextual links textarea holds invalid UTF-8: the lines are split without the `u` flag, which returned `false` and made `array_map()` throw; `sanitize_text_field()` then drops the invalid bytes.
- A phrase too long for PCRE, added on the Contextual links tab, no longer costs the other phrases in its pattern: a pattern that fails to compile is split in two until it compiles, and only the phrase that cannot compile is left out (`injil_help_ctx_patterns()`).
- Without mbstring, a phrase with a non-ASCII capital (Ṣābiʾūn) still matches its lower-case form in the text: a missed lookup is retried phrase by phrase, case-insensitively (`injil_help_ctx_lookup()`).
- The phrase patterns are scanned as one pattern would be: from the end of the last match, the earliest match of any pattern, the longest at one place. Collecting each pattern's matches apart missed a phrase that a passed-over match in another pattern overlapped ("King Saul and Paul of Tarsus" linked nothing when King Saul was already linked); 178 of 1,720 overlapping chains of real phrases were wrong, now none.
- The breadcrumb's links no longer count as links the text already makes, so the prose of a sub-page links its parent page again.
- A list item holding two paragraphs gets one new link, not one in each paragraph.
- A nested list item gets a link of its own: the outer item's link no longer uses up the items in a list inside it, and a link in a nested item no longer uses up the outer item's.
- An element left unclosed inside a region where no link may go closes with that region, so it no longer switches links off for the rest of the page.
- Addresses and slugs on the Contextual links tab keep their percent-encoded characters: `sanitize_text_field()` deleted every `%XX`, so `/gospels/%D8%A7/` was saved as `/gospels//`. The phrase is sanitised as text and the address with `esc_url_raw()`; a line without a valid address is dropped (`injil_help_sanitize_ctx_line()`, `injil_help_sanitize_ctx_slug()`).
- The four card sections on the front page are filled so the cards reach the foot of the heading and plate beside them, with no empty space under the grid at desktop widths: The Gospel According to Islam has 15 sections (adds I, VI, XI, XIV, XVI, XXI, XXIII, XXV, XXVII); Jesus in the Qur'an adds the full passages page, the borrowing objection and four bestofislam.org articles; Paul of Tarsus adds the bonus epistle and five apostleofdoom.org chapters; the five gospels add four of the discrepancy tables, the extracanonical manuscripts and the sayings of Thomas set aside. A card for a sister site carries the class `ih-entry--sister`, a full address with `rel="noopener"`, and the site's name with a small arrow in its `ih-meta` line (`style.css`). The patterns are included by `templates/front-page.html`, so the front page takes the cards with no step, unless the template was edited in the Site Editor (see UPGRADING.md).

## 3.7.1

- KFGQPC HAFS Uthmanic Script, the Qur'anic face, now ships with its licence: `assets/fonts/KFGQPC-Hafs-Uthmanic-LICENSE.txt`. The licence allows use, copying and distribution free of charge and forbids any modification, so the TTF stays exactly as issued (not converted to WOFF or WOFF2, not subset). The credits page names it. It remains the face for Qur'anic Arabic only, through the class `ih-quran`; hadith keep Noto Naskh Arabic and all other Arabic keeps Amiri. The same face is now the Qur'anic font of all seven themes of the network.
- A Qur'anic verse set as its own block (an `ih-quran` paragraph that is not a one-line gloss) gets a line height of 2.1 and right-to-left isolation, so the small high marks and the ayah-end sign have room. Letter spacing on `ih-quran` is held at zero.

## 3.7.0

- Contextual links. In the running text of a page, the first mention of a phrase the network map knows becomes a link to the inner page that treats it: a page on this site (the Gospel of Thomas, the Last Supper, the annunciation) or a page on one of the six sister sites (Paul of Tarsus on apostleofdoom.org, the Talmud and the crucifixion on bestofislam.org, Galilee and Ibrāhīm on abrahamic-religions.com, taḥrīf on compelling-evidence.com, and so on). There is no section or list of links: the links are in the sentences.
- The map is `inc/data/network-links.json`: 404 inner pages and 913 phrases across the seven sites, each phrase owned by one page, every path checked against the theme that serves it. The same file ships, byte for byte, in all seven themes.
- Links are made as the page renders (`inc/contextual-links.php`, `the_content` at 15); the stored text is untouched. Never in a heading, a quotation, a verse, the gospel text (`.ih-bible`), Arabic, a note, a table, the breadcrumb or an existing link. At most one new link in a paragraph or list item; each page linked once, and never the page itself; a page the text already links to is not linked again.
- A region where no link may go ends only at its own closing tag: every element inside it is tracked, so a nested `div` inside an Arabic or verse `div` cannot end the region early.
- New tab, Theme Options › Contextual links: links to this site and to the sister sites switched on or off separately, at most 4 and 3 on a page by default, more phrases of your own (`phrase | /path/` or a sister-site address), and pages that get none.

## 3.6.7

- لعنة الله, "the curse of God", is set after the name of Paul of Tarsus as a calligraphic mark, like the honorific ligatures. It is used twice:
  - on the Epistle's autograph line, where the writer leaves Paul of Tarsus's shadow;
  - on The false apostles, in the sentence that gives the website's verdict, that Paul of Tarsus failed the first community's test.
- The mark is `assets/images/lanat-allah.svg`: the outlines of Dubidam Arabic Regular (NamelaType), shaped by HarfBuzz, vowelled as لعنةُ الله. Dubidam's metadata names a free personal-use licence; a web licence is the site owner's to hold. `.ih-lanah` in `style.css` shows it as a mask in the palette's rubric red, so it follows the Colours tab and dark mode, and screen readers read it as "لعنة الله, the curse of God".
- It is placed by hand and kept rare. It does not appear in titles, excerpts, menus or structured data.

## 3.6.6

- Content intended for the database is stored only as JSON, as a security requirement. The 17 patterns that supply whole pages (`islamic-dilemma-page`, `criterion-statement`, the five `section-*` indexes, `answers-page`, `how-composed`, `corrections-log`, the five `discrepancy-*` tables, `manuscripts-table`, `thomas-correlation`) moved from PHP files in `patterns/` to `inc/data/patterns/{name}.json` (`title`, `slug`, `description`, `categories`, `keywords`, `content` as a list of lines of block markup). `injil_help_pattern_file()` reads them, refusing a name that is not a plain slug and a malformed file; `injil_help_page_content()` uses it instead of the pattern registry; `functions.php` registers the same files as block patterns, so the editor's injil.help category is unchanged. The four translated labels in them ("Full evidence:", "Islamic position", "Scholarly consensus", "The criterion") are now plain text, as the theme ships no translations. All 176 pages were built before and after and compared: titles, excerpts and content are byte for byte the same, so the update rewrites nothing. `patterns/` keeps only the front-page sections and the building blocks, which the theme never writes to the database. The theme reads no `.html` or `.txt` file to populate the database.
- A run in which a page could not be written (a missing or malformed data file) no longer stamps the version, so it is retried on the next admin screen load, the first run after activation included; the result notice still names the failure. `injil_help_pattern_file()` caches only patterns it loaded.
- The rest of the page text that lived in PHP moved to JSON as well: the eight commentaries and nine verse groups to `inc/data/commentaries.json`, and the words of the commentary pages (index, FAQs, group and part pages, colophon, the folds in the gospel), of the gospel sections (running heads, navigation, titles, excerpts), of The Qur'an on Jesus and the "Read next" label to `inc/data/text.json`, read with `injil_help_text()`. All 176 pages were compared before and after: the same, except for the credit below. If either file is missing or malformed the page list is empty and nothing is written.
- The Qur'an on Jesus (`/quran/passages/`) names its source on the page: the introduction credits Alham Ahmed's <em>The Gospel According to the Quran: Jesus and His Teachings</em> (Noor Lal Foundation, 2021), and a closing Source section gives the edition, the copyright holder and the translation, <em>The Clear Quran</em> by Dr. Mustafa Khattab, used with the Al-Furqaan Foundation's permission. The work was already credited on the credits page, the composition page, the sources list and the gospel index, but not on the page that carries all 45 passages.
- How this gospel was composed: the credits now say who wrote the 237 notes: 23 are the Clear Quran's footnotes to the Qur'anic passages, as Alham Ahmed's compilation prints them; most of the rest are Shafaat's; this website wrote the notes on the Qur'anic reading of the arrest, the substitution and the raising in sections 25 to 27 and rewrote the notes listed on the page. The FAQ answer had credited Shafaat with all the notes, and the section on the notes had given all the notes to the last three sections to the website, though seven of them are Shafaat's and seven the Clear Quran's. The page is rewritten on the update unless it was edited.
- Writing style v2 applied to all of the site's own text.
  - Reframes ("not X but Y", "The question is not X. It is Y.") now state the positive claim.
  - Vague authorities ("scholars say", "most scholars") now name the source the page already cites, such as Baur, Knox, Georgi, Raymond Brown, al-Kairānawī or Accad; where no source is named, the sentence now carries the claim without an unnamed authority.
  - Padding triads and anaphoric runs are cut, and counts are written as digits (27 sections, 8 works).
  - Long paragraphs are split, and page-announcement and recap sentences are cut.
  - Curly quotes and apostrophes are used throughout, entities included, and headings are in sentence case ("Paul of Tarsus, the false apostle"; "The Islamic Dilemma, answered", which is also the default home and pillar title).
- The commentary translations follow the hard rules: their 744 em dashes are now commas or parentheses, "in order to" is now "to", and "Moreover" is now "Also". Their wording is otherwise the translation as it was.
- Reproduced texts are left as their authors published them, as the style guide exempts them: Shafaat's gospel and notes, the Clear Quran, and the Mattison and Patterson-Meyer translations of Thomas.
- Two errors found on the way are corrected. The Thomas sayings-order test said a correlation near 0 is what copying would leave, against its own conclusion; it is what independence would show. The verse-index pattern called the Qur'an section Part III; it is Part IV.

## 3.6.5

- Rank Math SEO: the theme works inside it instead of stepping aside. With the plugin active and Theme Options › SEO on Automatic, Rank Math owns the head (title, description, canonical, robots, Open Graph, sitemap) and there is one structured-data graph, Rank Math's: the theme adds through `rank_math/json_ld` every node Rank Math does not make (Book and Chapter for the gospel, the commentaries and their translations, DefinedTermSet, Table, Dataset, Event, Quotation, FAQPage, the two subjects, the site navigation, the manuscript images with their licence) and gives Rank Math's Article its citations, subjects and the chapter or translation it belongs to; every reference to the theme's own organisation, page and article is pointed at Rank Math's, so nothing dangles and no node is printed twice. The theme's author stays its own Person node, since Rank Math's publisher may be an Organization. The theme declares `rank-math-breadcrumbs` support and prints Rank Math's trail in its breadcrumb row, so the visible trail and Rank Math's BreadcrumbList are one list. When Rank Math's homepage description is empty, the theme's is used. "Always on" still prints the theme's own head and graph as well; "Always off" leaves everything to the plugin. New file `inc/rank-math.php`.
- The front-page FAQ band has eight questions, two full rows of four (two columns below 1200px, one on phones); the two added, on what the Qur'an says about Jesus and whether a Muslim gospel is a new idea, come from the FAQs page, where the second now carries its label. The Five Gospels section on the front page has nine cards (the full text of Thomas and the sayings-order test added) and the other sections six, in three fixed columns, so every card grid ends on a full row at every width. The section head sits above its cards below 1200px.
- The Content tab's buttons report what they did. "Update all pages" and "Create missing pages" used to write all 176 pages in one request, which on an ordinary host could outrun PHP's time limit and never come back to the screen, so no message appeared. They now write the pages in batches of about twelve seconds over admin-ajax, show "Writing the pages: 40 of 176 done" while they work, and then reload the screen with the result at the top: how many pages were created, updated, already up to date, kept because you edited them, or could not be written. If another update is running they wait and retry; if a request fails the message says so and pressing the button again carries on. Without JavaScript the link still runs the whole job in one request and shows the same result. The automatic rewrite after activation or a theme update runs the same way, about twenty seconds per admin screen load, with a notice showing its progress and a notice when it is done. Code: `injil_help_populate_page()`, `injil_help_populate_step()`, `injil_help_populate_job_start()` and `_run()`, `injil_help_populate_summary()`, the `wp_ajax_injil_help_populate_batch` action.
- The two-column flow of prose pages added in 3.6.1 is removed: it split the section navigation and the share row across the columns. Content stays full width; only the gospel text keeps its Bible columns.
- New theme screenshot: the front page's opening band above The Five Gospels section with its nine cards in three full rows, including the two new Thomas cards.

## 3.6.4

- The development documents (SSOT.md, CHANGELOG.md, UPGRADING.md, with README.md and the dot-files already so marked) are `export-ignore`, so the archive Git Updater installs carries only what WordPress reads. The theme has no test files; the release checks live outside the repository.

## 3.6.3

- A last pass against the Google SEO Starter Guide: every page below the front page opens with a visible breadcrumb row (Home, the parent pages, the page), matching the BreadcrumbList; meta descriptions are trimmed to 160 characters at a sentence end; search result pages carry noindex; ten "its own page" anchors are rewritten as descriptive anchor text.
- Structured data widened to every schema.org type the content supports, whatever a search engine chooses to show: Organization gains a ContactPoint; WebSite its SearchAction and the subjects it is about (ʿĪsā ibn Maryam and the Qur'an as Person and Book nodes with Wikidata identifiers); the header navigation is an ItemList of SiteNavigationElement; every page node carries a SpeakableSpecification; article pages are Article + ScholarlyArticle with their Sources list as `citation` (CreativeWork, or ScholarlyArticle in a Periodical); the gospel sections are Chapters of the Book; each commentary page names the Arabic Book, its author and the English CreativeWork that translates it; the glossary is a DefinedTermSet of 71 DefinedTerms; comparison tables are Table nodes; the sayings-order test is a Dataset; the 49 dated entries of the timeline are Events; quotation blocks are Quotation nodes; the About page is ProfilePage and AboutPage; the sitemap's page list and the section indexes are ItemLists; FAQPage stays.

## 3.6.2

- Links to apostleofdoom.org and bestofislam.org are within the running text only: thirty sentences across the Paul of Tarsus pages, the Islamic Dilemma pages, the measure, John, the discrepancies index, the Qur'an pages, the resources pages and the bonus chapter name the article that takes the point further and the site it is on, the way any source is cited. The "On the writer's other websites" line of the Read next row, the footer links and the bestofislam.org sameAs entry added in 3.6.1 are removed; no site is presented as a companion or sister site.

## 3.6.1

- Content is full width by default: the text, tables and plates of every page fill the page between the side gutters, and the header, footer and front page follow. Theme Options › Branding › Content width offers full (default), wide (1240px, the width the header and footer had) and reading column (720px, the former default). The setting is the body class ih-width-full, -wide or -reading; the stylesheet overrides the constrained-layout widths WordPress prints. At full and wide widths a page of running prose is set in two columns on screens from 1280px, as the gospel sections are, so a line never runs past about eighty characters; headings, tables, plates, folds, asides, cards and the Read next row span both columns, and the gospel sections keep their own column setting.
- The Gospel of Thomas page says why Thomas is counted: a new section, "Why Thomas is counted: a sayings gospel of the kind Q was", with the evidence (Robinson’s LOGOI SOPHON on the genre, Crossan’s 37 units common to Thomas and Q, Koester on the six shared parables and the missing Son of Man layer, P.Oxy. 1 at about 200 CE, the shorter parables, the Jesus Seminar’s Five Gospels), the contrary case (Goodacre, Gathercole, Perrin) labelled, and the Qur’anic reason (the Injīl was given to ʿĪsā, and a sayings collection without a passion narrative is the nearest genre), with three questions and the sources; the gospels index FAQ says the same in short.
- "Paul of Tarsus" throughout the site’s own text, headings and link text included (twelve bare "Paul"s corrected); book titles, quotations and the glossary term keep their wording. Rule recorded in SSOT.
- Links to apostleofdoom.org and bestofislam.org where the subject meets (reworked in 3.6.2, which keeps only the links within the text).
- The hero lede and the intro paragraph of every section are justified like the body text; they sat outside the post content and were set ragged.
- Fixes from review: an edit to a starter page's title or excerpt now counts as an edit and keeps the page from being rewritten on update (a second hash, of the title and excerpt, is stored beside the content hash; pages written before 3.6.1 carry only the content hash until rewritten); a select option missing from a saved form falls back to its default instead of storing nothing.
- New theme screenshot of the front page as it now stands.

## 3.6.0

- Every URL is made of words, as the Google SEO Starter Guide asks. The 27 gospel sections move from /gospel-according-to-islam/1/ … /27/ to the words of their titles (/gospel-according-to-islam/john-the-baptist/, /trial-barabbas-and-raising-of-jesus/ …; the slugs are `section_slugs` in inc/data/gospel-islam.json). The commentary groups lose their verse numbers (/quran/commentaries/family-of-imran/, /neither-killed-nor-crucified/, /birth-of-jesus/ …), and a later part of a long commentary is a sibling page named for the work (/quran/commentaries/family-of-imran/tabari-part-two/) instead of a numbered child. On the first admin load the existing pages are renamed in place, keeping their IDs, edits and featured images (`former` paths on the page definitions; `injil_help_adopt_former_page()`); the filter that allowed numeric slugs and the rewrite rules for them are removed. The old URLs are not redirected.
- Internal linking. Every article ends in a "Read next" row of related pages: the curated map inc/data/related.json plus what the builders set (a gospel section names the commentary groups it rests on; a commentary page names the gospel sections, the Qur'an page and the passages page its verses belong with, its group, the index and its other parts). The folds under a gospel section link to every group page the commentary belongs to, and a group page links every part of a work. Before this release four pages had no inbound link from any page's content and 38 had one; now every page has at least one and all but two have two or more.
- New page: Pfander's Balance of Truth (/the-islamic-dilemma/balance-of-truth/). Karl Gottlieb Pfander's Mīzān al-Ḥaqq (German, 1829; Persian, 1835; English by R. H. Weakley, 1866; revised by Tisdall, 1910) is identified as the source of the Christian polemic the site answers: what its first part argues, where each claim meets this website (the identification of the Injīl with the four gospels, "whosoever accepts the Koran must accept them", "not a single passage" naming the corruption, "at what period, by whom", Jesus and the Torah, the collection of the Qur'an, the Prophet foretold), what he has right, the line from Pfander to the Islamic Dilemma, four questions and sources. The book is quoted in short phrases only and never reproduced. Linked from the pillar page (origin paragraph, FAQ, sources, the next-page line), the objections and responses pages, the timeline (1829 to 1835 and 1854 to 1864 entries), the source library (full citation, with al-Kairānawī's Iẕhār al-Ḥaqq and Powell) and the good news of Aḥmad. Starter content is 176 pages.
- The source library and the pillar page link Reconsidering the "Islamic Dilemma" on Academia.edu.
- Hardening: application passwords are switched off by default (`wp_is_application_passwords_available` returns false), so no REST API or XML-RPC log-in with a per-application password is possible. A new Security tab in Theme Options holds the block (`block_app_passwords`, on by default); untick it to let a client that needs them in.

## 3.5.2

- Images for the nativity and crucifixion tables (Giotto's Scrovegni Chapel frescoes, public domain), confirmed on Commons. Thirty pages now carry one; the good news of Aḥmad, the borrowing page and the composition page still do not.

## 3.5.1

- Images from Wikimedia Commons on twenty-eight article pages (the five gospels and the Thomas pages, the discrepancy tables, the six Paul of Tarsus pages, the two Islamic Dilemma pages and the measure, the Qur'an theme pages, the source library and the objections): each a public-domain or CC0 manuscript page or painting chosen for the page, set as a plate near the top with its caption, credit and a link to its Commons page. The same image is sideloaded into the Media Library and set as the page's featured image, a few pages per admin screen load, so the Open Graph card and the structured data carry it. The map is inc/data/images.json (file name, alt, caption, credit), read by inc/images.php; the pages still without one are the nativity and crucifixion tables, the good news of Aḥmad, the borrowing page and the composition page.
- The dilemma section on the front page is drawn as a diagram: the two claims side by side with "or" between them, a labelled rule joining them to the assumption they share, which now spans the width beneath them and says in a line why it fails. The cards used to sit in an open-ended grid that left a fourth empty column on wide screens.
- Fix: the entry cards on the home sections and the index pages no longer stretch to the height of the section head beside them (the grid's rows were filling the stretched container), so the coloured boxes fit their text again.
- Prose pass against the house writing rules: the composition page's departures list is plain bullets and its "not a translation, not a harmony, not a life" run is positive statements; "the measure is its rule" replaces "not its rule"; the pillar page's "reads them as free-standing statements" sentence, "What is worth noting", two "in order to" and "Nobody knows" rewritten.

## 3.5.0

- The Gospel According to Islam is set like a printed Bible: a running head, the section numeral, an argument line (title, verse count, the Qur'anic passages in the section), numbered verses, the Qur'an's own chapter-and-verse numbers in green, two columns on wide screens with a rule between, and the notes at the foot of the text in small type as a reference apparatus. Theme Options > Content > Gospel text layout chooses two columns, one column or plain paragraphs; the markup is the same in every case (assets/css/bible.css; body class ih-gospel-columns, -single or -plain).
- The verse breaks are plain "[v]" markers in inc/data/narrative.json, seeded from Shafaat's own 1985 verse division (all 638 verse starts matched the present text) and numbered at build time, so the division can be moved, added to or removed by editing the data. Qur'anic lines carry "[qS:A]" references. Four markup tokens serve the layout: [bible] ... [/bible], [notes], [p.classes] text, and the two markers; verse anchors (#v12, #q4-157) resolve on every section page.
- The gospel text brought into line with the Qur'an as the measure. Shafaat's ending (a namesake crucified by a clerical error, Jesus released, dying of illness on the road to Galilee) is replaced: they did not kill him, nor crucify him, but it was made to appear so to them, and God raised him to Himself (4:157-158; 3:55), with the commentators' reading that another was seen in his likeness, no name given, the empty tomb as Mark 16:1-8 leaves it, the reports as the sources record them, and his return before the Hour (4:159; 43:61); sections 26 and 27 retitled. Notes rewritten where they read the Qur'an's "Messiah" as a reserve, Jesus as "exploring" his office, a sign as a product of faith, or the devil's words as thoughts in Jesus's mind; "approaching forty" is "about thirty" (Luke 3:23) in step with the timeline; "my heavenly Father" and Mark's "only the Father" read "the Lord" in Jesus's mouth; Genesis 22:18 restored; "messianic trinity" and two polemical notes reworded; the Clear Quran footnote on the substitute brought into line with the commentators. 237 notes. Every departure is listed under "Where this gospel departs from Shafaat" on the composition page and logged on the corrections page; the pages that said his wording was unchanged now say he is the base text and the measure is the rule.
- Fixes from review: the correlation page explains R and p correctly (a near-zero R means no linear relationship was found; p is the probability of such a correlation arising if the orders were unrelated); a missing or malformed body_file fails its page file so nothing half-made is published; saying 62 of the Patterson and Meyer text completed; README version in step.
- The Five Gospels: the first part of the site now counts the Gospel of Thomas with Mark, Matthew, Luke and John in its name, its index pages, the hero, the footer and the FAQ links. The four canonical gospels are still "the four gospels" wherever the text means them.
- The Gospel of Thomas in full, on two pages: Mark M. Mattison's public-domain translation (/gospels/thomas/sayings/) and the Patterson and Meyer translation, used with permission (/gospels/thomas/sayings-patterson-meyer/). The texts are data files (inc/data/thomas-mattison.json, inc/data/thomas-patterson-meyer.json) appended to the page body through the new "body_file" key of a page definition.
- Does Thomas depend on the synoptic gospels? The sayings-order test (/gospels/thomas/correlation/, pattern thomas-correlation): the correlation analysis of the order of the parallel sayings against Matthew, Mark, Luke and Q, with its tables, what it shows and its limits.
- The Thomas introduction now gives the manuscripts precisely (Nag Hammadi Codex II, 2, fourth or fifth century; P.Oxy. 1, 654 and 655 with their dates and sayings), the range of dates with the scholars who hold them, Hippolytus as the first witness to the title, and the debate over independence; the correction is logged.
- Four more discrepancy tables, each with a Thomas column: the genealogies, the birth of Jesus, the last supper and the crucifixion (/gospels/discrepancies/genealogies/, /nativity/, /last-supper/, /crucifixion/; patterns discrepancy-*). The resurrection table gains the Thomas column and a note on why it is empty; the discrepancies index lists the five and explains "None" and "No account".
- The infancy gospels woven into the gospel: items from the Infancy Gospel of James (sections 1, 2 and 5) and the Infancy Gospel of Thomas (sections 4 and 14), summarised from Mattison's public-domain translations, with the same colour marks and notes as the Thomas sayings; the conflicting items (the cursing child) are set aside and the note says so. Data in inc/data/infancy.json, merged by injil_help_gospel_woven_sections() between Thomas and the substitution witnesses.
- Did the Qur'an borrow its stories? (/resources/borrowing/): the charge stated fairly, the parallels with the infancy gospels and where they stop, the question of a route, what can be conceded, and what would count as evidence.
- Manuscripts of the extracanonical gospels (/resources/manuscripts/, pattern manuscripts-table): what survives of Thomas, the infancy gospels and the others, in what language, and when it was copied.
- The pillar page (/the-islamic-dilemma/) keeps its four answers, its objection and reply, its link to the secular-form page and its six questions, and adds from the study Reconsidering the "Islamic Dilemma": where the argument came from (Shamoun, Wood, Pfander, Istifan Ward), the four substitutions it rests on, two further answers (the Bible's own textual history; Paul stands outside and the canon is not of one mind), the four registers of the verses on earlier scripture, five more questions (10:94, 3:84, 5:68, circularity, where the Injil is) and a list of sources. The existing answers are extended with the singular Injil, the Peshitta and the Diatessaron, muhaymin in Ibn Kathir and al-Tabari, and the classical scholarship on tahrif (Adang, Reynolds, Schaffner).
- The synoptic problem page says what Q contains, from The Critical Edition of Q.
- Two notes of the narrative corrected: Quirinius (note 55) and Nephthalim (note 66).
- Credits and the source library name Mattison, Patterson and Meyer, the correlation note, Kirby's Early Christian Writings, The Critical Edition of Q and the study on the Islamic Dilemma with the works it rests on.
- Starter content is now 175 pages; the Content tab lists them all.
- The documentation states that the theme is a custom child theme built for one site, The Rightly-Guided Injil at injil.help, and not intended for any other.

## 3.4.0

- Theme header: name The Injil, author MENJ (https://menj.blog), Theme URI the GitHub repository. The site title stays The Rightly-Guided Injīl.
- The classical commentaries on Jesus: eight tafsir works (al-Tabari, al-Baghawi, al-Qurtubi, Ibn Kathir, Tanwir al-Miqbas, al-Jalalayn, al-Durr al-Manthur, al-Sa'di) translated in full, with every report and its chain, on the verses that tell the story of Jesus (3:33-64, 4:155-159, 5:110-120, 19:16-37, 21:91, 23:50, 43:57-65, 57:27, 61:6). Published under /quran/commentaries/ and folded into fourteen sections of The Gospel According to Islam as "From the commentaries" blocks. Data in inc/data/tafsir/, builder inc/tafsir.php; credited on the credits page and the source library.
- Two markup tokens for page bodies: "? Question" + answer (FAQ entry, collected into FAQPage structured data) and "[details] Title" ... "[/details]" (a folded block). Block style is-style-fold.
- New page /the-islamic-dilemma/secular-form/: the Islamic Dilemma in its recent secular restatement (two steps, four exits) and the historical-critical reply that grants its first step, answered point by point, with the biblical dilemma the method creates for the Christian; linked from the pillar page and the responses page.
- Basilides: the introduction and note at section 26 now carry the attested facts (his dates, his teachers, the gospel attributed to him by Origen and Jerome, the twenty-four books of commentary, Agrippa Castor's lost refutation, Pseudo-Tertullian on martyrdom) with the Catholic Encyclopedia article cited.
- Kagnue, the display face, now also sets the titles of the section index pages and the home sections and the large numerals.
- Sources of the commentaries: the public-domain Arabic at tafsir.app for six works, greattafsirs.com for al-Jalalayn (the tafsir.app copy clips the end of several verses) and shamela.ws (book 12877) for Tanwir al-Miqbas. Where a commentator's chunk runs past the verses about Jesus, Mary, Zachariah and John (4:160-161, 43:66, 57:26, the tail of 5:109) the build cuts it at the commentator's own heading. About 200,000 Arabic words become about 315,000 English words; the gospel sections that carry the folded commentaries are large (section 2 is about 83,000 words), so a page cache is advisable.
- The Qur'an section index, the composition page, the credits page and the source library point to the commentaries; the source library lists the eight works and Lawson's survey of the commentaries on 4:157.
- Starter content is now 166 pages; the Content tab lists them all.
- readme: install steps shortened to the automatic path, the pattern, block-style and template lists brought up to date, the FAQ and markup tokens documented.
## 3.3.0

- Correction: the Basilides note and the crucifixion page no longer say that classical commentators named Simon of Cyrene; none did. The crucifixion page now sets out how the substitute was identified and when: the volunteer disciple and the arresting officer in al-Tabari, Judas in the Gospel of Barnabas, Simon of Cyrene from Basilides in modern apologetics, and the swoon reading from Ghulam Ahmad and Deedat, which the website does not use.
- Three early Christian witnesses to the substitution at the cross are woven into section 26 of The Gospel According to Islam as green-marked passages: the Gospel of Basilides (Irenaeus, Against Heresies 1.24.4: Simon of Cyrene crucified in Jesus's likeness), the crucifixion passage of the Second Treatise of the Great Seth (NHC VII, 2) and that of the Coptic Apocalypse of Peter (NHC VII, 3). In each the laughter attributed to Jesus is set aside, and in the Nag Hammadi texts their docetism and their polemics, with the notes saying so; inc/data/substitution.json, merged with the Thomas sections by injil_help_gospel_woven_sections().
- Al-Faruqi's Divine Transcendence and Its Expression (1983) cited on John (the Semitic sense of the titles and sayings; the Gnostic source of the Logos), Paul of Tarsus against Jesus (the mystery religions and the dying god) and A servant and a messenger (transcendence as the ground of the denials); added to the source library; the Credits page and readme state the arabesque principle behind the ornament.
- Stronger parallax: the lattice, the hero's three layers, the section-banner watermarks and tints, the manuscript plates, the card grids and the footer pattern move at different speeds as the page scrolls. Theme Options > Branding > Parallax depth: Strong, Gentle or Off.
- Structured data rebuilt as one JSON-LD graph per page (inc/schema.php): Organization with logo and sameAs, Person, WebSite with sitelinks search, typed page nodes (WebPage, CollectionPage with ItemList, ProfilePage, ContactPage, SearchResultsPage), BreadcrumbList, Article/BlogPosting with images and word count, Book for the gospel sections, ImageObject licence metadata for every manuscript image, FAQPage wherever there are FAQ items. New Theme Options > SEO fields for the author, profile links and logo.
- Six SEO FAQs on every main page: the pillar page, the measure, the five section indexes and the composition page.
- KFGQPC Hafs is now applied only through the class ih-quran and is reserved for Qur'anic text; ih-ar alone is generic Arabic in Amiri. Every Qur'anic gloss carries ih-quran, dir="rtl" and lang="ar".
- Section index pages carry an overview of the part and a pointer to the next; the corrections log explains how corrections work; the resurrection table has notes on reading it.
- All content is now JSON under inc/data/: the starter pages (pages/*.json), the narrative, the section titles and Introduction, the Qur'anic passages and the Gospel of Thomas sections. The .txt data files and the PHP arrays are gone; the pages render identically.
- The article pages are written in full in place of the earlier outlines: the four gospels, the synoptic problem and the discrepancy index; the six Paul of Tarsus pages; the six Qur'an pages (800 to 1,300 words each, with sources). The timeline runs from 6 BCE to 1985 in four periods; the glossary covers the Arabic, Greek, Hebrew and Syriac terms and the vocabulary of New Testament scholarship; the source library indexes every work cited by kind; About, Contact, Terms, Privacy and Bonus chapters are full pages, and the privacy policy describes the share links.
- Share row under posts and pages: X, Facebook, WhatsApp, Telegram, Reddit, LinkedIn, Bluesky, Threads, Pinterest and LINE, with Copy link and a native share button where the browser allows. Icons from the Minimalist Social Icons Pack as palette-coloured masks. New Theme Options > Sharing tab.
- The default social image is now bundled (assets/images/og-default.jpg) instead of hotlinked from Wikimedia Commons.
- Theme updates rewrite only the starter pages whose text changed and that you have not edited (each page the theme writes carries a hash of its content; an edited page is kept and marked on the Content tab, and only the "Update all pages" button overwrites it), keep no revision for the rewrite, and flush rewrite rules only when permalinks had to be set. Page text is slashed before saving, so backslashes survive. A missing or malformed data file leaves the version unrecorded so the run is retried.
- The Resources section is back on the front page.
- No placeholder content: the corrections log opens with the real record instead of a sample row; the measure states the website's position on alteration; the pillar page's objection, reply, response and two open questions are written; the Objection-and-reply and Gospel-chapter patterns carry worked examples; the unused placeholder cover art is removed; first activation bins WordPress's sample post, page and comment and replaces the default tagline.
- "How this gospel was composed" rewritten in full: the base text, the six-chapter arrangement, the Qur'anic passages set in the story, the notes, the Gospel of Thomas and the measure applied.
- Pages read as one story. Front page: the argument, the books, the man who changed the message, the measure, the gospel restored, the apparatus, then the FAQs; each section carries a Part label. Paul of Tarsus opens with the seven letters, the evidence the rest depends on; the FAQs follow the same order as the site; the Gospels index names the role of the entries that are not one of the four.
- Theme Options page colours follow the Colours tab; badge text and icons use the parchment token instead of white.
- languages/ folder added for translation files; readme corrected on page rewriting.
## 3.2.1

- Starter pages now update themselves: after a theme update, the first admin screen load rewrites every starter page from the new text (once per version). Edits made to those pages are replaced. Theme Options > Content also has an "Update all pages from the theme" button.
- Main navigation links no longer underline on hover.
## 3.2.0

- The Gospel According to Islam now follows The Narrative of Jesus, Son of Mary: the text in paragraphs rather than numbered verses, grouped into six chapters over the same 27 section pages (/gospel-according-to-islam/1/ to /27/), opening with Al-Fatiha and closing with the Qur'an's own statements on God's oneness. Alham Ahmed's Qur'anic passages stand where they fall in the story, under their own headings, and all 235 notes run in one numbering. Where a note says "note 19" it is now a link to that note on its page.
- Source data lives in inc/data/narrative/ (chapter files and two note files); inc/gospel-islam.php builds the pages. The earlier verse files and the per-chapter Qur'an appendices are removed; the full compilation remains on /quran/passages/.
- Obvious scanning errors in the pasted text were corrected (stray marks, broken references such as Micah 5:2 and Mark 11:1-11, "Bethsaida!"); "Paul" in the notes is now "Paul of Tarsus".
## 3.1.1

- Fixes from review: Site Icon instructions for a block theme; the gold label colour now follows the palette; every manuscript image's alt text names its date and holding institution; the Theme Options page loads its styles and remembers the selected tab after saving; the page builder has a lock against creating a page twice; footnote markers work in headings and quotes; short intro and description lines are no longer justified.
## 3.1.0

- New ornament layer: star-lattice background, tinted section banners with watermark icons, hero wash, icons on cards, headings and badges, framed manuscript images, patterned footer and a gentle reveal on scroll. Switchable under Theme Options > Branding > Ornament (Rich, Subtle, Off).
## 3.0.0

- The compiled Qur'anic passages on Jesus (145 verses with headings and footnotes, inc/data/quran-jesus.txt, inc/quran-jesus.php) sit beside the chapters of The Gospel According to Islam that follow them, and are gathered on /quran/passages/.
- The Gospel According to Islam now uses Dr. Ahmad Shafaat's text (Vantage Press, 1985) as its base: 27 chapters in verse form with the author's notes as numbered footnotes, plus the Introduction. The pages carry only the text and notes; all credits are on the separate Credits page (/credits/), linked from the footer. Source and parsing: inc/data/gospel-islam.txt and inc/gospel-islam.php.
- The earlier Gospel of Thomas sections are carried into the matching chapters (2, 8, 9, 22, 24, 26). Starter content is now 73 pages.
## 2.13.0

- New page /resources/responses/: four published articles (Samuel Green, John MacArthur, Entrusted to the Dirt, Stand to Reason) with their claims stated and answered; four new FAQs; cross-links from the Objections page, Resources index and Source library. Starter content is now 49 pages.
## 2.12.0

- New objection and reply on the Objections page: the Oasis and McGrath video "The Gospel(s) in Islam and Christianity"; added to the Source library.
- Fix: the two links in the Gospel of Thomas introduction carried a stray backslash and did not work.
## 2.11.0

- Thomas sayings that are unclear or beyond the measure are now included in the Gospel According to Islam chapters (70 sayings in all), each with a colour badge: green agrees, blue beyond the measure, gold unclear. The set-aside page now lists only the 44 sayings that conflict with the Qur'an (red) or repeat another (grey).
## 2.10.0

- Each Thomas saying used in the Gospel According to Islam now carries a footnote with its canonical parallel and the Qur'anic verse it agrees with (40 sayings).
- New page /gospels/thomas/set-aside/ lists the other 74 sayings, each with the reason it was set aside. Starter content is now 49 pages.
## 2.9.2

- The Gospel of Thomas page is now an introduction, like the other four. Its sayings are woven into the chapters of The Gospel According to Islam (1 to 6) where they agree with the Qur'an as the measure, each marked with its saying number and credited to T. O. Lambdin's translation.
## 2.9.1

- Bonus epistle moved to /bonus/epistle/ (with a /bonus/ parent page) and annotated with 21 footnotes giving scripture references and editorial notes.
## 2.9.0

- New bonus chapter /bonus/epistle/: an English rendering, in Authorised-Version style, of the Malay letter Epistel Kepada Jemaat Paulus.
## 2.8.0

- New page /gospels/thomas/: a digest of the 114 sayings of the Gospel of Thomas in our own words, with canonical parallels and credit to T. O. Lambdin's translation. Added to the Gospels index and the Source library.
## 2.7.0

- Starter content: 49 pages created on activation, with a Content tab in Theme Options to create any that are missing.
- "Paul" is now "Paul of Tarsus" in the main navigation and footer.
- Body text is justified.
## 2.6.0

- New tabbed Theme Options page (Colours, Branding, SEO) under Appearance.
## 2.5.2

- Keyword-rich alt text and titles on all manuscript images; default Open Graph image set.
## 2.5.1

- Version bump so Git Updater offers 2.5.0 changes.
## 2.5.0

- "Answers" and "Short answers" renamed "FAQs" in the header, footer, patterns and docs (the page slug stays /answers/).
- Brandmark added to the footer.
- New screenshot.

