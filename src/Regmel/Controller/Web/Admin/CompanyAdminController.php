<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\DocumentService\OrganizationTimelineDocumentService;
use App\Enum\OrganizationTypeEnum;
use App\Enum\UserRolesEnum;
use App\Repository\Interface\InitiativeRepositoryInterface;
use App\Service\Interface\InscriptionOpportunityServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\PhaseServiceInterface;
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

class CompanyAdminController extends AbstractAdminController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly OrganizationTimelineDocumentService $documentService,
        private readonly InitiativeRepositoryInterface $initiativeRepository,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly PhaseServiceInterface $phaseService,
        public readonly InscriptionOpportunityServiceInterface $inscriptionOpportunityService,
    ) {
    }

    private function renderCompanyList(array $companies): Response
    {
        return $this->render('regmel/admin/company/list.html.twig', [
            'companies' => $companies,
            'token' => $this->security->getUser() ? $this->jwtManager->create($this->security->getUser()) : null,
        ], parentPath: '');
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or 
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'") or 
        is_granted("'.UserRolesEnum::ROLE_COMPANY->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/empresas', name: 'admin_regmel_company_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $user = $this->security->getUser();
        $filterType = $request->query->get('tipo');

        if (true === in_array(UserRolesEnum::ROLE_ADMIN->value, $user->getRoles())) {
            $criteria = ['type' => OrganizationTypeEnum::EMPRESA->value];
            $allCompanies = $this->organizationService->findBy($criteria);

            $companies = array_filter($allCompanies, function ($organization) use ($filterType) {
                $extra = $organization->getExtraFields();

                return !$filterType || ($extra['tipo'] ?? null) === $filterType;
            });

            $types = array_unique(array_filter(array_map(function ($organization) {
                return $organization->getExtraFields()['tipo'] ?? null;
            }, $allCompanies)));

            return $this->render('regmel/admin/company/list.html.twig', [
                'companies' => $companies,
                'types' => $types,
                'token' => $this->jwtManager->create($user),
                'context_title' => 'my_companies',
            ], parentPath: '');
        }

        $agents = $user->getAgents();

        if ($agents->isEmpty()) {
            $this->addFlash('error', $this->translator->trans('user_associated'));

            return $this->redirectToRoute('admin_dashboard');
        }

        $companies = $this->organizationService->getCompaniesByAgents($agents);

        return $this->render('regmel/admin/company/list.html.twig', [
            'companies' => $companies,
            'types' => [],
            'token' => $this->jwtManager->create($user),
            'context_title' => 'my_companies',
        ], parentPath: '');
    }

    #[Route('/painel/admin/empresas/{id}', name: 'admin_regmel_company_details', methods: ['GET'])]
    public function details(Uuid $id): Response
    {
        $company = $this->organizationService->findOneBy([
            'id' => $id,
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]);

        $this->denyAccessUnlessGranted('get', $company);

        $timeline = $this->documentService->getEventsByEntityId($id);

        $createdById = $company->getCreatedBy()->getId()->toRfc4122();

        $proposals = $this->initiativeRepository->findBy([
            'organizationFrom' => $id->toRfc4122(),
        ]);

        $opportunity = $this->inscriptionOpportunityService->findOpportunityByOrganization($company->getId());

        $isPhaseActive = $this->phaseService->isPhaseActive($opportunity->getId());

        return $this->render('regmel/admin/company/details.html.twig', [
            'company' => $company,
            'proposals' => $proposals,
            'timeline' => $timeline,
            'createdById' => $createdById,
            'phase' => $isPhaseActive,
        ], parentPath: '');
    }

    #[Route('/painel/admin/empresas/{id}/editar', name: 'admin_regmel_company_edit', methods: ['GET', 'POST'])]
    public function edit(Uuid $id, Request $request): Response
    {
        try {
            $company = $this->organizationService->get($id);

            $this->denyAccessUnlessGranted('edit', $company);
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('admin_regmel_company_list');
        }

        if ($request->isMethod('GET')) {
            return $this->renderCompanyList(
                $this->organizationService->findBy(['type' => OrganizationTypeEnum::EMPRESA->value])
            );
        }

        $this->validCsrfToken('edit-company', $request);

        try {
            $this->organizationService->update($id, [
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'extraFields' => array_merge($company->getExtraFields(), array_intersect_key($request->request->all(), array_flip([
                    'cnpj',
                    'site',
                    'telefone',
                    'email',
                    'tipo',
                ]))),
            ]);

            $this->addFlash('success', $this->translator->trans('view.company.message.updated'));
        } catch (TypeError|Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->list();
        }

        return $this->redirectToRoute('admin_regmel_company_list');
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/empresas/list/download', name: 'admin_regmel_company_list_download', methods: ['GET'])]
    public function exportCompaniesCsv(Request $request): Response
    {
        $tipo = $request->query->get('tipo');

        $type = OrganizationTypeEnum::EMPRESA->value;

        $companies = $tipo
            ? $this->organizationService->findByCompanyFilters($tipo)
            : $this->organizationService->findBy(['type' => $type]);

        return $this->organizationService->generateCsv($companies, 'empresas.csv', $type);
    }
}
