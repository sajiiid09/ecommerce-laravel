<?php

namespace App\Enums;

enum MenuItemType: string
{
    case CustomUrl = 'custom_url';
    case Route = 'route';
    case Page = 'page';
    case Category = 'category';
    case Brand = 'brand';
    case Product = 'product';
}
