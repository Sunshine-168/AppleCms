# Theme Request Form

> Purpose: fill this in and we'll **generate a custom theme to your requirements**


**Basics**

- Site name: `________________`　Contact / date: `________________`
- New theme name: `________________`　Directory name (lowercase Latin, 2–32 chars, starts with a letter): `________________`


---

## A. Color (pick a preset OR customize each)

> Choose one path. ① is easiest; ② is precise. Color format: `#` + 3/6/8 hex digits (e.g. `#2563eb`).

**Path ① pick a ready-made palette** (tick one):
[ ] Indigo　[ ] Ocean　[ ] Sunset　[ ] Forest　[ ] Rose　[ ] Slate

**Path ② custom 8 colors** (fill only what you want; blanks use defaults):

| Role | Where it's used | Your value |
| --- | --- | --- |
| Primary brand `accent` | Buttons / links / highlights / current state | `__________` |
| Text on brand `accent_ink` | Text on solid buttons (usually `#ffffff`) | `__________` |
| Page background `bg` | Site-wide background (dark value for a dark theme) | `__________` |
| Card background `surface` | Cards / panels | `__________` |
| Secondary background `surface_2` | Inputs / muted blocks | `__________` |
| Primary text `text` | Headings / body | `__________` |
| Secondary text `text_muted` | Captions / meta | `__________` |
| Border `border` | Dividers / strokes | `__________` |

> Want a **dark theme**? Set `bg`/`surface`/`surface_2` to dark values and `text` to a light value. The brand color is used in both light and dark modes.
> ⚠️ Self-check contrast: `text`↔`bg` and `accent_ink`↔`accent` must be clearly readable.

---

## B. Font & size

- Font (tick one):
  [ ] Inter + Noto SC (default)　[ ] Noto SC first　[ ] Microsoft YaHei　[ ] Serif (Georgia+Noto Serif SC)　[ ] Kai　[ ] System default
- Base font size (12–22, default 16): `______ px`

---

## C. Brand assets

- Logo: [ ] Use the theme's built-in mark　[ ] I'll provide one (attach image, PNG/SVG)
- Footer copyright text (blank = auto "© Year, URL, Email, ICP"): `________________________________`
- Default avatar: [ ] System default　[ ] I'll provide one (attach image)

---

## D. Top navigation

- Categories for the top menu, listed in **order** (write category names; we'll map to IDs):
  1.`__________` 2.`__________` 3.`__________` 4.`__________` 5.`__________` 6.`__________`

---

## E. Footer

- Categories to show in the footer: `________________________`
- Custom footer links:

| Text | URL |
| --- | --- |
| `__________` | `________________________` |
| `__________` | `________________________` |

---

## F. Homepage sections

Tick "Show", put a number in "Order" (1 = top), fill "Count" (1–30) and "Style / setting" as needed. Unticked = off.

| Show | Section (key) | Content | Order | Count | Style / setting (allowed values) |
| :---: | --- | --- | :---: | :---: | --- |
| [ ] | Hero carousel `hero` | Video banner | `__` | `__` | Sort: [ ]Hottest [ ]Newest [ ]Rating |
| [ ] | Hot videos `vod_hot` | Video | `__` | `__` | Style: [ ]Default [ ]Slider [ ]Grid [ ]List |
| [ ] | Latest videos `vod_latest` | Video | `__` | `__` | Style: [ ]Default [ ]Slider [ ]Grid [ ]List |
| [ ] | Weekly ranking `rank_week` | Video | `__` | `__` | Period: [ ]Day [ ]Week [ ]Month [ ]All |
| [ ] | Latest articles `art_list` | Article | `__` | `__` | Style: [ ]Default [ ]Grid [ ]List |
| [ ] | Featured topics `topic_list` | Topic | `__` | `__` | — |
| [ ] | Live channels `live_list` | Live | `__` | `__` | — |
| [ ] | Popular manga `manga_list` | Manga | `__` | `__` | Style: [ ]Default [ ]Grid [ ]List |
| [ ] | Category ranking boards `rank_modules` | Video (multi-col) | `__` | `__` | — |
| [ ] | Site directory `website_list` | Sites | `__` | `__` | — |
| [ ] | Guestbook `gbook_list` | Guestbook | `__` | `__` | — |

- Want a section limited to one category? Note "Section → Category": `________________________`
- Extra custom sections (content type: Video/Article/Manga/Ranking):

| Title | Content type | Category (name or ID) | Count | Style |
| --- | --- | --- | --- | --- |
| `__________` | `________` | `________` | `__` | `________` |

> For **Category**, write the category **name** or its numeric **ID** (IDs are shown in the admin **Category** list). Leave blank to include all categories.

---

## G. Other notes

`________________________________________________________________`
`________________________________________________________________`

---