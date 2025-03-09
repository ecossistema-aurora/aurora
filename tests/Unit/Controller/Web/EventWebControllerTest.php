<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Web;

use App\DataFixtures\Entity\EventFixtures;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class EventWebControllerTest extends AbstractWebTestCase
{
    public function testListRouteRendersHTMLSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/eventos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.event-card');
    }

    public function testGetOneRouteNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/eventos/'.Uuid::v4()->toRfc4122());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetOneRouteForExistingEvent(): void
    {
        $client = static::createClient();
        $existingUuid = EventFixtures::EVENT_ID_1;

        $client->request('GET', '/eventos/'.$existingUuid);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('h2.name__entity-details');
    }
}
