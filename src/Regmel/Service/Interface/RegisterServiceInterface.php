<?php

declare(strict_types=1);

namespace App\Regmel\Service\Interface;

use App\Entity\Opportunity;
use App\Entity\Organization;
use App\Entity\User;
use App\Enum\OrganizationTypeEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface RegisterServiceInterface
{
    public function saveOrganization(array $data, ?UploadedFile $uploadedFile = null): Organization;

    public function findOpportunitiesBy(OrganizationTypeEnum $enum): array;

    public function saveUser(array $data): User;

    public function resendTerm(string $organizationId, ?UploadedFile $uploadedFile);

    public function findOpportunityWithActivePhase(string $organizationType): Opportunity|bool;
}
