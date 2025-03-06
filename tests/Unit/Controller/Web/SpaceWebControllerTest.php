<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Web;

use App\DataFixtures\Entity\SpaceFixtures;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class SpaceWebControllerTest extends AbstractWebTestCase
{
    public function testListRouteRendersHTMLSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/espacos');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.space-card');
    }

    public function testGetOneRouteNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/espacos/'.Uuid::v4()->toRfc4122());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetOneRouteForExistingSpace(): void
    {
        $client = static::createClient();

        $existingUuid = SpaceFixtures::SPACE_ID_1;

        $client->request('GET', '/espacos/'.$existingUuid);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'SECULT');
    }
}
