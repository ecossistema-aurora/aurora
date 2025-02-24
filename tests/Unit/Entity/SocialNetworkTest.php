<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\SocialNetwork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class SocialNetworkTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $socialNetwork = new SocialNetwork();

        $id = Uuid::v4();
        $code = 10;
        $baseUrl = 'https://www.example.com/';

        $socialNetwork->setId($id);
        $socialNetwork->setCode($code);
        $socialNetwork->setBaseUrl($baseUrl);

        $this->assertSame($id, $socialNetwork->getId());
        $this->assertSame($code, $socialNetwork->getCode());
        $this->assertSame($baseUrl, $socialNetwork->getBaseUrl());
    }

    public function testToArray(): void
    {
        $socialNetwork = new SocialNetwork();

        $id = Uuid::v4();
        $code = 10;
        $baseUrl = 'https://www.example.com/';

        $socialNetwork->setId($id);
        $socialNetwork->setCode($code);
        $socialNetwork->setBaseUrl($baseUrl);

        $expectedArray = $socialNetwork->toArray();

        $this->assertSame($expectedArray, [
            'id' => $id->toRfc4122(),
            'code' => $code,
            'baseUrl' => $baseUrl,
        ]);
    }
}
