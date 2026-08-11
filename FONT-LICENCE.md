# Thaana Webfont — Licence Position

**Status: OUTSTANDING. A licensed Thaana webfont must be supplied before go-live.**

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

No `@font-face` is declared. `resources/css/app.css` defines:

```css
:root {
    --font-thaana: 'MV Faseyha', 'Faruma', sans-serif;
}
```

Thaana therefore resolves against faces already installed on the reader's
machine — MV Faseyha first, then Faruma (widely installed in the Maldives),
then a generic sans-serif. Readers without either installed will see Thaana in
a fallback face. **This is acceptable for development and UAT. It is not
acceptable for go-live**, because it makes rendering dependent on the visitor's
machine rather than on what the VPS serves.

## What is needed to close this

The academy has chosen **MV Faseyha**, and has advised it is free to download.
"Free to download" is not by itself a licence to redistribute, so before the
file is committed the following must be recorded here:

1. **The file** — placed at `resources/fonts/MVFaseyha.woff2`.
   `.woff2` is strongly preferred over `.ttf`: roughly a third of the size over
   the wire (BACKLOG.md P3-05).
2. **Source URL** — where the file was obtained.
3. **Licence name and text** — or a URL to it, confirming redistribution from a
   web server is permitted.
4. **Date obtained** and the person who obtained it.

Then uncomment the `@font-face` block already prepared in
`resources/css/app.css`, run `npm run build`, and update the table below.

## Fonts currently bundled

| File | Family | Licence | Redistribution permitted | Source |
|---|---|---|---|---|
| _none_ | — | — | — | — |

---

*Last updated: WP-3. Owner: W-Academy / Devcity.*
