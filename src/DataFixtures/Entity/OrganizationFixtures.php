<?php

declare(strict_types=1);

namespace App\DataFixtures\Entity;

use App\Entity\Agent;
use App\Entity\Organization;
use App\Enum\OrganizationTypeEnum;
use App\Enum\SocialNetworkEnum;
use App\Service\Interface\FileServiceInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class OrganizationFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public const string ORGANIZATION_ID_PREFIX = 'organization';
    public const string ORGANIZATION_ID_1 = 'bc89ea8d-6ad7-4cb8-92a9-b56ce203c7dd';
    public const string ORGANIZATION_ID_2 = 'a65aa657-c537-1f33-c06e-31c2e219136e';
    public const string ORGANIZATION_ID_3 = 'd12ead05-ef32-157a-c59e-4a83147ed9ec';
    public const string ORGANIZATION_ID_4 = 'd68da96e-a834-1bb1-cb3d-5ac2c2dbae7b';
    public const string ORGANIZATION_ID_5 = 'd430ade5-7f3d-1817-cae0-7152674ade73';
    public const string ORGANIZATION_ID_6 = '5d85a939-263f-19b5-c912-7825967271a4';
    public const string ORGANIZATION_ID_7 = '26c2aaf2-bf38-11d9-c036-7d6b4e56c350';
    public const string ORGANIZATION_ID_8 = '7241a715-453a-12db-c707-725dc3ab988c';
    public const string ORGANIZATION_ID_9 = '7cb6a1b8-f33e-1218-cb41-820b0f74e4d1';
    public const string ORGANIZATION_ID_10 = '8c4ca8bd-6e33-1b62-c58b-a66969c49f66';
    public const string ORGANIZATION_ID_11 = '8c4ca8bd-6e33-1b62-c58b-a66969c49f77';

    public const array ORGANIZATIONS = [
        [
            'id' => self::ORGANIZATION_ID_1,
            'name' => 'Fortaleza',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Municipio de Fortaleza, Capital do Ceará',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-01',
                'email' => 'fortaleza@example.com',
                'phone' => '(85) 99999-0001',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Fortaleza',
                'site' => 'https://www.fortaleza.ce.gov.br',
                'cityId' => '97847c18-ac1c-4a00-93d4-b4a3e72a262c',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'fortaleza_ce',
            ],
            'createdAt' => '2024-07-10T11:30:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_2,
            'name' => 'São Gonçalo do Amarante',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'São Gonçalo do Amarante, no Ceará',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
                AgentFixtures::AGENT_ID_2,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-02',
                'email' => 'sga@example.com',
                'phone' => '(85) 99999-0002',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social SGA',
                'site' => 'https://www.sga.ce.gov.br',
                'cityId' => '52b2be97-63ce-4887-bb6f-cf84186de797',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'saogonacalo_amarante_ce',
            ],
            'createdAt' => '2024-07-11T10:49:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_3,
            'name' => 'Alto Santo',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Município de Alto Santo, no Ceará',
            'createdBy' => AgentFixtures::AGENT_ID_3,
            'owner' => AgentFixtures::AGENT_ID_3,
            'agents' => [
                AgentFixtures::AGENT_ID_3,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-03',
                'email' => 'altosanto@example.com',
                'phone' => '(85) 99999-0003',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Alto Santo',
                'site' => 'https://www.altosanto.ce.gov.br',
                'cityId' => 'b2ba1218-d357-4152-8621-45d629436ce1',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'devsdosertao',
            ],
            'createdAt' => '2024-07-16T17:22:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_4,
            'name' => 'Recife',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Recife, Pernambuco',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-04',
                'email' => 'recife@example.com',
                'phone' => '(85) 99999-0004',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Recife',
                'site' => 'https://www.recife.pe.gov.br',
                'cityId' => 'a97c7beb-9476-4347-ad2a-b60aaa58abd5',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'sertaodev',
            ],
            'createdAt' => '2024-07-17T15:12:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_5,
            'name' => 'Parambu',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Parambu-CE',
            'createdBy' => AgentFixtures::AGENT_ID_3,
            'owner' => AgentFixtures::AGENT_ID_3,
            'agents' => [
                AgentFixtures::AGENT_ID_3,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-05',
                'email' => 'parambu@example.com',
                'phone' => '(85) 99999-0005',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Parambu',
                'site' => 'https://www.parambu.ce.gov.br',
                'cityId' => 'a0c15b26-a166-4edc-b16a-62df904a51ad',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'grupoderapente',
            ],
            'createdAt' => '2024-07-22T16:20:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_6,
            'name' => 'Russas-CE',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Cidade de Russas, Municipio no Ceará',
            'createdBy' => AgentFixtures::AGENT_ID_2,
            'owner' => AgentFixtures::AGENT_ID_2,
            'agents' => [
                AgentFixtures::AGENT_ID_2,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-06',
                'email' => 'russas@example.com',
                'phone' => '(85) 99999-0006',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Russas',
                'site' => 'https://www.russas.ce.gov.br',
                'cityId' => '7b5eec8d-1f95-44a5-93a9-2fbac8c2c16c',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'comunidadevidacomcristo',
            ],
            'createdAt' => '2024-08-10T11:26:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_7,
            'name' => 'Mamanguape-PB',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Municipio de Mamanguape-PB, não confundir com Maranguape-CE',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-07',
                'email' => 'mamanguape@example.com',
                'phone' => '(85) 99999-0007',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Mamanguape',
                'site' => 'https://www.mamanguape.pb.gov.br',
                'cityId' => '7a68955d-5aa3-4c44-9f43-8328562f4e75',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'candombleraizesdobrasil',
            ],
            'createdAt' => '2024-08-11T15:54:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_8,
            'name' => 'Maranguape',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Municipio de Maranguape-CE',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-08',
                'email' => 'maranguape@example.com',
                'phone' => '(85) 99999-0008',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Maranguape',
                'site' => 'https://www.maranguape.ce.gov.br',
                'cityId' => '705e1340-8bf8-47db-ba51-4584c7cd9662',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'baiaodedev',
            ],
            'createdAt' => '2024-08-12T14:24:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_9,
            'name' => 'Arneiroz',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Municipio de Arneiros, no sertão cearense',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => self::ORGANIZATION_ID_8,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-09',
                'email' => 'arneiroz@example.com',
                'phone' => '(85) 99999-0009',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Arneiroz',
                'site' => 'https://www.arneiroz.ce.gov.br',
                'cityId' => '984ef221-73f4-422e-8aaa-28e290559e15',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'phpeste',
            ],
            'createdAt' => '2024-08-13T20:25:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_10,
            'name' => 'Indaiatuba',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Municipio de Indaiatuba-SP',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => self::ORGANIZATION_ID_9,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-10',
                'email' => 'indaiatuba@example.com',
                'phone' => '(85) 99999-0010',
                'tipo' => 'OSC',
                'companyName' => 'Razão Social Indaiatuba',
                'site' => 'https://www.indaiatuba.sp.gov.br',
                'cityId' => 'ee246666-c1a9-4880-872a-3299b094bc06',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'forrotonemveno',
            ],
            'createdAt' => '2024-08-14T10:00:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
        [
            'id' => self::ORGANIZATION_ID_11,
            'name' => 'Empresa Teste AI',
            'image' => null,
            'type' => OrganizationTypeEnum::EMPRESA->value,
            'description' => 'Organização do tipo EMPRESA para testes do painel',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => null,
            'space' => null,
            'extraFields' => [
                'cnpj' => '00.000.000/0001-00',
                'email' => 'teste@gmail.com,',
                'telefone' => '(85) 99999-9999',
                'tipo' => 'OSC',
                'site' => 'https://www.empresa.com.br',
            ],
            'socialNetworks' => [
                SocialNetworkEnum::INSTAGRAM->value => 'empresa_ai_test',
            ],
            'createdAt' => '2024-08-20T09:00:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ],
    ];

    public const array ORGANIZATIONS_UPDATED = [
        [
            'id' => self::ORGANIZATION_ID_1,
            'name' => 'Fortaleza',
            'image' => null,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
            'description' => 'Municipio de Fortaleza, Capital do Ceará',
            'createdBy' => AgentFixtures::AGENT_ID_1,
            'owner' => AgentFixtures::AGENT_ID_1,
            'agents' => [
                AgentFixtures::AGENT_ID_1,
            ],
            'parent' => null,
            'space' => null,
            'createdAt' => '2024-07-10T11:30:00+00:00',
            'updatedAt' => '2024-07-10T12:20:00+00:00',
            'deletedAt' => null,
        ],
    ];

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TokenStorageInterface $tokenStorage,
        private readonly SerializerInterface $serializer,
        private readonly FileServiceInterface $fileService,
        private readonly ParameterBagInterface $parameterBag,
    ) {
        parent::__construct($entityManager, $tokenStorage);
    }

    public function getDependencies(): array
    {
        return [
            AgentFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->createOrganizations($manager);
        $this->updateOrganizations($manager);
        $this->manualLogout();
    }

    private function mountOrganization(array $organizationData, array $context = []): Organization
    {
        $agents = $organizationData['agents'] ?? [];
        unset($organizationData['agents']);

        /** @var Organization $organization */
        $organization = $this->serializer->denormalize($organizationData, Organization::class, context: $context);

        foreach ($agents ?? [] as $agentId) {
            $organization->addAgent(
                $this->getReference(sprintf('%s-%s', AgentFixtures::AGENT_ID_PREFIX, $agentId), Agent::class)
            );
        }

        $organization->setCreatedBy($this->getReference(sprintf('%s-%s', AgentFixtures::AGENT_ID_PREFIX, $organizationData['createdBy']), Agent::class));
        $organization->setOwner($this->getReference(sprintf('%s-%s', AgentFixtures::AGENT_ID_PREFIX, $organizationData['owner']), Agent::class));

        return $organization;
    }

    private function createOrganizations(ObjectManager $manager): void
    {
        $counter = 0;

        foreach (self::ORGANIZATIONS as $organizationData) {
            if (5 > $counter) {
                $file = $this->fileService->uploadImage($this->parameterBag->get('app.dir.organization.profile'), ImageFixtures::getOrganizationImage());
                $organizationData['image'] = $file;
            }

            $organization = $this->mountOrganization($organizationData);

            $this->setReference(sprintf('%s-%s', self::ORGANIZATION_ID_PREFIX, $organizationData['id']), $organization);

            $this->manualLoginByAgent($organizationData['createdBy']);

            $manager->persist($organization);
            $counter++;
        }

        $manager->flush();
    }

    public function updateOrganizations(ObjectManager $manager): void
    {
        foreach (self::ORGANIZATIONS_UPDATED as $organizationData) {
            $organizationObj = $this->getReference(sprintf('%s-%s', self::ORGANIZATION_ID_PREFIX, $organizationData['id']), Organization::class);

            $organization = $this->mountOrganization($organizationData, ['object_to_populate' => $organizationObj]);

            $this->manualLoginByAgent($organizationData['createdBy']);

            $manager->persist($organization);
        }

        $manager->flush();
    }
}
