# 05 — Page Build Sequence

This is the recommended step-by-step build order.

---

## Phase A — Shared storefront shell

### A1. Promo bar

Match:

- red background
- delivery text
- free-delivery text
- payment message
- small offer pill/button on the right

Desktop can show all content. Mobile can reduce to one/two short messages or horizontal overflow.

### A2. Main header

Build these subcomponents:

- logo
- location selector
- search input
- category selector
- search button
- account shortcut
- wishlist shortcut + count badge
- cart shortcut + count badge

Desktop: one horizontal row.

Tablet/mobile: simplify into two rows rather than shrinking everything.

### A3. Category navigation

- blue background
- all-categories trigger
- grocery, fashion, electronics, beauty, home & living, baby care, sports, books
- red Offers Zone item

On mobile, replace with menu/drawer.

### A4. Trust strip

Reusable four-item strip:

- Fast Delivery
- Easy Returns
- Secure Payments
- Cash on Delivery

### A5. Footer

Build once before page implementation.

---

## Phase B — Catalog primitives

Build before the homepage:

1. product price
2. old price
3. discount badge
4. rating
5. wishlist icon button
6. product card
7. compact product card
8. section heading + View All
9. category/brand tile
10. horizontal product row
11. promotional banner
12. pagination
13. sort control
14. filter group

Acceptance test: you can reproduce at least three different product sections without duplicating markup.

---

## Phase C — Homepage

Reference: `homepage(1).png`.

### C1. Left category panel

Desktop-only or large-tablet display.

Include icon + text + chevron rows.

### C2. Hero area

Create a reusable hero carousel.

Desktop structure:

```text
left category rail | large hero | two stacked promo cards
```

Mobile:

```text
hero only
promo cards below
categories become horizontal cards/menu
```

### C3. Shop by Category

Use image tiles with short labels.

### C4. Flash Sale

Include:

- title
- countdown display
- product row/grid
- View All Deals

Countdown can be static demo text initially.

### C5. Smart Combo Deals

Use warm cream background and four combo cards.

### C6. Best Sellers

Compact product cards.

### C7. Top Brands

Logo/brand tiles.

### C8. Fresh Picks

Compact product cards.

### C9. Customer testimonials

Use three bordered cards with avatar, rating, location, quote.

---

## Phase D — Category / Product Listing

Reference: `product_lisitng(1).png`.

### D1. Desktop sidebar

Filter sections:

- subcategories
- price
- brands
- ratings
- availability
- delivery options

### D2. Category hero

Warm promotional header with title, description and category imagery.

### D3. Toolbar

- result count
- grid/list toggle
- sort select

### D4. Category chips

All / Staples / Beverages / Snacks / Household / Baby Care.

### D5. Product grid

Desktop: 4 cards in the main content area at the target width.

Tablet: 2–3.

Mobile: 2 where practical.

### D6. Mobile filters

Hide desktop sidebar and expose a Filter button that opens the generic drawer from the left/bottom.

### D7. Additional promotional banner and recommendations

Build using existing components.

---

## Phase E — Search Results

Reference: `search_result_page.png`.

Reuse category components, but add:

- `Search results for “rice”`
- result count
- popular search chips
- search-specific filter groups such as rice type and pack size
- relevance sort
- pagination

Do not fork a totally separate product card or filter system.

---

## Phase F — Product Detail

Reference: `produtc_detail(1).png`.

### F1. Image gallery

Desktop:

```text
thumbnail rail | large main image
```

Mobile:

```text
main image
horizontal thumbnail strip
```

Features:

- active thumbnail border
- previous/next controls
- optional video thumbnail placeholder
- zoom icon can be visual-only initially

### F2. Product info

- brand
- title
- rating/review count
- sold count
- price + old price + discount
- savings text
- stock status
- feature bullets
- variants
- quantity
- Add to Cart
- Buy Now
- wishlist/compare actions

### F3. Service benefits

Cash on Delivery, bKash/Nagad, fast delivery, easy returns.

### F4. Tabs

Use Sheaf UI Tabs for:

- Description
- Specifications
- Reviews
- Shipping & Returns

### F5. Seller card

Desktop side card in detail section.

### F6. Reviews

- summary score
- star distribution
- review cards

### F7. Frequently bought together

Create a horizontal composed bundle row.

### F8. Recommendations and recently viewed

Use existing product components.

---

## Phase G — Global Cart Drawer

Reference: `cart_drawer(1).png`.

Implement before the full cart page so Add to Cart can already feel complete.

Sections:

- title + close button
- item count
- cart item list
- quantity controls
- remove action
- subtotal
- delivery/savings message
- View Cart
- Proceed to Checkout

Ensure correct z-index and scroll containment.

---

## Phase H — Shopping Cart

