<?php
namespace App\Enums;
enum ProductVisibility: string { case Visible = 'visible'; case CatalogSearch = 'catalog_search'; case CatalogOnly = 'catalog_only'; case SearchOnly = 'search_only'; case Hidden = 'hidden'; }
