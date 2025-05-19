<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\Entity\Initiative;
use App\Entity\InscriptionOpportunity;
use App\Entity\InscriptionPhase;
use App\Entity\Opportunity;
use App\Entity\Organization;
use App\Enum\InscriptionOpportunityStatusEnum;
use App\Enum\InscriptionPhaseStatusEnum;
use App\Enum\OrganizationTypeEnum;
use App\Enum\StatusProposalEnum;
use App\Environment\ConfigEnvironment;
use App\Exception\UnableCreateFileException;
use App\Regmel\Service\Interface\ProposalServiceInterface;
use App\Repository\Interface\InitiativeRepositoryInterface;
use App\Repository\OrganizationRepository;
use App\Service\AbstractEntityService;
use App\Service\InitiativeService;
use App\Service\Interface\CityServiceInterface;
use App\Service\Interface\EmailServiceInterface;
use App\Service\Interface\FileServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\OpportunityService;
use App\Service\PhaseService;
use App\Service\UserService;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use ZipArchive;

readonly class ProposalService extends AbstractEntityService implements ProposalServiceInterface
{
    public function __construct(
        private FileServiceInterface $fileService,
        private ParameterBagInterface $parameterBag,
        private InitiativeRepositoryInterface $initiativeRepository,
        private OrganizationRepository $organizationRepository,
        private OpportunityService $opportunityService,
        private CityServiceInterface $cityService,
        private EntityManagerInterface $entityManager,
        private PhaseService $phaseService,
        protected TokenStorageInterface $tokenStorage,
        private Security $security,
        private InitiativeService $initiativeService,
        private UserService $userService,
        private SerializerInterface $serializer,
        private OrganizationServiceInterface $organizationService,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private readonly ConfigEnvironment $configEnvironment,
        private readonly EmailServiceInterface $emailService,
    ) {
        parent::__construct(
            $this->security,
            $this->serializer,
            null,
            $this->entityManager,
            Initiative::class,
            $this->fileService,
            $this->parameterBag,
        );
    }

    public function saveProposal(Organization $company, array $data, ?UploadedFile $map = null, ?UploadedFile $project = null): Initiative
    {
        $user = $this->security->getUser();

        $municipality = $this->organizationRepository->findOrganizationByCityId(
            $data['city']
        );

        $municipalityTermStatus = $municipality?->getExtraFields()['term_status'] ?? null;
        $status = match ($municipalityTermStatus) {
            'approved' => StatusProposalEnum::RECEBIDA->value,
            'rejected', 'awaiting' => StatusProposalEnum::ENVIADA->value,
            default => StatusProposalEnum::SEM_ADESAO->value,
        };

        $initiative = new Initiative();
        $initiative->setId(Uuid::v4());
        $initiative->setName($data['name']);
        $initiative->setCreatedBy($user->getAgents()->first());
        $initiative->setCreatedAt(new DateTimeImmutable());
        $initiative->setOrganizationFrom($company);
        $initiative->setOrganizationTo($municipality);

        if (null === $municipality) {
            $municipality = $this->cityService->get($data['city']);
            $state = $municipality->getState()->getAcronym();
            $cityId = $municipality->getId();
            $cityCode = $municipality->getCityCode();
            $cityName = $municipality->getName().'-'.$state;
            $region = $municipality->getState()->getRegion();
        } else {
            $state = $municipality->getExtraFields()['state'];
            $cityId = $municipality->getExtraFields()['cityId'];
            $cityCode = $municipality->getExtraFields()['cityCode'] ?? '';
            $cityName = $municipality->getName().'-'.$state;
            $region = $municipality->getExtraFields()['region'];
        }

        $mapFileName = null;
        $projectFileName = null;

        if (null !== $map) {
            $mapFileName = $this->uploadFile($map, $company->getName(), $cityName, $cityCode, extraName: 'area-poligonal');
        }

        if (null !== $project) {
            $projectFileName = $this->uploadFile($project, $company->getName(), $cityName, $cityCode, extraName: 'projeto');
        }

        $initiative->setExtraFields([
            'status' => $status,
            'area_size' => (float) $data['area_size'],
            'quantity_houses' => (float) $data['quantity_houses'],
            'area_characteristic' => $data['area_characteristic'],
            'map_file' => $mapFileName,
            'project_file' => $projectFileName,
            'cityCode' => $cityCode,
            'city_name' => $cityName,
            'city_id' => $cityId,
            'state' => $state,
            'region' => $region,
        ]);

        $this->initiativeRepository->save($initiative);

        return $initiative;
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

    private function uploadFile(
        UploadedFile $uploadedFile,
        string $company,
        string $municipality,
        string|int $cityCode,
        string $version = '01',
        string $extraName = '',
    ): string {
        $pdf = $this->fileService->uploadMixedFile(
            $uploadedFile,
            extraPath: '/regmel/company/documents',
            optionalName: "Proposta-{$company}-{$extraName}-{$municipality}-{$cityCode}-{$version}",
        );

        return $pdf->getFilename();
    }

    public function isDuplicateOrganization(string $name, string $cityId): bool
    {
        return $this->organizationRepository->isOrganizationDuplicate($name, $cityId);
    }

    public function generateProposalCode(Initiative $proposal): string
    {
        $extraFields = $proposal->getExtraFields();

        return 'MCM-'.$extraFields['cityCode'].'-1-'.(new DateTime())->format('Y').'-'.substr($proposal->getId()->toRfc4122(), 0, 4);
    }

    private function generateUrlForField(Initiative $entity, string $fieldName, string $routeName): string
    {
        $extraFields = $entity->getExtraFields();

        return isset($extraFields[$fieldName])
            ? $this->urlGenerator->generate($routeName, [
                'id' => $entity->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
            : '';
    }

    public function getCsvHeaders(): array
    {
        return [
            $this->translator->trans('csv.header.id'),
            $this->translator->trans('csv.header.region'),
            $this->translator->trans('csv.header.state'),
            $this->translator->trans('csv.header.city'),
            $this->translator->trans('csv.header.created_by'),
            $this->translator->trans('csv.header.company_name'),
            $this->translator->trans('csv.header.company_cnpj'),
            $this->translator->trans('csv.header.company_phone'),
            $this->translator->trans('csv.header.map_file'),
            $this->translator->trans('csv.header.project_file'),
            $this->translator->trans('csv.header.houses_quantity'),
            $this->translator->trans('csv.header.total_area'),
            $this->translator->trans('csv.header.total_value'),
            $this->translator->trans('csv.header.proposal_status'),
            $this->translator->trans('csv.header.intervention_area_name'),
            $this->translator->trans('csv.header.proposal_type'),
            $this->translator->trans('csv.header.proposal_date'),
            $this->translator->trans('csv.header.proposal_update_date'),
        ];
    }

    public function getCsvRow(object $entity): array
    {
        if (!$entity instanceof Initiative) {
            throw new InvalidArgumentException($this->translator->trans('error.invalid_entity'));
        }

        $variables = (new ConfigEnvironment())->aurora()['variables'];

        $pricePerHousehold = $variables['price_per_household'];
        $extraFields = $entity->getExtraFields();
        $organizationFrom = $entity->getOrganizationFrom();
        $housesQuantity = (float) ($extraFields['quantity_houses'] ?? 0);
        $totalValue = $housesQuantity * $pricePerHousehold;

        $mapFileLink = $this->generateUrlForField($entity, 'map_file', 'admin_regmel_proposal_mapa');
        $projectFileLink = $this->generateUrlForField($entity, 'project_file', 'admin_regmel_proposal_projeto');

        $env = $this->configEnvironment->aurora();

        $region = $extraFields['region'];
        $state = $extraFields['state'];
        $modificationDate = $entity->getUpdatedAt() ?? $this->translator->trans('csv.info.not_modified');
        $phoneCompany = $organizationFrom->getExtraFields()['telefone'] ?? '';

        return [
            $this->generateProposalCode($entity),
            $region,
            $state,
            $extraFields['city_name'] ?? '',
            $entity->getCreatedBy()->getName() ?? '',
            $organizationFrom->getName(),
            $organizationFrom->getExtraFields()['cnpj'] ?? '',
            $phoneCompany,
            $mapFileLink,
            $projectFileLink,
            $housesQuantity,
            $extraFields['area_size'] ?? 0,
            number_format($totalValue, 2, ',', '.'),
            $extraFields['status'] ?? '',
            $entity->getName() ?? '',
            $env['proposals']['area_characteristics'][$extraFields['area_characteristic']] ?? '',
            $entity->getCreatedAt()->format('d/m/Y H:i:s'),
            $modificationDate,
        ];
    }

    public function exportProjectFiles(array $proposals): string
    {
        $zipFilePath = sprintf('%s/storage/regmel/company/documents/export.zip', $this->parameterBag->get('kernel.project_dir'));

        $zip = new ZipArchive();

        if (true !== $zip->open($zipFilePath, ZipArchive::CREATE)) {
            throw new UnableCreateFileException();
        }

        foreach ($proposals as $proposal) {
            if (true === empty($proposal->getExtraFields()['project_file'])) {
                continue;
            }

            $filePath = $this->getDocumentPath($proposal->getExtraFields()['project_file']);

            if (true === file_exists($filePath)) {
                $zip->addFile($filePath, basename($filePath));
            }
        }

        $zip->close();

        return $zipFilePath;
    }

    public function exportMapFiles(array $proposals): string
    {
        $zipFilePath = sprintf('%s/storage/regmel/company/documents/export.zip', $this->parameterBag->get('kernel.project_dir'));

        $zip = new ZipArchive();

        if (true !== $zip->open($zipFilePath, ZipArchive::CREATE)) {
            throw new UnableCreateFileException();
        }

        foreach ($proposals as $proposal) {
            if (true === empty($proposal->getExtraFields()['map_file'])) {
                continue;
            }

            $filePath = $this->getDocumentPath($proposal->getExtraFields()['map_file']);

            if (true === file_exists($filePath)) {
                $zip->addFile($filePath, basename($filePath));
            }
        }

        $zip->close();

        return $zipFilePath;
    }

    private function getDocumentPath(string $file): string
    {
        $path = $this->parameterBag->get('kernel.project_dir');

        return "{$path}/storage/regmel/company/documents/{$file}";
    }

    public function updateStatusProposal(Uuid $id, StatusProposalEnum $status, string $reason): void
    {
        $initiative = $this->initiativeService->get($id);
        $extraFields = $initiative->getExtraFields();
        $extraFields['status'] = $status->value;
        $extraFields['status_reason'] = $status->value;
        $initiative->setExtraFields($extraFields);
        $this->initiativeRepository->save($initiative);

        $emails = $initiative->getOrganizationTo()->getAgents()->map(fn ($agent) => $agent->getUser()?->getEmail())->toArray();

        $this->emailService->sendTemplatedEmail(
            $emails,
            'Status da proposta atualizado',
            '_emails/new-proposal-status.html.twig',
            [
                'municipalityName' => $initiative->getOrganizationTo()->getName(),
                'companyName' => $initiative->getOrganizationFrom()->getName(),
            ]
        );
    }
}
