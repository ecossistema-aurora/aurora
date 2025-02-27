<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Space;
use App\Entity\SpaceLinkEntity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class SpaceLinkEntityTest extends TestCase
{
    public function testGetters(): void
    {
        $spaceLinkEntity = new SpaceLinkEntity();

        $id = Uuid::v4();
        $spaceId = Uuid::v4();

        $space = new Space();
        $space->setId($spaceId);

        $spaceLinkEntity->setId($id);
        $spaceLinkEntity->setEntity($space);
        $spaceLinkEntity->setAgent(true);
        $spaceLinkEntity->setEvent(true);
        $spaceLinkEntity->setInitiative(true);

        $this->assertSame($id->toRfc4122(), $spaceLinkEntity->getId()->toRfc4122());
        $this->assertSame($spaceId->toRfc4122(), $spaceLinkEntity->getEntity()->getId()->toRfc4122());
        $this->assertSame(true, $spaceLinkEntity->canLinkWithAgent());
        $this->assertSame(true, $spaceLinkEntity->canLinkWithEvent());
        $this->assertSame(true, $spaceLinkEntity->canLinkWithInitiative());
        $this->assertSame(false, $spaceLinkEntity->canLinkWithSpace());
        $this->assertSame(false, $spaceLinkEntity->canLinkWithOpportunity());
        $this->assertSame(false, $spaceLinkEntity->canLinkWithOrganization());
    }

    public function testToArray(): void
    {
        $spaceLinkEntity = new SpaceLinkEntity();

        $id = Uuid::v4();
        $spaceId = Uuid::v4();

        $space = new Space();
        $space->setId($spaceId);

        $spaceLinkEntity->setId($id);
        $spaceLinkEntity->setEntity($space);
        $spaceLinkEntity->setAgent(true);
        $spaceLinkEntity->setEvent(true);
        $spaceLinkEntity->setInitiative(true);

        $expectedArray = [
            'id' => $id->toRfc4122(),
            'entity' => $space->getId()->toRfc4122(),
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => false,
            'opportunity' => false,
            'organization' => false,
        ];
        $actualArray = $spaceLinkEntity->toArray();

        $this->assertEquals($expectedArray, $actualArray);
    }
}
