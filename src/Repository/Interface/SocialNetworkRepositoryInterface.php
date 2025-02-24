<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\SocialNetwork;

interface SocialNetworkRepositoryInterface
{
    public function save(SocialNetwork $socialNetwork): SocialNetwork;
}
