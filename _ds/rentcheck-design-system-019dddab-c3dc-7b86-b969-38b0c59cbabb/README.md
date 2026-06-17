# RentCheck Design System

> Verified rental review platform for the Serbian housing market — reputaciona infrastruktura za rentiranje.

RentCheck lets tenants rate properties and landlords while giving landlords a portable, fraud-resistant tenant reputation score (the "Rental Passport"). It targets Belgrade's rental market with JMBG-linked identity verification, Bayesian trust scoring, and a Mapbox-powered property map.

---

## Sources

| Resource | Location |
|---|---|
| Local codebase (Next.js 14, TypeScript) | `Rental reviews/` (mounted via File System Access) |
| GitHub repo | `https://github.com/Maksim-Manojlovic/Rental-review` |
| Logo SVG | `assets/logo.svg` (house emoji placeholder — see ICONOGRAPHY) |
| OG image | `assets/og-image.png` |
| Design tokens source | `Rental reviews/src/design-system/tokens.ts` |
| Tailwind config | `Rental reviews/tailwind.config.ts` |
| Global CSS | `Rental reviews/src/app/globals.css` |

---

## Products / Surfaces

| Surface | Path | Description |
|---|---|---|
| **Marketing site** | `/(public)/page.tsx` | Landing page with hero, stats, how-it-works, trust tier showcase, value props for 3 roles |
| **Marketplace** | `/(public)/marketplace` | Grid of property cards with filters (municipality, trust score, price range) |
| **Map view** | `/(public)/map` | Mapbox GL dark map with property markers, hotspot overlays, municipality toggles |
| **Property detail** | `/(public)/property/[id]` | Full property info, trust score ring, score bars, review list |
| **Rental Passport** | `/(public)/passport/[token]` | Public read-only tenant reputation profile with share link |
| **Tenant dashboard** | `/(dashboard)/tenant` | Passport management, saved properties, review history |
| **Landlord dashboard** | `/(dashboard)/landlord` | Property management, invite code generator, tenant lookup |
| **Agency dashboard** | `/(dashboard)/agency` | Multi-agent management, bulk property upload, verified badge |
| **Admin panel** | `/(admin)` | Moderation queue, verification control, hotspot analytics |
| **Auth flows** | `/(auth)` | Login (with "Remember Me"), register, onboarding, reset password |
| **Review wizard** | `/review/new` | 3-step wizard with draft persistence in localStorage |

---

## CONTENT FUNDAMENTALS

### Language
- **Serbian (sr-RS)** throughout the UI — all copy is in Serbian except for technical labels (e.g. `Trust Score`, `Rental Passport`, `Elite Partner`, `Investment Radar`)
- English is reserved for: brand name ("RentCheck"), feature names treated as proper nouns, and API/code identifiers

### Tone
- **Institutional trust meets approachability.** The voice is direct, credible, and slightly formal — like a responsible government service that also cares about UX
- No exclamation points in body copy. No hype.
- Short, declarative sentences: *"Proverite reputaciju stanodavca pre potpisivanja ugovora."*
- CTA copy is action-verb-first: *"Registruj se besplatno"*, *"Pretraži stanove"*, *"Počni iznova"*

### Casing
- Headings: **Sentence case** only — *"Tri koraka do pouzdane informacije"* not *"Tri Koraka Do Pouzdane Informacije"*
- Section eyebrows / labels: **ALL CAPS**, wide tracking — *"KAKO FUNKCIONIŠE"*, *"TRUST SCORE"*
- Buttons: Sentence case — *"Registruj se"*, *"Prijavi se"*
- Status labels (badges): Title case — *"Pod proverom"*, *"Elite Partner"*

### I vs You
- Addresses the user as **second person informal** ("ti"): *"Gradi Rental Passport koji pokazuje..."*, *"Proverite reputaciju..."*
- The platform refers to itself as *"RentCheck"* — not "we" or "naša platforma"

### Emoji
- **None** in the UI. The placeholder logo uses a house emoji but this is not representative of the final logo.
- Trust tier names use no emoji — only the score ring, color, and text label

