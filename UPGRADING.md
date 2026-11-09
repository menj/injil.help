# Upgrading the theme

How to move a site from one version to the next, and where the theme is going. The roadmap comes first; the notes for each version are under "Updating an existing site" at the end.

## Roadmap

These are plans, not promises. Each item rests on something on record: this file's earlier list, the code, the server logs of October and November 2026, a limit the README documents, or a feature another of the owner's themes already has. Items leave this list when they ship and go into `CHANGELOG.md`. GitHub had no open issues or pull requests when the list was last checked (2026-11-05), and the code carries no TODO markers. Each item names the files it touches, so it can be picked up cold.

### Next

- Bring the remaining checks into the repository and run them in CI. Why: the ten tests in `tests/` are in the repository, but the page and link check, the block-balance render and the structured-data check still live in a scratch folder (README, Development workflow). Put them beside the tests with one command, and run everything in a GitHub Actions workflow on every push and pull request; add a JSON Schema per data file under `inc/data/schema/`.
- Fail loudly on bad data. Why: `injil_help_pages_file()` and `injil_help_data_file()` return an empty array for a missing or malformed file, which silently leaves pages missing. Keep the safe return, record the failure, show an admin notice naming the file, and add a Site Health test that reports every data file.
- Guard the parent theme. Why: nothing checks that Twenty Twenty-Five is present or recent enough. Show an admin notice and skip what depends on it, and pin the tested parent version in `SSOT.md` and `style.css`, as the compelling-evidence theme does with `CE_PARENT_TESTED_VERSION`.
- Security headers on front-end responses (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, HSTS as an opt-in). Why: the bestofislam theme sends them from `inc/security-headers.php`; this theme sends none, and the README already notes that a `script-src 'self'` policy would work here.

### Later

- Key points for the eight Journal posts, as a `points` list in `inc/data/posts.json` written to `_injil_help_points`. Why: the "In short" box is built (3.25.7), but no post carries key points yet, so it shows nowhere until the owner types them.
- A Doctor tab in Theme Options. Why: one screen would show missing or duplicated starter pages, edited pages, oversized pages, failing data files, the parent theme, missing fonts, the page cache and SEO plugin, and `max_input_vars` and `post_max_size` against the largest page. Waits on the data checks above, which it would display.
- Preview before rewriting. Why: the first admin load after an update rewrites changed pages at once. A dry run listing what will change and what is kept, with a diff link, and an option to keep one revision. Waits on the Doctor tab, where it would sit.
- Export and import edits. Why: a rebuild from scratch loses the pages an owner edited and the Theme Options; the abrahamic theme already exports and imports its options. Export both as JSON and import them on a fresh install.
- WP-CLI commands: `wp injil pages create`, `wp injil pages update [--all]`, `wp injil check`, `wp injil doctor`. Why: population and every check could then run on staging and in deployment scripts. Waits on the checks and the Doctor tab.
- An integrity manifest of `inc/data/` and the fonts. Why: the upload guard catches a missing or unparsable PHP file, never a truncated data file or font. The compelling-evidence theme checks its article batches against SHA-256 checksums in a manifest.
- Page size. Why: the gospel sections that carry the folded commentaries are large (section 2 is about 560 KB of HTML). Load each fold's body on demand from a REST endpoint (`/wp-json/injil/v1/commentary/{work}/{surah}/{from}-{to}`), or move a section's commentaries to a child page, or keep requiring a page cache. Waits on a choice between the three.
- An accessibility and performance pass. Why: the folds, FAQs and share row have not been audited with a screen reader, and the fonts are not subset (README, Performance). Measure Core Web Vitals on four page types and record them in `SSOT.md`.
- Arabic beside the English in the commentaries, behind a toggle. Why: the public-domain Arabic (about 200,000 words) is in hand; ship it as separate JSON loaded on demand. Waits on the page-size choice.
- Content: commentaries on the remaining verses about Jesus and his family (19:2-15, 2:87, 2:253, 3:65-80, 4:171-172, 5:17, 5:72-78, 9:30-31, 33:7, 42:13, 61:14); more commentators (Muqātil ibn Sulaymān, al-Māturīdī, al-Zamakhsharī, Ibn ʿAṭiyya, al-Rāzī, al-Bayḍāwī, al-Nasafī, Ibn ʿĀshūr, al-Ṭūsī, al-Ṭabrisī); a second translator's reading of 4:155-159, 3:54-58 and 5:116-120; a hadith page; the Arabic Infancy Gospel, the Gospel of the Hebrews and the Ebionites and the Diatessaron. Why: each is listed in this file's earlier plan and adds to pages that exist; each waits on its sources being worked through.
- Paul of Tarsus: Acts against the letters, Marcion and the first canon, the Jerusalem church after James. Why: in the earlier plan; waits on writing.
- Images for the remaining article pages, and the Birmingham and Sanaa folios at full resolution with zoom. Why: plates already come from the Media Library (3.20.0); the pages named in the earlier plan (the good news of Aḥmad, the borrowing page, the composition page) still have none.

