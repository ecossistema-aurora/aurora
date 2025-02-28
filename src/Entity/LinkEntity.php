<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\EntityEnum;
use App\Repository\LinkEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: LinkEntityRepository::class)]
#[ORM\Table(name: 'link_entity')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'entity_type', type: Types::INTEGER)]
#[ORM\DiscriminatorMap([
    EntityEnum::SPACE->value => SpaceLinkEntity::class,
])]
abstract class LinkEntity extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    private ?Uuid $id = null;

    private ?Space $entity = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['space.get'])]
    private bool $agent = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['space.get'])]
    private bool $event = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['space.get'])]
    private bool $initiative = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['space.get'])]
    private bool $space = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['space.get'])]
    private bool $opportunity = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['space.get'])]
    private bool $organization = false;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(?Uuid $id): void
    {
        $this->id = $id;
    }

    public function canLinkWithOrganization(): bool
    {
        return $this->organization;
    }

    public function setOrganization(bool $organization): void
    {
        $this->organization = $organization;
    }

    public function canLinkWithOpportunity(): bool
    {
        return $this->opportunity;
    }

    public function setOpportunity(bool $opportunity): void
    {
        $this->opportunity = $opportunity;
    }

    public function canLinkWithSpace(): bool
    {
        return $this->space;
    }

    public function setSpace(bool $space): void
    {
        $this->space = $space;
    }

    public function canLinkWithInitiative(): bool
    {
        return $this->initiative;
    }

    public function setInitiative(bool $initiative): void
    {
        $this->initiative = $initiative;
    }

    public function canLinkWithEvent(): bool
    {
        return $this->event;
    }

    public function setEvent(bool $event): void
    {
        $this->event = $event;
    }

    public function canLinkWithAgent(): bool
    {
        return $this->agent;
    }

    public function setAgent(bool $agent): void
    {
        $this->agent = $agent;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'entity' => $this->entity->getId()->toRfc4122(),
            'agent' => $this->agent,
            'event' => $this->event,
            'initiative' => $this->initiative,
            'space' => $this->space,
            'opportunity' => $this->opportunity,
            'organization' => $this->organization,
        ];
    }
}
