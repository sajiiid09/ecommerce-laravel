# 07 — Definition of Done

Use this as the final frontend-only completion checklist.

## Implementation status — 2026-08-23

The implementation and route-rendering coverage are complete. `php artisan test`
passes 16 assertions and `npm run build` succeeds. The remaining manual release
gate is visual regression against the supplied mockups at the listed viewport
widths; it requires an interactive browser pass before this checklist can be
formally checked off in full.

See `08_DEMO_STATE_AND_BACKEND_HANDOFF.md` for the intentional demo-state
limitations and backend replacement points.

## Foundation

- [ ] Laravel 13 project scaffolding is complete manually.
- [ ] Livewire v4 is installed and page routing works.
- [ ] Tailwind v4 CSS-first theme is configured.
- [ ] Sheaf UI primitives needed by the storefront are installed.
- [ ] Inter is loaded.
- [ ] `--container-8xl: 90rem` is defined and `max-w-8xl` works.
- [ ] StoreZ colors, radii and shadows are centralized.
- [ ] StoreZ logo asset is used as an image.
- [ ] Standard no-image placeholder exists.

## Global shell

- [ ] Promo bar matches the StoreZ red hierarchy.
- [ ] Main header is reusable.
- [ ] Global search is reusable.
- [ ] Desktop category navigation is reusable.
- [ ] Mobile navigation works.
- [ ] Trust strip is reusable.
- [ ] Footer is reusable.
- [ ] Footer does not include phone/app-store promotional UI.
- [ ] Internal navigation uses `wire:navigate` where appropriate.

## Reusable primitives

- [ ] Button variants are centralized.
- [ ] Card/surface rules are centralized.
- [ ] Drawer is reusable and accessible.
- [ ] Carousel is reusable.
- [ ] Quantity stepper is reusable.
- [ ] Price component is reusable.
- [ ] Discount badge is reusable.
- [ ] Rating is reusable.
- [ ] Status badge is reusable.
- [ ] Breadcrumb is reusable.
- [ ] Pagination is reusable.
- [ ] Filter group is reusable.

## Catalog

- [ ] Product card supports default layout.
- [ ] Product card supports compact layout.
- [ ] Product grid is responsive.
- [ ] Wishlist visual state works.
- [ ] Add to Cart opens/updates the cart drawer.
- [ ] Product imagery uses consistent aspect ratios and `object-contain`.

## Pages

- [ ] Homepage implemented.
- [ ] Product listing/category implemented.
- [ ] Search results implemented.
- [ ] Product detail implemented.
- [ ] Cart drawer implemented.
- [ ] Cart page implemented.
- [ ] Checkout implemented.
- [ ] Checkout success implemented.
- [ ] Login implemented.
- [ ] Register implemented.
- [ ] Wishlist implemented.
- [ ] My Account implemented.
- [ ] My Orders implemented.
- [ ] Order Tracking implemented.
- [ ] Offers & Deals implemented.
- [ ] Brand page implemented.

## Demo state

- [ ] Shared fixture provider exists.
- [ ] Product data is not duplicated across pages.
- [ ] Cart data has one source of truth.
- [ ] Wishlist data has one source of truth.
- [ ] Order status/timeline comes from normalized data.
- [ ] No database dependency exists.
- [ ] No migration/model is required to view the frontend.

## Responsive

- [ ] 375px layout checked.
- [ ] 430px layout checked.
- [ ] 768px layout checked.
- [ ] 1024px layout checked.
- [ ] 1280px layout checked.
- [ ] 1440px+ layout checked.
- [ ] No horizontal overflow.
- [ ] Filter sidebar becomes drawer on smaller screens.
- [ ] Account sidebar becomes drawer/menu on smaller screens.
- [ ] Checkout becomes single column on smaller screens.
- [ ] Order tracking timeline becomes vertical on smaller screens.

## Accessibility

- [ ] Keyboard focus is visible.
- [ ] Inputs have labels.
- [ ] Drawer closes with Escape.
- [ ] Important icon-only buttons have accessible labels.
- [ ] Product images have alt text.
- [ ] Selected states are not color-only.
- [ ] Key mobile controls have practical touch targets.
- [ ] Contrast is checked for blue/red/navy text combinations.

## Visual consistency

- [ ] Primary action is blue by default.
- [ ] Red is reserved for promotion/urgent commerce.
- [ ] Success/savings use green.
- [ ] Ratings use amber.
- [ ] Borders are preferred over heavy shadows.
- [ ] Card radius is consistent.
- [ ] Input/button radius is consistent.
- [ ] Product cards maintain compact marketplace density.
- [ ] Footer creates a strong dark visual endpoint.

## Backend-ready cleanup

Before starting database work:

- [ ] Identify all fixture-provider calls that will become repositories/models.
- [ ] Keep Blade component props independent of Eloquent models where practical.
- [ ] Keep status strings/enums consistent.
- [ ] Keep route names final enough that backend wiring will not require page rewrites.
- [ ] Remove any temporary page-local duplicated fixture arrays.
- [ ] Document which demo interactions are intentionally non-persistent.

Once every item above is complete, the storefront UI is ready for the next phase: data modeling, authentication, catalog persistence, cart/order services, payment configuration and admin tooling.
