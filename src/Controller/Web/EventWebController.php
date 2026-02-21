<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Enum\AgeClassificationEnum;
use App\Request\Query\Filters;
use App\Service\Interface\CulturalLanguageServiceInterface;
use App\Service\Interface\EventServiceInterface;
use App\Service\Interface\StateServiceInterface;
use App\Service\Interface\TagServiceInterface;
use App\ValueObject\DashboardCardItemValueObject as CardItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventWebController extends AbstractWebController
{
    public function __construct(
        public readonly EventServiceInterface $service,
        private readonly TranslatorInterface $translator,
        private readonly CulturalLanguageServiceInterface $culturalLanguageService,
        private readonly TagServiceInterface $tagService,
        private readonly StateServiceInterface $stateService,
    ) {
    }

    public function list(Filters $filters, Request $request): Response
    {
        $requestFilters = array_merge(
            $filters->toArray(),
            ['draft' => false],
            $request->query->all()
        );

        $parsedFilters = $this->getOrderParam($requestFilters);

        $events = $this->service->list(
            params: $parsedFilters['filters'],
            order: $parsedFilters['order']
        );
        $totalEvents = count($events);

        $days = $filters->toArray()['days'] ?? 7;
        $recentEvents = $this->service->countRecentRecords($days);
        $openedEvents = $this->service->countOpenedEvents();
        $finishedEvents = $this->service->countFinishedEvents();

        $dashboard = [
            'color' => '#f5b932',
            'items' => [
                new CardItem(icon: 'description', quantity: $totalEvents, text: 'view.event.quantity.total'),
                new CardItem(icon: 'event_note', quantity: $openedEvents, text: 'view.event.quantity.opened'),
                new CardItem(icon: 'event_available', quantity: $finishedEvents, text: 'view.event.quantity.finished'),
                new CardItem(icon: 'today', quantity: $recentEvents, text: $this->translator->trans('view.event.quantity.last_days', ['{days}' => $days])),
            ],
        ];

        $language = $this->culturalLanguageService->list();
        $ageRating = AgeClassificationEnum::cases();
        $tag = $this->tagService->list();
        $state = $this->stateService->list();

        return $this->render('event/list.html.twig', [
            'dashboard' => $dashboard,
            'events' => $events,
            'totalEvents' => $totalEvents,
            'languages' => $language,
            'ageRatings' => $ageRating,
            'tags' => $tag,
            'states' => $state,
        ]);
    }

    public function show(Uuid $id): Response
    {
        $event = $this->service->get($id);

        return $this->render('event/show.html.twig', ['event' => $event]);
    }
}
