# StoreZ Brand & UI Guidelines

> **Basis:** This guide is derived from the supplied StoreZ homepage, product listing, product detail, cart, checkout, search, authentication, wishlist, customer account, orders, offers, brand-store, and order-tracking mockups.
>
> The exact font family cannot be verified from raster mockup images alone. The typography specification below therefore uses **Inter** as the recommended implementation font because it closely matches the clean geometric sans-serif appearance of the mockups.

---

## 1. Brand Overview

**Brand name:** StoreZ  
**Category:** General e-commerce / marketplace  
**Visual personality:** Modern, dependable, fast, accessible, promotional, high-contrast

The StoreZ interface consistently uses:

- Blue for navigation, primary actions, links, and trust
- Red for promotions, discounts, urgency, and important commerce actions
- White as the main content surface
- Dark navy for the footer and strong brand framing
- Light gray borders and subtle tinted backgrounds to keep dense commerce layouts readable
- Green for success, savings, stock, and delivery completion
- Amber/yellow for ratings and promotional highlights

The visual direction should remain **clean and structured rather than decorative**.

---

# 2. Logo

## Primary Logo

The StoreZ wordmark uses:

- **“Store”** in bright electric blue
- **“Z”** in vivid red
- Slight italic/slanted visual energy
- Heavy bold custom wordmark styling

### Logo Colors

Colors sampled from the supplied logo asset:

| Token | Approx. Hex | Usage |
|---|---:|---|
| Logo Blue | `#0046E2` | “Store” portion of logo |
| Logo Red | `#FB0111` | “Z” portion of logo |

> Treat the logo as an image/SVG asset. Do not recreate the StoreZ wordmark using a normal UI font.

## Logo Clear Space

Keep clear space around the logo equal to at least **25% of the logo height**.

## Minimum Size

Recommended minimum widths:

- Desktop header: **110–130 px**
- Mobile header: **90–110 px**
- Small branding placements: **80 px minimum**

## Logo Background

Preferred:

- White
- Very light neutral background

Avoid placing the primary logo directly over:

- Busy photography
- Strong gradients
- Dark blue backgrounds without a dedicated light/white logo version

---

# 3. Core Color Palette

The mockups contain small color variations from page to page. For implementation, use the following normalized palette so the entire storefront stays consistent.

## Primary Brand Colors

| Token | Hex | Tailwind-style Name | Main Usage |
|---|---:|---|---|
| Brand Blue | `#0756C9` | `store-blue` | Primary CTA, links, controls |
| Brand Blue Dark | `#0647A6` | `store-blue-dark` | Hover states |
| Electric Logo Blue | `#0046E2` | `logo-blue` | Logo only |
| Brand Red | `#F10B12` | `store-red` | Promotions, Buy Now, offer zone |
| Logo Red | `#FB0111` | `logo-red` | Logo only |
| Deep Navy | `#07366F` | `store-navy` | Footer, strong dark surfaces |

### Primary Blue

`#0756C9`

Use for:

- Add to Cart buttons
- Search button
- Selected tabs
- Account sidebar active state
- Pagination active state
- Links
- Focus borders
- Selected options
- Progress indicators

### Brand Red

`#F10B12`

Use for:

- Offer Zone
- Promotional top bar
- Discount badges
- Sale price emphasis
- Buy Now
- High-priority promotional CTAs
- Cart/notification count badges

**Do not use red as the default primary button color.** Blue remains the main action color.

---

# 4. Supporting Colors

## Neutral Palette

| Token | Hex | Usage |
|---|---:|---|
| Ink / Heading | `#0F172A` | Main headings and important text |
| Body Text | `#334155` | Standard content |
| Muted Text | `#64748B` | Secondary information |
| Placeholder | `#94A3B8` | Inputs and metadata |
| Border | `#E2E8F0` | Cards, inputs, separators |
| Soft Border | `#EDF1F6` | Subtle separators |
| Surface | `#FFFFFF` | Main cards and page |
| Page Soft | `#F8FAFC` | Secondary background |
| Blue Tint | `#EFF6FF` | Selected/secure/info panels |
| Warm Promo | `#FFF4E8` | Grocery/deal promotional blocks |

