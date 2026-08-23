# 04 — Interaction and Demo State

## Goal

The UI must feel interactive without introducing database work.

Use mock/fixture data and keep the state strategy easy to replace later.

## Shared fixture provider

Create a single temporary data provider, for example:

```text
app/Support/StorefrontDemoData.php
```

It should expose normalized arrays for:

- categories
- products
- brands
- promotions
- user/profile
- addresses
- cart items
- wishlist ids
- orders
- order tracking timeline
- payment methods
- trust/benefit items

Do not scatter large hard-coded product arrays across page views.

## Recommended state split

### Livewire owns

- search text/query state
- filters and sorting
- pagination demo state
- checkout step state
- address selection
- delivery option selection
- order tabs/filter state
- account tabs if server-like behavior is desired

### Alpine owns

- mobile menu open/close
- cart drawer open/close
- image carousel active slide
- thumbnail selection
- simple dropdown visibility
- client-only quantity demo behavior
- lightweight wishlist/cart demo state if you want cross-page continuity without backend persistence

### Blade owns

- rendering normalized data
- visual status mapping
- component variants

## Cart drawer

Implement early because it is globally reused.

Required behavior:

1. cart icon opens drawer
2. overlay appears
3. page body cannot scroll behind drawer
4. Escape closes drawer
5. overlay click closes drawer
6. close button closes drawer
7. quantity +/- updates demo subtotal
8. remove item works
9. empty-cart view exists
10. CTA links to cart/checkout
11. drawer is full width on small screens and approximately 420–460px on desktop

Prefer one source of cart state for both the drawer and cart page.

For the frontend-only phase, acceptable approaches are:

- a global Alpine store initialized from fixture data
- Livewire session/demo properties

A global Alpine store is simplest if the main objective is visual interaction.

## Wishlist

Requirements:

- toggle from product cards
- active heart uses red
- wishlist page reads the same demo state
- remove from wishlist
- move single item to cart
- move all to cart

Do not implement persistence yet unless you explicitly choose browser `localStorage` for demo continuity.

## Search and filters

Search page state:

```text
query
sort
viewMode
selectedCategories
selectedBrands
priceMin
priceMax
packSizes
availability
page
```

Category page state is similar but can use category-specific filter groups.

Use Livewire so these interactions already resemble the future backend implementation.

## Product detail interactions

Use Alpine for:

- active image
- previous/next image
- selected thumbnail
- selected size/variant
- local quantity stepper

Use Livewire only if the action needs cross-component/shared state.

## Checkout prototype

No payment call is needed.

The frontend flow should still support:

1. select address
2. edit/add address opens modal/drawer mock
3. select delivery method
4. enter notes
5. continue to payment/review step
6. choose demo payment method
7. place demo order
8. navigate to success page

You can represent checkout as either:

- one Livewire page with `step = 1|2|3`, or
- three child step components controlled by a parent page

Prefer one parent Livewire page with Blade child components until the logic becomes large.

## Auth pages

Treat login/register as frontend-only forms.

Requirements:

- input focus states
- password show/hide
- remember checkbox
- forgot-password link placeholder
- customer/business selection on register
- terms checkbox
- Google/Facebook buttons as non-functional demo actions for now

Do not add real social auth in this phase.

## Order tracking

All timeline data comes from fixtures.

Represent status as data, for example:

```php
[
    'status' => 'out_for_delivery',
    'steps' => [
        ['key' => 'placed', 'completed' => true],
        ['key' => 'confirmed', 'completed' => true],
        ['key' => 'packed', 'completed' => true],
        ['key' => 'shipped', 'completed' => true],
        ['key' => 'out_for_delivery', 'active' => true],
        ['key' => 'delivered', 'completed' => false],
    ],
]
```

Do not hard-code a different timeline layout for each status.

## Events

If Blade/Alpine and Livewire need to communicate, keep event names domain-oriented:

```text
cart-opened
cart-item-added
wishlist-updated
checkout-step-changed
filter-drawer-opened
```

Avoid global event spaghetti.

## Upgrade path when the backend starts

Keep these interfaces stable:

- product card input shape
- cart item input shape
- order card input shape
- address option shape
- status values

Later replace `StorefrontDemoData` with Eloquent/repositories without changing most Blade components.
