# Product Variants Implementation

## Status

Paused at the user's request on 2026-08-30. The implementation is partially complete and has not yet had its final formatter, build, browser smoke test, or full regression pass.

## Goal

Make variable products production-ready for combinations such as:

- Color: Black, White
- Size: Small, Large
- Four generated sellable variants: Black/Small, Black/Large, White/Small, White/Large

The selected variant must remain correct through the storefront, cart, checkout, and order creation.

## Completed in this session

### Variant generation and reconciliation

- Updated `app/Services/ProductVariantService.php`.
- Generation now builds the complete Cartesian product of product options.
- Combination keys use stable option/value slugs, for example `color=black|size=small`, instead of option-value IDs.
- Re-running generation is intended to be idempotent.
- Existing soft-deleted variants can be restored when the same option/value slugs are added again.
- Obsolete combinations are soft-deleted during generation.
- Default variant normalization was added so one active variant is selected as default when possible.
- Variant saves now invalidate the product catalog cache.
- Variant service pricing rejects a sale price above the regular price and derives compare-at pricing from regular/sale pricing.

### Admin variant editor

- Updated `app/Livewire/Pages/Admin/Catalog/Products/Variants.php`.
- Option names and values are trimmed, slug-validated, and checked for duplicates within the product/option.
- Removing an option or value immediately reconciles generated variants.
- Variant prices now use normal BDT values such as `1299.00` in the editor and convert to minor units before persistence.
- Manual compare-at editing was removed from the variant editor.
- Low-stock threshold defaults to `10`.
- Selection is cleared or refreshed when the selected variant is removed.
- Updated `resources/views/livewire/pages/admin/catalog/products/variants.blade.php` for BDT price fields and removal of Compare At.

### Product publishing guard

- Updated `app/Services/ProductService.php`.
- A variable product cannot be published unless it already has at least one active variant with option values.

### Storefront

- Updated `app/Livewire/Pages/Store/Product.php` with:
  - `buyNow(int $variantId, int $quantity = 1)`
  - Add-to-cart compatibility preserved
- Updated `resources/views/pages/store/product.blade.php`.
- Storefront matching now requires all selected option slugs to match the variant.
- Unavailable option combinations are disabled.
- Price, discount, availability, stock count, and selected variant image update from the selected variant.
- Product gallery fallback is used when a variant has no image.
- Add to Cart and Buy Now are disabled for unavailable or missing variants.
- Buy Now adds the selected variant and redirects to the existing checkout route.

### Tests added or updated

- `tests/Feature/ProductCatalogCompletionTest.php`
  - Updated variable-product fixture to publish only after variants exist.
  - Added generation idempotency and remove/restore coverage.
  - Added duplicate/blank option-value validation coverage.
  - Added BDT variant price conversion and derived compare-at coverage.
  - Added variable-product publish guard coverage.
- `tests/Feature/CommerceShowcaseTest.php`
  - Added a multi-variant cart, Buy Now, checkout, order snapshot, and inventory deduction test.

## Verification already completed

Passing checks during this session:

- Variant reconciliation, storefront mapping, validation, and publishing guard: `4 passed (29 assertions)`.
- Commerce variant/cart/checkout test: `2 passed (21 assertions)`.
- Earlier focused catalog checks: `5 passed (66 assertions)`.
- PHP lint passed for the changed PHP files before the latest test additions.

A broader earlier catalog run had one known unrelated failure in the existing brand-link test. It should be rechecked after resuming, but it was not caused by the variant work.

## Next steps when resuming

1. Run `vendor/bin/pint --dirty --format agent`.
2. Run focused tests again after formatting:

   ```powershell
   php artisan test --compact tests/Feature/AdminCatalogTest.php tests/Feature/ProductCatalogCompletionTest.php tests/Feature/CommerceShowcaseTest.php --filter="variant|Variant|option|Option|buy now|checkout"
   ```

3. Run the full relevant files without a filter and separate any pre-existing failures.
4. Run `npm run build` because the storefront Blade/Alpine behavior depends on the frontend build pipeline.
5. Use the browser control tool for a real smoke test:
   - Create a draft variable shirt.
   - Add Color Black/White and Size Small/Large.
   - Generate four variants.
   - Set different prices and stock values.
   - Publish the product.
   - Select each combination on the product page.
   - Confirm price, stock, SKU, and image changes.
   - Confirm Add to Cart creates separate variant lines.
   - Confirm Buy Now redirects to checkout with the selected variant.
   - Place a COD order and confirm the correct inventory decrements.
6. Inspect `git diff --check` and review the final diff for unintended changes.

## Important implementation notes

- Do not replace stable slug-based combination keys with IDs; IDs change when a deleted option value is recreated.
- Because `ProductVariant` uses `SoftDeletes`, “delete” means remove from active catalog while retaining historical records.
- The existing worktree contains earlier user changes for Tiptap, brand handling, pricing, cart, checkout, and table layout. Preserve those changes.
- The current `ProductService` publishing guard may require adjusting any remaining tests or seeders that create and publish a variable product before generating variants.
- The browser smoke test was not completed before pausing.
