<?php

namespace App\Enums;

enum MenuLocation: string
{
    case HeaderPrimary = 'header_primary';
    case HeaderSecondary = 'header_secondary';
    case Mobile = 'mobile';
    case FooterShop = 'footer_shop';
    case FooterHelp = 'footer_help';
    case FooterCompany = 'footer_company';
    case FooterLegal = 'footer_legal';
}
