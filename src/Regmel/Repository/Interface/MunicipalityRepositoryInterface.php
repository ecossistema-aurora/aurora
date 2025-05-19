<?php

declare(strict_types=1);

namespace App\Regmel\Repository\Interface;

use App\Entity\Organization;
use App\Enum\StatusProposalEnum;

interface MunicipalityRepositoryInterface
{
    public function updateProposals(Organization $organization, StatusProposalEnum $statusFrom, StatusProposalEnum $statusTo): void;
}
