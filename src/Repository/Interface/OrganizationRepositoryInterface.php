<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Organization;

interface OrganizationRepositoryInterface
{
    public function save(Organization $organization): Organization;

    public function findMunicipalitiesByAgents(iterable $agents): array;

    public function findOrganizationByRegionAndState(string $region, string $state): array;

    public function findOrganizationByCompanyFilters(string $tipo): array;
}
