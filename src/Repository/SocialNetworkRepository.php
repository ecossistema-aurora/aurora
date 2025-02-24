<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SocialNetwork;
use App\Repository\Interface\SocialNetworkRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class SocialNetworkRepository extends AbstractRepository implements SocialNetworkRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialNetwork::class);
    }

    public function save(SocialNetwork $socialNetwork): SocialNetwork
    {
        $this->getEntityManager()->persist($socialNetwork);
        $this->getEntityManager()->flush();

        return $socialNetwork;
    }
}
