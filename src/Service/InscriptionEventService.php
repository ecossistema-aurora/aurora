<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\InscriptionEventDto;
use App\Entity\InscriptionEvent;
use App\Exception\InscriptionEvent\AlreadyInscriptionEventException;
use App\Exception\InscriptionEvent\InscriptionEventResourceNotFoundException;
use App\Repository\Interface\InscriptionEventRepositoryInterface;
use App\Service\Interface\InscriptionEventServiceInterface;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class InscriptionEventService extends AbstractEntityService implements InscriptionEventServiceInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly InscriptionEventRepositoryInterface $repository,
    ) {
        parent::__construct($security, $serializer, $validator);
    }

    public function list(Uuid $eventId, int $limit = 50): array
    {
        return $this->repository->findInscriptionsByEvent($eventId->toRfc4122(), $limit);
    }

    public function get(Uuid $event, Uuid $id): InscriptionEvent
    {
        $inscriptionEvent = $this->repository->findOneInscriptionEvent($id->toRfc4122(), $event->toRfc4122());

        if (null === $inscriptionEvent) {
            throw new InscriptionEventResourceNotFoundException();
        }

        return $inscriptionEvent;
    }

    public function create(Uuid $event, array $inscriptionEvent): InscriptionEvent
    {
        $inscriptionEvent['event'] = $event->toRfc4122();

        $inscriptionEventDto = $this->validateInput($inscriptionEvent, InscriptionEventDto::class, InscriptionEventDto::CREATE);

        $inscriptionEventObj = $this->serializer->denormalize($inscriptionEventDto, InscriptionEvent::class);

        try {
            return $this->repository->save($inscriptionEventObj);
        } catch (UniqueConstraintViolationException) {
            throw new AlreadyInscriptionEventException();
        }
    }

    public function remove(Uuid $event, Uuid $id): void
    {
        $inscriptionEvent = $this->repository->findOneBy([
            'id' => $id,
            'event' => $event,
        ]);

        if (null === $inscriptionEvent) {
            throw new InscriptionEventResourceNotFoundException();
        }

        $inscriptionEvent->setDeletedAt(new DateTime());

        $this->repository->save($inscriptionEvent);
    }

    public function update(Uuid $event, Uuid $identifier, array $inscriptionEvent): InscriptionEvent
    {
        $inscriptionEvent['event'] = $event->toRfc4122();

        $inscriptionEventFromDB = $this->repository->findOneBy([
            'id' => $identifier,
            'event' => $event,
        ]);

        if (null === $inscriptionEventFromDB) {
            throw new InscriptionEventResourceNotFoundException();
        }

        $inscriptionEventDto = $this->validateInput($inscriptionEvent, InscriptionEventDto::class, InscriptionEventDto::UPDATE);

        $inscriptionEventObj = $this->serializer->denormalize($inscriptionEventDto, InscriptionEvent::class, context: [
            'object_to_populate' => $inscriptionEventFromDB,
        ]);

        $inscriptionEventObj->setUpdatedAt(new DateTime());

        return $this->repository->save($inscriptionEventObj);
    }
}
