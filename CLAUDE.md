# Working on this repository

Read `FARUQI.md` first. It sets out the Faruqian core, al-tawḥīd as Ismāʿīl Rājī al-Fārūqī expounded it in *Al Tawhid: Its Implications for Thought and Life*, which runs through the themes and content of this site and its eight sister sites. Do not deviate from it.

Then read `SSOT.md`: it holds the fixed facts and the rules for contributors, and it wins over every other file on matters of fact. `README.md` is the technical reference.

Three rules that apply to every edit:

- **Never use the word "horn".** It is banned for the Islamic Dilemma and everywhere else on the site, in text, titles, excerpts, CSS class names, comments and documentation. The two parts of the dilemma are its two "claims" ("both claims", "the first claim", "its two-part form"). Before committing, run `git grep -niP '\bhorn' -- ':!SSOT.md' ':!CLAUDE.md'` and expect no output; these two files name the word only to ban it.
- **Write every piece of English prose to `.claude/skills/anti-ai-writing-style/SKILL.md`.** Load that skill before drafting or revising page text, excerpts, FAQs, patterns, the readme, README or changelog entries. Quotations and reproduced texts keep their authors' wording.
- **Every title tag is `<page title, keyword or description> | <site name>`, under 60 characters.** That leaves 32 characters for the page part. A page whose title is longer needs a short title in `inc/data/seo-titles.json`. `SSOT.md` has the details.
