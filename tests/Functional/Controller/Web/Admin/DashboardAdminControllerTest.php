<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Web\Admin;

use App\Controller\Web\Admin\DashboardAdminController;
use App\Entity\Agent;
use App\Entity\User;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\EventServiceInterface;
use App\Service\Interface\InitiativeServiceInterface;
use App\Service\Interface\InscriptionOpportunityServiceInterface;
use App\Service\Interface\OpportunityServiceInterface;
use App\Service\Interface\SpaceServiceInterface;
use DateTime;
use ErrorException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;

class DashboardAdminControllerTest extends KernelTestCase
{
    private AgentServiceInterface $agentService;
    private OpportunityServiceInterface $opportunityService;
    private EventServiceInterface $eventService;
    private SpaceServiceInterface $spaceService;
    private InitiativeServiceInterface $initiativeService;
    private InscriptionOpportunityServiceInterface $inscriptionService;
    private Environment $twig;
    private DashboardAdminController $controller;
    private User $userMock;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->agentService = new class implements AgentServiceInterface {
            private array $agents = [];
            private int $count = 0;

            public function setAgents(array $agents): void
            {
                $this->agents = $agents;
            }

            public function setCount(int $count): void
            {
                $this->count = $count;
            }

            public function getAgentsFromLoggedUser(): array
            {
                return $this->agents;
            }

            public function create(array $agent): Agent
            {
                return new Agent();
            }

            public function createFromUser(array $user): void
            {
                // Stub
            }

            public function findBy(array $params = [], int $limit = 50): array
            {
                return [];
            }

            public function findOneBy(array $params): ?Agent
            {
                return null;
            }

            public function get(Uuid $id): Agent
            {
                return new Agent();
            }

            public function list(int $limit = 50, array $params = [], string $order = 'DESC'): array
            {
                return [];
            }

            public function remove(Uuid $id): void
            {
                // Stub
            }

            public function update(Uuid $id, array $agent): Agent
            {
                return new Agent();
            }

            public function updateImage(Uuid $id, UploadedFile $uploadedFile): Agent
            {
                return new Agent();
            }

            public function count(?User $user = null): int
            {
                return $this->count;
            }
        };

        $this->opportunityService = $this->createMock(OpportunityServiceInterface::class);
        $this->eventService = $this->createMock(EventServiceInterface::class);
        $this->spaceService = $this->createMock(SpaceServiceInterface::class);
        $this->initiativeService = $this->createMock(InitiativeServiceInterface::class);
        $this->inscriptionService = $this->createMock(InscriptionOpportunityServiceInterface::class);
        $this->twig = $this->createMock(Environment::class);

        $this->userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userMock->method('getId')->willReturn(Uuid::v4());

        $this->controller = $this->getMockBuilder(DashboardAdminController::class)
            ->setConstructorArgs([
                $this->agentService,
                $this->opportunityService,
                $this->eventService,
                $this->spaceService,
                $this->initiativeService,
                $this->inscriptionService,
            ])
            ->onlyMethods(['getUser'])
            ->getMock();
        $this->controller->method('getUser')->willReturn($this->userMock);

        $this->controller->setContainer(static::getContainer());
    }

    public function testConstructorInjectsDependencies(): void
    {
        $this->assertInstanceOf(DashboardAdminController::class, $this->controller);
    }

    public function testIndexReturnsResponseWithExpectedData(): void
    {
        if (method_exists($this->agentService, 'setAgents')) {
            $agentTest = new Agent();
            $this->agentService->setAgents([$agentTest]);
            $this->agentService->setCount(10);
        }

        $this->inscriptionService
            ->method('findRecentByUser')
            ->willReturn([
                [
                    'opportunity' => [
                        'id' => 'opportunity1-id',
                        'name' => 'Opportunity Test 1',
                    ],
                    'createdAt' => new DateTime('2025-03-20'),
                ],
                [
                    'opportunity' => [
                        'id' => 'opportunity2-id',
                        'name' => 'Opportunity Test 2',
                    ],
                    'createdAt' => new DateTime('2025-03-20'),
                ],
            ]);

        $this->opportunityService->method('count')->willReturn(5);
        $this->eventService->method('count')->willReturn(3);
        $this->spaceService->method('count')->willReturn(7);
        $this->initiativeService->method('count')->willReturn(2);

        $response = $this->controller->index();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('dashboard', $response->getContent());
    }

    public function testIndexThrowsExceptionWhenNoAgentFound(): void
    {
        if (method_exists($this->agentService, 'setAgents')) {
            $this->agentService->setAgents([]);
        }

        set_error_handler(function ($errno, $errstr, $errfile, $errline): void {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        $this->expectException(ErrorException::class);

        try {
            $this->controller->index();
        } finally {
            restore_error_handler();
        }
    }
}
