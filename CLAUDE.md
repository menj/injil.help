# Working on this repository

Read `SSOT.md` first: it holds the fixed facts and the rules for contributors, and it wins over every other file. `README.md` is the technical reference.

Two rules that apply to every edit:

- **Never use the word "horn".** It is banned for the Islamic Dilemma and everywhere else on the site, in text, titles, excerpts, CSS class names, comments and documentation. The two parts of the dilemma are its two "claims" ("both claims", "the first claim", "its two-part form"). Before committing, run `git grep -niP '\bhorn' -- ':!SSOT.md' ':!CLAUDE.md'` and expect no output; these two files name the word only to ban it.
- **Write every piece of English prose to `.claude/skills/anti-ai-writing-style/SKILL.md`.** Load that skill before drafting or revising page text, excerpts, FAQs, patterns, the readme, README or changelog entries. Quotations and reproduced texts keep their authors' wording.
