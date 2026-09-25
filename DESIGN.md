---
name: EMPP
description: Electrospun Membrane Property Predictor. A scientific-instrument layer over Sneat/Bootstrap, shared by the tool and the public site.
colors:
  primary: "#34498c"
  primary-hover: "#2a3c75"
  primary-active: "#233264"
  primary-soft: "#eceff7"
  primary-deep: "#1a2547"
  ink: "#19202e"
  text: "#3c4557"
  muted: "#6a7284"
  muted-ground: "#5b6376"
  line: "#e1e4ea"
  line-strong: "#cdd2db"
  bg: "#f3f4f6"
  surface: "#ffffff"
  stage: "#0e1320"
  field-ink: "#e6e9f4"
  field-soft: "#aab4d6"
  success: "#1f7a5a"
  warning: "#a86400"
  danger: "#b3261e"
  chart-blue: "#2a78d6"
  chart-orange: "#eb6834"
  chart-green: "#1baf7a"
  chart-amber: "#eda100"
typography:
  display:
    fontFamily: "IBM Plex Sans, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "clamp(2.5rem, 5.6vw, 4.5rem)"
    fontWeight: 600
    lineHeight: 1.04
    letterSpacing: "-0.035em"
  headline:
    fontFamily: "IBM Plex Sans, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "clamp(1.75rem, 3vw, 2.375rem)"
    fontWeight: 600
    lineHeight: 1.15
    letterSpacing: "-0.025em"
  title:
    fontFamily: "IBM Plex Sans, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 600
    lineHeight: 1.3
    letterSpacing: "0"
  body:
    fontFamily: "IBM Plex Sans, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: 1.53
  body-site:
    fontFamily: "IBM Plex Sans, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.6
  caption:
    fontFamily: "IBM Plex Sans, system-ui, -apple-system, Segoe UI, sans-serif"
    fontSize: "0.8125rem"
    fontWeight: 400
    lineHeight: 1.45
  label:
    fontFamily: "IBM Plex Mono, ui-monospace, SFMono-Regular, Consolas, monospace"
    fontSize: "0.6875rem"
    fontWeight: 500
    letterSpacing: "0.08em"
  readout:
    fontFamily: "IBM Plex Mono, ui-monospace, SFMono-Regular, Consolas, monospace"
    fontSize: "2.25rem"
    fontWeight: 500
    lineHeight: 1.1
    letterSpacing: "-0.02em"
    fontFeature: "tnum"
  numeric:
    fontFamily: "IBM Plex Mono, ui-monospace, SFMono-Regular, Consolas, monospace"
    fontFeature: "tnum"
rounded:
  sm: "4px"
  md: "6px"
  full: "50%"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  panel: "28px"
  section: "clamp(4.5rem, 9vw, 7rem)"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.sm}"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
  button-primary-active:
    backgroundColor: "{colors.primary-active}"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text}"
    rounded: "{rounded.sm}"
  button-secondary-hover:
    backgroundColor: "{colors.bg}"
    textColor: "{colors.ink}"
  button-field-light:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.primary-deep}"
    rounded: "{rounded.sm}"
    padding: "10px 20px"
    height: "44px"
  button-field-light-hover:
    backgroundColor: "{colors.primary-soft}"
  button-field-ghost:
    textColor: "{colors.surface}"
    rounded: "{rounded.sm}"
    padding: "10px 20px"
    height: "44px"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
  card:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.md}"
  menu-item-active:
    backgroundColor: "{colors.primary-soft}"
    textColor: "{colors.primary}"
    rounded: "{rounded.sm}"
    padding: "0.55rem 0.75rem"
  step-marker:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.muted}"
    rounded: "{rounded.full}"
    size: "28px"
  step-marker-done:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
  micrograph-stage:
    backgroundColor: "{colors.stage}"
    rounded: "{rounded.sm}"
  readout-value:
    textColor: "{colors.ink}"
    typography: "{typography.readout}"
---

# Design System: EMPP

## Overview

**Creative North Star: "The Instrument Record"**

