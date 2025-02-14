<?php

declare(strict_types=1);

namespace App\DataFixtures\Entity;

use App\Entity\LinkEntity;
use App\Entity\SpaceLinkEntity;
use App\Enum\EntityEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class SpaceLinkEntityFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public const string LINK_ENTITY_ID_PREFIX = 'link-entity';
    public const string LINK_ENTITY_ID_1 = 'b982c6f3-d792-4707-b580-8bc95eb02d35';
    public const string LINK_ENTITY_ID_2 = '492488c2-36fc-4ce8-bd1e-033b772128fc';
    public const string LINK_ENTITY_ID_3 = '6ddd2e47-c8dc-4e54-be56-079890f5ee8d';
    public const string LINK_ENTITY_ID_4 = '04575db7-1169-4fa7-8b97-84f5c8b638c1';
    public const string LINK_ENTITY_ID_5 = '36766110-bfcf-47bc-85fd-9c27e5f0781b';
    public const string LINK_ENTITY_ID_6 = '440e12cd-43bb-4922-aa93-96b9bfa1c2bc';
    public const string LINK_ENTITY_ID_7 = '8b595aa4-149d-4b18-a311-241c548857fd';
    public const string LINK_ENTITY_ID_8 = 'b2c1e24c-8df2-41a1-94ad-c4aa742110ad';
    public const string LINK_ENTITY_ID_9 = 'ab5bae95-b12e-4de2-b64d-eba9c419bb13';
    public const string LINK_ENTITY_ID_10 = 'bdefc6cc-b965-4714-bff8-aa1fd6c8e6cb';

    public const array SPACE_LINKS_ENTITIES = [
        [
            'id' => self::LINK_ENTITY_ID_1,
            'entity' => SpaceFixtures::SPACE_ID_1,
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => true,
            'opportunity' => true,
            'organization' => true,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_2,
            'entity' => SpaceFixtures::SPACE_ID_2,
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => true,
            'opportunity' => true,
            'organization' => true,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_3,
            'entity' => SpaceFixtures::SPACE_ID_3,
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => true,
            'opportunity' => true,
            'organization' => true,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_2,
        ],
        [
            'id' => self::LINK_ENTITY_ID_4,
            'entity' => SpaceFixtures::SPACE_ID_4,
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => true,
            'opportunity' => true,
            'organization' => true,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_5,
            'entity' => SpaceFixtures::SPACE_ID_5,
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => true,
            'opportunity' => true,
            'organization' => true,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_6,
            'entity' => SpaceFixtures::SPACE_ID_6,
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => true,
            'opportunity' => true,
            'organization' => true,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_7,
            'entity' => SpaceFixtures::SPACE_ID_7,
            'agent' => true,
            'event' => true,
            'initiative' => true,
            'space' => true,
            'opportunity' => true,
            'organization' => true,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_8,
            'entity' => SpaceFixtures::SPACE_ID_8,
            'agent' => false,
            'event' => false,
            'initiative' => false,
            'space' => false,
            'opportunity' => false,
            'organization' => false,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_9,
            'entity' => SpaceFixtures::SPACE_ID_9,
            'agent' => false,
            'event' => false,
            'initiative' => false,
            'space' => false,
            'opportunity' => false,
            'organization' => false,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
        [
            'id' => self::LINK_ENTITY_ID_10,
            'entity' => SpaceFixtures::SPACE_ID_10,
            'agent' => false,
            'event' => false,
            'initiative' => false,
            'space' => false,
            'opportunity' => false,
            'organization' => false,
            'entityType' => EntityEnum::SPACE->value,
            'createdBy' => AgentFixtures::AGENT_ID_1,
        ],
    ];

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TokenStorageInterface $tokenStorage,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct($entityManager, $tokenStorage);
    }

    public function getDependencies(): array
    {
        return [
            SpaceFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->truncateTable(LinkEntity::class);
        $this->createLinksEntities($manager);
        $this->manualLogout();
    }

    private function createLinksEntities(ObjectManager $manager): void
    {
        foreach (self::SPACE_LINKS_ENTITIES as $spaceLinkEntityData) {
            $space = $this->getReference(sprintf('%s-%s', SpaceFixtures::SPACE_ID_PREFIX, $spaceLinkEntityData['entity']));

            /* @var SpaceLinkEntity $spaceLinkEntity */
            $spaceLinkEntity = $this->serializer->denormalize($spaceLinkEntityData, SpaceLinkEntity::class);
            $spaceLinkEntity->setEntity($space);

            $this->setReference(sprintf('%s-%s', self::LINK_ENTITY_ID_PREFIX, $spaceLinkEntityData['id']), $spaceLinkEntity);

            $this->manualLoginByAgent($spaceLinkEntityData['createdBy']);

            $manager->persist($spaceLinkEntity);
        }

        $manager->flush();
    }
}
