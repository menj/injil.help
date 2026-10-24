# Upgrading

What each release does to an existing site, and what to do before and after updating. Read with `CHANGELOG.md`.

## How updates reach the site

Git Updater reads the theme header (`GitHub Theme URI`, `Primary Branch: main`) and offers the release whose tag matches `Version:` in `style.css`. After the update, the first load of any admin screen runs the starter-content update once for that version:

- every starter page whose text changed in the new version is rewritten from the theme;
- a page you have edited since the theme last wrote it is left alone and marked "edited, kept on update" on Appearance › Theme Options › Content;
- unchanged pages are not touched, no revisions are kept, and rewrite rules are flushed only if permalinks had to be set.

If you want your edited pages replaced as well, use "Update all pages from the theme" on the Content tab, which asks for confirmation.

Before any update: take a database backup. After any update: open the Content tab once (that is what triggers the rewrite), then check the front page and one page from each part.

## 3.4.0

**New pages.** 92 pages under `/quran/commentaries/` and one at `/the-islamic-dilemma/secular-form/` are created on the first admin load. The Content tab will list 166 pages.

**Large pages.** Fourteen gospel sections now carry the classical commentaries as folded blocks. Section 2 is about 83,000 words (about 560 KB of HTML), sections 1 and 27 about 55,000 and 61,000. Everything is folded by default and nothing reflows, but:

- use a page cache (any full-page cache plugin, or the host's), so the sections are served as static HTML;
- if PHP's `max_input_vars` or `post_max_size` is low, saving one of those sections in the block editor may fail; raise them or edit those sections through the data files rather than the editor;
- the block editor itself is slow on the largest sections. That is expected; the pages are meant to be read, not re-edited.

**Edited sections.** If you edited a gospel section before 3.4.0, it is kept and the commentaries are not added to it. To receive them, either use "Update all pages from the theme" (which replaces every edited page) or re-apply your edits after a one-off rewrite.

**Structured data.** Pages built from JSON can now carry FAQ entries (the `? Question` token); each such page gains a FAQPage node. No action needed.

**Fonts.** Kagnue now also sets the section index titles, the home section titles and the large numerals. If you had overridden those headings with custom CSS, check them.

**Rollback.** Switching back to 3.3.0 leaves the 93 new pages in place as ordinary pages; delete them from the Content tab or Pages if you roll back for good.

## 3.3.0

- All content moved to JSON under `inc/data/`. If you had edited the old `.txt` data files, carry the edits into the JSON; the pages render identically.
- The update rewrites nineteen article pages, the index pages, the composition page and the gospel sections; edited pages are kept (new in this release: the hash check that protects them).
- The share row appears under every post and page. Turn it off or trim it under Theme Options › Sharing.
- The default social image is now bundled; if you relied on the hotlinked one, nothing changes for visitors.
- The structured data is a single graph per page. If an SEO plugin is active the theme's output stops; set the author, profile links and logo in the plugin instead of the new SEO tab fields.
- Theme Options › Branding gains "Parallax depth"; sites that want the 3.2 feel choose "Gentle".

## 3.2.1

- First release that rewrites starter pages on update. Edits to starter pages were replaced in this version; from 3.3.0 they are kept.

## 3.2.0

- The gospel changed from numbered verses to the narrative form in six chapters over the same 27 URLs. Links to `/gospel-according-to-islam/N/` still work; links to verse anchors do not.
- The per-chapter Qur'an appendices were removed; the full compilation remains at `/quran/passages/`.

## 3.1.1 and 3.1.0

- The ornament layer is new and on by default (Rich). Turn it down or off under Theme Options › Branding › Ornament.

## 3.0.0

- The gospel's base text changed to Shafaat (1985). Starter content grew to 73 pages. The earlier Thomas chapters were folded into the matching sections.

## 2.x

- 2.7.0 introduced starter content (49 pages) and the Content tab. Sites older than that must activate the theme once more after updating so the pages are created, or press "Create missing pages" on the Content tab.
- 2.6.0 introduced the tabbed Theme Options page; colours set earlier in theme.json still apply unless overridden there.