EMPP looks like the output of a measuring instrument, not a marketing product. A SEM micrograph sits on the microscope's own black; the values it yields sit on a white sheet divided by hairlines, set in a monospaced face with tabular figures and a quieter unit after each number. One accent, the blue sampled from the EMPP logo, marks what is active, done, or actionable. The brand's only atmospheric moment is the deep logo-blue field with the nanofibre texture, which frames entry points (the sign-in panel, the public site's header and close) and never the working content.

The tool (dashboard, study pages, forms) and the public site are one world. The app layer re-skins the Sneat/Bootstrap template: pills flattened to 4px, hover lifts and coloured button shadows removed, table heads and menu section headers set as mono labels. The public site extends the same tokens and adds a surface-specific set: the deep field header, a lifted study-record sheet, and a single fill-in animation that plays the study once. Density is moderate and tabular; content is grouped by rules and numbered steps rather than by boxes and icons.

**Key Characteristics:**
- Logo blue as the single accent; cool blue-grey neutrals; status colours only for status.
- IBM Plex Sans for words, IBM Plex Mono with tabular figures for every measured or predicted value.
- Hairline structure (1px) over shadow; surfaces rest flat.
- Micrographs always on stage black with a mono data strip.
- Numbered circular step markers carry the image-to-performance path everywhere.
- Motion runs once, on reveal, and respects reduced motion.

## Colors

A cool, near-monochrome instrument palette: one saturated logo blue over blue-tinted greys, with a near-black stage reserved for images.

### Primary
- **Logo Blue** (primary): buttons, links, active menu item text, focus rings (at 45% alpha), progress bars, chart curves, done-step fills. Hover deepens to **Pressed Blue** (primary-hover), press to **Held Blue** (primary-active).
- **Blue Wash** (primary-soft): selected/active backgrounds (active menu item, hovered table row, label buttons and badges, porosity scale track, the study-run highlight).
- **Deep Field Blue** (primary-deep): the brand ground under the fibre texture (auth panel, site header, site close and footer); also the ring of marker dots on plots.

### Tertiary (data series)
- **Series Blue / Orange / Green / Amber** (chart-blue, chart-orange, chart-green, chart-amber): the categorical set for Chart.js series in the filtration simulation. Used only inside charts, never in UI chrome.

### Neutral
- **Ink** (ink): headings, typed input text, readout values.
- **Slate Text** (text): body copy, table cells, secondary button text.
- **Muted Slate** (muted): units, captions, placeholders, mono labels on white.
- **Muted Slate, Ground** (muted-ground): small secondary text that sits directly on the grey page ground; introduced on the public site because Muted Slate falls short of 4.5:1 there.
- **Hairline** (line) and **Strong Hairline** (line-strong): dividers and card borders; input, secondary-button and table-rule strokes respectively.
- **Page Ground** (bg): app and site background; also the recessed ground for dropzones, notes columns and record footers.
- **Sheet White** (surface): cards, sheets, inputs, the side menu.
- **Stage Black** (stage): behind SEM micrographs and their data strip only.
- **Field Ink / Field Soft** (field-ink, field-soft): body text and lead text on the deep field.

### Status
- **Success** (success), **Warning** (warning), **Danger** (danger): pipeline done status, KPI good/moderate/poor grading, invalid fields. Pair each with a pale tint of itself as a ground when grading a value.

### Named Rules
**The One Blue Rule.** Logo Blue is the only accent. Status colours mean status, series colours live inside charts; nothing else gets a hue.

**The Stage Black Rule.** A micrograph is never shown on white or the page ground. It sits on Stage Black, clipped to the small radius, with its scale or caption in a mono strip along the bottom.

**The Legible Ground Rule.** Small secondary text needs 4.5:1 on the ground it actually sits on. On white, Muted Slate passes; on the grey page ground, use Muted Slate, Ground.

## Typography

**Display Font:** IBM Plex Sans (with system-ui, Segoe UI, sans-serif)
**Body Font:** IBM Plex Sans
**Label/Mono Font:** IBM Plex Mono (with ui-monospace, Consolas, monospace)

**Character:** A technical sans for language and its mono sibling for measurement; the pair reads as a lab instrument's panel. Headings are semibold with slightly negative tracking; nothing is light or decorative.

### Hierarchy
- **Display**: the public site's hero headline only, max 20ch, balanced wrap, on the deep field.
- **Headline**: site section heads and the auth panel headline.
- **Title**: block and fieldset headings inside cards and the study record (the record's own title steps up to 1.25rem).
- **Body**: the app inherits Sneat's size; the public site reads at the slightly larger body-site. Lead paragraphs stay within 44 to 52ch.
- **Caption**: figure captions, legend hints, footers, table-step headers.
- **Label**: uppercase mono labels for table column heads, menu section headers, pipeline status and the label above a KPI or readout value.
- **Readout**: large measured or predicted values; the unit follows at half size in Muted Slate with 0.3em space. KPI grids scale it down to 1.5 to 1.625rem; the site's porosity readout scales it up to clamp(2.25rem, 4vw, 3rem).
- **Numeric**: any number that must align in columns (table cells, numeric inputs, ticks, plot axes).

### Named Rules
**The Measured Value Rule.** Every measured, predicted or computed number is Plex Mono with tabular figures, its unit set smaller and muted after it. A number in the sans face is a label, not a measurement.

## Layout

The app follows Sneat's layout: a white side menu separated by a hairline, a detached translucent top bar (white at 92% with a 6px blur), and content in `container-xxl` on the page ground. Forms group fields in titled fieldsets separated by a hairline and 1.5 to 1.75rem of space; account pages cap at 720px. Auth screens split into a brand panel (44%, min 340px) and a form column (max 400px, 560px wide variant); below 992px the panel drops and the logo moves above the form.

The public site centres content at 1200px with a fluid gutter (clamp(1rem, 4vw, 2.5rem)). The deep field holds nav and headline; the study record overlaps the fold with a negative top margin and splits into three columns (5 : 5 : 3, micrograph / data / notes) divided by vertical hairlines. It collapses to two columns at 1080px (notes run full width below), one column at 720px. Sections below breathe on the section rhythm, and tabular content (the steps table, credits) stays tabular, reflowing to stacked rows with inline labels on mobile.

Spacing follows a 4px base: tight inline gaps of 4 to 8px, field and row gaps of 16px, card and record-panel padding of 24 to 28px, 20px on small screens.

## Elevation & Depth

Flat at rest, structured by hairlines. Cards and the detached navbar carry only a 1px ambient shadow that is nearly invisible; depth reads from borders and the step from page ground to white sheet. Floating layers (dropdowns) get one soft drop. The public site's study record is the single lifted surface: it rises out of the deep field with a long, soft blue-tinted shadow.

### Shadow Vocabulary
- **Hairline ambient** (`box-shadow: 0 1px 2px rgba(25, 32, 46, 0.06)`): cards, detached navbar.
- **Floating menu** (`box-shadow: 0 8px 24px rgba(25, 32, 46, 0.1)`): dropdown menus.
- **Rising sheet** (`box-shadow: 0 1px 2px rgba(25, 32, 46, 0.06), 0 24px 48px -24px rgba(26, 37, 71, 0.35)`): the site's study record over the deep field only.
- **Focus halo** (`box-shadow: 0 0 0 3px rgba(52, 73, 140, 0.12)`): focused inputs and input groups.

### Named Rules
**The Resting Flat Rule.** Buttons and cards never lift, translate or glow on hover; state is a colour change. The theme layer explicitly strips Sneat's hover transforms and coloured button shadows.

## Shapes

Small, precise corners: 6px for containers (cards, alerts, dropdowns, the record sheet, dropzones), 4px for controls and inner elements (buttons, inputs, pagination, menu items, micrograph stage). Circles are reserved for numbered step markers, plot markers and avatars; thin bar tracks (porosity scale, standards ruler) use half their height as radius. Borders are 1px hairlines; 2px is used for step-marker rings, the dashed dropzone and the heavy top rule of a table. Dividers are rules, not gaps with fills.

## Components

### Buttons
Quiet and square-shouldered; weight 500 in the app, 600 on the site.
- **Shape:** small radius (4px); pill variants are forced back to 4px.
- **Primary:** Logo Blue fill, white text; hover Pressed Blue, press Held Blue; disabled at 55% opacity.
- **Secondary (outline):** white with a Strong Hairline border and Slate Text; hover shifts to the page ground, Ink text, Muted Slate border.
- **Label:** Blue Wash ground with Logo Blue text for low-emphasis actions.
- **On the deep field (site):** a white button with Deep Field Blue text (hover Blue Wash) as the lead action, and a ghost button with a 45%-white border as the second. Site buttons are at least 44px tall.
- **Focus:** a 2px outline in Logo Blue (45% alpha in the app, solid on the site, white on the deep field), offset 2 to 3px.

### Cards / Containers
- **Corner Style:** 6px.
- **Background:** Sheet White on the page ground.
- **Shadow Strategy:** Hairline ambient only (see Elevation).
- **Border:** 1px Hairline; headers and footers separated by a hairline, titles in Ink at 600.
- **Readout grids:** KPI and parameter grids are cells separated by 1px hairlines inside one bordered container, each cell a label over a readout.

### Inputs / Fields
- **Style:** white, Strong Hairline stroke, 4px radius, Ink text; placeholders in Muted Slate at full opacity. Labels are 0.75rem, weight 500, sentence case. Numeric inputs use the numeric face.
- **Focus:** border turns Logo Blue with the 3px Focus halo; input groups carry the halo on the whole group.
- **Error:** Danger border and a Danger-tinted halo that wins over focus.

### Navigation
- **App side menu:** white, hairline right edge; items 500 weight in Slate Text with 4px radius; hover on the page ground; active item in Logo Blue on Blue Wash at 600, with a 3px Logo Blue rail at the menu's left edge. Section headers use the mono label style.
- **Top bar:** translucent white, detached, hairline border, page title in Ink at 0.9375rem/600.
- **Account tabs:** underline tabs, 2px Logo Blue underline on the active tab.
- **Site nav:** on the deep field, Field Ink links at 0.9375rem/500 that turn white and underline on hover; the link list hides below 860px, the sign-in link below 520px.

### Step Markers
The four-step path (image, features, porosity, filtration) is drawn with numbered circles everywhere: the new-study step indicator, the study pipeline, the auth panel list and the site record. Pending is a Strong Hairline ring on white with Muted Slate numerals; current or next is a Logo Blue ring and numeral; done is a Logo Blue fill with white numeral (or check). Numerals are mono or tabular. In the pipeline, a 2px connector turns Logo Blue once the step is done.

### Tables
Column heads in the mono label style; cells in Slate Text; numeric columns in the numeric face, right-aligned. Whole-row links highlight with Blue Wash on hover and take an inset focus outline.

### Micrograph Stage
The SEM image in a 4:3 frame on Stage Black, clipped to 4 to 6px, with a mono strip along the bottom (caption on a black gradient in the app; a solid stage strip with a white scale bar on the site).

### Dropzone
A dashed 2px Strong Hairline box on the page ground, 6px radius, at least 280px tall; hover or drag-over turns the border Logo Blue and the ground Blue Wash.

### Study Record (public site)
A white sheet with hairline-divided head, three-column body, filtration row and page-ground footer. Blocks are titled with step markers; values are definition lists with mono values right-aligned against sans terms. Margin notes sit in a page-ground column, numbered with filled Logo Blue markers. Signature motion: on first reveal the record fills in once as a study run, a scan sweep over the micrograph, each block briefly washed in Blue Wash in sequence, the porosity bar filling and the grade-efficiency curve drawing, with the site ease-out curve. Content is visible at first paint for reduced-motion, no-JS and print; nothing already seen is erased.

## Do's and Don'ts

### Do:
- **Do** set every measured or predicted value in Plex Mono with tabular figures and a smaller muted unit.
- **Do** keep Logo Blue as the only accent; use Blue Wash for selected and active grounds.
- **Do** divide content with 1px hairlines and keep surfaces flat at rest.
- **Do** show micrographs on Stage Black with a mono strip.
- **Do** use numbered circular markers for the image-to-performance steps.
- **Do** run motion once on reveal, and only under prefers-reduced-motion: no-preference.
- **Do** check small secondary text against the ground it sits on: Muted Slate on white, Muted Slate, Ground on the page ground.

### Don't:
- **Don't** set a measured value in the sans face or with proportional figures.
- **Don't** use pill-shaped buttons; controls take the 4px radius.
- **Don't** lift, translate or glow buttons and cards on hover.
- **Don't** introduce a second accent hue; status and chart-series colours stay in their roles.
- **Don't** use the deep fibre field behind working content; it frames entry points only.
- **Don't** loop an animation on a value; a result is revealed once and then holds still.
