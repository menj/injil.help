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
3a. Optional: create categories for the parts: The Four Gospels, Paul of Tarsus, Jesus in the Qur'an, The Gospel According to Islam, Resources.
4. Create the section pages /gospels/, /paul/, /quran/, /gospel-according-to-islam/, /resources/ and /answers/. Give each the "Section index" template and insert its matching "Section index: ..." pattern (or "FAQs page").
5. Create /method/ (insert "Criterion statement"), /corrections/ ("Corrections log"), and the article pages the section entries link to. Edit the entry links in each pattern as articles go live.

== Starter content ==
The theme ships its own pages and creates them on activation (inc/starter-content-pages.php): the pillar page /the-islamic-dilemma/, /method/, /corrections/, /answers/, the five section pages and their entries (four gospels, synoptic problem, discrepancy tables, six Paul of Tarsus pages, six Qur'an pages, the 27 chapters of The Gospel According to Islam with their front matter, timeline, glossary, source library, objections), plus About, Terms, Privacy, Contact, Credits and Sitemap. Appearance > Theme Options > Content lists every page and creates any that are missing (for example after you delete one). Pages are published; edit them in the block editor. The articles are concise and sourced, and every page is complete when it is created: there is no placeholder text anywhere in the theme. On the first activation the theme also bins WordPress's own sample post, sample page and sample comment (if they were never edited) and replaces the "Just another WordPress site" tagline, so a new install is ready for visitors as it stands. After a theme update, every starter page whose text changed in the new version is rewritten from the theme (once per version, on the first admin screen load), except pages you have edited since the theme wrote them: each page the theme writes carries a hash of its content, an edited page no longer matches, and it is kept and marked "edited, kept on update" on the Content tab. Pages whose text did not change are left alone, and no revision is kept for the rewrite. Theme Options > Content has an "Update all pages from the theme" button that rewrites every page on demand, edited ones included, behind a confirmation.

== Content data ==
The article text, the gospel, the Qur'anic passages and the page list are data in inc/data/, all of it JSON, so that text can be edited, translated or reviewed without touching PHP. The block patterns in patterns/ hold the page layouts and the copy that belongs to them: the front-page sections, the section indexes with their overviews and FAQs, the pillar page, the measure, the composition page, the corrections log and the discrepancy table. Files in inc/data/:
* pages/pillar.json, gospels.json, paul.json, quran.json, gospel.json, bonus.json, resources.json, site.json: the starter pages, one file per part of the site, in site order. Each entry has path, title and excerpt, and either pattern (a block pattern supplies the content) or body (lines of compact markup: "## " heading, "### " sub-heading, "- " list item, "> " quote, "@consensus" / "@minority" / "@islamic" claim label, "[agree]" and the other colour marks at the start of a list item, inline {{fn:...}} footnotes, "[page-list]" for the sitemap). template names a page template.
* narrative.json: The Narrative of Jesus, Son of Mary. chapters > sections > lines, with "### " Qur'anic headings and [^n] note markers; notes keyed by number, one numbering through the whole.
* gospel-islam.json: the 27 section titles and the Introduction.
* quran-jesus.json: the Qur'anic passages on Jesus, 13 surahs, 45 passages, 145 verses, with headings and footnotes.
* thomas.json: the Gospel of Thomas sections, keyed by the section they are woven into.
inc/starter-content-pages.php loads the page files; inc/gospel-islam.php and inc/quran-jesus.php build the gospel sections and the Qur'an page.

== Structure ==
Modelled on apostleofdoom.org: a title-page hero; each part of the study as a block with a gloss (Greek for New Testament parts, Arabic for Qur'anic parts, each with its verse), an intro, a "Read first" link and numbered entries; a band of FAQs with "Full evidence" links; a footer of study, reference and method links.

== What it adds ==
* Patterns (category "injil.help"): front-page hero, parts grid, criterion statement, answer capsule, discrepancy table (resurrection accounts), Qur'an verse index, gospel verse, how-this-gospel-was-composed, objection and reply, FAQ, corrections log.
* Block styles: ornament separator, claim labels (consensus / minority / Islamic position), verse source labels (Qur'an / NT-attested / Editorial), answer capsule, gospel verse, objection and reply, criterion box, discrepancy table, FAQ item.
* Schema: Article on single posts and pages; FAQPage built from Details blocks set to "FAQ item". Turns itself off when Yoast, Rank Math or SEOPress is active. Override with the injil_help_output_schema filter.
* Template "Wide page (tables)" for posts heavy with comparison tables.
* Share row under the content of posts and pages (inc/share.php): X, Facebook, WhatsApp, Telegram, Reddit, LinkedIn, Bluesky, Threads, Pinterest and LINE, plus Copy link and a More… button that opens the device's share sheet where the browser supports the Web Share API. Icons are CSS masks in assets/icons/social/, coloured by the palette; styles in assets/css/share.css, script in assets/js/share.js. Networks, post types and the label are set under Theme Options > Sharing; add or remove a network with the injil_help_share_networks filter.

== Fonts ==
Bundled in assets/fonts and declared in theme.json, so every face is available in the editor's font picker. Roles:
* Sabon Next LT: body text. EB Garamond: headings. Kagnue: site title and display. Evangelion CPA: labels, kickers, navigation.
* SBL Greek, SBL Hebrew, SBL BibLit: biblical languages and transliteration (also applied by lang="grc", lang="he").
* KFGQPC Hafs Uthmanic (v2.2, the King Fahd Complex face): reserved strictly for Qur'anic text. It is applied only through the class ih-quran; every Qur'anic gloss in the header, footer and section heads carries ih-gloss ih-ar ih-quran. Noto Naskh Arabic: hadith (class ih-hadith). Amiri: all other Arabic (class ih-ar, or lang="ar"). Do not set Hafs on anything that is not the Qur'an. Dubidam Arabic, Arslan Wessam: Arabic display.
* Estrangelo Edessa: Syriac (lang="syc"). Noto Sans Cuneiform (lang="akk"). Special Elite: corrections log (class ih-record). Ferments, Angel: available, unused by default.

Licensing: Sabon Next LT is a commercial Linotype face; confirm your licence covers web embedding before going live. Angel is freeware and asks for its README to be kept with the font (assets/fonts/Angel-README.txt). The rest are OFL or free for use; keep their licence files.

== SEO: "the Islamic Dilemma" ==
* Create the pillar page at /the-islamic-dilemma/ (slug exactly "the-islamic-dilemma"), give it the "Wide page (tables)" template or the default, and insert the pattern "The Islamic Dilemma, answered (pillar page)". Write its excerpt as the meta description, keyword first, under 160 characters.
* The theme sets the home title to "The Islamic Dilemma, Answered | <site name>", the pillar title to "The Islamic Dilemma, Answered: Qur'an 5:47 and the Injīl", and outputs meta description and Open Graph tags. Change the strings with the injil_help_seo_defaults filter. With Yoast, Rank Math or SEOPress active, the theme outputs none of this; set the same values in the plugin.
* Structured data (inc/schema.php): one JSON-LD @graph per page, built from the types Google Search documents that this site has content for. On every page: Organization (name, URL, logo, sameAs), Person (the author; name, URL and profile links from Theme Options > SEO, falling back to the page author), WebSite (with the sitelinks SearchAction on the front page), and a page node typed by what the page is: WebPage, CollectionPage (section indexes, with an ItemList of their entries; archives), ProfilePage (About the author, mainEntity the Person), ContactPage, SearchResultsPage. Below the front page, BreadcrumbList from the page hierarchy. On article pages and posts, Article or BlogPosting with headline, images, author, publisher, dates, word count and section; the 27 gospel sections and the Introduction are isPartOf a Book node for Shafaat's work. Every manuscript figure becomes an ImageObject with contentUrl, caption, creditText, acquireLicensePage (its Commons page), license (read from the caption: Public domain or CC BY) and creator where a photographer is named; the default social image carries the same. Every page with FAQ items (Details blocks styled "FAQ item", or "Short answer" groups) gets a FAQPage. Nodes reference each other by @id. Filter injil_help_schema_graph to change the graph; injil_help_output_schema to turn it off.
* FAQs: every main page carries six questions and answers written as search queries, under the entries on the section indexes and at the foot of the pillar page, the measure and the composition page; the front page carries six in its FAQ band and the FAQs page twelve. Each feeds that page's FAQPage markup.
* The keyword appears in the home kicker and lede, the home dilemma section, the first nav item, the first two FAQs, and the footer. Link to /the-islamic-dilemma/ from every new article that touches Qur'an 5:47, taḥrīf or the identity of the Injīl, with varied anchor text.
* Set a default social image with the injil_help_default_og_image filter.

== Images ==
Manuscript images load from Wikimedia Commons (Special:FilePath URLs, sized by ?width=), each with its credit and a link to its Commons page in the caption. All are public domain except the Kairouan Qur'an photograph (Richard Mortel, CC BY 2.0), which must keep its credit. Every image carries a descriptive alt attribute (what it is, its date and holding institution) and a short title; keep that pattern for new images, and write alt text for the subject, not for decoration. WordPress adds lazy loading itself. The default Open Graph image is a bundled copy of the Birmingham Qur'an manuscript (assets/images/og-default.jpg, public domain, 1280 × 833); replace it under Theme Options > SEO or with the injil_help_default_og_image filter. To host the manuscript images yourself, download from the linked Commons page into the Media Library and swap the URL. Readers' browsers contact upload.wikimedia.org; mention this in the privacy policy.

== Branding ==
* Brandmark: assets/brandmark.svg (an open codex with rays of light, Qur'an 5:46). It appears as the link before the site title in the header and at the top of the footer's first column (class ih-mark; the footer adds ih-mark-foot).
* Favicon: if no Site Icon is set under Settings > General > Site Icon (or in the Site Editor), the theme outputs assets/brandmark.svg as an SVG favicon. A Site Icon takes precedence unless Theme Options > Branding is set to force the brandmark or to use a custom favicon URL. Some older browsers ignore SVG favicons, so set a PNG Site Icon for full coverage.

== Theme Options ==
Appearance > Theme Options is a tabbed page (stored in the injil_help_options option):
* Colours: nine palette overrides with colour pickers; empty keeps the default.
* Branding: ornament layer (Rich, Subtle, Off) and the reveal animation; show or hide the footer brandmark; always use the brandmark as favicon even with a Site Icon; custom favicon URL.
* Sharing: show or hide the share row, on posts and/or pages; which networks, in order; the row's label.
* Content: lists every starter page with its status, creates missing ones, and rewrites all of them from the theme on demand.
* SEO: theme SEO output automatic, always on or always off; home title and description; pillar page title; default social image URL; structured data: author name and URL, profile links (sameAs), organization logo URL.
The page's CSS and JS live in assets/css/admin.css and assets/js/admin.js; its own colours follow the Colours tab.

== Ornament ==
Icons, pattern, banners and motion are a separate layer: assets/css/ornament.css (loaded after style.css), assets/js/ornament.js (optional reveal-on-scroll), assets/icons/*.svg (line icons) and assets/images/pattern-star.svg and divider.svg. Everything is drawn with CSS masks, so each decoration takes its colour from the palette tokens and follows Theme Options > Colours.
* Star lattice behind the page, a colour wash and pattern on the front-page hero, a tinted banner with a watermark icon on each section page (book for the gospels, quill for Paul of Tarsus, star for the Qur'an, lamp for The Gospel According to Islam), a flourish under each section title.
* Entry cards carry a section icon, lift on hover and show an arrow; headings carry a small star; saying badges carry an icon (check, compass, question, cross, minus); manuscript images are matted and framed; the footer carries the pattern.
* Theme Options > Branding > Ornament: Rich, Subtle (fainter pattern) or Off, a switch for the reveal animation, and Parallax depth: Strong (default), Gentle or Off. Off removes the layer and loads neither file. Motion respects the visitor's reduced-motion setting.
* Parallax: ornament.js gives each layered element its progress through the viewport (--ih-p, -1 to 1) and the root the scroll position (--ih-sy); ornament.css turns these into transforms at different rates. The star lattice drifts against the scroll; in the hero the pattern, the text and the manuscript image move at three speeds; the watermark icon on each section banner sweeps up and turns as the banner passes; the banner's tint slides; manuscript plates lift; the cards in a grid shear in alternate directions; the footer pattern slides. Everything is a transform, so nothing reflows and scrolling stays smooth. Gentle is 45% of Strong; small screens use shorter travel and leave the card grids still.

== Customising colours ==
Colours are tokens in theme.json (Appearance > Editor > Styles > Colors, or edit settings.color.palette). Change a value there and every pattern follows, because the stylesheet reads them as CSS variables (var(--wp--preset--color--SLUG)).
* base #f6f1e6 Parchment; contrast #2a2420 Ink; accent-1 #1f5c4a Qur'anic green; accent-2 #9b2c2c Rubric red; accent-3 #c39a3f Gold; accent-4 #2d4a7a Lapis; accent-5 #ece3d1 Vellum; accent-6 #d8cbb2 Rule; muted #6b6158.
* Tints: green-deep, green-tint, red-tint, lapis-tint, gold-tint.
* Radii and font roles are variables at the top of style.css (--ih-r-sm, --ih-r-md, --ih-r-lg, --ih-serif, --ih-sans, --ih-display, --ih-greek, --ih-quran).
The same nine colours can be set without code under Appearance > Theme Options > Colours; they override theme.json and apply to the site and the editor.

== Releasing ==
Bump Version in style.css and INJIL_HELP_VERSION in functions.php together, add a Changelog entry here, merge to main, and tag the commit with the same number (for example v2.5.1). Git Updater reads the header (GitHub Theme URI, Primary Branch: main) and offers the update.

== Changelog ==
= 3.3.0 =
* Al-Faruqi's Divine Transcendence and Its Expression (1983) cited on John (the Semitic sense of the titles and sayings; the Gnostic source of the Logos), Paul of Tarsus against Jesus (the mystery religions and the dying god) and A servant and a messenger (transcendence as the ground of the denials); added to the source library; the Credits page and readme state the arabesque principle behind the ornament.
* Stronger parallax: the lattice, the hero's three layers, the section-banner watermarks and tints, the manuscript plates, the card grids and the footer pattern move at different speeds as the page scrolls. Theme Options > Branding > Parallax depth: Strong, Gentle or Off.
* Structured data rebuilt as one JSON-LD graph per page (inc/schema.php): Organization with logo and sameAs, Person, WebSite with sitelinks search, typed page nodes (WebPage, CollectionPage with ItemList, ProfilePage, ContactPage, SearchResultsPage), BreadcrumbList, Article/BlogPosting with images and word count, Book for the gospel sections, ImageObject licence metadata for every manuscript image, FAQPage wherever there are FAQ items. New Theme Options > SEO fields for the author, profile links and logo.
* Six SEO FAQs on every main page: the pillar page, the measure, the five section indexes and the composition page.
* KFGQPC Hafs is now applied only through the class ih-quran and is reserved for Qur'anic text; ih-ar alone is generic Arabic in Amiri. Every Qur'anic gloss carries ih-quran, dir="rtl" and lang="ar".
* Section index pages carry an overview of the part and a pointer to the next; the corrections log explains how corrections work; the resurrection table has notes on reading it.
* All content is now JSON under inc/data/: the starter pages (pages/*.json), the narrative, the section titles and Introduction, the Qur'anic passages and the Gospel of Thomas sections. The .txt data files and the PHP arrays are gone; the pages render identically.
* The article pages are written in full in place of the earlier outlines: the four gospels, the synoptic problem and the discrepancy index; the six Paul of Tarsus pages; the six Qur'an pages (800 to 1,300 words each, with sources). The timeline runs from 6 BCE to 1985 in four periods; the glossary covers the Arabic, Greek, Hebrew and Syriac terms and the vocabulary of New Testament scholarship; the source library indexes every work cited by kind; About, Contact, Terms, Privacy and Bonus chapters are full pages, and the privacy policy describes the share links.
* Share row under posts and pages: X, Facebook, WhatsApp, Telegram, Reddit, LinkedIn, Bluesky, Threads, Pinterest and LINE, with Copy link and a native share button where the browser allows. Icons from the Minimalist Social Icons Pack as palette-coloured masks. New Theme Options > Sharing tab.
* The default social image is now bundled (assets/images/og-default.jpg) instead of hotlinked from Wikimedia Commons.
* Theme updates rewrite only the starter pages whose text changed and that you have not edited (each page the theme writes carries a hash of its content; an edited page is kept and marked on the Content tab, and only the "Update all pages" button overwrites it), keep no revision for the rewrite, and flush rewrite rules only when permalinks had to be set. Page text is slashed before saving, so backslashes survive. A missing or malformed data file leaves the version unrecorded so the run is retried.
* The Resources section is back on the front page.
* No placeholder content: the corrections log opens with the real record instead of a sample row; the measure states the website's position on alteration; the pillar page's objection, reply, response and two open questions are written; the Objection-and-reply and Gospel-chapter patterns carry worked examples; the unused placeholder cover art is removed; first activation bins WordPress's sample post, page and comment and replaces the default tagline.
* "How this gospel was composed" rewritten in full: the base text, the six-chapter arrangement, the Qur'anic passages set in the story, the notes, the Gospel of Thomas and the measure applied.
* Pages read as one story. Front page: the argument, the books, the man who changed the message, the measure, the gospel restored, the apparatus, then the FAQs; each section carries a Part label. Paul of Tarsus opens with the seven letters, the evidence the rest depends on; the FAQs follow the same order as the site; the Gospels index names the role of the entries that are not one of the four.
* Theme Options page colours follow the Colours tab; badge text and icons use the parchment token instead of white.
* languages/ folder added for translation files; readme corrected on page rewriting.
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
Share icons (assets/icons/social/): Minimalist Social Icons Pack, GPL. The LinkedIn icon originates from Font Awesome Free (https://fontawesome.com), CC BY 4.0; the credit is carried on the Credits page. The brand marks remain trademarks of their owners. assets/icons/share.svg and link.svg are the theme's own.
Default social image: Birmingham Qur'an manuscript (Cadbury Research Library, University of Birmingham), public domain, via Wikimedia Commons.
The Gospel According to Islam: text and notes by Ahmad Shafaat (New York: Vantage Press, 1985), used as the base text of this website's gospel. Spelling and scanning errors in the supplied text were corrected; the Gospel of Thomas sections and their notes were added by this website. Gospel of Thomas saying numbers follow the standard edition; the summaries are the website's own, prepared from T. O. Lambdin's translation in The Nag Hammadi Library in English (Brill, 1988).

== Design ==
After Ahmad Shafaat, The Gospel According to Islam (Vantage Press, 1985): chapter-and-verse layout, verse numbers in the margin, rubric-red chapter heads, notes beside the text. Palette: parchment, ink, Qur'anic green, rubric red, gold, lapis.
The ornament layer follows Ismail al-Faruqi, Divine Transcendence and Its Expression (ABIM, Kuala Lumpur, 1983), pp. 28-32: stylisation and geometry as the refusal to let anything in nature stand for the divine, and the endlessly repeating pattern as the expression of God's inexpressibility. Hence the eight-pointed star lattice, the line icons, and no figurative image of anything sacred; the only pictures are manuscripts.
