# The W-ACADEMY acronym

Supplied by W-Academy over WhatsApp on **21 August 2026** (screenshots
timestamped 00:15–00:16, forwarded 22 Aug). Three messages:

1. a short letter → word list (English),
2. the same list expanded with a description per letter (English),
3. the same again in Dhivehi.

This is a **brand/values framework** — distinct from the four life pillars in
`W CDMY.pdf` (home, school, religion, sport), which are the behaviour-assessment
framework and are unaffected by this document.

It is the first content the customer has supplied **in English**, so unlike
everything in `TRANSCRIPTION-DV.md` the `_en` side here is their own words and
does not need translating.

## Status

Both languages supplied by the academy, both carried verbatim. Neither side is
a translation of the other — they wrote each independently — so do **not**
"align" them. The Dhivehi carries a bracketed gloss the English has no
counterpart for, the W entry has no gloss at all, and the Achievement body
ends without a full stop. All of that is theirs.

**Where it landed:** `public.values.items` in `lang/dv/public.php` and
`lang/en/public.php`, rendered by
`resources/views/public/partials/acronym-values.blade.php` on `/about`. It
replaced four values that had been authored for the demo, so
`public.values.items` is no longer DEMO COPY — only `public.values.heading`
still is (the academy sent the eight entries without one).

Content in a lang file, not a seeder: nothing to re-seed on deploy.

**Proving tests:** `PublicPagesTest::test_about_renders_every_acronym_value_in_the_active_locale`
and `::test_the_english_value_letters_spell_the_academy_name`.

Only editorial change made to either language: zero-width spaces and stray
double spaces from the WhatsApp paste were stripped. No word was altered.

## Open question for the customer — the ninth entry

The acronym W-ACADEMY is **eight** letters. The short list (message 1) has
eight entries and matches:

    W - Winning mindset
    A - Agility
    C - Cooperation
    A - Achievement
    D - Discipline
    E - Energy
    M - Motivation
    Y - Youth

The long English message (message 2) has **nine** — an extra `E - Enable`
inserted between Motivation and Youth. The **Dhivehi message has eight**, with
no Enable.

Three signals say Enable is a stray paste, not a ninth value:

- it breaks the acronym (W-A-C-A-D-E-M-**E**-Y spells nothing);
- it is formatted unlike its neighbours — `E -Enable` with a hyphen rather than
  an em dash, and its body starts mid-sentence with a leading space
  ("` maintain, or improve physical ability…`");
- its text is a general definition of *sport*, addressed to spectators, not a
  statement about how the academy develops a player.

**Confirm with the academy before publishing.** Assumed dropped unless they say
otherwise; that is the only reading consistent with their own Dhivehi.

## English — verbatim

### W — Winning Mindset

Developing the mental resilience to approach every match with confidence, learn
from setbacks, and maintain a focus on continuous improvement both on and off
the pitch.

### A — Agility

Building sharp physical footwork, quick reaction times, and tactical
adaptability to read the game fast and execute decisions under pressure.

### C — Cooperation

Fostering strong teamwork, trust, and clear communication, teaching players that
individual talent shines brightest within a unified squad.

### A — Achievement

Setting clear individual and team goals, celebrating progress, and inspiring
players to reach new milestones in their football development.

### D — Discipline

Instilling dedication, tactical obedience, respect for officials and opponents,
and a reliable work ethic in training and daily routines.

### E — Energy

Bringing high intensity, passion, and enthusiasm to every session, creating a
dynamic environment that fuels high performance.

### M — Motivation

Igniting the internal drive to practice relentlessly, master complex skills, and
overcome challenges on the path to growth.

### E - Enable  ← see "the ninth entry" above; probably not part of the acronym

 maintain, or improve physical ability and skills while providing enjoyment to
participants and entertainment for spectators.

### Y — Youth

Empowering the next generation of football talent, nurturing young potential
with age-appropriate training, character building, and guidance for long-term
athletic success.

## Dhivehi — verbatim

Supplied as text on 22 August 2026 (the screenshots were not transcribed by
eye — see the note in the git history for why that would have been a guess).

### ޑަބްލިއު — މޮޅުވުމުގެ ވިސްނުން

ކޮންމެ ހާލަތެއްގަވެސް އިތުބާރާއެކު ކުރިމަތިލާނެ ނަފްސާނީ ކެތްތެރިކަން ތަރައްގީކޮށް، ނާކާމިޔާބީތަކުން ދަސްކޮށް، ދަނޑުގެ ތެރޭގައާއި ދަނޑުން ބޭރުގައި ވެސް މެދުނުކެނޑި ކުރިއެރުން ހޯދުމަށް ފޯކަސް ކުރުން.

