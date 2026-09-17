# Theme Request Form — FILLED EXAMPLE

> Example of a completed [THEME-REQUEST-FORM.en.md](THEME-REQUEST-FORM.en.md). This is what a user hands back — paste it into **Theme Design → Generate with AI** (or attach this file) and it generates the draft.

**Basics**

- Site name: `AniPulse`　Contact / date: `Mio Tanaka / 2026-07-01`
- New theme name: `AniPulse Dark`　Directory name (lowercase Latin, 2–32 chars, starts with a letter): `anipulse`

---

## A. Color (pick a preset OR customize each)

**Path ② custom 8 colors** (chosen — a dark, electric-violet anime look):

| Role | Where it's used | Your value |
| --- | --- | --- |
| Primary brand `accent` | Buttons / links / highlights / current state | `#8b5cf6` |
| Text on brand `accent_ink` | Text on solid buttons | `#ffffff` |
| Page background `bg` | Site-wide background | `#0b0a12` |
| Card background `surface` | Cards / panels | `#14121f` |
| Secondary background `surface_2` | Inputs / muted blocks | `#1d1a2b` |
| Primary text `text` | Headings / body | `#ece9f5` |
| Secondary text `text_muted` | Captions / meta | `#9a93b0` |
| Border `border` | Dividers / strokes | `#2a2740` |

> Dark theme: bg/surface/surface_2 are dark and text is light. Accent violet is used in both light and dark modes.

---

## B. Font & size

- Font: **[x] Inter + Noto SC (default)**　[ ] Noto SC first　[ ] Microsoft YaHei　[ ] Serif　[ ] Kai　[ ] System default
- Base font size (12–22, default 16): `16 px`

---

## C. Brand assets

- Logo: [ ] Use the theme's built-in mark　**[x] I'll provide one** (attached: `anipulse-logo.svg`)
- Footer copyright text: `© 2026 AniPulse. All rights reserved.`
- Default avatar: **[x] System default**　[ ] I'll provide one

---

## D. Top navigation

- Categories for the top menu, in order:
  1.`Anime` 2.`Donghua` 3.`Movies` 4.`Specials` 5.`News` 6.`______`
- Notes: `Anime should be first. Please leave out any adult categories.`

---

## E. Footer

- Categories to show in the footer: `Anime, Movies`
- Custom footer links:

| Text | URL |
| --- | --- |
| `About` | `/about` |
| `Contact` | `/contact` |
| `DMCA` | `/dmca` |

---

## F. Homepage sections

| Show | Section (key) | Content | Order | Count | Style / setting |
| :---: | --- | --- | :---: | :---: | --- |
| [x] | Hero carousel `hero` | Video banner | 1 | 6 | Sort: **[x] Rating** |
| [x] | Hot videos `vod_hot` | Video | 2 | 12 | Style: **[x] Slider** |
| [x] | Latest videos `vod_latest` | Video | 3 | 18 | Style: **[x] Grid** |
| [x] | Weekly ranking `rank_week` | Video | 4 | 10 | Period: **[x] Week** |
| [x] | Featured topics `topic_list` | Topic | 5 | 6 | — |
| [x] | Latest articles `art_list` | Article | 6 | 6 | Style: **[x] Grid** |
| [x] | Popular manga `manga_list` | Manga | 7 | 8 | Style: **[x] Grid** |
| [ ] | Live channels `live_list` | Live | — | — | — |
| [ ] | Category ranking boards `rank_modules` | Video (multi-col) | — | — | — |
| [ ] | Site directory `website_list` | Sites | — | — | — |
| [ ] | Guestbook `gbook_list` | Guestbook | — | — | — |

- Section limited to one category? `Hot videos → Anime`
- Extra custom sections (Category accepts a name **or** a numeric ID):

| Title | Content type | Category (name or ID) | Count | Style |
| --- | --- | --- | --- | --- |
| `Simulcast This Season` | `Video` | `Anime` | `12` | `slider` |
| `Top Rated Movies` | `Video` | `6` | `10` | `grid` |

---

## G. Other notes

`Please keep it dark by default and uncluttered. Nice-to-have (only if possible): 20px rounded cards with a subtle neon glow on hover, and our brand font "Satoshi". If those aren't supported, ignore them — the colors and layout above matter most.`

---

*Notes for reviewers: the two "nice-to-have" asks in G (20px radius / hover glow, and the Satoshi brand font) are outside what Theme Design can configure, so the AI generator will list them as **Not applied** in the coverage report — everything else maps cleanly to the draft.*