### Ideas

- A verse lookup box ("4:157") that jumps to the Qur'an page, the gospel section and each commentary. Every commentary heading already names its verse; it needs stable `#4-157` anchors.
- Glossary terms marked on first use, with the definition on hover and focus. The compelling-evidence and bestofislam themes link or annotate glossary terms in the running text; here the glossary is a page.
- A dark scheme. The palette is tokens, and the abrahamic theme already switches light and dark from one palette. Check the plates and the lattice against a dark base.
- A print stylesheet for every page (no ornament, notes at the end, folds open), and an EPUB or PDF of The Gospel According to Islam from `narrative.json`. Today only `bible.css` and `progress.css` carry print rules; the abrahamic and bestofislam themes ship a print stylesheet.
- Reading aids on the long pages: remember the reader's place (local storage only), "continue reading", and the reading time and contents list the Journal posts now carry (3.25.7). The abrahamic theme shows reading time on its pages.
- A red-letter edition, with the words of Jesus marked `[r]` … `[/r]` in the narrative data.
- A parallel view of the Qur'an, the gospel section and the commentaries for one verse, and the synoptic parallels with Thomas for a section.
- A corrections feed, and a "last corrected" date on every page that has an entry.
- Language-keyed data directories (`inc/data/ms/`, `inc/data/ar/`), starting with Malay; an Arabic interface needs an RTL stylesheet.
- Theme Options › Commentaries: show or hide the folds, choose the works and their order, and whether folds open by default.
- A moderated question form on the FAQs page.
- Answer the bare `/wp-json/oembed/1.0/embed` probes earlier. The November 2026 log shows 168 of them answering 404 with a full WordPress load each.

## Mobile-first audit, 3.38.1

Measured on 9 November 2026 in a scratch WordPress with Chromium and axe-core, at 320, 360, 375, 390, 414, 480, 768, 1024 and 1280 pixels, on the front page, a long Journal post with a wide table, a code block, an embed, a gallery and long unbroken URLs, a category archive, the Journal list, search with and without results, the 404 page, the log-in screen, two Bible sections, three wide-table pages, a section page and the contact page.

The theme was already close: no page scrolled sideways at any of the nine widths, before or after. The issues were small.

| Finding | Severity | Risk of the fix | Action |
|---|---|---|---|
| Layout shift of 0.10 to 0.20 on the Bible, wide-table and Journal pages at 320 to 414 px, from the fonts swapping in after first paint | Medium | Low | Fixed: preload of EB Garamond, Evangelion CPA and (Bible sections) Kagnue |
| Section, answer and entry card grids built with `max-width` queries that took columns away | Low | Low | Fixed: one column first, `min-width` queries add columns; column count identical at 17 test widths |
| Footer brand mark 32 px square | Low | Low | Fixed: invisible 44 px tap area |
| Log-in links, "Remember me" label and password eye under 44 px | Low | Low | Fixed in `login.css` |
| A caption with one long word could widen its figure | Low | Low | Fixed: `overflow-wrap:anywhere` |
| Gallery captions scroll inside the image when long (core behaviour, axe `scrollable-region-focusable`) | Medium | Medium | Not changed; see below |
| Two heading-order notices on search results and a section page (content level) | Low | Medium | Not changed; the 3.38.1 accessibility work covers search and archive titles |
| Block navigation button has no `aria-expanded` of its own (core behaviour); Escape closes the menu and focus returns to the button | Low | Medium | Not changed |
| Fonts are TTF, 16 MB on disk; a page loads about 2.6 MB of them (EB Garamond 831 KB, SBL Greek 1.1 MB) | Medium | High | Flagged |
| 17 other `max-width` queries in `style.css` and the module stylesheets | Low | High | Flagged |
| No comment form in the templates, so no form inputs to review besides search and log-in | Info | None | None |

