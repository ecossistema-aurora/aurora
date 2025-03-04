<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\LinkEntity;
use App\Repository\Interface\LinkEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class LinkEntityRepository extends AbstractRepository implements LinkEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkEntity::class);
    }

    public function save(LinkEntity $linkEntity): LinkEntity
    {
        $this->getEntityManager()->persist($linkEntity);
        $this->getEntityManager()->flush();

        return $linkEntity;
    }
}
