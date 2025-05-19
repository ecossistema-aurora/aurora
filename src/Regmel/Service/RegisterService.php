<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\DTO\OrganizationDto;
use App\DTO\UserDto;
use App\Entity\InscriptionOpportunity;
use App\Entity\InscriptionPhase;
use App\Entity\Opportunity;
use App\Entity\Organization;
use App\Entity\User;
use App\Enum\InscriptionOpportunityStatusEnum;
use App\Enum\InscriptionPhaseStatusEnum;
use App\Enum\OrganizationTypeEnum;
use App\Enum\StatusProposalEnum;
use App\Regmel\Service\Interface\MunicipalityServiceInterface;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Repository\Interface\OrganizationRepositoryInterface;
use App\Repository\Interface\PhaseRepositoryInterface;
use App\Service\Interface\AccountEventServiceInterface;
use App\Service\Interface\FileServiceInterface;
use App\Service\OpportunityService;
use App\Service\OrganizationService;
use App\Service\PhaseService;
use App\Service\UserService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class RegisterService implements RegisterServiceInterface
{
    public function __construct(
        private readonly OrganizationService $organizationService,
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer,
        private readonly OrganizationRepositoryInterface $organizationRepository,
        private readonly OpportunityService $opportunityService,
        private readonly FileServiceInterface $fileService,
        private readonly EntityManagerInterface $entityManager,
        private readonly PhaseService $phaseService,
        private readonly AccountEventServiceInterface $accountEventService,
        private readonly MunicipalityServiceInterface $municipalityService,
        protected TokenStorageInterface $tokenStorage,
        private readonly PhaseRepositoryInterface $phaseRepository,
    ) {
    }

    public function saveUser(array $data): User
    {
        try {
            $user = $this->userService->validateInput($data, UserDto::class, UserDto::CREATE);

            $userObj = $this->userService->create($user);
        } catch (Exception $exception) {
            throw $exception;
        }

        return $userObj;
    }

    public function saveOrganization(array $data, ?UploadedFile $uploadedFile = null): Organization
    {
        if (null !== $uploadedFile) {
            $fileName = $this->getTermFileName(
                $data['organization']['name'] ?? '',
                $data['organization']['extraFields']['state'] ?? '',
                $data['organization']['extraFields']['term_version'] ?? 0
            );
            $data['organization']['extraFields']['form'] = $this->uploadFile($uploadedFile, $fileName);
        }

        $organization = $this->organizationService->validateInput($data['organization'], OrganizationDto::class, OrganizationDto::CREATE);
        $user = $this->userService->validateInput($data['user'], UserDto::class, UserDto::CREATE);

        /** @var Organization $organizationObj */
        $organizationObj = $this->serializer->denormalize($organization, Organization::class);

        try {
            $userObj = $this->userService->create($user);
            $userObj->setRoles($data['user']['roles']);

            $this->manualLogin($userObj);

            $agent = $userObj->getAgents()->first();

            $organizationObj->setOwner($agent);
            $organizationObj->setCreatedBy($agent);
            $organizationObj->addAgent($agent);

            $this->organizationRepository->save($organizationObj);

            if (OrganizationTypeEnum::MUNICIPIO->value === $organizationObj->getType()) {
                $this->municipalityService->updateProposals($organizationObj, StatusProposalEnum::SEM_ADESAO, StatusProposalEnum::ENVIADA);
            }

            $this->createInscriptionForOrganization($data['opportunity'], $organizationObj);

            $this->manualLogout();
        } catch (Exception $exception) {
            throw $exception;
        }

        $this->accountEventService->notifyManagerOfNewRegistration(
            $userObj->getName(),
            $organizationObj->getName(),
            $organizationObj->getType(),
            $organizationObj->getCreatedAt(),
        );

        return $organizationObj;
    }

    public function resendTerm(string $organizationId, ?UploadedFile $uploadedFile): void
    {
        $organization = $this->organizationService->get(Uuid::fromString($organizationId));

        if (null !== $uploadedFile) {
            $extraFields = $organization->getExtraFields();

            $termoVersion = $extraFields['term_version'] + 1;

            $fileName = $this->getTermFileName(
                $organization->getName(),
                $extraFields['state'],
                $termoVersion
            );

            $extraFields = array_merge($extraFields, ['term_version' => $termoVersion, 'term_status' => 'awaiting', 'form' => $this->uploadFile($uploadedFile, $fileName)]);

            $organization->setExtraFields($extraFields);

            $this->organizationRepository->save($organization);

            $this->accountEventService->notifyManagerOfNewMunicipalityDocument($organization->getName());
        }
    }

    public function findOpportunitiesBy(OrganizationTypeEnum $enum): array
    {
        $opportunities = $this->opportunityService->findBy();

        return array_filter(
            $opportunities,
            fn (Opportunity $opportunity) => ($opportunity->getExtraFields()['type'] ?? null) === $enum->value
        );
    }

    private function createInscriptionForOrganization(string $opportunityId, Organization $organization): void
    {
        $opportunityId = Uuid::fromString($opportunityId);

        $opportunity = $this->opportunityService->get(
            $opportunityId
        );

        $inscription = new InscriptionOpportunity();
        $inscription->setOpportunity($opportunity);
        $inscription->setOrganization($organization);
        $inscription->setId(Uuid::v4());
        $inscription->setStatus(InscriptionOpportunityStatusEnum::ACTIVE->value);

        $firstPhase = current($this->phaseService->list($opportunityId, 1));

        if (false !== $firstPhase) {
            $inscriptionPhase = new InscriptionPhase();
            $inscriptionPhase->setId(Uuid::v4());
            $inscriptionPhase->setOrganization($inscription->getOrganization());
            $inscriptionPhase->setPhase($firstPhase);
            $inscriptionPhase->setStatus(InscriptionPhaseStatusEnum::ACTIVE->value);

            $this->entityManager->persist($inscriptionPhase);
        }

        $this->entityManager->persist($inscription);
        $this->entityManager->flush();
    }

    private function uploadFile(UploadedFile $uploadedFile, ?string $fileName = null): string
    {
        $pdf = $this->fileService->uploadPDF($uploadedFile, $fileName, extraPath: '/regmel/municipality/documents');

        return $pdf->getFilename();
    }

    private function manualLogin(User $user): void
    {
        $token = new UsernamePasswordToken($user, 'web');
        $this->tokenStorage->setToken($token);
    }

    private function manualLogout(): void
    {
        $this->tokenStorage->setToken(null);
    }

    public function isDuplicateOrganization(string $name, string $cityId): bool
    {
        return $this->organizationRepository->isOrganizationDuplicate($name, $cityId);
    }

    private function getTermFileName(string $municipality, string $state, int $version): string
    {
        $municipality = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $municipality);
        $municipality = str_replace(' ', '', ucwords($municipality));

        return sprintf('Termo-%s-%s-%d', $municipality, $state, $version);
    }

    public function findOpportunityWithActivePhase(string $organizationType): Opportunity|bool
    {
        $opportunities = $this->findOpportunitiesBy(OrganizationTypeEnum::from($organizationType));

        foreach ($opportunities as $opportunity) {
            $currentPhase = $this->phaseRepository->findCurrentPhase(new DateTime(), $opportunity);

            if ($currentPhase && $currentPhase->getOpportunity()->getId() === $opportunity->getId()) {
                return $opportunity;
            }
        }

        return false;
    }
}
