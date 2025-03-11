<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Web\Admin;

use App\DataFixtures\Entity\SpaceTypeFixtures;
use App\Tests\AbstractWebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class SpaceTypeAdminControllerTest extends AbstractWebTestCase
{
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = self::getContainer()->get(TranslatorInterface::class);
    }

    public function testListPageRenderHTMLWithSuccess(): void
    {
        $this->client->request('GET', '/painel/admin/tipo-de-espaco/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', $this->translator->trans('space_type'));
    }

    public function testCreatePageRenderHTML(): void
    {
        $this->client->request('GET', '/painel/admin/tipo-de-espaco/adicionar');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', $this->translator->trans('view.space_type.create'));
    }

    public function testCreateWithValidData(): void
    {
        $this->client->request('POST', '/painel/admin/tipo-de-espaco/adicionar', [
            'name' => 'New Space Type',
        ]);

        $this->assertResponseRedirects('/painel/admin/tipo-de-espaco/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.toast.success .toast-body', $this->translator->trans('view.space_type.message.created'));
    }

    public function testCreateWithInvalidData(): void
    {
        $this->client->request('POST', '/painel/admin/tipo-de-espaco/adicionar', [
            'name' => 'T',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.danger .toast-body', 'Nome: O valor é muito curto. Deveria de ter 2 caracteres ou mais.');
    }

    public function testEditPageRenderHTML(): void
    {
        $this->client->request('GET', '/painel/admin/tipo-de-espaco/editar/'.SpaceTypeFixtures::SPACE_TYPE_ID_3);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', $this->translator->trans('view.space_type.edit'));
    }

    public function testEditWithValidData(): void
    {
        $this->client->request('POST', '/painel/admin/tipo-de-espaco/editar/'.SpaceTypeFixtures::SPACE_TYPE_ID_3, [
            'name' => 'Updated Space Type',
        ]);

        $this->assertResponseRedirects('/painel/admin/tipo-de-espaco/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.toast.success .toast-body', $this->translator->trans('view.space_type.message.updated'));
    }

    public function testEditWithInvalidData(): void
    {
        $this->client->request('POST', '/painel/admin/tipo-de-espaco/editar/'.SpaceTypeFixtures::SPACE_TYPE_ID_3, [
            'name' => 'T',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.danger .toast-body', 'Nome: O valor é muito curto. Deveria de ter 2 caracteres ou mais.');
    }
}
