# Changelog

All notable changes to The Injil theme. The `Changelog` section of `readme.txt` mirrors this file; `UPGRADING.md` says what each release means for an existing site. Versions follow `Version:` in `style.css` and the tag on `main`.

## 3.7.4

- The Islamic Dilemma pillar page (/the-islamic-dilemma/, `inc/data/patterns/islamic-dilemma-page.json`, included by `inc/data/pages/pillar.json`) carries "The Islamic Dilemma across the network" before its Sources: an `is-style-criterion` box with one line per sister piece and its angle, linking compelling-evidence.com/articles/islamic-dilemma-quran-and-bible/, abrahamic-religions.com/islamic-dilemma/, bestofislam.org/islamic-dilemma/, apostleofdoom.org/islamic-dilemma/, murtadd.org/guides/islamic-dilemma/ and lailatulqadar.guide/islamic-dilemma/ (`rel="noopener"`, same tab, as the contextual links do).
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
- The dilemma section on the front page is drawn as a diagram: the two horns side by side with "or" between them, a labelled rule joining them to the assumption they share, which now spans the width beneath them and says in a line why it fails. The cards used to sit in an open-ended grid that left a fourth empty column on wide screens.
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

