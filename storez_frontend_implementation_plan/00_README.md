# StoreZ Frontend Implementation Plan

## Goal

Build the supplied StoreZ storefront UI in **Laravel 13 + Livewire v4 + Tailwind CSS v4 + Sheaf UI**, with **frontend/demo data only** for this phase.

This phase intentionally excludes:

- database schema and migrations
- Eloquent models/repositories
- real authentication
- payment gateway integration
- order persistence
- inventory persistence
- admin/CMS implementation
- APIs

The goal is a reusable, component-based UI that can later be connected to a real Laravel backend without rewriting the visual layer.

## Source of truth

Use these StoreZ references as the visual baseline:

- `homepage.png`
- `product_lisitng.png`
- `produtc_detail.png`
- `cart_drawer.png`
- `checkout.png`
- `order_success.png`
- `search_result_page.png`
- `login_page.png`
- `register_page.png`
- `wislist.png`
- `account_page.png`
- `my_order.png`
- `order_tracking.png`
- `offerdeal_page.png`
- `logo.png`
- `storez_branding_guidelines.md`
- `storez_frontend_demo.html`

Use only StoreZ-related visual references. Ignore unrelated uploads.

## Architectural direction

Use four layers:

1. **Livewire page components** — routable pages and page-level state.
2. **StoreZ Blade components** — product cards, price blocks, account sidebar, order cards, etc.
3. **Sheaf UI primitives** — button, card, input, select, checkbox, radio, modal, dropdown, tabs, separator, icon, avatar where suitable.
4. **Alpine.js micro-interactions** — drawer, image carousel, lightweight client-only toggles, mobile menus, and temporary demo cart/wishlist state.

Do not make every component a Livewire component. Display-only UI should remain Blade.

## Recommended implementation order

1. Foundation and Tailwind theme
2. Application shell: promo bar, header, nav, footer, trust strip
3. Generic UI primitives and StoreZ wrappers
4. Product/card/catalog components
5. Homepage
6. Product listing/category page
7. Search results page
8. Product detail page + image carousel
9. Cart drawer + cart page
10. Checkout + success page
11. Authentication mockups
12. Wishlist
13. Account dashboard
14. My Orders
15. Order Tracking
16. Offers & Deals
17. Brand page
18. Responsive pass
19. Accessibility and interaction QA
20. Visual regression pass against mockups

## Files in this plan

- `01_FOUNDATION_AND_THEME.md` — Tailwind v4 tokens, max width, layout rules, assets.
- `02_COMPONENT_ARCHITECTURE.md` — component boundaries and proposed file structure.
- `03_ROUTES_AND_PAGE_MAP.md` — route/page mapping and reference image matrix.
- `04_INTERACTION_AND_DEMO_STATE.md` — Livewire vs Alpine responsibilities, no-DB fixture strategy.
- `05_PAGE_BUILD_SEQUENCE.md` — detailed page-by-page implementation steps.
- `06_RESPONSIVE_ACCESSIBILITY_QA.md` — breakpoints, mobile transformations, keyboard/focus and QA.
- `07_DEFINITION_OF_DONE.md` — final implementation checklist.

## Key design constants

- Primary blue: `#0756C9`
- Blue hover: `#0647A6`
- Promotion red: `#F10B12`
- Deep navy: `#07366F`
- Heading ink: `#0F172A`
- Body: `#334155`
- Muted: `#64748B`
- Border: `#E2E8F0`
- Success: `#16A34A`
- Rating/warning: `#F59E0B`
- Recommended font: **Inter**
- Control radius: `8px`
- Card radius: `12px`
- Desktop content width: approximately `1400–1440px`

## Important StoreZ-specific rules

- Blue is the default action color.
- Red is reserved for offers, discounts, Buy Now, Proceed to Checkout, notification counters, and urgent commerce emphasis.
- Prefer light borders to heavy shadows.
- Keep product images centered with `object-contain`.
- Use a standard no-image placeholder when assets are missing.
- Keep the footer dark navy/blue.
- Do **not** include the mobile phone promotional image or Google Play/App Store buttons in the footer for the current implementation.
