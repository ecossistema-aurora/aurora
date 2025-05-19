<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\Document\SpaceTimeline;
use App\DocumentService\InitiativeTimelineDocumentService;
use App\Enum\UserRolesEnum;
use App\Exception\ValidatorException;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\InitiativeServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class InitiativeAdminController extends AbstractAdminController
{
    public const CREATE_FORM_ID = 'add-initiative';

    public function __construct(
        private readonly InitiativeServiceInterface $service,
        private readonly InitiativeTimelineDocumentService $documentService,
        private readonly AgentServiceInterface $agentService,
        private readonly TranslatorInterface $translator,
        private readonly SpaceTimeline $spaceTimeline,
    ) {
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function create(): Response
    {
        $agents = $this->agentService->findBy();

        return $this->render('initiative/create.html.twig', [
            'id' => Uuid::v4()->toRfc4122(),
            'agents' => $agents,
            'form_id' => self::CREATE_FORM_ID,
        ]);
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function store(Request $request): Response
    {
        $this->validCsrfToken(self::CREATE_FORM_ID, $request);

        $data = $request->request->all();

        try {
            $this->service->create($data);

            $this->addFlash('success', $this->translator->trans('view.initiative.message.created'));
        } catch (ValidatorException $exception) {
            $this->addFlash('error', $this->translator->trans('view.entities.message.required_fields'));

            $agents = $this->agentService->findBy();

            return $this->render('initiative/create.html.twig', [
                'id' => Uuid::v4(),
                'agents' => $agents,
                'form_id' => self::CREATE_FORM_ID,
            ]);
        } catch (Exception $exception) {
            $this->addFlash('error', $this->translator->trans('view.entities.message.required_fields'));

            $agents = $this->agentService->findBy();

            return $this->render('initiative/create.html.twig', [
                'id' => Uuid::v4(),
                'agents' => $agents,
                'form_id' => self::CREATE_FORM_ID,
            ]);
        }

        return $this->redirectToRoute('admin_initiative_list');
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function list(): Response
    {
        $initiatives = $this->service->findBy();

        return $this->render('initiative/list.html.twig', [
            'initiatives' => $initiatives,
        ]);
    }

    public function remove(?Uuid $id): Response
    {
        $initiative = $this->service->get($id);

        $this->denyAccessUnlessGranted('remove', $initiative);

        $this->service->remove($id);

        $this->addFlash('success', 'Initiative removed');

        return $this->redirectToRoute('admin_initiative_list');
    }

    public function timeline(Uuid $id): Response
    {
        $initiative = $this->service->get($id);

        $this->denyAccessUnlessGranted('get', $initiative);

        $events = $this->documentService->getEventsByEntityId($id);

        return $this->render('initiative/timeline.html.twig', [
            'initiative' => $initiative,
            'events' => $events,
        ]);
    }

    public function edit(Uuid $id): Response
    {
        $initiative = $this->service->get($id);

        $this->denyAccessUnlessGranted('edit', $initiative);

        return $this->render('initiative/edit.html.twig', [
            'initiative' => $initiative,
        ]);
    }
}
