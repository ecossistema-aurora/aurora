<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Space;

interface SpaceRepositoryInterface
{
    public function save(Space $space): Space;

    public function findByNameAndLinkableEntityType(string $name, string $linkEntity, int $limit): array;
}
