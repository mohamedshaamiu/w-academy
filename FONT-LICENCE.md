# Thaana Webfont — Licence Position

**Status: font supplied and bundled. One item outstanding — the licence text
has not been captured in writing (see "What is still needed").**

Tracked as SPEC.md §14 ("Licensed Thaana webfont — must be procured before
go-live. See `FONT-LICENCE.md`.") and BACKLOG.md **P1-B**.

---

## What was bundled, and why it was removed

The Phase 1 build shipped **`resources/fonts/MVBoli.ttf`** (79,320 bytes),
declared in `resources/css/app.css` via `@font-face` and emitted by Vite as
`public/build/assets/MVBoli-*.ttf`.

**MV Boli is a proprietary Microsoft font.** It ships with Windows — a
byte-identical copy sits in `C:\Windows\Fonts\mvboli.ttf` on the development
machine, which is where the bundled copy came from. Windows font licences
permit local *use*; they do not permit *redistribution*, and serving a font
file from a web server to every visitor is redistribution, not use.

SPEC.md §3.4 is explicit:

> Do not bundle a proprietary font without a licence permitting web
> redistribution.

The file was therefore deleted from the repository and from the build in
WP-3. It is not in the current tree, and
`LocalisationTest::test_no_proprietary_font_binary_in_repository` fails the
build if it — or any other operating-system font — reappears.

## Two separate defects, both fixed

The font was also **set as the global `sans` family** in `tailwind.config.js`,
so every English page rendered Latin text in a Thaana face. SPEC.md §3.4:

> applied via a `.font-thaana` class and set as the body font ONLY when locale
> is `dv`. It must never be set as the global `sans` family.

Both are corrected:

| | Before | Now |
|---|---|---|
| Global `sans` | `['MV Boli', 'Faruma', 'system-ui', 'sans-serif']` | Latin stack only |
| Thaana face | `.font-thaana` hand-written class | `font-thaana` Tailwind utility bound to `var(--font-thaana)` |
| Body class | `font-thaana` on **every** page | `font-thaana` when locale is `dv`, `font-sans` otherwise |
| Bundled binary | `MVBoli.ttf` | none |

Enforced by `LocalisationTest::test_thaana_font_class_applies_only_in_dv_locale`.

## What renders today

**MV Faseyha**, supplied by the academy and bundled at
`resources/fonts/MVFaseyha.otf` (25,180 bytes). Declared in
`resources/css/app.css`:

```css
@font-face {
    font-family: 'MV Faseyha';
    src: url('../fonts/MVFaseyha.otf') format('opentype');
    font-display: swap;
}

:root {
    --font-thaana: 'MV Faseyha', 'Faruma', sans-serif;
}
```

Vite emits it to `public/build/assets/MVFaseyha-*.otf`, so the VPS serves it —
nothing is hotlinked. The internal family name in the font's `name` table is
exactly `MV Faseyha`, matching the CSS. Faruma remains as a fallback against
the reader's own installed copy, covering the moment before the webfont loads;
it is not bundled and not redistributed.

## What is still needed

The academy advised MV Faseyha is free to download, and supplied the file.
"Free to download" is not by itself a licence to redistribute from a web
server, so for completeness the following should be recorded here:

1. **Source URL** — where the file was obtained.
2. **Licence name and text**, or a URL to it, confirming web redistribution is
   permitted.
3. **Date obtained** and by whom.

This is a paperwork gap, not a technical one. The font renders correctly today.

Optional improvement: converting the `.otf` to `.woff2` would cut roughly
40% off the transfer size (BACKLOG.md P3-05). At 25 KB the gain is small and
it needs tooling that is not in this project, so it was not done.

## Fonts currently bundled

| File | Family | Licence | Redistribution permitted | Source |
|---|---|---|---|---|
| `resources/fonts/MVFaseyha.otf` | MV Faseyha | _to be recorded_ | stated as free to download by the academy; not yet evidenced in writing | supplied by the academy |

## Not bundled

The academy's font folder also contained `MV_Waheed.otf`, `faruma.ttf`,
`MVTypewriter_reg.ttf` and `MVTypewriter_bol.ttf`. None is used by the
application and none has a recorded licence, so none was committed — the
folder is gitignored. Add any of them here only with its licence recorded,
so the MV Boli mistake is not repeated.

---

*Last updated: WP-3. Owner: W-Academy / Devcity.*