### Numbers & Stats
- Serbian number locale: `toLocaleString("sr-RS")` — periods as thousand separators
- Percentages and scores are written as plain numbers: `94`, `0–100`, not `94%` for trust scores
- Prices: `600 EUR / mesec` — always in EUR with Serbian abbreviation

### Specific Phrases
- Invite code displayed in monospace, wide tracking: `RC-4F7K2M`
- "Bayesian algoritam" (not "AI" or "veštačka inteligencija")
- Trust tiers: Pod proverom · Neutralan · Pouzdan · Elite Partner
- Verification levels: Neverifikovan · Silver verifikovan · Gold verifikovan · ID Verifikovan

---

## VISUAL FOUNDATIONS

### Color Vibe
Dark-first, deep navy — evokes premium fintech / PropTech trust platforms. The color palette is cool-toned dark blues with a single warm-green (emerald) accent. No warm backgrounds, no pastel modes, no gradients on UI surfaces.

### Backgrounds
- **Page bg**: `#0A1628` — deepest obsidian navy
- **Cards**: `#111E33` — raised surface
- **Elevated panels/inputs**: `#1A2D47`
- **Modals/tooltips**: `#243656`
- **No full-bleed photography.** No background images or patterns. One ambient radial glow in the hero (`rgba(29,158,117, 0.07)`).

### Color Usage Rules
- Emerald (`#1D9E75` / `#9FE1CB`) = trust, success, primary action
- Red = danger, low trust, disputed reviews
- Amber = warning, pending, mid trust
- Green = good trust tier
- Blue = info, ID verification badge
- All semantic colors are used at low opacity for backgrounds (`/5`, `/8`, `/10`) and full opacity only for text and borders

### Typography
- **DM Sans** (400/500 only) — used for everything: display, headings, body, labels
- **JetBrains Mono** — invite codes, score numbers in specific contexts, `<code>` elements
- Font weights: strictly 400 (normal) and 500 (medium). 600/700 deliberately avoided — too heavy for this dark UI
- Base font size: 14px; heading scale uses negative letter-spacing (-0.02em to -0.05em)
- Eyebrow labels: ALL CAPS, 11px, `letter-spacing: 0.08em`, emerald color

### Spacing
- 8px base grid. All spacing values are multiples of 4px or 8px.
- Cards have `padding: 24px` (p-6)
- Sections use `padding: 96px 24px` (py-24 px-6)
- Max content width: 1280px (`max-w-dashboard`)

### Cards
- `border-radius: 12px` (rounded-xl)
- Border: `0.5px solid rgba(46, 66, 104, 0.8)` — very subtle
- Background: `#111E33`
- Shadow: `0 1px 3px rgba(0,0,0,0.4)` — minimal
- Hover: border tints to `emerald-600/40` — no shadow change, no scale
- No left-border accent pattern. No colored top borders.

### Buttons
| Variant | Bg | Text | Hover |
|---|---|---|---|
| primary | `#0F6E56` | `#EDF2F7` | `#1D9E75` |
| secondary | `#1A2D47` + border | `#EDF2F7` | `#243656` |
| ghost | transparent | `#8DA4BE` | text → primary, bg faint |
| outline | transparent + emerald border | `#9FE1CB` | bg `emerald/10` |
| danger | `#A32D2D` | `#EDF2F7` | `#E24B4A` |

- Height: sm=28px, md=36px, lg=44px
- Border radius: 8px (rounded-md)
- Transition: 200ms spring easing

### Borders
- Default: `0.5px` — intentionally thin, feels premium
- Emphasis: `1px` solid emerald for selected/featured states
- Focus ring: `3px emerald/35` outline

### Animations
- Easing: `cubic-bezier(0.16, 1, 0.3, 1)` — spring out, slightly bouncy settle
- Fast: 120ms, Base: 200ms, Slow: 350ms
- Key animations: `fade-in` (opacity + 4px translateY), `slide-up` (16px), `score-ring` (stroke-dashoffset, 0.8s)
- Staggered children: 60ms increment delays
- Respects `prefers-reduced-motion`

### Hover / Press States
- Text links: color transition secondary → primary
- Cards: border color softens to emerald tint
- Buttons: background lightens slightly (not darkens)
- No scale transforms on hover or press
- No box-shadow changes on hover

