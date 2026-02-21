<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use App\Entity\InscriptionEvent;
use App\Repository\Interface\EventRepositoryInterface;
use DateTime;
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
            ->orderBy('e.'.key($orderBy), current($orderBy))
            ->setMaxResults($limit);

        $this->applyFilters($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        $filterMappings = $this->getFilterMappings();

        foreach ($filters as $key => $value) {
            if (!isset($filterMappings[$key])) {
                continue;
            }

            $map = $filterMappings[$key];

            if (isset($map['join'])) {
                $joins = is_array($map['join'][0]) ? $map['join'] : [$map['join']];

                foreach ($joins as $join) {
                    if (!in_array($join[1], $qb->getAllAliases())) {
                        $qb->join($join[0], $join[1]);
                    }
                }
            }

            $map['condition']($qb, $value);
        }
    }

    private function getFilterMappings(): array
    {
        return [
            'name' => [
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('e.name LIKE :name')->setParameter('name', "%{$value}%"),
            ],
            'draft' => [
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('e.draft = :draft')->setParameter('draft', $value, ParameterType::BOOLEAN),
            ],
            'culturalLanguages' => [
                'join' => ['e.culturalLanguages', 'cl'],
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('cl.id = :languageId')->setParameter('languageId', $value),
            ],
            'tags' => [
                'join' => ['e.tags', 't'],
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('t.id = :tagId')->setParameter('tagId', $value),
            ],
            'state' => [
                'join' => [['e.space', 'sp'], ['sp.address', 'a'], ['a.city', 'c'], ['c.state', 'st']],
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('st.id = :stateId')->setParameter('stateId', $value),
            ],
            'city' => [
                'join' => [['e.space', 'sp'], ['sp.address', 'a'], ['a.city', 'c']],
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('c.id = :cityId')->setParameter('cityId', $value),
            ],
            'ageRating' => [
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere("JSON_GET_TEXT(e.extraFields, 'ageRating') = :ageRating")
                    ->setParameter('ageRating', (string) $value),
            ],
            'period' => [
                'condition' => fn (QueryBuilder $qb, $value) => $this->applyPeriodFilter($qb, $value),
            ],
        ];
    }

    public function countOpenedEvents(): int
    {
        $now = new DateTime();

        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.deletedAt IS NULL')
            ->andWhere('e.draft = false')
            ->andWhere('e.startDate <= :now')
            ->andWhere('COALESCE(e.endDate, e.startDate) >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countFinishedEvents(): int
    {
        $now = new DateTime();

        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.deletedAt IS NULL')
            ->andWhere('e.draft = false')
            ->andWhere('COALESCE(e.endDate, e.startDate) < :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function applyPeriodFilter(QueryBuilder $qb, mixed $period): void
    {
        if (!is_array($period)) {
            return;
        }

        if (!empty($period['start']) && !empty($period['end'])) {
            $start = new DateTime($period['start']);
            $end = new DateTime($period['end']);

            $qb->andWhere('COALESCE(e.endDate, e.startDate) >= :periodStart')
                ->andWhere('e.startDate <= :periodEnd')
                ->setParameter('periodStart', $start)
                ->setParameter('periodEnd', $end);
        }
    }
}
