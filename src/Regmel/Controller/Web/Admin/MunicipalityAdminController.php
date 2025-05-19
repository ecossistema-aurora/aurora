<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\DocumentService\OrganizationTimelineDocumentService;
use App\Enum\OrganizationTypeEnum;
use App\Enum\RegionEnum;
use App\Enum\StatusProposalEnum;
use App\Enum\UserRolesEnum;
use App\Regmel\Service\Interface\MunicipalityServiceInterface;
use App\Regmel\Service\Interface\ProposalServiceInterface;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\StateServiceInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

class MunicipalityAdminController extends AbstractAdminController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly OrganizationTimelineDocumentService $documentService,
        private readonly ProposalServiceInterface $proposalService,
        private readonly MunicipalityServiceInterface $municipalityService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly StateServiceInterface $stateService,
        private readonly RegisterServiceInterface $registerService,
    ) {
    }

    private function renderOrganizationList(array $municipalities): Response
    {
        return $this->render('regmel/admin/municipality/list.html.twig', [
            'municipalities' => $municipalities,
            'token' => $this->security->getUser() ? $this->jwtManager->create($this->security->getUser()) : null,
        ], parentPath: '');
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or 
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'") or 
        is_granted("'.UserRolesEnum::ROLE_MUNICIPALITY->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios', name: 'admin_regmel_municipality_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $user = $this->security->getUser();

        $filterRegion = $request->query->get('region');
        $filterState = $request->query->get('state');

        $regions = RegionEnum::cases();
        $states = $this->stateService->findBy(['region' => $filterRegion]);

        if (true === in_array(UserRolesEnum::ROLE_ADMIN->value, $user->getRoles())) {
            $criteria = ['type' => OrganizationTypeEnum::MUNICIPIO->value];
            $allMunicipalities = $this->organizationService->findBy($criteria);

            $municipalities = array_filter($allMunicipalities, function ($organization) use ($filterRegion, $filterState) {
                $extra = $organization->getExtraFields();

                return (!$filterRegion || ($extra['region'] ?? null) === $filterRegion)
                    && (!$filterState || ($extra['state'] ?? null) === $filterState);
            });

            return $this->render('regmel/admin/municipality/list.html.twig', [
                'municipalities' => $municipalities,
                'regions' => $regions,
                'states' => $states,
                'token' => $this->jwtManager->create($user),
                'context_title' => 'my_municipalities',
            ], parentPath: '');
        }

        $agents = $user->getAgents();

        if ($agents->isEmpty()) {
            $this->addFlash('error', $this->translator->trans('user_associated'));

            return $this->redirectToRoute('admin_dashboard');
        }

        $municipalities = $this->organizationService->getMunicipalitiesByAgents($agents);

        return $this->render('regmel/admin/municipality/list.html.twig', [
            'municipalities' => $municipalities,
            'regions' => $regions,
            'states' => $states,
            'token' => $this->jwtManager->create($user),
            'context_title' => 'my_municipalities',
        ], parentPath: '');
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MUNICIPALITY->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios/{id}', name: 'admin_regmel_municipality_details', methods: ['GET'])]
    public function details(Uuid $id): Response
    {
        $municipality = $this->organizationService->findOneBy([
            'id' => $id,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        // $this->denyAccessUnlessGranted('get', $municipality);

        $createdById = $municipality->getCreatedBy()->getId()->toRfc4122();

        if (null === $municipality) {
            throw $this->createNotFoundException($this->translator->trans('municipality_found'));
        }

        $proposals = $this->municipalityService->getProposals($municipality);

        $timeline = $this->documentService->getAllEventsByOrganizationId($id);

        return $this->render('regmel/admin/municipality/details.html.twig', [
            'municipality' => $municipality,
            'timeline' => $timeline,
            'proposals' => $proposals,
            'createdById' => $createdById,
        ], parentPath: '');
    }

    #[Route('/painel/admin/municipios/{id}/editar', name: 'admin_regmel_municipality_edit', methods: ['GET', 'POST'])]
    public function edit(Uuid $id, Request $request): Response
    {
        try {
            $organization = $this->organizationService->get($id);

            $this->denyAccessUnlessGranted('edit', $organization);
        } catch (Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->redirectToRoute('admin_organization_list');
        }

        if ($request->isMethod('GET')) {
            return $this->renderOrganizationList(
                $this->organizationService->findBy(['type' => OrganizationTypeEnum::MUNICIPIO->value])
            );
        }

        $this->validCsrfToken('edit-organization', $request);

        try {
            $this->organizationService->update($id, [
                'description' => $request->get('description'),
                'extraFields' => array_merge(
                    $organization->getExtraFields(),
                    array_intersect_key($request->request->all(), array_flip([
                        'site',
                        'telefone',
                        'email',
                        'hasHousingExperience',
                        'hasPlhis',
                    ]))
                ),
            ]);

            if ($uploadedImage = $request->files->get('profileImage')) {
                $this->organizationService->updateImage($id, $uploadedImage);
            }

            $this->addFlashSuccess($this->translator->trans('view.organization.message.updated'));
        } catch (TypeError|Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->list($request);
        }

        return $this->redirectToRoute('admin_regmel_municipality_list');
    }

    #[Route('/painel/admin/municipios/{id}/upload-term', name: 'admin_regmel_municipality_upload_term', methods: ['GET', 'POST'])]
    public function uploadTerm(Uuid $id, Request $request): Response
    {
        $uploadedFile = $request->files->get('joinForm');

        if ($uploadedFile) {
            try {
                $this->registerService->resendTerm($id->toRfc4122(), $uploadedFile);
                $this->addFlash('success', 'Termo enviado com sucesso!');
            } catch (Exception $e) {
                $this->addFlash('error', 'Erro ao enviar o termo');
            }
        } else {
            $this->addFlash('error', 'Erro ao enviar o termo');
        }

        return $this->redirectToRoute('admin_regmel_municipality_list');
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MUNICIPALITY->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios/{municipalityId}/remove/{agentId}', name: 'admin_regmel_municipality_remove', methods: ['GET'])]
    public function remove(Uuid $agentId, Uuid $municipalityId): Response
    {
        $this->organizationService->removeAgent($agentId, $municipalityId);

        $this->addFlash('success', $this->translator->trans('view.organization.message.deleted_member'));

        return $this->redirectToRoute('admin_regmel_municipality_details', ['id' => $municipalityId]);
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios/list/download', name: 'admin_regmel_municipality_list_download', methods: ['GET'])]
    public function exportMunicipalitiesCsv(Request $request): Response
    {
        $region = $request->query->get('region');
        $state = $request->query->get('state') ?: null;

        $type = OrganizationTypeEnum::MUNICIPIO->value;

        $municipalities = $region
            ? $this->organizationService->findByMunicipalityFilters($region, $state)
            : $this->organizationService->findBy(['type' => $type]);

        return $this->organizationService->generateCsv($municipalities, 'municipios.csv', $type);
    }

    #[Route('/painel/admin/municipios/{municipalityId}/propostas/{id}/status', name: 'admin_regmel_proposal_update_status', methods: ['POST'])]
    public function updateStatusProposal(Request $request, $municipalityId, Uuid $id): Response
    {
        $status = StatusProposalEnum::from($request->request->get('status'));
        $reason = $request->request->get('reason');

        if (true === empty(trim($reason))) {
            $this->addFlash('error', 'O motivo é obrigatório');
        } else {
            $this->proposalService->updateStatusProposal($id, $status, $reason);
        }

        return $this->redirectToRoute('admin_regmel_municipality_details', ['id' => $municipalityId]);
    }
}
