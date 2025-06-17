<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use App\Entity\InscriptionEvent;
use App\Repository\Interface\EventRepositoryInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends AbstractRepository implements EventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $event): Event
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();

        return $event;
    }

    public function findByAgent(string $agentId): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('e')
            ->from(Event::class, 'e')
            ->join(InscriptionEvent::class, 'ie', 'WITH', 'ie.event = e.id')
            ->where('ie.agent = :agentId')
            ->setParameter('agentId', $agentId)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByFilters(array $filters, array $orderBy, int $limit): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.createdAt', $orderBy['createdAt'])
            ->setMaxResults($limit);

        $this->applyFilters($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        $filterMappings = $this->getFilterMappings();

        foreach ($filters as $key => $value) {
            $map = $filterMappings[$key];

            if (true === isset($map['join'])) {
                $joins = is_array($map['join'][0]) ? $map['join'] : [$map['join']];
                foreach ($joins as $join) {
                    $qb->join($join[0], $join[1]);
                }
            }

            $map['condition']($qb, $value);
        }
    }

    private function getFilterMappings(): array
    {
        return [
            'name' => [
                'condition' => fn ($qb, $value) => $qb->andWhere('e.name LIKE :name')->setParameter('name', "%$value%"),
            ],
            'draft' => [
                'condition' => fn ($qb, $value) => $qb->andWhere('e.draft = :draft')->setParameter('draft', $value, ParameterType::BOOLEAN),
            ],
        ];
    }
}
