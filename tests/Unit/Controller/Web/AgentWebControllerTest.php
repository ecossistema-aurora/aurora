<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Web;

use App\DataFixtures\Entity\AgentFixtures;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class AgentWebControllerTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testListRouteRendersHTMLSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/agentes');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.agent-card');
    }

    public function testGetOneRouteNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/agentes/'.Uuid::v4()->toRfc4122());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetOneRouteForExistingAgent(): void
    {
        $client = static::createClient();

        $existingUuid = AgentFixtures::AGENT_ID_1;

        $client->request('GET', '/agentes/'.$existingUuid);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Alessandro');

        $this->assertStringContainsString($existingUuid, $client->getResponse()->getContent());
    }
}