### އޭ — އެޖިލިޓީ (ހަލުވިކަން)

ހަލުވި ފޫޓްވޯކާއި، އަވަސް ރިއެކްޝަން އަދި ޓެކްޓިކަލް ގޮތުން ހާލަތަށް އޭރަކު އަންނަ ބަދަލުތަކަށް ހޭނުމުގެ ގާބިލުކަން އިތުރުކޮށް، ކުޅުން އަވަހަށް ދެނެގަނެ، ޕްރެޝަރުގެ ވަގުތުތަކުގައި ރަނގަޅު ނިންމުންތައް ނިންމަން ދަސްކޮށްދިނުން.

### ސީ — ކޯޕަރޭޝަން (އެއްބާރުލުން)

ވަރުގަދަ ޓީމްވޯކާއި، އިތުބާރާއި، ސާފު މުވާސަލާތީ ގުޅުމެއް އުފައްދައި، ވަކިވަކި ކުޅުންތެރިންގެ ހުނަރު އެންމެ ފުރިހަމައަށް ފެންނާނީ އެއްބައިވަންތަ ޓީމެއްގެ ތެރެއިންކަން ކުޅުންތެރިންނަށް ދަސްކޮށްދިނުން.

### އޭ — އެޗީވްމަންޓް (ކާމިޔާބީ)

ވަކިވަކި ކުޅުންތެރިންނާއި މުޅި ޓީމުގެ ލަނޑުދަނޑިތައް ސާފުކޮށް ކަނޑައެޅުމާއި، ލިބޭ ކުރިއެރުންތައް ފާހަގަކޮށް ކާމިޔާބީތަކާއި ހިސާބަށް ވާސިލްވުމަށް ކުޅުންތެރިންނަށް ހިތްވަރުދީ ބާރުއެޅުން

### ޑީ — ޑިސިޕްލިން (ސުލޫކާއި އަޚުލާޤު)

ފަރިތަކުރުންތަކުގައާއި ދުވަހުން ދުވަހަށް ކުރާ ކަންކަމުގައި ސާބިތުކަމާއެކު ދެމިތިބުމާއި، ޓެކްޓިކަލް އިރުޝާދުތަކަށް ކިޔަމަންވުމާއި، އޮފިޝަލުންނާއި އިދިކޮޅު ކުޅުންތެރިންނަށް އިޙްތިރާމްކުރުމުގެ އިތުރުން، އިތުބާރުހުރި މަސައްކަތުގެ އަޚްލާޤެއް ކުޅުންތެރިންގެ ކިބައިގައި އަށަގެންނެވުން.

### އީ — އެނާޖީ (ޖޯޝާއި ހަކަތަ)

ކޮންމެ ތަމްރީނު ސެޝަނެއްގައިވެސް މަތީ އިންޓެންސިޓީއާއި، ލޯތްބާއި، ޖޯޝާއެކު ބައިވެރިވެ، ކުޅުންތެރިންގެ އެންމެ ފުރިހަމަ ކުޅުން ނެރުމަށް މަގުފަހިކޮށްދޭ ދިރުންހުރި މާހައުލެއް އުފެއްދުން.

### އެމް — މޮޓިވޭޝަން (ޝައުގުވެރިކަމާއި ހިތްވަރު)

ވަރުބަލިކަމެއްނެތި ފަރިތަކުރުމަށާއި، އުނދަގޫ ހުނަރުތައް ފުރިހަމައަށް ދަސްކުރުމުގެ އިތުރުން، ކުރިއެރުމުގެ މަގުގައި ކުރިމަތިވާ ގޮންޖެހުންތަކުން އަރައިގަތުމަށް ކުޅުންތެރިޔާގެ އެތެރެއިން އުފެދޭ އަޒުމާއި ޝައުގުވެރިކަން އާލާކޮށްދިނުން.

### ވައި — ޔޫތު (ޒުވާން ޖީލު)

އަންނަން އޮތް ޖީލުގެ ފުޓްބޯޅަ ހުނަރުވެރިން ބާރުވެރިކުރުވައި، އުމުރާ ގުޅޭ ތަމްރީނުތަކާއި، ރަނގަޅު އަޚްލާޤު ބިނާކުރުމުގެ އިތުރުން، ދިގުމުއްދަތުގެ ކާމިޔާބީއަށް މަގުދައްކައިދީގެން ޒުވާން ކުޅުންތެރިންގެ ކިބައިގައިވާ ހުނަރު ފުރިހަމައަށް ތަރައްޤީކޮށް ތަރުބިއްޔަތުކުރުން.
