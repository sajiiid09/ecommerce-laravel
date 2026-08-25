<?php

namespace App\Enums;

enum AnnouncementStyle: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
}
