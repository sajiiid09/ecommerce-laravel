# 01 — Foundation and Tailwind v4 Theme

## Step 1 — Keep scaffolding outside this plan

You will manually scaffold Laravel 13, Livewire v4, Tailwind v4 and Sheaf UI. Once they are installed, start implementation from `resources/css/app.css`, the Livewire layout, and public assets.

Do not create database files during this phase.

## Step 2 — Create the StoreZ Tailwind v4 theme

Prefer Tailwind v4 CSS-first configuration using `@theme`.

Suggested `resources/css/app.css` foundation:

```css
@import "tailwindcss";

@theme {
    --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;

    --color-store-blue: #0756c9;
    --color-store-blue-dark: #0647a6;
    --color-store-logo-blue: #0046e2;
    --color-store-red: #f10b12;
    --color-store-logo-red: #fb0111;
    --color-store-navy: #07366f;

    --color-store-ink: #0f172a;
    --color-store-text: #334155;
    --color-store-muted: #64748b;
    --color-store-placeholder: #94a3b8;
    --color-store-border: #e2e8f0;
    --color-store-soft: #f8fafc;
    --color-store-blue-soft: #eff6ff;
    --color-store-promo-soft: #fff4e8;

    --color-store-success: #16a34a;
    --color-store-warning: #f59e0b;
    --color-store-error: #dc2626;

    --radius-control: 0.5rem;
    --radius-card: 0.75rem;

    --shadow-store-soft: 0 8px 30px rgb(15 23 42 / 0.08);

    /* 90rem = 1440px; this creates max-w-8xl in Tailwind v4. */
    --container-8xl: 90rem;
}
```

Use `max-w-8xl` as the StoreZ desktop shell width.

## Step 3 — Standard page container

Create one Blade container component rather than repeating shell classes everywhere.

Recommended API:

```blade
<x-store.container>
    ...
</x-store.container>
```

Suggested base classes:

```text
w-full max-w-8xl mx-auto px-4 sm:px-5 lg:px-6
```

This keeps the mockup's wide marketplace layout while remaining fluid.

Optional variant:

```blade
<x-store.container size="narrow">...</x-store.container>
```

Use narrow only for auth or special content, not normal commerce pages.

## Step 4 — Base typography

Apply to the application root:

```text
font-sans text-store-text bg-white antialiased
```

Recommended scale:

- page title: `text-2xl md:text-3xl font-extrabold tracking-tight text-store-ink`
- major section title: `text-xl md:text-2xl font-bold text-store-ink`
- card title: `text-sm md:text-base font-semibold`
- body: `text-sm md:text-base`
- metadata: `text-xs text-store-muted`
- product price: `text-lg md:text-xl font-extrabold text-store-red`

Do not make all labels bold.

## Step 5 — Shared surface rules

Create consistent component classes/tokens:

### Cards

```text
bg-white border border-store-border rounded-card
```

### Inputs

```text
h-11 md:h-12 rounded-control border border-store-border bg-white
focus:border-store-blue focus:ring-2 focus:ring-store-blue/10
```

### Primary button

```text
bg-store-blue hover:bg-store-blue-dark text-white rounded-control
```

### Promotional CTA

```text
bg-store-red hover:bg-red-700 text-white rounded-control
```

Use promotional red only when the design calls for it.

## Step 6 — Asset organization

Recommended public structure:

```text
public/
└── images/
    ├── brand/
    │   └── storez-logo.png
    ├── placeholders/
    │   └── no-image.png
    ├── banners/
    ├── categories/
    ├── products/
    ├── brands/
    ├── payments/
    └── avatars/
```

Rules:

- use the provided StoreZ logo image; do not recreate the wordmark with text
- use `object-contain` for product imagery
- define a single no-image placeholder and reuse it everywhere
- keep official payment logos in their official colors

## Step 7 — Layout file

Create the main Livewire layout with this hierarchy:

```text
<html>
  <head>
    Vite CSS/JS
  </head>
  <body>
    promo bar
    main header
    category navigation
    main page slot
    trust strip
    footer
    cart drawer portal/root
  </body>
</html>
```

Suggested layout location:

```text
resources/views/layouts/app.blade.php
```

Do not place page-specific content in the layout.

## Step 8 — Footer direction

Footer sections should include:

- About StoreZ
- Customer Service
- My Account
- Popular Categories
- support/contact area if needed
- newsletter form
- payment methods
- Terms & Conditions
- Privacy Policy
- copyright

Do not include:

- phone mockup
- Google Play button
- App Store button

## Step 9 — Icon system

Use one icon system consistently.

Preferred:

1. Sheaf UI icon primitive, if already installed
2. Heroicons
3. Lucide only if you deliberately choose it for the whole storefront

Do not mix three icon families on the same page.

## Step 10 — Motion policy

Start with Tailwind/Alpine transitions.

Use GSAP only if a hero/promo carousel needs richer motion later.

Recommended timings:

- hover: 150–200ms
- dropdown: 150–250ms
- drawer: 250–350ms
- page-level enter transition: 250–400ms

Respect `prefers-reduced-motion`.
