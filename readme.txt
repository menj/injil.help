=== The Rightly-Guided Injīl ===
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 7.4
Template: twentytwentyfive
License: GPLv2 or later

Child theme of Twenty Twenty-Five for injil.help.

== Install ==
1. Install and keep Twenty Twenty-Five (the parent). Do not delete it.
2. Upload this folder to wp-content/themes/injil-help/ and activate "The Rightly-Guided Injīl". Set Settings > General > Site Title to "The Rightly-Guided Injīl" (the header and footer read it from there).
3. (Automatic) On activation the theme creates all 73 pages listed under Theme Options > Content, sets pretty permalinks, and fills them with content. Steps 4 and 5 below are then done for you; follow them only to rebuild a page by hand.
3a. Optional: create categories for the parts: The Five Gospels, Paul of Tarsus, Jesus in the Qur'an, The Gospel According to Islam, Resources.
4. Create the section pages /gospels/, /paul/, /quran/, /gospel-according-to-islam/, /resources/ and /answers/. Give each the "Section index" template and insert its matching "Section index: ..." pattern (or "FAQs page").
5. Create /method/ (insert "Criterion statement"), /corrections/ ("Corrections log"), and the article pages the section entries link to. Edit the entry links in each pattern as articles go live.

== Starter content ==
The theme ships its own pages and creates them on activation (inc/starter-content-pages.php): the pillar page /the-islamic-dilemma/, /method/, /corrections/, /answers/, the five section pages and their entries (four gospels, synoptic problem, discrepancy tables, six Paul of Tarsus pages, six Qur'an pages, the 27 chapters of The Gospel According to Islam with their front matter, timeline, glossary, source library, objections), plus About, Terms, Privacy, Contact, Credits and Sitemap. Existing pages are never overwritten. Appearance > Theme Options > Content lists every page and creates any that are missing (for example after you delete one). Pages are published; edit them in the block editor. The articles are concise drafts with sources; expand them as you write.

== Structure ==
Modelled on apostleofdoom.org: a title-page hero; each part of the study as a block with a gloss (Greek for New Testament parts, Arabic for Qur'anic parts, each with its verse), an intro, a "Read first" link and numbered entries; a band of FAQs with "Full evidence" links; a footer of study, reference and method links.

== What it adds ==
* Patterns (category "injil.help"): front-page hero, parts grid, criterion statement, answer capsule, discrepancy table (resurrection accounts), Qur'an verse index, gospel verse, how-this-gospel-was-composed, objection and reply, FAQ, corrections log.
* Block styles: ornament separator, claim labels (consensus / minority / Islamic position), verse source labels (Qur'an / NT-attested / Editorial), answer capsule, gospel verse, objection and reply, criterion box, discrepancy table, FAQ item.
* Schema: Article on single posts and pages; FAQPage built from Details blocks set to "FAQ item". Turns itself off when Yoast, Rank Math or SEOPress is active. Override with the injil_help_output_schema filter.
* Template "Wide page (tables)" for posts heavy with comparison tables.

== Fonts ==
Bundled in assets/fonts and declared in theme.json, so every face is available in the editor's font picker. Roles:
* Sabon Next LT: body text. EB Garamond: headings. Kagnue: site title and display. Evangelion CPA: labels, kickers, navigation.
* SBL Greek, SBL Hebrew, SBL BibLit: biblical languages and transliteration (also applied by lang="grc", lang="he").
* KFGQPC Hafs Uthmanic: Qur'anic text (class ih-quran). Noto Naskh Arabic: hadith (class ih-hadith). Amiri: other Arabic (lang="ar"). Dubidam Arabic, Arslan Wessam: Arabic display.
* Estrangelo Edessa: Syriac (lang="syc"). Noto Sans Cuneiform (lang="akk"). Special Elite: corrections log (class ih-record). Ferments, Angel: available, unused by default.

Licensing: Sabon Next LT is a commercial Linotype face; confirm your licence covers web embedding before going live. Angel is freeware and asks for its README to be kept with the font (assets/fonts/Angel-README.txt). The rest are OFL or free for use; keep their licence files.

== SEO: "the Islamic Dilemma" ==
* Create the pillar page at /the-islamic-dilemma/ (slug exactly "the-islamic-dilemma"), give it the "Wide page (tables)" template or the default, and insert the pattern "The Islamic Dilemma, answered (pillar page)". Write its excerpt as the meta description, keyword first, under 160 characters.
* The theme sets the home title to "The Islamic Dilemma, Answered | <site name>", the pillar title to "The Islamic Dilemma, Answered: Qur'an 5:47 and the Injīl", and outputs meta description, Open Graph, WebSite, Article, BreadcrumbList and FAQPage schema. Change the strings with the injil_help_seo_defaults filter. With Yoast, Rank Math or SEOPress active, the theme outputs none of this; set the same values in the plugin.
* The keyword appears in the home kicker and lede, the home dilemma section, the first nav item, the first two FAQs, and the footer. Link to /the-islamic-dilemma/ from every new article that touches Qur'an 5:47, taḥrīf or the identity of the Injīl, with varied anchor text.
* Set a default social image with the injil_help_default_og_image filter.

== Images ==
Manuscript images load from Wikimedia Commons (Special:FilePath URLs, sized by ?width=), each with its credit and a link to its Commons page in the caption. All are public domain except the Kairouan Qur'an photograph (Richard Mortel, CC BY 2.0), which must keep its credit. Every image carries a descriptive alt attribute (what it is, its date and holding institution) and a short title; keep that pattern for new images, and write alt text for the subject, not for decoration. WordPress adds lazy loading itself. The default Open Graph image is the Birmingham Qur'an manuscript; override it with the injil_help_default_og_image filter. To host them yourself, download from the linked Commons page into the Media Library and swap the URL. Readers' browsers contact upload.wikimedia.org; mention this in the privacy policy.

== Branding ==
* Brandmark: assets/brandmark.svg (an open codex with rays of light, Qur'an 5:46). It appears as the link before the site title in the header and at the top of the footer's first column (class ih-mark; the footer adds ih-mark-foot).
* Favicon: if no Site Icon is set under Settings > General > Site Icon (or in the Site Editor), the theme outputs assets/brandmark.svg as an SVG favicon. A Site Icon takes precedence unless Theme Options > Branding is set to force the brandmark or to use a custom favicon URL. Some older browsers ignore SVG favicons, so set a PNG Site Icon for full coverage.

== Theme Options ==
Appearance > Theme Options is a tabbed page (stored in the injil_help_options option):
* Colours: nine palette overrides with colour pickers; empty keeps the default.
* Branding: show or hide the footer brandmark; always use the brandmark as favicon even with a Site Icon; custom favicon URL.
* Content: lists every starter page with its status and creates missing ones.
* SEO: theme SEO output automatic, always on or always off; home title and description; pillar page title; default social image URL.
The page's CSS and JS live in assets/css/admin.css and assets/js/admin.js.

== Ornament ==
Icons, pattern, banners and motion are a separate layer: assets/css/ornament.css (loaded after style.css), assets/js/ornament.js (optional reveal-on-scroll), assets/icons/*.svg (line icons) and assets/images/pattern-star.svg and divider.svg. Everything is drawn with CSS masks, so each decoration takes its colour from the palette tokens and follows Theme Options > Colours.
* Star lattice behind the page, a colour wash and pattern on the front-page hero, a tinted banner with a watermark icon on each section page (book for the gospels, quill for Paul of Tarsus, star for the Qur'an, lamp for The Gospel According to Islam), a flourish under each section title.
* Entry cards carry a section icon, lift on hover and show an arrow; headings carry a small star; saying badges carry an icon (check, compass, question, cross, minus); manuscript images are matted and framed; the footer carries the pattern.
* Theme Options > Branding > Ornament: Rich, Subtle (fainter pattern) or Off, and a switch for the reveal animation. Off removes the layer and loads neither file. Motion respects the visitor's reduced-motion setting.

== Customising colours ==
Colours are tokens in theme.json (Appearance > Editor > Styles > Colors, or edit settings.color.palette). Change a value there and every pattern follows, because the stylesheet reads them as CSS variables (var(--wp--preset--color--SLUG)).
* base #f6f1e6 Parchment; contrast #2a2420 Ink; accent-1 #1f5c4a Qur'anic green; accent-2 #9b2c2c Rubric red; accent-3 #c39a3f Gold; accent-4 #2d4a7a Lapis; accent-5 #ece3d1 Vellum; accent-6 #d8cbb2 Rule; muted #6b6158.
* Tints: green-deep, green-tint, red-tint, lapis-tint, gold-tint.
* Radii and font roles are variables at the top of style.css (--ih-r-sm, --ih-r-md, --ih-r-lg, --ih-serif, --ih-sans, --ih-display, --ih-greek, --ih-quran).
The same nine colours can be set without code under Appearance > Theme Options > Colours; they override theme.json and apply to the site and the editor.

== Releasing ==
Bump Version in style.css and INJIL_HELP_VERSION in functions.php together, add a Changelog entry here, merge to main, and tag the commit with the same number (for example v2.5.1). Git Updater reads the header (GitHub Theme URI, Primary Branch: main) and offers the update.

== Changelog ==
= 3.4.0 =
* The Islamic Dilemma page is rewritten from the study Reconsidering the "Islamic Dilemma" (M. E. N. Juferi, 2026): where the argument came from (Answering Islam, Sam Shamoun; David Wood; Pfander), six answers, how the verses work, objection and reply, eight FAQs and a source list. Credited on Credits and the Source library.

= 3.3.2 =
* Fact-check of the Thomas correlation page and the Islamic Dilemma page: Goodacre's book title corrected (The Case for Thomas's Familiarity with the Synoptics); the note's limits stated on the page; Pfander's dates and the popularisers named only where checked.

= 3.3.1 =
* New page /gospels/thomas/correlation/: a statistical test of whether the order of the parallel sayings in Thomas follows Matthew, Mark, Luke or Q (no significant correlation), with both data tables. Linked from the Thomas introduction; credited on Credits and the Source library.

= 3.3.0 =
* Every placeholder is gone. The pillar page, the measure page and the corrections log carry real text; two editor patterns that held only drafting prompts and an unused cover image were removed.
* Gospel of Thomas: full text on its own page in Mark M. Mattison's public-domain translation, and a second page in the Patterson and Meyer translation (used with permission). Dating, manuscripts and the independence debate are taken from Peter Kirby's Early Christian Writings and gospels.net; credited on the Thomas, Credits and Source library pages.
* New pages: Did the Qur'an borrow its stories? (/resources/borrowing/) and Manuscripts of the extracanonical gospels (/resources/manuscripts/).
* The Gospel According to Islam: sections 1, 2, 4 and 14 now carry marked items from the Infancy Gospel of James and the Infancy Gospel of Thomas, set against the Qur'an.
* Discrepancy tables: genealogies, nativity, last supper and crucifixion added, each with a Thomas column; the index page lists and explains them.
* The synoptic problem page now says what Q contains.
* Fixed "Quirinus" and "Nephthalin" in the notes.

= 3.2.2 =
* Discrepancy tables: the Gospel of Thomas now has a column on the resurrection table (no account in any row) and a note, and the index page says so.
* Credits and the Thomas page now also cite the Patterson and Meyer translation (The Complete Gospels) as a second text used for checking; no wording from it is reproduced.
* The gospels section is now The Five Gospels: Mark, Matthew, Luke, John and Thomas. Titles, intros, footer, hero, 404 and links updated; the front page now lists the Gospel of Thomas. Statements about the four canonical gospels (Irenaeus, Qur'an 5:47, the Diatessaron) are unchanged.

= 3.2.1 =
* Starter pages now update themselves: after a theme update, the first admin screen load rewrites every starter page from the new text (once per version). Edits made to those pages are replaced. Theme Options > Content also has an "Update all pages from the theme" button.
* Main navigation links no longer underline on hover.
= 3.2.0 =
* The Gospel According to Islam now follows The Narrative of Jesus, Son of Mary: the text in paragraphs rather than numbered verses, grouped into six chapters over the same 27 section pages (/gospel-according-to-islam/1/ to /27/), opening with Al-Fatiha and closing with the Qur'an's own statements on God's oneness. Alham Ahmed's Qur'anic passages stand where they fall in the story, under their own headings, and all 235 notes run in one numbering. Where a note says "note 19" it is now a link to that note on its page.
* Source data lives in inc/data/narrative/ (chapter files and two note files); inc/gospel-islam.php builds the pages. The earlier verse files and the per-chapter Qur'an appendices are removed; the full compilation remains on /quran/passages/.
* Obvious scanning errors in the pasted text were corrected (stray marks, broken references such as Micah 5:2 and Mark 11:1-11, "Bethsaida!"); "Paul" in the notes is now "Paul of Tarsus".
= 3.1.1 =
* Fixes from review: Site Icon instructions for a block theme; the gold label colour now follows the palette; every manuscript image's alt text names its date and holding institution; the Theme Options page loads its styles and remembers the selected tab after saving; the page builder has a lock against creating a page twice; footnote markers work in headings and quotes; short intro and description lines are no longer justified.
= 3.1.0 =
* New ornament layer: star-lattice background, tinted section banners with watermark icons, hero wash, icons on cards, headings and badges, framed manuscript images, patterned footer and a gentle reveal on scroll. Switchable under Theme Options > Branding > Ornament (Rich, Subtle, Off).
= 3.0.0 =
* The compiled Qur'anic passages on Jesus (145 verses with headings and footnotes, inc/data/quran-jesus.txt, inc/quran-jesus.php) sit beside the chapters of The Gospel According to Islam that follow them, and are gathered on /quran/passages/.
* The Gospel According to Islam now uses Dr. Ahmad Shafaat's text (Vantage Press, 1985) as its base: 27 chapters in verse form with the author's notes as numbered footnotes, plus the Introduction. The pages carry only the text and notes; all credits are on the separate Credits page (/credits/), linked from the footer. Source and parsing: inc/data/gospel-islam.txt and inc/gospel-islam.php.
* The earlier Gospel of Thomas sections are carried into the matching chapters (2, 8, 9, 22, 24, 26). Starter content is now 73 pages.
= 2.13.0 =
* New page /resources/responses/: four published articles (Samuel Green, John MacArthur, Entrusted to the Dirt, Stand to Reason) with their claims stated and answered; four new FAQs; cross-links from the Objections page, Resources index and Source library. Starter content is now 49 pages.
= 2.12.0 =
* New objection and reply on the Objections page: the Oasis and McGrath video "The Gospel(s) in Islam and Christianity"; added to the Source library.
* Fix: the two links in the Gospel of Thomas introduction carried a stray backslash and did not work.
= 2.11.0 =
* Thomas sayings that are unclear or beyond the measure are now included in the Gospel According to Islam chapters (70 sayings in all), each with a colour badge: green agrees, blue beyond the measure, gold unclear. The set-aside page now lists only the 44 sayings that conflict with the Qur'an (red) or repeat another (grey).
= 2.10.0 =
* Each Thomas saying used in the Gospel According to Islam now carries a footnote with its canonical parallel and the Qur'anic verse it agrees with (40 sayings).
* New page /gospels/thomas/set-aside/ lists the other 74 sayings, each with the reason it was set aside. Starter content is now 49 pages.
= 2.9.2 =
* The Gospel of Thomas page is now an introduction, like the other four. Its sayings are woven into the chapters of The Gospel According to Islam (1 to 6) where they agree with the Qur'an as the measure, each marked with its saying number and credited to T. O. Lambdin's translation.
= 2.9.1 =
* Bonus epistle moved to /bonus/epistle/ (with a /bonus/ parent page) and annotated with 21 footnotes giving scripture references and editorial notes.
= 2.9.0 =
* New bonus chapter /bonus/epistle/: an English rendering, in Authorised-Version style, of the Malay letter Epistel Kepada Jemaat Paulus.
= 2.8.0 =
* New page /gospels/thomas/: a digest of the 114 sayings of the Gospel of Thomas in our own words, with canonical parallels and credit to T. O. Lambdin's translation. Added to the Gospels index and the Source library.
= 2.7.0 =
* Starter content: 49 pages created on activation, with a Content tab in Theme Options to create any that are missing.
* "Paul" is now "Paul of Tarsus" in the main navigation and footer.
* Body text is justified.
= 2.6.0 =
* New tabbed Theme Options page (Colours, Branding, SEO) under Appearance.
= 2.5.2 =
* Keyword-rich alt text and titles on all manuscript images; default Open Graph image set.
= 2.5.1 =
* Version bump so Git Updater offers 2.5.0 changes.
= 2.5.0 =
* "Answers" and "Short answers" renamed "FAQs" in the header, footer, patterns and docs (the page slug stays /answers/).
* Brandmark added to the footer.
* New screenshot.

== Credits ==
The Gospel According to Islam: text and notes by Ahmad Shafaat (New York: Vantage Press, 1985), used as the base text of this website's gospel. Spelling and scanning errors in the supplied text were corrected; the Gospel of Thomas sections and their notes were added by this website. Gospel of Thomas saying numbers follow the standard edition; the summaries are the website's own, prepared from T. O. Lambdin's translation in The Nag Hammadi Library in English (Brill, 1988).

== Design ==
After Ahmad Shafaat, The Gospel According to Islam (Vantage Press, 1985): chapter-and-verse layout, verse numbers in the margin, rubric-red chapter heads, notes beside the text. Palette: parchment, ink, Qur'anic green, rubric red, gold, lapis.
