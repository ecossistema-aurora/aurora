<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\LinkEntity;

interface LinkEntityRepositoryInterface
{
    public function save(LinkEntity $linkEntity): LinkEntity;
}
