<?php

return [
    'catalog_import_export' => (bool) env('FEATURE_CATALOG_IMPORT_EXPORT', false),
    'marketing' => (bool) env('FEATURE_MARKETING', false),
    'advanced_seo' => (bool) env('FEATURE_ADVANCED_SEO', false),
    'advanced_inventory_history' => (bool) env('FEATURE_ADVANCED_INVENTORY_HISTORY', false),
    'reviews' => (bool) env('FEATURE_REVIEWS', true),
    'guest_checkout' => (bool) env('FEATURE_GUEST_CHECKOUT', true),
];
