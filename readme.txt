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
3. Create categories for the parts: The Four Gospels, Paul, Jesus in the Qur'an, The Gospel According to Islam, Resources.
4. Create the section pages /gospels/, /paul/, /quran/, /gospel-according-to-islam/, /resources/ and /answers/. Give each the "Section index" template and insert its matching "Section index: ..." pattern (or "FAQs page").
5. Create /method/ (insert "Criterion statement"), /corrections/ ("Corrections log"), and the article pages the section entries link to. Edit the entry links in each pattern as articles go live.

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
Manuscript images load from Wikimedia Commons (Special:FilePath URLs, sized by ?width=), each with its credit and a link to its Commons page in the caption. All are public domain except the Kairouan Qur'an photograph (Richard Mortel, CC BY 2.0), which must keep its credit. To host them yourself, download from the linked Commons page into the Media Library and swap the URL. Readers' browsers contact upload.wikimedia.org; mention this in the privacy policy.

== Branding ==
* Brandmark: assets/brandmark.svg (an open codex with rays of light, Qur'an 5:46). It appears as the link before the site title in the header and at the top of the footer's first column (class ih-mark; the footer adds ih-mark-foot).
* Favicon: if no Site Icon is set under Appearance > Customize > Site Identity, the theme outputs assets/brandmark.svg as an SVG favicon. A Site Icon always takes precedence. Some older browsers ignore SVG favicons, so set a PNG Site Icon for full coverage.

== Customising colours ==
Colours are tokens in theme.json (Appearance > Editor > Styles > Colors, or edit settings.color.palette). Change a value there and every pattern follows, because the stylesheet reads them as CSS variables (var(--wp--preset--color--SLUG)).
* base #f6f1e6 Parchment; contrast #2a2420 Ink; accent-1 #1f5c4a Qur'anic green; accent-2 #9b2c2c Rubric red; accent-3 #c39a3f Gold; accent-4 #2d4a7a Lapis; accent-5 #ece3d1 Vellum; accent-6 #d8cbb2 Rule; muted #6b6158.
* Tints: green-deep, green-tint, red-tint, lapis-tint, gold-tint.
* Radii and font roles are variables at the top of style.css (--ih-r-sm, --ih-r-md, --ih-r-lg, --ih-serif, --ih-sans, --ih-display, --ih-greek, --ih-quran).
The theme has no settings screen; all configuration is through the Site Editor and theme.json.

== Releasing ==
Bump Version in style.css and INJIL_HELP_VERSION in functions.php together, add a Changelog entry here, merge to main, and tag the commit with the same number (for example v2.5.1). Git Updater reads the header (GitHub Theme URI, Primary Branch: main) and offers the update.

== Changelog ==
= 2.5.1 =
* Version bump so Git Updater offers 2.5.0 changes.
= 2.5.0 =
* "Answers" and "Short answers" renamed "FAQs" in the header, footer, patterns and docs (the page slug stays /answers/).
* Brandmark added to the footer.
* New screenshot.

== Design ==
After Ahmad Shafaat, The Gospel According to Islam (Vantage Press, 1985): chapter-and-verse layout, verse numbers in the margin, rubric-red chapter heads, notes beside the text. Palette: parchment, ink, Qur'anic green, rubric red, gold, lapis.
