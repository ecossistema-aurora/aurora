<?php

declare(strict_types=1);

namespace App\Regmel\Command;

use App\Entity\Agent;
use App\Entity\Opportunity;
use App\Entity\Phase;
use App\Entity\User;
use App\Enum\OrganizationTypeEnum;
use App\Enum\UserRolesEnum;
use App\Enum\UserStatusEnum;
use App\Repository\Interface\UserRepositoryInterface;
use App\Security\PasswordHasher;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Uid\Uuid;

class CreateDemoForRegmelCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepositoryInterface $userRepository,
        protected TokenStorageInterface $tokenStorage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('------------------------------------');

        $this->createOpportunities($output, $io);

        $this->createUsers($output, $io);

        $output->writeln('------------------------------------'.PHP_EOL);

        return Command::SUCCESS;
    }

    private function manualLogin(User $user): void
    {
        $token = new UsernamePasswordToken($user, 'web');
        $this->tokenStorage->setToken($token);
    }

    protected function configure(): void
    {
        $this
            ->setName('app:demo-regmel')
            ->setDescription('Run command that prepare data for regmel 2025.');
    }

    private function createUsers(OutputInterface $output, SymfonyStyle $io): void
    {
        $user = new User();
        $user->setFirstName('Manager');
        $user->setLastName('SNP');
        $user->setEmail('admin@snp.email');
        $user->setPassword(PasswordHasher::hash('Aurora@2024'));
        $user->setStatus(UserStatusEnum::ACTIVE->value);
        $user->addRole(UserRolesEnum::ROLE_MANAGER->value);
        $user->setId(Uuid::fromString('2dcc70ff-717b-4b32-8bca-396ef956e196'));

        $agent = new Agent();
        $agent->setId(Uuid::fromString('2dcc70ff-717b-4b32-8bca-396ef956e196'));
        $agent->createFromUser($user);

        $user->addAgent($agent);

        $this->entityManager->persist($user);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        $io->title('Manager User created');
        $output->writeln('User: admin@snp.email');
        $output->writeln('Pass: Aurora@2024');
        $output->writeln('------------------------------------'.PHP_EOL);
    }

    private function createOpportunities(OutputInterface $output, SymfonyStyle $io): void
    {
        $user = $this->userRepository->findOneBy(['email' => 'admin@regmel.com']);
        $agent = $user->getAgents()->first();

        $opportunityForMunicipality = new Opportunity();
        $opportunityForMunicipality->setId(Uuid::fromString('d67f0342-0da0-4b89-b841-caea111e4866'));
        $opportunityForMunicipality->setName('Adesão dos Municípios - 2025');
        $opportunityForMunicipality->setCreatedBy($agent);
        $opportunityForMunicipality->setImage('/img/regmel/icon.svg');
        $opportunityForMunicipality->setExtraFields([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        $opportunityForCompany = new Opportunity();
        $opportunityForCompany->setId(Uuid::fromString('5a3df77e-62de-43ee-8492-fb2283aa2fe8'));
        $opportunityForCompany->setName('Propostas das Empresas/OSCs - 2025');
        $opportunityForCompany->setCreatedBy($agent);
        $opportunityForCompany->setImage('/img/regmel/icon.svg');
        $opportunityForCompany->setExtraFields([
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]);

        $this->manualLogin($user);

        $this->entityManager->persist($opportunityForMunicipality);
        $this->entityManager->persist($opportunityForCompany);
        $this->entityManager->flush();

        $io->title('Created Opportunity for Municipality: ');
        $output->writeln('Name: '.$opportunityForMunicipality->getName());

        $output->writeln('------------------------------------'.PHP_EOL);
        $io->title('Created Opportunity for Company: ');
        $output->writeln('Name: '.$opportunityForCompany->getName());
        $output->writeln('------------------------------------');

        $this->createPhasesForMunicipality($opportunityForMunicipality);
        $this->createPhasesForCompany($opportunityForCompany);
    }

    private function createPhasesForMunicipality(Opportunity $opportunityForMunicipality): void
    {
        $phase1Municipality = new Phase();
        $phase1Municipality->setOpportunity($opportunityForMunicipality);
        $phase1Municipality->setCreatedBy($opportunityForMunicipality->getCreatedBy());
        $phase1Municipality->setId(Uuid::fromString('ef62e816-00a4-43cf-930f-796edb7c6175'));
        $phase1Municipality->setStatus(true);
        $phase1Municipality->setName('Credenciamento e Termo de Adesão');
        $phase1Municipality->setDescription('Cadastro e Validação do Termo de Adesão do Município');
        $phase1Municipality->setSequence(1);
        $phase1Municipality->setStartDate(new DateTime('2025-04-22'));
        $phase1Municipality->setEndDate(new DateTime('2025-05-31'));
        $phase1Municipality->setCriteria([
            'oficio' => true,
        ]);

        $this->entityManager->persist($phase1Municipality);
        $this->entityManager->flush();
    }

    private function createPhasesForCompany(Opportunity $opportunity): void
    {
        $phase1 = new Phase();
        $phase1->setOpportunity($opportunity);
        $phase1->setCreatedBy($opportunity->getCreatedBy());
        $phase1->setId(Uuid::fromString('e9075d53-abd6-4568-b531-4f3bb90fb734'));
        $phase1->setStatus(true);
        $phase1->setName('Cadastro e Envio de Propostas');
        $phase1->setDescription('Envio de Propostas aos Municipios');
        $phase1->setSequence(1);
        $phase1->setStartDate(new DateTime('2025-04-22'));
        $phase1->setEndDate(new DateTime('2025-05-31'));
        $phase1->setCriteria([
            'oficio' => true,
        ]);

        $this->entityManager->persist($phase1);
        $this->entityManager->flush();
    }
}