## Semantic Colors

| Meaning | Hex | Usage |
|---|---:|---|
| Success Green | `#16A34A` | In stock, delivered, savings |
| Success Background | `#DCFCE7` | Success badge background |
| Warning Amber | `#F59E0B` | Ratings, warning indicators |
| Warning Background | `#FEF3C7` | Pending/shipped badge |
| Error Red | `#DC2626` | Validation/error/destructive actions |
| Error Background | `#FEE2E2` | Cancelled/error badge |
| Info Blue | `#2563EB` | Informational state |
| Info Background | `#DBEAFE` | Info badge background |

---

# 5. Recommended CSS Variables

```css
:root {
  --store-blue: #0756c9;
  --store-blue-dark: #0647a6;
  --store-logo-blue: #0046e2;

  --store-red: #f10b12;
  --store-logo-red: #fb0111;

  --store-navy: #07366f;

  --store-ink: #0f172a;
  --store-text: #334155;
  --store-muted: #64748b;
  --store-placeholder: #94a3b8;

  --store-border: #e2e8f0;
  --store-surface: #ffffff;
  --store-page-soft: #f8fafc;
  --store-blue-soft: #eff6ff;
  --store-promo-soft: #fff4e8;

  --store-success: #16a34a;
  --store-warning: #f59e0b;
  --store-error: #dc2626;
}
```

---

# 6. Typography

## Recommended Font

### Primary UI Font

**Inter**

Fallback:

```css
font-family:
  Inter,
  ui-sans-serif,
  system-ui,
  -apple-system,
  BlinkMacSystemFont,
  "Segoe UI",
  sans-serif;
```

Recommended web import:

```css
font-family: "Inter", sans-serif;
```

### Alternative

If a slightly rounder/geometric appearance is desired:

- **Poppins** may be used for headings
- Keep **Inter** for dense commerce UI, forms, tables, prices, and metadata

Do not mix more than two type families.

---

# 7. Typography Scale

## Desktop

| Role | Size | Weight | Line Height |
|---|---:|---:|---:|
| Display / Campaign | 48–64 px | 800–900 | 1.05–1.10 |
| Page H1 | 28–36 px | 800 | 1.15 |
| Section H2 | 20–24 px | 700–800 | 1.25 |
| Card H3 | 16–18 px | 600–700 | 1.3 |
| Body | 14–16 px | 400 | 1.5 |
| Product Name | 14–16 px | 500–600 | 1.35 |
| Price | 18–24 px | 700–800 | 1.2 |
| UI Label | 13–14 px | 500–600 | 1.35 |
| Metadata | 11–13 px | 400–500 | 1.4 |

## Mobile

Reduce large headings, but keep normal body text readable:

- H1: 24–30 px
- H2: 19–22 px
- Body: 14–16 px
- Button: 14–16 px

---

# 8. Font Weight Usage

Use weights intentionally:

- `400` — body copy and descriptions
- `500` — secondary labels
- `600` — navigation, product title, field labels
- `700` — buttons, section titles, totals
- `800` — page titles and prices
- `900` — large campaign/hero typography only

Avoid making every label bold.

---

# 9. Page Background & Surfaces

The dominant storefront background is:

`#FFFFFF`

Cards should generally remain white with a light gray border.

Secondary or informational surfaces may use:

- `#F8FAFC`
- `#EFF6FF`
- `#FFF4E8`
- Very light green/red/amber status backgrounds

Avoid large gray page backgrounds. The StoreZ design relies on a bright, clean retail canvas.

---

# 10. Border Radius

The mockups use moderate rounding rather than very rounded SaaS-style components.

Recommended tokens:

| Token | Radius | Usage |
|---|---:|---|
| `radius-sm` | 6 px | Badges, small controls |
| `radius-md` | 8 px | Inputs, buttons |
| `radius-lg` | 10–12 px | Product cards |
| `radius-xl` | 12–16 px | Large panels, hero cards |
| `radius-pill` | 9999 px | Category chips, status pills |

Default application:

```css
button,
input,
select {
  border-radius: 8px;
}

.card {
  border-radius: 12px;
}
```

---

# 11. Borders & Shadows

