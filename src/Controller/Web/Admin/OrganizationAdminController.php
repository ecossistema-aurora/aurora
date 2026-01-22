<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\DocumentService\OrganizationTimelineDocumentService;
use App\Enum\OrganizationTypeEnum;
use App\Enum\UserRolesEnum;
use App\Exception\ValidatorException;
use App\Service\ActivityAreaService;
use App\Service\Interface\OrganizationServiceInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganizationAdminController extends AbstractAdminController
{
    public const string VIEW_ADD = 'organization/add.html.twig';
    public const string VIEW_EDIT = 'organization/edit.html.twig';
    public const string VIEW_TIMELINE = 'organization/timeline.html.twig';

    public const string CREATE_FORM_ID = 'add-organization';
    public const string EDIT_FORM_ID = 'organization-edit';

    public function __construct(
        private readonly OrganizationServiceInterface $service,
        private readonly TranslatorInterface $translator,
        private readonly OrganizationTimelineDocumentService $documentService,
        private readonly ActivityAreaService $activityAreaService,
        private readonly Security $security,
    ) {
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    private function renderOrganizationList(array $organizations, ?array $organization = null, ?string $formId = null, ?string $token = null): Response
    {
        return $this->render('organization/list.html.twig', compact('organizations', 'organization', 'formId', 'token'));
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function list(): Response
    {
        return $this->renderOrganizationList(
            $this->service->findBy([
                'createdBy' => $this->getUser()->getId(),
            ])
        );
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function getOne(Uuid $id): Response
    {
        $organization = $this->service->findOneBy([
            'id' => $id,
        ]);

        if (null === $organization) {
            throw $this->createNotFoundException($this->translator->trans('organization_found'));
        }

        $createdById = $organization->getCreatedBy()->getId()->toRfc4122();

        $events = $this->documentService->getAllEventsByOrganizationId($id);

        return $this->render('_admin/organization/one.html.twig', [
            'organization' => $organization,
            'events' => $events,
            'createdById' => $createdById,
        ], parentPath: '');
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function add(Request $request, ValidatorInterface $validator): Response
    {
        if ('POST' !== $request->getMethod()) {
            return $this->render(self::VIEW_ADD, [
                'form_id' => self::CREATE_FORM_ID,
                'activityAreas' => $this->activityAreaService->list(),
                'types' => OrganizationTypeEnum::getValues(),
            ]);
        }

        $this->validCsrfToken(self::CREATE_FORM_ID, $request);

        try {
            $this->service->create([
                'id' => Uuid::v4(),
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'type' => $request->get('type'),
                'activityAreas' => $request->get('activityAreas'),
                'createdBy' => $this->security->getUser()->getAgents()->getValues()[0]->getId(),
                'owner' => $this->security->getUser()->getAgents()->getValues()[0]->getId(),
                'extraFields' => [
                    'site' => $request->get('site'),
                    'email' => $request->get('email'),
                    'phone' => $request->get('phone'),
                ],
            ]);
        } catch (ValidatorException $exception) {
            return $this->render(self::VIEW_ADD, [
                'errors' => $exception->getConstraintViolationList(),
                'form_id' => self::CREATE_FORM_ID,
                'activityAreas' => $this->activityAreaService->list(),
                'types' => OrganizationTypeEnum::getValues(),
            ]);
        } catch (Exception $exception) {
            return $this->render(self::VIEW_ADD, [
                'errors' => [$exception->getMessage()],
                'activityAreas' => $this->activityAreaService->list(),
                'types' => OrganizationTypeEnum::getValues(),
                'form_id' => self::CREATE_FORM_ID,
            ]);
        }

        $this->addFlash('success', $this->translator->trans('view.organization.message.created'));

        return $this->redirectToRoute('admin_organization_list');
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function remove(?Uuid $id): Response
    {
        $this->service->remove($id);

        $this->addFlash('success', $this->translator->trans('view.organization.message.deleted'));

        return $this->redirectToRoute('admin_organization_list');
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function removeAgent(Uuid $id, Uuid $agentId): Response
    {
        $this->service->removeAgent($agentId, $id);

        $this->addFlash('success', $this->translator->trans('view.organization.message.deleted_member'));

        return $this->redirectToRoute('admin_organization_get', ['id' => $id]);
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function timeline(Uuid $id): Response
    {
        $events = $this->documentService->getEventsByEntityId($id);

        return $this->render(self::VIEW_TIMELINE, [
            'organization' => $this->service->get($id),
            'events' => $events,
        ]);
    }

    public function edit(Uuid $id): Response
    {
        $organization = $this->service->get($id);
        $agents = $organization->getAgents();

        $this->denyAccessUnlessGranted('edit', $organization);

        return $this->render(self::VIEW_EDIT, [
            'organization' => $organization,
            'agents' => $agents,
            'form_id' => self::EDIT_FORM_ID,
        ]);
    }
}
