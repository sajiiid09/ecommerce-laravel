<?php

namespace App\Enums;

enum BannerPlacement: string
{
    case Homepage = 'homepage';
    case Category = 'category';
    case Offers = 'offers';
    case Checkout = 'checkout';
}
