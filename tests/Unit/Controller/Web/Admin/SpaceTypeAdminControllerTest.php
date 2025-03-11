<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Web\Admin;

use App\Controller\Web\Admin\SpaceTypeAdminController;
use App\DataFixtures\Entity\SpaceTypeFixtures;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpaceTypeAdminControllerTest extends AbstractWebTestCase
{
    private SpaceTypeAdminController $controller;

    private const string BASE_URL = '/painel/admin/tipo-de-espaco/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = static::getContainer()->get(SpaceTypeAdminController::class);
    }

    public function testSpaceTypePageExists(): void
    {
        $this->assertInstanceOf(Response::class, $this->controller->list());
    }

    public function testListPageRenderHTMLWithSuccess(): void
    {
        $this->client->request(Request::METHOD_GET, self::BASE_URL);

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h2', $this->translator->trans('space_type'));
    }

    public function testCreatePageRenderHTML(): void
    {
        $this->client->request(Request::METHOD_GET, self::BASE_URL.'adicionar');

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h2', $this->translator->trans('view.space_type.create'));
    }

    public function testCreateWithValidData(): void
    {
        $this->client->request(Request::METHOD_POST, self::BASE_URL.'adicionar', [
            'name' => 'New Space Type',
        ]);

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $this->assertResponseRedirects(self::BASE_URL);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.toast.success .toast-body', $this->translator->trans('view.space_type.message.created'));
    }

    public function testCreateWithInvalidData(): void
    {
        $this->client->request(Request::METHOD_POST, self::BASE_URL.'adicionar', [
            'name' => 'T',
        ]);

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('.toast.danger .toast-body', 'Nome: O valor é muito curto. Deveria de ter 2 caracteres ou mais.');
    }

    public function testEditPageRenderHTML(): void
    {
        $this->client->request(Request::METHOD_GET, self::BASE_URL.'editar/'.SpaceTypeFixtures::SPACE_TYPE_ID_3);

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h2', $this->translator->trans('view.space_type.edit'));
    }

    public function testEditWithValidData(): void
    {
        $this->client->request(Request::METHOD_POST, self::BASE_URL.'editar/'.SpaceTypeFixtures::SPACE_TYPE_ID_3, [
            'name' => 'Updated Space Type',
        ]);

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $this->assertResponseRedirects(self::BASE_URL);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.toast.success .toast-body', $this->translator->trans('view.space_type.message.updated'));
    }

    public function testEditWithInvalidData(): void
    {
        $this->client->request(Request::METHOD_POST, self::BASE_URL.'editar/'.SpaceTypeFixtures::SPACE_TYPE_ID_3, [
            'name' => 'T',
        ]);

        $response = $this->client->getResponse();
        $this->assertInstanceOf(Response::class, $response);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('.toast.danger .toast-body', 'Nome: O valor é muito curto. Deveria de ter 2 caracteres ou mais.');
    }
}
