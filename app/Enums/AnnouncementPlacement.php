<?php

namespace App\Enums;

enum AnnouncementPlacement: string
{
    case TopBar = 'top_bar';
    case Storefront = 'storefront';
    case Checkout = 'checkout';
    case Account = 'account';
}
