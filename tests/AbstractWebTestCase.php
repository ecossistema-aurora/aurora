<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Utils\WebLoginHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        WebLoginHelper::login($this->client, 'henriquelopeslima@example.com', 'Aurora@2024');

        $this->translator = self::getContainer()->get(TranslatorInterface::class);
    }
}
