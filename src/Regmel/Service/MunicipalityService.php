<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\Entity\Organization;
use App\Enum\StatusProposalEnum;
use App\Regmel\Repository\Interface\MunicipalityRepositoryInterface;
use App\Regmel\Service\Interface\MunicipalityServiceInterface;
use App\Repository\InitiativeRepository;

readonly class MunicipalityService implements MunicipalityServiceInterface
{
    public function __construct(
        private InitiativeRepository $initiativeRepository,
        private MunicipalityRepositoryInterface $municipalityRepository,
    ) {
    }

    public function getProposals(Organization $municipality): array
    {
        return $this->initiativeRepository->findBy(['organizationTo' => $municipality->getId()]);
    }

    public function updateProposals(Organization $municipality, StatusProposalEnum $statusFrom, StatusProposalEnum $statusTo): void
    {
        $this->municipalityRepository->updateProposals($municipality, $statusFrom, $statusTo);
    }
}
