<?php

declare(strict_types=1);

namespace App\Regmel\Service\Interface;

use App\Entity\Organization;
use App\Enum\StatusProposalEnum;

interface MunicipalityServiceInterface
{
    public function getProposals(Organization $municipality): array;

    public function updateProposals(Organization $municipality, StatusProposalEnum $statusFrom, StatusProposalEnum $statusTo): void;
}