Reference: supplied StoreZ cart mockup.

Desktop:

```text
cart table/list: ~70%
summary: ~30%
```

Implement:

- product info
- unit price
- discount indicator
- quantity stepper
- subtotal
- remove
- continue shopping
- update cart demo action
- clear cart
- coupon input
- order summary
- delivery progress
- payment/support benefit card
- recommended products

Mobile:

- each row becomes a stacked card
- summary moves below items
- checkout CTA can become sticky near viewport bottom if helpful

---

## Phase I — Checkout

Reference: `checkout.png`.

### I1. Checkout stepper

1. Delivery Information
2. Payment Method
3. Review & Place Order

### I2. Delivery information

- saved address cards
- selected address blue border
- edit button
- add new address button
- billing-address option
- name/phone/district/area/address/landmark fields

### I3. Delivery options

- Standard
- Express

### I4. Notes

Textarea.

### I5. Sticky order summary

Desktop only.

On mobile it becomes a normal block below/above CTA.

### I6. Payment/review steps

Build these even if the reference mainly shows step 1, using the same design language.

Payment methods:

- Cash on Delivery
- bKash
- Nagad
- Card

No real gateway call yet.

---

## Phase J — Checkout Success

Reference: `order_success.png`.

Implement:

- success icon
- title
- order id
- estimated delivery
- payment method
- delivery address
- “what happens next” 3-step row
- Track Order
- Continue Shopping
- Download Invoice visual action
- order items summary
- help card
- recommended products

All actions except normal navigation can remain demo-only.

---

## Phase K — Login and Register

References: `login_page.png`, `register_page.png`.

Create shared auth layout component:

```text
form panel | benefits/illustration panel
```

### Login

- Google/Facebook buttons
- email/phone
- password
- show/hide password
- remember me
- forgot password
- sign in
- register link
- secure-sign-in notice

### Register

- Google/Facebook buttons
- full name
- email
- phone
- password
- password strength visual
- confirm password
- customer/business choice
- terms checkbox
- create account
- sign-in link

Use a no-image/placeholder illustration if exact illustration assets are not available.

---

## Phase L — Wishlist

Reference: `wislist.png`.

Implement:

- item count
- share wishlist
- move all to cart
- six-column desktop product grid target
- remove icon
- heart active state
- Add to Cart

Reuse product-card atoms but allow a wishlist-specific card composition if the top-right controls differ enough.

---

## Phase M — My Account

Reference: `account_page.png`.

### M1. Account sidebar

Reusable across account pages.

Desktop: fixed-width left panel.

Mobile: drawer or top account menu.

### M2. Stat cards

- Total Orders
- Pending Orders
- Wishlist Items
- Reward Points

### M3. Recent Orders

Desktop table; mobile stacked rows/cards.

### M4. Saved Addresses

Two address cards.

### M5. Payment Methods & Benefits

Payment/service rows.

### M6. Recently Viewed

Reuse product cards.

---

## Phase N — My Orders

Reference: `my_order.png`.

Implement:

- account sidebar
- order tabs: all/processing/shipped/delivered/cancelled/returned
- search input
- filter button
- order cards
- status badges
- View Order Details
- Buy Again / Track Order actions
- pagination

Order cards should be data-driven and not status-specific duplicated markup.

---

## Phase O — Order Tracking

Reference: `order_tracking.png`.

Implement:

- order meta header
- item thumbnails
- payment/delivery/address/total metadata
- support/invoice actions
- six-step order timeline
- delivery partner card
- delivery updates vertical timeline
- order items summary
- Buy Again
- Return / Replace
- Back to My Orders

Desktop timeline is horizontal; mobile becomes vertical.

---

## Phase P — Offers & Deals

Reference: `offerdeal_page.png`.

Reuse listing/filter components.

Add:

- page title/subtitle
- Share Offers
- deal-type filters
- large weekend/mega-sale banner
- offer category chips
- countdown
- limited-time product grid
- combo/bank/electronics promo cards
- second deal grid

Discount red can be more visually prominent here than normal listing pages.

---

## Phase Q — Brand Page

Reference: the supplied frontend demo HTML plus the same StoreZ design language.

Implement:

- breadcrumb
- brand hero
- brand identity/logo block
- follow/share actions
- brand rating/meta
- category chips/tabs
- brand product grid
- optional featured/promotional row

Keep all product cards identical to the catalog system.

---

## Phase R — Cross-page cleanup

After all pages exist:

1. normalize heading sizes
2. normalize card radius/borders
3. normalize product image ratios
4. verify no duplicate button styles
5. verify one filter drawer system
6. verify one cart drawer system
7. verify one status badge system
8. verify one account sidebar
9. verify one footer/header
10. remove page-specific hacks that should be shared components