Remaining risks and follow-up:

- Convert the fonts to WOFF2 and subset them, then preload the smaller files. Why: the reading face and the Greek face are 1.9 MB together on every page.
- Move the remaining `max-width` queries (`is-style-gospel-verse`, `is-style-objection-reply`, the header and footer groups, the ornament and share rules) to `min-width`, one block at a time with a screenshot at 781 and 782 pixels. Why: they work, but they remove styles at small sizes in place of adding them at large ones.
- Decide whether gallery captions should clip or grow. Why: core gives them `overflow:auto` inside the image; a long caption on a small image scrolls.
- Check the pages with a real phone and a screen reader. The lab used Chromium with touch emulation.

## Updating an existing site

The general steps, for any version:

1. Take a database backup.
2. Update through Git Updater, or upload the zip under Appearance › Themes › Add New and choose "Replace active with uploaded". Never switch to another theme first: a switch takes the error pages and the `.htaccess` sections away. By FTP: turn on hidden files so the theme's `.htaccess` goes up; upload the folder into `wp-content/themes/` under a temporary name, its `.htaccess` file first, so on Apache the paths it refuses stay closed while the rest arrives (on Nginx, which ignores it, add the theme rule from the README's security notes before the first FTP update); rename the old folder away and, straight after, give the new one the old folder's exact name (`injil-help`, or `injil.help` where the site runs a copy of the repository), with the admin closed in between. Never upload WordPress core to a live folder without its `wp-config.php`. Once the site is installed, keep this installer lock at the top of the site's `.htaccess`, above every `# BEGIN` section: `<IfModule mod_rewrite.c>`, `RewriteEngine On`, `RewriteRule ^(.+/)?wp-admin/(install|setup-config)\.php$ - [F,L]`, `</IfModule>`, one per line. On Nginx the same lock is `location ~ ^/(.+/)?wp-admin/(install|setup-config)\.php$ { return 403; }`. The 3.25.5 note below gives it as a block, with the virtual-host form.
3. Open any admin screen as an administrator (page rewrites and the error pages need `edit_theme_options`, the development-file clean-up needs `update_themes`). The theme rewrites the pages whose text changed and keeps the ones you edited, writes its error pages, checks its `.htaccess` sections (at most hourly), and on a production site removes its development files.
4. Read the note for the version you are moving to.

**To 3.36.1.** Upload the whole theme. Nothing else changes.

**To 3.35.0.** Upload the whole theme. The Glossary refreshes on the first admin load, and any edits made to it are replaced.

**To 3.34.0.** Upload the whole theme. The Glossary, the page on the Messiah in Islam and the table page refresh on the first admin load, and any edits made to them are replaced.

**To 3.33.0.** Upload the whole theme. The pages with new images refresh on the first admin load, and the images copy into the Media Library six at a time over the next admin loads. Edits made to those pages are replaced.

**To 3.32.0.** Upload the whole theme. The page width changes on its own: a site on the former default, full width, moves to the reading column the first time it loads; pick full width again under Theme Options > Branding to keep it. Two new pages are written on the first admin load, and sections 8, 9, 13, 18, 21, 22 and 24 of the Gospel According to Islam gain a block of early-writings sayings. A section page you edited keeps your text; delete it to take the new one.

