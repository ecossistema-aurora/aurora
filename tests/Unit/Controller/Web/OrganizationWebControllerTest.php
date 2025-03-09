<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Web;

use App\Controller\Web\OrganizationWebController;
use App\DataFixtures\Entity\OrganizationFixtures;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class OrganizationWebControllerTest extends AbstractWebTestCase
{
    public function testListRouteRendersHTMLSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/organizacoes');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h5.organization-name');
    }

    public function testGetOneRouteNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/organizacoes/'.Uuid::v4()->toRfc4122());
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetOneRouteForExistingOrganization(): void
    {
        $client = static::createClient();
        $existingUuid = Uuid::fromString(OrganizationFixtures::ORGANIZATION_ID_1);
        $client->request('GET', '/organizacoes/'.$existingUuid->toRfc4122());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
        $this->assertSelectorTextContains('h1', 'PHP com Rapadura');
    }

    public function testControllerGetOneMethodDirectly(): void
    {
        $controller = self::getContainer()->get(OrganizationWebController::class);
        $controller->setContainer(self::getContainer());
        $response = $controller->getOne(Uuid::fromString(OrganizationFixtures::ORGANIZATION_ID_1));
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('PHP com Rapadura', $response->getContent());
    }

    public function testControllerListMethodDirectly(): void
    {
        $controller = self::getContainer()->get(OrganizationWebController::class);
        $controller->setContainer(self::getContainer());
        $request = new Request();
        $request->attributes->set('_route', 'web_organization_list');
        $requestStack = self::getContainer()->get('request_stack');
        $requestStack->push($request);
        $response = $controller->list($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('organization-name', $response->getContent());
        $requestStack->pop();
    }
}