## Borders

Standard:

```css
border: 1px solid #e2e8f0;
```

Selected state:

```css
border: 2px solid #0756c9;
```

## Shadows

StoreZ should use very subtle shadows.

Recommended:

```css
box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
```

Prefer borders over heavy shadows for:

- Product cards
- Forms
- Checkout panels
- Account cards

---

# 12. Spacing System

Use a **4 px base spacing scale**.

Recommended values:

`4, 8, 12, 16, 20, 24, 32, 40, 48, 64`

Common patterns:

- Button vertical padding: `10–12 px`
- Button horizontal padding: `16–24 px`
- Card padding: `16–24 px`
- Major section gap: `24–32 px`
- Page section spacing: `32–48 px`
- Product-grid gap: `12–20 px`

---

# 13. Layout

## Maximum Width

Recommended desktop content width:

```css
max-width: 1400px;
margin-inline: auto;
padding-inline: 24px;
```

The mockups favor a wide marketplace layout rather than a narrow editorial layout.

## Main Grid Patterns

### Product Listing

```text
Sidebar: 220–260 px
Main content: remaining width
```

### Checkout / Cart

```text
Main form/cart: 65–72%
Order summary: 28–35%
```

### Account Area

```text
Account sidebar: 220–250 px
Main dashboard: remaining width
```

---

# 14. Header System

The StoreZ header is a major branding element and should remain consistent on all storefront pages.

## Layer 1 — Promotional Bar

Background:

`#F10B12`

Text:

`#FFFFFF`

Typical content:

- Fast delivery
- Free-delivery threshold
- Payment options
- Current offer message

Keep this bar short and compact.

## Layer 2 — Main Header

Contains:

1. StoreZ logo
2. Delivery location
3. Large search field
4. Category search selector
5. Account
6. Wishlist
7. Cart

Background:

`#FFFFFF`

## Layer 3 — Category Navigation

Background:

`#0756C9`

Text:

`#FFFFFF`

The Offers Zone item uses:

`#F10B12`

---

# 15. Search Field

The large global search control is one of the most important UI elements.

Recommended:

- Height: 44–48 px
- White background
- `1 px` neutral border
- 10–12 px corner radius
- Category selector within the field
- Blue square/rounded search button at right

Focus state:

```css
border-color: #0756c9;
box-shadow: 0 0 0 3px rgba(7, 86, 201, 0.10);
```

---

# 16. Buttons

## Primary Button

Background:

`#0756C9`

Text:

`#FFFFFF`

Use for:

- Add to Cart
- Sign In
- Create Account
- Continue to Payment
- Track Order
- Main form submission

Hover:

`#0647A6`

## Red Commerce CTA

Background:

`#F10B12`

Use selectively for:

- Buy Now
- Proceed to Checkout
- Promotional campaigns

## Outline Button

```css
background: #ffffff;
border: 1px solid #0756c9;
color: #0756c9;
```

Use for secondary actions.

## Destructive Button

Use red text/border or error red for:

- Delete address
- Remove item
- Clear cart

Avoid making destructive controls visually equal to the main purchase CTA.

---

# 17. Product Cards

Product cards should consistently contain:

1. Product image
2. Discount badge, if applicable
3. Wishlist icon
4. Product title
5. Brand
6. Current price
7. Previous price
8. Rating/review count
9. Add to Cart button

## Product Image

- White or neutral background
- `object-fit: contain`
- Product centered
- Avoid dramatic crop or decorative backgrounds

If an image is unavailable, use a standardized **No Image** placeholder instead of leaving the area blank.

## Product Title

- 14–16 px
- 500–600 weight
- Dark ink
- Maximum 2–3 lines in card layouts

## Price

Current:

`#F10B12`

Weight:

`700–800`

Old price:

`#94A3B8`

with strikethrough.

## Discount Badge

Background:

`#F10B12`

Text:

`#FFFFFF`

Radius:

`4–6 px`

Use uppercase:

`20% OFF`

---

# 18. Rating Style

Star:

`#F59E0B`

Rating text:

`#334155`

Review count:

`#64748B`

Example:

```text
★ 4.6 (2.1K)
```