**To 3.31.0.** Upload the whole theme. The Glossary and Objections pages refresh on the first admin load and gain a contents list, and any edits made to them are replaced. The automatic list is off until you switch it on in Theme Options > Branding.

**To 3.30.0.** Upload the whole theme. The Aḥmad page refreshes on the first admin load, and any edits made to it are replaced.

**To 3.29.0.** Upload the whole theme. The Glossary and the Aḥmad page refresh on the first admin load, and any edits made to those two pages are replaced.

**To 3.28.0.** Upload the whole theme. The Glossary, the page on the Injīl and the page on the Torah in Islam refresh on the first admin load, and any edits made to them are replaced.

**To 3.27.0.** Upload the whole theme. The Glossary, the page on the end of ʿĪsā's earthly mission and the table page refresh on the first admin load, and any edits made to them are replaced.

**To 3.26.0.** Upload the whole theme. The Glossary and the Aḥmad page refresh on the first admin load, and any edits made to those two pages are replaced.

**To 3.25.10.** Upload the whole theme. Nothing else changes. On a site in a subfolder, put a copy of `assets/images/favicon.ico` in the domain root (or, with a Site Icon set, that icon as `favicon.ico`), since browsers ask for `/favicon.ico` there.

**To 3.25.9.** Upload the whole theme. The new files are `inc/favicon.php` and eight in `assets/images/`: `favicon.svg`, `favicon.ico`, `favicon-32.png`, `apple-touch-icon.png`, `icon-192.png`, `icon-512.png`, `icon-maskable-512.png` and `site.webmanifest`. Open one admin screen so the error pages are written again with the icon. If a `favicon.ico` file sits in the site root, the web server sends it and the theme never answers; delete it to use the theme's.

**To 3.25.8.** Documentation only: the FTP steps send the `.htaccess` file first. Upload the whole theme. On Nginx, replace the theme rule in the server block with the one in the README, which also covers the `-new` and `-old` folders.

**To 3.25.7.** Upload the whole theme, `inc/data/` included, then open any admin screen. The old commentary addresses redirect again; nothing else to do. Key points for a Journal post go in the new Key points box on its edit screen. The theme is now GPL version 3 or later; the new `LICENSE` file holds the text.

**To 3.25.6.** Nothing to do. Documentation only.

**To 3.25.5.** Open any admin screen after the update. On Apache the theme adds a "The Injil: deny direct core files" section to the site's `.htaccess`, after the WordPress section. If a plugin or the host needs one of those files by address, add `define( 'INJIL_HELP_ALLOW_CORE_FILES', true );` to `wp-config.php`, and the next admin screen takes the section out. XML-RPC now stays on by itself while Jetpack is active, so a site that switched "Turn off XML-RPC" off for Jetpack can switch it on again; `define( 'INJIL_HELP_ALLOW_XMLRPC', true );` keeps XML-RPC on for any other client. If the Security tab says the theme could not write `.htaccess`, paste the lines it shows, or ask the host. On Nginx, add the rules from the Security section of the README to the server block.

Then lock the installer, which the theme cannot do for you. The site was taken over through an open `wp-admin/setup-config.php` while WordPress core sat on the server without `wp-config.php`: in that state WordPress never loads the theme, and the installer answers anyone. Never upload WordPress core to a live folder without its `wp-config.php`. Upload `wp-config.php` first, or unpack core in a folder the web server does not serve and move it in whole. And add this rule at the top of the site's `.htaccess`, above every `# BEGIN` section, so no plugin or theme rewrites it:

```
# Installer lock: the site is installed. Keep this above every BEGIN section.
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^(.+/)?wp-admin/(install|setup-config)\.php$ - [F,L]
</IfModule>
```

The `(.+/)?` covers WordPress in a subfolder of the `.htaccess` folder too, such as `wp/wp-admin/setup-config.php`. On Nginx the lock is `location ~ ^/(.+/)?wp-admin/(install|setup-config)\.php$ { return 403; }`, and in an Apache virtual host `<LocationMatch "^/(.+/)?wp-admin/(install|setup-config)\.php$"> Require all denied </LocationMatch>`. Updates of WordPress are untouched, since they run through `wp-admin/upgrade.php`. Take the rule out only for the length of a fresh install.