### Imagery
- No decorative photography in the current codebase
- Property listings show user-uploaded images (object-cover in card)
- Map: Mapbox dark-v11 style — matches the dark UI palette
- No illustrations, no hand-drawn assets

### Iconography (see ICONOGRAPHY section)
- Lucide React icons throughout — stroke-based, 2px stroke weight
- No filled icons
- Sizes: 11–28px depending on context

### Transparency / Blur
- Navbar: `backdrop-blur-md` + `bg-surface-base/80` — frosted glass sticky header
- Map popups: `bg-surface-base/70` + `backdrop-blur-sm`
- Investment Radar badge: `bg-surface-base/80` + `backdrop-blur-sm`
- Used sparingly — only for overlaid UI elements

### Corner Radii
- sm: 4px — tiny badges, tags
- md: 8px — buttons, inputs, icon containers
- lg: 12px — cards, panels (most common)
- xl: 16px — modals, drawers
- full: 9999px — avatars, pills, score ring track

### Trust Score Ring
- SVG circle element. Track: `#1A2D47`. Fill: color by tier.
- Sizes: sm (44px, r=17), md (64px, r=26), lg (88px, r=36)
- Stroke: 4–6px. Animated on mount with 0.8s spring easing.
- Score number rendered as SVG `<text>` inside the ring in DM Sans 500

---

## ICONOGRAPHY

### Icon System
**Lucide React** (`lucide-react`) — all icons are Lucide stroke icons, 2px stroke weight, no fill.

Key icons used throughout:
- Navigation: `Home`, `Building2`, `MapPin`, `Map`, `Users`, `Menu`, `X`
- Actions: `ArrowRight`, `ChevronLeft`, `ChevronRight`, `Shield`, `Clock`, `BarChart2`
- Status: `CheckCircle`, `TrendingUp`, `AlertCircle`
- Dashboard: `Settings`, `LogOut`, `Bell`

### Usage Rules
- Icon size in buttons: 16px (md/lg), 14px (sm)
- Standalone icons (section markers): 18–24px
- Color: inherits from parent (`currentColor`) or explicit `text-emerald-400`, `text-ink-secondary`, `text-ink-tertiary`
- No icon-only buttons without aria-label

### Logo
The "logo" is currently a **wordmark**: `Rent` (ink-primary) + `Check` (emerald-400) in DM Sans medium.  
The `logo.svg` in `assets/` is a placeholder house emoji — **not representative of a production logo**.  
→ **Ask for a real SVG wordmark or logomark from the client.**

---

## File Index

```
rentcheck-design-system/
├── README.md                    ← this file
├── colors_and_type.css          ← CSS vars for all tokens + semantic type classes
├── SKILL.md                     ← agent skill descriptor
├── assets/
│   ├── logo.svg                 ← placeholder logo (house emoji)
│   └── og-image.png             ← OG/social share image
├── preview/
│   ├── colors-surface.html      ← surface color swatches
│   ├── colors-accent.html       ← emerald + semantic colors
│   ├── colors-trust.html        ← trust score color tiers
│   ├── colors-status.html       ← badge/status semantic colors
│   ├── type-scale.html          ← full type scale specimen
│   ├── type-labels.html         ← label + caption styles
│   ├── spacing-tokens.html      ← spacing scale
│   ├── radius-shadows.html      ← border radius + shadow tokens
│   ├── buttons.html             ← all button variants + states
│   ├── badges.html              ← verification + status badges
│   ├── cards.html               ← card variants
│   ├── trust-score-ring.html    ← TrustScoreRing all sizes + tiers
│   ├── score-bars.html          ← ScoreBar component
│   └── inputs.html              ← form inputs + states
└── ui_kits/
    └── rentcheck/
        ├── README.md
        ├── index.html           ← main UI kit (landing + marketplace)
        ├── Tokens.jsx           ← shared tokens + CSS
        ├── Navbar.jsx
        ├── PropertyCard.jsx
        ├── TrustScoreRing.jsx
        ├── Badges.jsx
        ├── Buttons.jsx
        └── PassportCard.jsx
```