Avoid using yellow for general UI actions.

---

# 19. Wishlist

Wishlist state:

- Empty/ inactive heart: neutral gray outline
- Saved state: red heart
- Hover: subtle red tint

Recommended active color:

`#EF4444`

---

# 20. Forms

Form controls should remain simple and functional.

## Inputs

- White background
- 1 px gray border
- 8 px radius
- 44–48 px height
- 14–16 px input text

## Labels

- Dark text
- 13–14 px
- Weight 500–600

Required field indicator:

`#DC2626`

## Focus

Use blue, not red:

```css
border-color: #0756c9;
box-shadow: 0 0 0 3px rgba(7, 86, 201, 0.1);
```

---

# 21. Selection Controls

Selected:

- Blue border
- Pale blue background where appropriate
- Blue radio/checkbox

Examples:

- Address selection
- Shipping method
- Product variant
- Category chip
- Account navigation

Do not rely on color alone; preserve radio/check icons or border differences.

---

# 22. Status Badges

## Delivered / Success

- Text: `#15803D`
- Background: `#DCFCE7`

## Processing / Pending

- Text: `#C2410C`
- Background: `#FFEDD5`

## Shipped / Information

- Text: `#1D4ED8`
- Background: `#DBEAFE`

## Cancelled

- Text: `#B91C1C`
- Background: `#FEE2E2`

Use compact pill badges with medium font weight.

---

# 23. Promotional UI

Promotion areas may use warmer backgrounds than normal cards.

Recommended backgrounds:

- Warm cream: `#FFF4E8`
- Pale yellow: `#FEF9C3`
- Pale blue: `#EFF6FF`
- Pale green: `#F0FDF4`
- Pale red: `#FEF2F2`

Keep StoreZ blue/red present in the heading or CTA so promotions still feel branded.

---

# 24. Iconography

Use simple outline icons similar to the supplied mockups.

Recommended icon libraries:

- Lucide
- Heroicons

Default treatment:

- Stroke width: approximately 1.5–2 px
- 16–20 px for inline UI
- 20–24 px for buttons/navigation
- 24–32 px for service-benefit cards

Primary icon color:

`#0756C9`

Secondary:

`#64748B`

Success:

`#16A34A`

Avoid mixing filled cartoon icons with outline UI icons unless the element is explicitly promotional.

---

# 25. Service / Trust Strip

The repeated trust strip in the mockups includes:

- Fast Delivery
- Easy Returns
- Secure Payments
- Cash on Delivery

Style:

- White background
- Light border
- Blue or green line icons
- Strong small heading
- Muted descriptive text
- Even horizontal spacing

This pattern should appear near the footer on major storefront pages.

---

# 26. Footer

The footer is a strong dark-blue brand area.

Recommended base:

`#07366F`

A subtle horizontal gradient may be used:

```css
background:
  linear-gradient(
    90deg,
    #063b75,
    #0d4f99
  );
```

Footer text:

- Heading: white
- Body/link: light blue-gray
- Hover: white

### Important

The current StoreZ frontend direction should **not include**:

- Mobile phone promotional mockup
- Google Play download button
- Apple App Store download button

Keep instead:

- About StoreZ
- Customer Service
- My Account
- Popular Categories
- Support/contact information
- Newsletter
- Payment methods
- Terms & Conditions
- Privacy Policy

---

# 27. Payment Branding

Payment methods shown throughout the StoreZ concepts include:

- Visa
- Mastercard
- bKash
- Nagad
- Rocket
- Cash on Delivery

Third-party payment logos should use their official brand assets and colors rather than recoloring them to StoreZ blue.

---

# 28. Commerce Hierarchy

StoreZ uses color to establish a clear hierarchy:

### Level 1 — Primary interaction

Blue

Examples:

- Add to Cart
- Search
- Continue
- Save
- Track

### Level 2 — Urgent commerce / promotion

Red

Examples:

- Buy Now
- Proceed to Checkout
- Offers
- Discounts

### Level 3 — Positive state

Green

Examples:

- In Stock
- Delivered
- Savings
- Confirmation

### Level 4 — Supporting information

Gray / slate

Examples:

