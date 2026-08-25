<?php

namespace App\Enums;

enum PageType: string
{
    case Standard = 'standard';
    case Landing = 'landing';
    case Contact = 'contact';
    case Legal = 'legal';
}
