<?php

declare(strict_types=1);

namespace App\Enum;

use App\Enum\Trait\EnumTrait;

enum AgeRatingEnum: string
{
    use EnumTrait;

    case FREE = 'Livre';
    case GREATER_THAN_10 = 'Para maiores de 10 anos';
    case GREATER_THAN_12 = 'Para maiores de 12 anos';
    case GREATER_THAN_14 = 'Para maiores de 14 anos';
    case GREATER_THAN_16 = 'Para maiores de 16 anos';
    case GREATER_THAN_18 = 'Para maiores de 18 anos';
}