- SKU
- Delivery metadata
- Review count
- Secondary text

---

# 29. Responsive Behavior

## Desktop

- Wide grid layouts
- Left filter/account sidebar
- Sticky order summary where useful
- 4–6 product cards per row depending on container size

## Tablet

- 2–4 cards per row
- Sidebar may collapse into filter button/drawer
- Header navigation can simplify

## Mobile

- 2 product cards per row where practical
- Single-column checkout
- Account sidebar becomes drawer/menu
- Order cards stack vertically
- Cart becomes a slide-over drawer or dedicated full-width panel
- Search remains prominent

Never simply shrink the desktop layout.

---

# 30. Motion

Animation should be subtle.

Recommended durations:

- Hover: `150–200 ms`
- Drawer: `250–350 ms`
- Page fade/slide: `250–400 ms`
- Dropdown: `150–250 ms`

Recommended easing:

```css
cubic-bezier(0.2, 0.8, 0.2, 1)
```

GSAP is appropriate for:

- Hero carousel
- Cart drawer
- Promotional carousel
- Small page transitions

Do not animate normal product-card grids excessively.

---

# 31. Accessibility

Maintain:

- Minimum 4.5:1 contrast for normal text
- Visible keyboard focus
- Proper form labels
- Text alternatives for product images
- Icons paired with text where meaning is important
- Status differences that are not color-only
- 44 px minimum practical touch target for key mobile controls

---

# 32. Tailwind CSS Theme Example

```js
tailwind.config = {
  theme: {
    extend: {
      colors: {
        store: {
          blue: "#0756C9",
          "blue-dark": "#0647A6",
          "logo-blue": "#0046E2",

          red: "#F10B12",
          "logo-red": "#FB0111",

          navy: "#07366F",

          ink: "#0F172A",
          text: "#334155",
          muted: "#64748B",
          border: "#E2E8F0",

          success: "#16A34A",
          warning: "#F59E0B",
          error: "#DC2626",
        },
      },

      fontFamily: {
        sans: [
          "Inter",
          "ui-sans-serif",
          "system-ui",
          "sans-serif",
        ],
      },

      borderRadius: {
        card: "12px",
        control: "8px",
      },

      boxShadow: {
        soft: "0 8px 30px rgba(15,23,42,0.08)",
      },
    },
  },
};
```

---

# 33. Quick UI Token Reference

```text
Brand Blue           #0756C9
Brand Blue Hover     #0647A6
Logo Blue            #0046E2

Brand Red            #F10B12
Logo Red             #FB0111

Footer Navy          #07366F

Heading              #0F172A
Body                 #334155
Muted                #64748B
Border               #E2E8F0
Background           #FFFFFF
Soft Background      #F8FAFC

Success              #16A34A
Warning / Rating     #F59E0B
Error                #DC2626

Primary Radius       8px
Card Radius          12px
Pill Radius          9999px

Primary Font         Inter
Base Spacing Unit    4px
Desktop Max Width    ~1400px
```

---

# 34. Visual Do / Don't

## Do

- Keep the overall interface bright and predominantly white.
- Use StoreZ blue as the default interaction color.
- Reserve red for deals, discounts, promotional navigation and urgent commerce actions.
- Use consistent card borders and corner radii.
- Keep product imagery centered and clean.
- Maintain strong separation between price, old price, discount and rating.
- Use dark navy in the footer for a strong visual endpoint.
- Use compact, information-dense layouts appropriate for marketplace shopping.

## Don't

- Do not use red and blue equally everywhere.
- Do not use heavy drop shadows on every card.
- Do not make every element highly rounded.
- Do not use multiple unrelated font families.
- Do not use oversized decorative illustrations in normal transaction flows.
- Do not replace official payment-brand colors with StoreZ colors.
- Do not introduce a mobile-app promotional phone image or app-store download buttons into the footer.
- Do not use large dark backgrounds throughout product browsing pages.

---

# 35. Overall Design Rule

A StoreZ page should feel recognizable even without the logo.

The strongest recurring visual signature is:

> **White commerce surface + StoreZ blue navigation/actions + red promotional emphasis + dark navy footer + compact bordered product cards + clean sans-serif typography.**
