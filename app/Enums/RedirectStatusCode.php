<?php

namespace App\Enums;

enum RedirectStatusCode: int
{
    case MovedPermanently = 301;
    case Found = 302;
    case Temporary = 307;
    case Permanent = 308;
}
