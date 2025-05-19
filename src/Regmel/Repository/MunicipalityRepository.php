<?php

declare(strict_types=1);

namespace App\Regmel\Repository;

use App\Entity\Organization;
use App\Enum\StatusProposalEnum;
use App\Regmel\Repository\Interface\MunicipalityRepositoryInterface;
use App\Repository\OrganizationRepository;

class MunicipalityRepository extends OrganizationRepository implements MunicipalityRepositoryInterface
{
    public function updateProposals(Organization $organization, StatusProposalEnum $statusFrom, StatusProposalEnum $statusTo): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE initiative 
            SET 
                organization_to_id = :organizationId::uuid,
                extra_fields = jsonb_set(
                    extra_fields::jsonb, 
                    '{status}', 
                    to_jsonb(:statusTo::text)
                )
            WHERE 
                extra_fields->>'city_id' = :cityId
                AND extra_fields->>'status' = :statusFrom";

        $conn->executeStatement($sql, [
            'organizationId' => $organization->getId()->toRfc4122(),
            'cityId' => $organization->getExtraFields()['cityId'],
            'statusFrom' => $statusFrom->value,
            'statusTo' => $statusTo->value,
        ]);
    }
}