**To 3.25.4.** Open any admin screen after the update as a user who can update themes (on a network, a network administrator). On a production site the theme deletes `.git`, `.claude`, `tests` and the Markdown notes from its folder; if you deploy the theme with `git pull`, add `define( 'INJIL_HELP_KEEP_DEV_FILES', true );` to `wp-config.php` first. A local copy should set `WP_ENVIRONMENT_TYPE` to `local` or `development`. On Nginx, which reads no `.htaccess`, add the deny rule from the Security section of the README. The content sync publishes `/privacy-policy/` from the draft WordPress made on install.

**To 3.25.3.** Nothing to do. The front page takes the new order from the theme. If you saved your own copy of the Front Page template in the Site Editor, that copy keeps its order; reset it under Appearance › Editor › Templates to take the new one.

**To 3.25.2.** Open any admin screen as an administrator after the update. The theme writes its static error pages into `wp-content/injil-help-errors/`. If `.htaccess` has `ErrorDocument` lines that point at `/wp-content/themes/injil.help/errors/`, replace them with the lines from the Error pages section. The old lines still work on a site at the domain root.

**To 3.25.1.** Open any admin screen as an administrator (a user who can edit theme options; on a network, on the main site, since the sites share `wp-content`) after the update and the theme writes the error pages; the button is no longer needed. A copy another theme left in `wp-content` is replaced, and a file without a theme's mark is left alone. If you use the static pages, add the new `ErrorDocument 504` line from the Error pages section to `.htaccess`.

**To 3.23.0.** The 404 page now carries `noindex, follow` as soon as the theme updates. The error pages wait for you: open Theme Options, scroll to Error pages at the foot and press Install error pages. If a row says another file is there, a plugin or host wrote that file; the theme leaves it alone. To use the static pages for server errors, add the four `ErrorDocument` lines from the same section to `.htaccess`.


Git Updater reads the theme header and offers the release whose tag matches `Version:`. After the update, the first load of any admin screen rewrites the starter pages whose text changed, keeps pages you edited (marked "edited, kept on update" on the Content tab), keeps no revisions, and flushes rewrite rules only if permalinks had to be set. "Update all pages from the theme" replaces every page, edited ones included, after a confirmation. Take a database backup before updating; open the Content tab once after.

