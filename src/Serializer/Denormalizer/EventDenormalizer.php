<?php

declare(strict_types=1);

namespace App\Serializer\Denormalizer;

use App\Entity\ActivityArea;
use App\Entity\Agent;
use App\Entity\Event;
use App\Entity\Initiative;
use App\Entity\Space;
use App\Entity\Tag;
use App\Service\Interface\FileServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class EventDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'serializer.normalizer.object')]
        private DenormalizerInterface $denormalizer,
        private FileServiceInterface $fileService,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (false === is_array($data)) {
            return $this->denormalizer->denormalize(['id' => $data], $type, $format, $context);
        }

        if (Event::class !== $type) {
            return $data;
        }

        if (true === array_key_exists('image', $data)) {
            $this->uploadImage($data, $context['object_to_populate'] ?? null);
        }

        /** @var Event $event */
        $event = $this->denormalizer->denormalize($data, Event::class, $format, $context);

        if (isset($data['agentGroup'])) {
            $data['agentGroup'] = $this->entityManager->getRepository(Agent::class)->find($data['agentGroup']);
            $event->setAgentGroup($data['agentGroup']);
        }

        if (isset($data['space'])) {
            $data['space'] = $this->entityManager->getRepository(Space::class)->find($data['space']);
            $event->setSpace($data['space']);
        }

        if (isset($data['initiative'])) {
            $data['initiative'] = $this->entityManager->getRepository(Initiative::class)->find($data['initiative']);
            $event->setInitiative($data['initiative']);
        }

        if (isset($data['parent'])) {
            $data['parent'] = $this->entityManager->getRepository(Event::class)->find($data['parent']);
            $event->setParent($data['parent']);
        }

        if (isset($data['createdBy'])) {
            $data['createdBy'] = $this->entityManager->getRepository(Agent::class)->find($data['createdBy']);
            $event->setCreatedBy($data['createdBy']);
        }

        if (true === array_key_exists('activityAreas', $data)) {
            $activityAreas = array_map(
                fn (string $id) => $this->entityManager->find(ActivityArea::class, $id),
                $data['activityAreas']
            );

            $event->setActivityAreas(new ArrayCollection($activityAreas));
        }

        if (true === array_key_exists('tags', $data)) {
            $tags = array_map(
                fn (string $id) => $this->entityManager->find(Tag::class, $id),
                $data['tags']
            );

            $event->setTags(new ArrayCollection($tags));
        }

        return $event;
    }

    private function uploadImage(array &$data, ?Event $eventFromDb = null): void
    {
        if (false === is_null($eventFromDb) && true === is_string($eventFromDb->getImage())) {
            $this->fileService->deleteFileByUrl($eventFromDb->getImage());
        }

        if ($data['image'] instanceof File) {
            $data['image'] = $this->fileService->getFileUrl($data['image']->getPathname());
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Event::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => null,
            '*' => false,
            Event::class => true,
        ];
    }
}
