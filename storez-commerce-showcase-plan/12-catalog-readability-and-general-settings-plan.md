# Catalog Readability and General Store Settings

## Summary

Fix mojibake text in the Products, Brands, Tags, and Variants admin pages, keep the Products filter because it is already functional, and add a dedicated Admin -> Settings -> General page for store identity and contact information.

Currency configuration will be intentionally omitted for now. Existing BDT display and transaction behavior will remain unchanged.

## Implementation Changes

- Replace corrupted source literals such as `Ãƒ...`, `â...`, and `à§...` with valid UTF-8 or HTML entities for:
  - arrows;
  - em dashes;
  - close/remove icons;
  - Bangladeshi taka symbols.
- Apply the cleanup to the requested catalog views and related visible catalog controls without changing catalog data or business logic.
- Preserve the Products Filters button because it currently opens working status, category, and brand filters. Ensure its labels and reset action remain readable.
- Create a General Settings Livewire page at `/admin/settings/general`.
- Store settings in the existing `site_settings` table through `SiteSettingsService`, using a `general` group:
  - store name;
  - tagline;
  - logo and favicon media references;
  - support email;
  - support phone;
  - business address;
  - timezone.
- Seed defaults with `firstOrCreate` so existing admin settings are never overwritten.
- Use existing admin authorization and Sheaf UI form primitives where applicable.
- Add the General Settings route, admin navigation item, active-state handling, and page-title mapping.
- Expose saved identity/contact values through the storefront layout/composer and use them for storefront branding, document-title fallbacks, footer contact information, and logo metadata.
- Keep existing Content -> Header and Content -> Footer settings independent unless a field is specifically moved; avoid conflicting precedence.
- Invalidate the relevant settings cache after every save.
- Keep BDT formatting, order currency, Stripe currency, and payment behavior unchanged.
- Update `11-implementation-progress.md` with implementation and verification results.

## Interfaces

Add a general settings contract through the existing settings service:

```text
get('general', key, default)
set('general', key, value)
```

The General Settings Livewire component will expose:

```text
storeName
tagline
logoMediaId
faviconMediaId
supportEmail
supportPhone
address
timezone
save()
```

Validation will include required store name, valid email when supplied, reasonable field lengths, valid media IDs, and a supported timezone.

## Tests and Verification

Add Pest coverage for:

- Mojibake strings are absent from the requested catalog views.
- Products, Brands, Tags, and Variants retain readable labels, fallback text, arrows, and currency symbols.
- The Products Filters button remains functional and updates status/category/brand filters.
- Resetting product filters restores the complete result set.
- Unauthenticated users cannot access General Settings.
- Non-admin users receive `403`.
- Admin users can view and save General Settings.
- Validation rejects invalid email, media, timezone, and oversized values.
- Existing settings are preserved when unrelated fields are saved.
- Defaults are seeded without overwriting administrator values.
- Settings cache invalidation is reflected immediately in storefront branding/contact output.
- Existing BDT product, order, and payment formatting remains unchanged.

Run:

```powershell
php artisan test --compact tests/Feature/ProductCatalogCompletionTest.php
php artisan test --compact tests/Feature/ContentManagementTest.php
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
git diff --check
```

## Assumptions

- "Skip currency logic" means currency fields will not be added to this iteration.
- Currency remains fixed to BDT for display, orders, and payments.
- Store identity/contact settings are database-managed and editable without deployment changes.
- Existing Header and Footer content settings remain available and are not silently replaced.
- Persisted catalog values will not be mass-rewritten; only corrupted source UI literals and defaults will be corrected.
