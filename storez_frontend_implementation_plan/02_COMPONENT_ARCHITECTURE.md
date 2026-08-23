# 02 — Component Architecture

## Principle

Use the smallest component that removes real duplication or owns reusable behavior.

Avoid two extremes:

- one giant Livewire component per page with hundreds of lines of markup
- turning every text block into a Blade component

## Component layers

### 1. Sheaf UI primitives

Keep installed Sheaf UI components under its normal `ui` namespace.

Examples to use when available:

- button
- card
- input
- select
- checkbox
- radio
- modal
- dropdown
- tabs
- separator
- icon
- avatar

Because Sheaf UI follows a source-owned/copy-in approach, customize these primitives to the StoreZ tokens rather than overriding them from many page files.

### 2. Generic StoreZ UI primitives

Create these when Sheaf UI does not directly match the design:

```text
resources/views/components/store/ui/
├── container.blade.php
├── drawer.blade.php
├── carousel.blade.php
├── icon-button.blade.php
├── quantity-stepper.blade.php
├── status-badge.blade.php
├── rating.blade.php
├── price.blade.php
├── discount-badge.blade.php
├── empty-state.blade.php
└── skeleton.blade.php
```

### 3. Layout components

```text
resources/views/components/store/layout/
├── promo-bar.blade.php
├── header.blade.php
├── desktop-nav.blade.php
├── mobile-nav.blade.php
├── search-bar.blade.php
├── trust-strip.blade.php
├── footer.blade.php
└── breadcrumb.blade.php
```

### 4. Commerce components

```text
resources/views/components/store/catalog/
├── product-card.blade.php
├── compact-product-card.blade.php
├── product-grid.blade.php
├── filter-sidebar.blade.php
├── filter-group.blade.php
├── sort-control.blade.php
├── category-chip.blade.php
├── section-header.blade.php
├── brand-card.blade.php
├── promo-banner.blade.php
└── recommendation-row.blade.php
```

### 5. Product-detail components

```text
resources/views/components/store/product/
├── image-gallery.blade.php
├── variant-picker.blade.php
├── purchase-actions.blade.php
├── feature-strip.blade.php
├── seller-card.blade.php
├── specification-table.blade.php
├── review-summary.blade.php
├── review-card.blade.php
└── frequently-bought.blade.php
```

### 6. Checkout/cart components

```text
resources/views/components/store/checkout/
├── cart-drawer.blade.php
├── cart-item-row.blade.php
├── cart-summary.blade.php
├── order-summary.blade.php
├── checkout-stepper.blade.php
├── address-option.blade.php
├── delivery-option.blade.php
├── payment-method-row.blade.php
└── support-card.blade.php
```

### 7. Account/order components

```text
resources/views/components/store/account/
├── sidebar.blade.php
├── profile-card.blade.php
├── stat-card.blade.php
├── address-card.blade.php
├── recent-order-row.blade.php
├── order-card.blade.php
├── order-status.blade.php
├── order-timeline.blade.php
└── delivery-partner-card.blade.php
```

## Livewire page components

Use Livewire v4 routable page components for page-level state.

Recommended namespace organization:

```text
resources/views/pages/
├── store/
│   ├── home/
│   ├── category/
│   ├── search/
│   ├── product/
│   ├── cart/
│   ├── checkout/
│   ├── checkout-success/
│   ├── wishlist/
│   ├── offers/
│   └── brand/
├── account/
│   ├── dashboard/
│   ├── orders/
│   └── order-tracking/
└── auth/
    ├── login/
    └── register/
```

For a project of this size, prefer Livewire v4 multi-file page components so PHP state and Blade markup remain separated while still living together conceptually.

## Shadcn-style ownership rule

Use the shadcn idea, not shadcn markup itself:

- source code lives in your project
- variants are explicit
- composition is preferred over inheritance
- primitives remain generic
- StoreZ-specific components wrap primitives only when they add domain meaning
- avoid package-specific abstractions that make later customization difficult

Example:

```blade
<x-ui.button>...</x-ui.button>
```

is a primitive.

```blade
<x-store.product.purchase-actions ... />
```

is domain composition that may internally use `x-ui.button`.

## Drawer design

Create one reusable drawer/sheet component.

Required API ideas:

```blade
<x-store.ui.drawer
    name="cart"
    side="right"
    size="md"
    title="Shopping Cart"
>
    ...
</x-store.ui.drawer>
```

Support:

- right/left side
- overlay
- Escape close
- overlay click close
- focus return to trigger
- body scroll lock
- `aria-modal="true"`
- responsive full width on small phones
- transition 250–350ms

If Sheaf UI modal slide-over already meets these needs, wrap/customize it rather than writing a second dialog system.

## Carousel design

Create one generic carousel for:

- homepage hero
- product image gallery
- recommendations
- recently viewed
- promotional tiles

Do not make a single carousel component handle every layout with a huge prop list.

Recommended split:

- `x-store.ui.carousel` — horizontal track, arrows, dots, keyboard behavior
- `x-store.product.image-gallery` — product thumbnails + main image, composed from carousel behavior
- `x-store.catalog.recommendation-row` — product-card horizontal scroller

Use Alpine for carousel interaction unless server state is actually needed.

## Product card API

A product card should accept normalized data such as:

```text
id
slug
name
brand
image
price
oldPrice
discount
rating
reviews
inStock
```

Variants:

- default
- compact
- horizontal

Do not duplicate separate markup for every page unless the visual structure truly differs.

## Status badge API

One component should cover:

- delivered
- processing
- shipped
- cancelled
- returned
- out-for-delivery

Map semantic status to label, icon and classes inside the component.

## Component responsibility table

| Concern | Best home |
|---|---|
| route/page state | Livewire page |
| visual card | Blade component |
| button/input/modal/tabs | Sheaf UI primitive |
| cart drawer open/close | Alpine + drawer component |
| demo cart/wishlist state | Alpine store or Livewire demo state |
| search/filter query state | Livewire |
| product image switching | Alpine |
| mobile nav open/close | Alpine |
| order status rendering | Blade component |
| repeated fixture data | shared demo fixture provider |
