<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use App\Entity\InscriptionEvent;
use App\Repository\Interface\EventRepositoryInterface;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends AbstractRepository implements EventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByFilters(array $filters, array $order = [], int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('e');

        $this->applyFilters($qb, $filters);

        foreach ($order as $key => $value) {
            $qb->addOrderBy("e.$key", $value);
        }

        return $qb
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
                    if (!in_array($join[1], $qb->getAllAliases(), true)) {
                        $qb->leftJoin($join[0], $join[1]);
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
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('lower(e.name) LIKE lower(:name)')->setParameter('name', "%{$value}%"),
            ],
            'draft' => [
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('e.draft = :draft')->setParameter('draft', $value, Types::BOOLEAN),
            ],
            'deletedAt' => [
                'condition' => fn (QueryBuilder $qb, $value) => null === $value
                    ? $qb->andWhere('e.deletedAt IS NULL')
                    : $qb->andWhere('e.deletedAt = :deletedAt')->setParameter('deletedAt', $value),
            ],
            'culturalLanguages' => [
                'join' => ['e.culturalLanguages', 'cl'],
                'condition' => fn (QueryBuilder $qb, $value) => $qb->andWhere('cl.id = :culturalLanguageId')->setParameter('culturalLanguageId', $value),
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
                'join' => ['e.eventSchedules', 'es'],
                'condition' => fn (QueryBuilder $qb, $value) => $this->applyPeriodFilter($qb, $value),
            ],
        ];
    }

    private function applyPeriodFilter(QueryBuilder $qb, mixed $period): void
    {
        if (!is_array($period) || empty($period['start']) || empty($period['end'])) {
            return;
        }

        $qb->andWhere('es.startHour BETWEEN :startPeriod AND :endPeriod')
            ->setParameter('startPeriod', new DateTimeImmutable($period['start']), Types::DATETIME_IMMUTABLE)
            ->setParameter('endPeriod', new DateTimeImmutable($period['end']), Types::DATETIME_IMMUTABLE);
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
}
