<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\Document\SpaceTimeline;
use App\DocumentService\SpaceTimelineDocumentService;
use App\Service\Interface\ActivityAreaServiceInterface;
use App\Service\Interface\SpaceServiceInterface;
use App\Service\TagService;
use DateTime;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

class SpaceAdminController extends AbstractAdminController
{
    private const string VIEW_LIST = 'space/list.html.twig';
    private const string VIEW_ADD = 'space/create.html.twig';
    private const string VIEW_EDIT = 'space/edit.html.twig';

    public const string CREATE_FORM_ID = 'add-space';
    public const string EDIT_FORM_ID = 'edit-space';

    public function __construct(
        private readonly SpaceServiceInterface $service,
        private readonly SpaceTimelineDocumentService $documentService,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
        private readonly SpaceTimeline $spaceTimeline,
        private readonly TagService $tagService,
        private readonly ActivityAreaServiceInterface $activityAreaService
    ) {
    }

    public function list(): Response
    {
        $spaces = $this->service->findBy();

        return $this->render(self::VIEW_LIST, [
            'spaces' => $spaces,
        ]);
    }

    public function remove(?Uuid $id): Response
    {
        $this->service->remove($id);

        $this->addFlashSuccess($this->translator->trans('view.space.message.deleted'));

        return $this->redirectToRoute('admin_space_list');
    }

    public function create(Request $request): Response
    {
        if (false === $request->isMethod(Request::METHOD_POST)) {
            $activityAreas = $this->activityAreaService->list();
            $tags = $this->tagService->list();

            return $this->render(self::VIEW_ADD, [
                'form_id' => self::CREATE_FORM_ID,
                'activityAreas' => $activityAreas,
                'tags' => $tags,
            ]);
        }

        $this->validCsrfToken(self::CREATE_FORM_ID, $request);

        $name = $request->request->get('name');
        $maxCapacity = (int) $request->request->get('maxCapacity');
        $isAccessible = (bool) $request->request->get('isAccessible');

        $extraFields = $request->request->get('extraFields', []);
        $areasOfActivity = $extraFields['areasOfActivity'] ?? [];
        $tagsInput = $extraFields['tags'] ?? [];

        $space = [
            'id' => Uuid::v4(),
            'name' => $name,
            'maxCapacity' => $maxCapacity,
            'isAccessible' => $isAccessible,
            'createdBy' => $this->security->getUser()->getAgents()->getValues()[0]->getId(),
            'extraFields' => [
                'areasOfActivity' => $areasOfActivity,
                'tags' => $tagsInput,
            ],
        ];

        try {
            $this->service->create($space);
            $this->addFlashSuccess($this->translator->trans('view.space.message.created'));
        } catch (Exception|TypeError $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->render(self::VIEW_ADD, [
                'error' => $exception->getMessage(),
                'form_id' => self::CREATE_FORM_ID,
            ]);
        }

        return $this->redirectToRoute('admin_space_list');
    }

    public function timeline(Uuid $id): Response
    {
        $events = $this->documentService->getEventsByEntityId($id);

        return $this->render('space/timeline.html.twig', [
            'space' => $this->service->get($id),
            'events' => $events,
        ]);
    }

    public function edit(Uuid $id, Request $request): Response
    {
        try {
            $space = $this->service->get($id);
        } catch (Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->redirectToRoute('admin_space_list');
        }

        if (Request::METHOD_POST !== $request->getMethod()) {
            $extraFields = $space->getExtraFields();
            $areasOfActivity = $extraFields['areasOfActivity'] ?? [];
            $tagsSelected = $extraFields['tags'] ?? [];

            $areasOfExpertiseStructured = array_map(function ($area) {
                return ['label' => $area, 'value' => $area];
            }, $areasOfActivity);

            $tagsStructured = array_map(function ($tag) {
                return ['label' => $tag, 'value' => $tag];
            }, $tagsSelected);

            $activityAreasFromDB = $this->activityAreaService->list();
            $tagsFromDB = $this->tagService->list();

            $areasOfActivityItems = array_map(function ($areaEntity) {
                return [
                    'label' => $areaEntity->getName(),
                    'value' => $areaEntity->getName(),
                ];
            }, $activityAreasFromDB);

            $tagItems = array_map(function ($tagEntity) {
                return [
                    'label' => $tagEntity->getName(),
                    'value' => $tagEntity->getName(),
                ];
            }, $tagsFromDB);

            return $this->render(self::VIEW_EDIT, [
                'space' => $space,
                'areasOfExpertise' => $areasOfExpertiseStructured,
                'tags' => $tagsStructured,
                'areasOfActivityItems' => $areasOfActivityItems,
                'tagItems' => $tagItems,

                'form_id' => self::EDIT_FORM_ID,
            ]);
        }

        $this->validCsrfToken(self::EDIT_FORM_ID, $request);

        $name = $request->request->get('name');
        $extraFieldsInput = $request->request->get('extraFields', []);
        $areasOfActivity = $extraFieldsInput['areasOfActivity'] ?? [];
        $tags = $extraFieldsInput['tags'] ?? [];
        $description = $request->request->get('extraFields')['description'] ?? null;
        $date = $request->request->get('date') ?? null;

        $dataToUpdate = [
            'name' => $name,
            'description' => $description,
            'date' => $date ? new DateTime($date) : null,
            'updatedBy' => $this->security->getUser()->getAgents()->getValues()[0]->getId(),
            'extraFields' => [
                'areasOfActivity' => $areasOfActivity,
                'tags' => $tags,
            ],
        ];

        try {
            $this->service->update($id, $dataToUpdate);
            $this->addFlashSuccess($this->translator->trans('view.space.message.updated'));

            return $this->redirectToRoute('admin_space_list');
        } catch (TypeError|Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->render(self::VIEW_EDIT, [
                'space' => $space,
                'error' => $exception->getMessage(),
                'form_id' => self::EDIT_FORM_ID,
            ]);
        }
    }
}
