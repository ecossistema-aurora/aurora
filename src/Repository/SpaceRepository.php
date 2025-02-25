<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Space;
use App\Repository\Interface\SpaceRepositoryInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

class SpaceRepository extends AbstractRepository implements SpaceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Space::class);
    }

    public function save(Space $space): Space
    {
        $this->getEntityManager()->persist($space);
        $this->getEntityManager()->flush();

        return $space;
    }

    public function findByNameAndLinkableEntityType(?string $name, int $codeEntityType, int $limit): array
    {
        $sql = <<<EOT
                select s.*
                from space s
                join link_entity le ON s.id = le.entity_id
                where le.entity_type = :entityType
                  and (cast(:name as varchar) is null or s.name ilike '%' || :name || '%')
                limit :limit
            EOT;

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Space::class, 's');
        $rsm->addFieldResult('s', 'id', 'id');
        $rsm->addFieldResult('s', 'name', 'name');

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);

        $query->setParameter('name', $name);
        $query->setParameter('limit', $limit);
        $query->setParameter('entityType', 4);

        return $query->getResult();
    }
}