- **3.20.2.** Nothing to do. A page or Journal post that carries no hash from the theme is kept on every update from now on, so it keeps your text and does not take the new one. To give such a page the theme's text, press "Update all pages from the theme".
- **3.38.0.** Upload the whole theme. The new page /resources/dictionaries/ is created on the first admin load, and the Resources index is rewritten (edits to it are replaced).
- **3.38.1.** Upload the whole theme. Nothing else to do. Clear the page cache so phones fetch the new stylesheets.
- **3.37.0.** Upload the whole theme. To use the Pexels client, set the key under Theme Options > Content or define `INJIL_HELP_PEXELS_KEY` in `wp-config.php`. Nothing else changes.
- **3.36.0.** Upload the whole theme. Nine pages refresh on the first admin load. Pixabay plates appear once the Media Library holds their copies (six per admin screen load); they never load from Pixabay for readers.
- **3.20.0.** Check the plates. Remote plates are now off, so a plate with no Media Library copy is hidden. The pages in `inc/data/images.json` already have their copies on a site that has run 3.18.0; the four plates of the front-page and section patterns are copied on the next few admin screen loads. Open the front page afterwards. If a plate is still missing (a failed fetch is retried after a day), switch on Theme Options › Content › Load plates from Wikimedia Commons and Pexels until it is there. The Privacy page is rewritten to say the images are served from the site, unless you edited it.
- **3.18.0.** Nothing to do. The first admin load after the update creates the Journal page and writes the 8 posts with their own dates; those still to come show as Scheduled under Posts. They go live only if WP-Cron runs on the host, so check that it does (or run a real cron job against `wp-cron.php`). Purge any page cache so the new menu link shows.
- **3.14.1.** Nothing to do. No address on this site changes. The links to the sister sites follow their new addresses, and the old ones redirect there anyway.
- **3.13.0.** Replace the theme files. The Security tab is on by default: XML-RPC, application passwords and REST writes from outside the browser stop at once. No page text is rewritten, but the update bins unedited "-2" and "-3" copies of the old numbered pages and drafts edited ones; check Pages > Drafts afterwards. After the October 2026 attack on the sister site abrahamic-religions.com, the owner checklist:
  1. Change the password of every administrator to a long random one used nowhere else, then Log Out Everywhere Else (Users › Profile), or press "Log out all sessions" on the Security tab.
  2. Create a new administrator whose name is not the site name, log in as it, and delete the old account, attributing its content to the new one.
  3. Press "Revoke all application passwords".
  4. Check Plugins for anything not installed by you (auto-posting, AI writers, remote management) and remove it. Check Users for unknown accounts.
  5. Run the spam scan, select the results, move them to the trash, then delete the empty spam categories.
  6. Purge any page cache, then resubmit the sitemap in Google Search Console.
  7. Nothing to do at the server: on Apache the theme writes a "Block xmlrpc.php" section into .htaccess itself, repairs it hourly from the admin, and removes it when XML-RPC is switched back on or the site moves to a theme outside the seven sister themes. Jetpack needs XML-RPC; if Jetpack is ever used, switch "Turn off XML-RPC" off.

  If a lock breaks a tool you rely on, `define( 'INJIL_HELP_SECURITY_LOCK', false );` in wp-config.php turns the three locks off without changing the options.
  Every old URL now redirects with a 301: the numbered gospel sections (/gospel-according-to-islam/1/ and the copies /1-2/, /1-3/), the commentary groups with verse numbers (/quran/commentaries/an-nisa-155-159/), the numbered commentary parts (/tabari/2/, /tabari/2-3/) and /about-the-author/. The update also bins unedited copies of a page left under an old slug or a -2 slug and turns edited copies into drafts, so no -2 URL stays public. Check Pages › Drafts afterwards for any such copy. Nothing else changes.
