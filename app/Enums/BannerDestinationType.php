<?php

namespace App\Enums;

enum BannerDestinationType: string
{
    case Url = 'url';
    case Page = 'page';
    case Category = 'category';
    case Brand = 'brand';
    case Product = 'product';
}
