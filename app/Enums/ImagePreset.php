<?php

namespace App\Enums;

enum ImagePreset: string
{
    case Product = 'product';

    case Logo = 'logo';

    case Banner = 'banner';

    case Category = 'category';

    case General = 'general';

    case Favicon = 'favicon';

    public function maxWidth(): int
    {
        return $this === self::Favicon ? 64 : ($this === self::Product ? 1200 : 1600);
    }

    public function maxHeight(): int
    {
        return $this === self::Favicon ? 64 : ($this === self::Product ? 1200 : 1600);
    }

    public function quality(): int
    {
        return 82;
    }

    public function cropsToSquare(): bool
    {
        return $this === self::Favicon;
    }
}
