# Changelog

All notable changes to The Injil theme. The `Changelog` section of `readme.txt` mirrors this file; `UPGRADING.md` says what each release means for an existing site. Versions follow `Version:` in `style.css` and the tag on `main`.

## 3.5.0

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

