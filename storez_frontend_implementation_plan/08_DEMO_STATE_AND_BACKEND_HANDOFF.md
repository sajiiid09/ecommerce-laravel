# 08 — Demo State and Backend Handoff

## Intentionally non-persistent interactions

The storefront is frontend-only. Cart and wishlist state lives in the root Alpine store in `components/layouts/app.blade.php`. It is shared by all rendered components during a browsing session, but resets on a full page reload and is never written to a database.

Checkout, account changes, authentication forms, newsletter subscription, address edits and order placement are visual demonstrations only. They do not submit credentials, charge a customer, create an order, or mutate inventory.

## Backend replacement points

`App\\Support\\StorefrontDemoData` is the single replacement boundary for this phase. Its methods map directly to later application services/repositories:

- `products`, `categories`, and `brands` → catalog repository/search service
- `cartItems` → cart service tied to a guest/session/customer identity
- `wishlistIds` → wishlist service
- `profile` and `addresses` → customer profile/address service
- `orders` and `trackingTimeline` → order and fulfilment services
- `paymentMethods` → payment configuration

Keep existing component props and route names stable as those services replace the fixture provider. The fixture fields intentionally use scalar/array shapes rather than Eloquent models so the Blade layer stays independent of persistence.
