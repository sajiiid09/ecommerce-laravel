# 06 — Responsive, Accessibility and QA Plan

## Responsive strategy

Do not shrink the desktop mockup proportionally.

Recompose it by breakpoint.

### Mobile: below `sm/md`

- one-column major layout
- product grids usually 2 columns
- cart rows become cards
- checkout becomes one column
- sticky order summary becomes normal flow
- account sidebar becomes drawer/menu
- filter sidebar becomes drawer
- product thumbnail rail becomes horizontal
- order tracking becomes vertical
- header becomes compact/two-row
- category nav collapses

### Tablet

- 2–4 product cards depending on context
- category/filter sidebar can collapse
- header can reduce shortcut labels
- checkout may remain one column until wide tablet/desktop

### Desktop

- use `max-w-8xl`
- 220–260px filter/account sidebar
- 4–6 product cards depending on section
- checkout/cart split roughly 65/35 or 70/30
- sticky summaries only when they do not overlap footer/content

## Suggested breakpoint behavior

Do not invent a custom breakpoint unless a real layout issue requires it.

Use Tailwind defaults first:

```text
sm
md
lg
xl
2xl
```

The 1440px max container controls excessive widening.

## Touch targets

Important controls should have a practical minimum target of approximately 44px:

- header actions
- mobile menu
- quantity +/-
- carousel arrows
- drawer close
- filter buttons
- checkout actions

## Keyboard requirements

### Drawer/modal

- Escape closes
- trigger can be reached by Tab
- close button is keyboard reachable
- focus is not lost after close

### Carousel

- arrow buttons are real buttons
- thumbnail buttons are real buttons
- active item has state indication beyond color where feasible

### Forms

- all fields have visible labels
- required state is announced/textual
- focus ring is visible
- password visibility button has accessible label

### Tabs

Prefer Sheaf UI tabs so keyboard semantics are already handled consistently.

## Color and state

Do not rely on color alone for:

- order status
- selected address
- selected delivery method
- active filter
- validation errors

Use icon/check/radio/border/text in addition to color.

## Image accessibility

Product image alt format:

```text
{Product name} — {size/variant if useful}
```

Decorative promo art can use empty alt text when the surrounding text already communicates the promotion.

## Visual QA checklist per page

Test at minimum:

- 375px
- 430px
- 768px
- 1024px
- 1280px
- 1440px+

Verify:

- no horizontal page scroll
- no clipped dropdown/drawer
- no product image distortion
- no CTA text wrapping badly
- no sticky summary overlapping footer
- breadcrumbs wrap acceptably
- dense account/order areas remain readable

## Interaction QA

### Cart

- add item
- increase/decrease
- remove
- empty state
- open/close repeatedly
- Escape close
- navigate to cart/checkout

### Wishlist

- toggle from card
- remove from wishlist page
- add to cart
- move all to cart

### Search/filter

- query updates
- chips toggle
- filters toggle
- mobile filter drawer
- sort changes
- pagination state

### Product detail

- gallery switch
- variant selection
- quantity
- add to cart
- buy now route
- tabs

### Checkout

- address selection
- delivery selection
- form fields
- step transition
- back/forward
- success navigation

## Visual regression workflow

For every completed page:

1. open target mockup beside browser
2. match outer container width
3. match major grid proportions
4. match section order
5. match heading/price hierarchy
6. match border radius and border color
7. match CTA hierarchy
8. match whitespace before fine-tuning icons

Do not chase 1–2px differences before the layout proportions are correct.

## Performance guardrails

Even in the frontend phase:

- lazy-load non-critical product images where appropriate
- set image width/height or aspect ratio to reduce layout shift
- avoid large base64 assets in Blade
- avoid one Livewire component per product card
- avoid Livewire requests for hover-only UI
- keep Alpine interactions local and simple
- do not add GSAP to normal card grids

## Livewire navigation note

If you use `wire:navigate`, initialize third-party JS on Livewire navigation lifecycle events rather than relying only on `DOMContentLoaded`.

Prefer no third-party JS for basic StoreZ interactions unless needed.
