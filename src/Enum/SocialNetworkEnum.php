<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\EnumTrait;

enum SocialNetworkEnum: int
{
    use EnumTrait;

    case FACEBOOK = 1;
    case INSTAGRAM = 2;
    case LINKEDIN = 3;
    case PINTEREST = 4;
    case SPOTIFY = 5;
    case VIMEO = 6;
    case TIKTOK = 7;
    case X = 8;
    case YOUTUBE = 9;
}