- **3.12.0.** Nothing to do. The log-in screen changes on its own, and the new structured data is built as each page is shown, so no page is rewritten. With Rank Math active, the new nodes join its graph as the other theme nodes do.
- **3.11.0.** Title tags change: every one is "page part | site name" under 60 characters, and the theme now sets it even when Rank Math, Yoast or SEOPress writes the head, so a page title set in the plugin is replaced by the theme's. To change a page's title tag, edit its entry in `inc/data/seo-titles.json`. 32 pages change URL, the pillar page among them (/the-islamic-dilemma/ to /islamic-dilemma/). The first admin loads rename the existing pages in place; IDs, edits and featured images stay, and the old URLs redirect with a 301. Resubmit the sitemap in Google Search Console. If you set a focus page or a redirect for the pillar in Rank Math, point it at /islamic-dilemma/. Links to the old paths inside pages you edited by hand still work through the redirect, but are best corrected or rewritten with "Update all pages from the theme". The other network sites carry their own copy of `network-links.json`; update this site's 4 entries there too.
- **3.10.0.** Three new pages are created on the first admin load (Why Islam?, Leave the gospel of Paul of Tarsus, Who you are as a servant of God); the Content tab lists 181. These pages are rewritten after the update unless you edited them: the pillar, What is the Injīl?, /quran/servant/, /paul/false-apostles/, /paul/paul-against-jesus/, the responses page, the source library and the Resources index. Nothing else to do.
- **3.9.0.** Two new pages are created on the first admin load (What is the Injīl? and the Torah in Islam); the Content tab lists 178. The pillar, /quran/, /quran/servant/, the glossary, the secular-form and Pfander pages, and the pages that carry al-Jalālayn on 3:50, are rewritten unless you edited them. The front-page diagram's CSS classes are renamed to `ih-claim*`: if you copied the diagram into a page or template of your own, rename the classes there or insert the pattern again. Resubmit the sitemap so the new pages are crawled.
- **3.8.0.** Seven pages are rewritten after the update unless you edited them: the pillar, /quran/, /quran/servant/, /paul/, /paul/acts-galatians/, /paul/paul-against-jesus/ and /paul/false-apostles/. Four page titles change (the Paul of Tarsus hub, the servant page, Acts against Galatians, the false apostles); the URLs do not. If you set your own pillar title under Theme Options, SEO, it still wins over the new default.
- **3.7.3.** The Islamic Dilemma secular-form page, the Mark page and the al-Saʿdī commentary page for 3:55 are rewritten after the update unless you edited them. The Mark page's featured image keeps the alt text it was given when it was first downloaded: in the Media Library, open "Shorter ending of Mark 16 from Codex Sinaiticus" and set its alternative text to "Codex Sinaiticus, fourth century: the Gospel of Mark ending at 16:8".
- **3.7.2.** The credits page is rewritten after the update unless you edited it. `assets/fonts/KFGQPC-Hafs-Uthmanic.ttf` gives way to `KFGQPC-Hafs-Uthmanic.woff2`; an upload over the old folder leaves the TTF behind, which nothing reads. Clear caches so visitors fetch the new font.
- **3.7.0.** Nothing to do, and no page is rewritten: the contextual links are made as each page is shown. To have fewer, lower the caps under Theme Options › Contextual links; to have none, untick both boxes. A phrase that links somewhere you do not want can be pointed elsewhere with a line in "More phrases", which wins over the shared map.
- **3.6.7.** Two pages change, the Epistle and The false apostles, and are rewritten after the update unless you edited them. To place the mark yourself, add `<span class="ih-lanah" role="img" aria-label="لعنة الله, the curse of God"></span>` after the name in the code editor.
- **3.6.6.** Nothing to do. The page patterns are now JSON files and every page is built as before. Almost every page's text changes with the writing-style pass, so the update rewrites every page you have not edited, a few at a time as you move between admin screens. Pages you edited are kept; "Update all pages" on the Content tab rewrites them too if you want the new text. Child themes or plugins that called `get_registered( 'injil-help/…' )` for one of the 17 page patterns still find it, since the theme registers them from the JSON.
- **3.6.5.** With Rank Math installed, leave Theme Options › SEO on Automatic and turn on Rank Math's breadcrumbs (General Settings › Breadcrumbs; the theme already declares support, so the trail appears in the theme's row). Set the homepage title in Rank Math's Titles & Meta; the theme supplies the description if Rank Math has none. Two columns are used only in The Gospel According to Islam (its 27 sections and its Introduction); every other page is one full-width column. The front page, the FAQs page and the Introduction are rewritten after the update, a few at a time as you move between admin screens; a notice at the top shows the progress and then what was done. "Update all pages from the theme" now shows its progress and its result too.
- **3.6.4.** The development documents are left out of the release archive; nothing on the site changes.
- **3.6.3.** A breadcrumb row appears at the top of every page below the front page, and the structured data graph grows to every schema.org type the content supports; nothing on the site needs doing.
- **3.6.1.** Every page is full width by default, and on screens from 1280px a page of running prose is set in two columns so the lines stay readable. To have the former single reading column back, choose it under Theme Options › Branding › Content width; nothing else changes and no page is rewritten.
- **3.6.0.** Application passwords are switched off by default; any client that logged in with one (a mobile app, a publishing tool) will stop working until you untick "Block application passwords" under Theme Options › Security. The 27 gospel sections, the nine commentary groups and the later parts of long commentaries change URL (words instead of numbers). The first admin load renames the existing pages in place: same IDs, edits and featured images; nothing is duplicated. From 3.8.3 the old URLs redirect to the new ones with a 301. Links inside pages you edited by hand still point at the numeric URLs and must be corrected by hand or with "Update all pages from the theme". One new page (Pfander's Balance of Truth); the Content tab lists 176. Every unedited page is rewritten once for its "Read next" row.
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
