<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SpaceLinkEntity extends LinkEntity
{
    #[ORM\OneToOne(targetEntity: Space::class, inversedBy: 'linkEntity')]
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    private Space $entity;

    public function getEntity(): Space
    {
        return $this->entity;
    }

    public function setEntity(Space $entity): void
    {
        $this->entity = $entity;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId()->toRfc4122(),
            'entity' => $this->entity->getId()->toRfc4122(),
            'agent' => $this->isAgent(),
            'event' => $this->isEvent(),
            'initiative' => $this->isInitiative(),
            'space' => $this->isSpace(),
            'opportunity' => $this->isOpportunity(),
            'organization' => $this->isOrganization(),
        ];
    }
}
