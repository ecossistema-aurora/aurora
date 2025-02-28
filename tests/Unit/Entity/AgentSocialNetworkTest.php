<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Agent;
use App\Entity\AgentSocialNetwork;
use App\Entity\SocialNetwork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class AgentSocialNetworkTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $agentSocialNetwork = new AgentSocialNetwork();
        $value = '@test';

        $agent = new Agent();
        $agentId = Uuid::v4();
        $agent->setId($agentId);

        $socialNetwork = new SocialNetwork();
        $socialNetworkId = Uuid::v4();
        $socialNetwork->setId($socialNetworkId);

        $agentSocialNetwork->setAgent($agent);
        $agentSocialNetwork->setSocialNetwork($socialNetwork);
        $agentSocialNetwork->setValue($value);

        $this->assertSame($agentId->toRfc4122(), $agentSocialNetwork->getAgent()->getId()->toRfc4122());
        $this->assertSame($socialNetworkId->toRfc4122(), $agentSocialNetwork->getSocialNetwork()->getId()->toRfc4122());
        $this->assertSame($value, $agentSocialNetwork->getValue());
    }

    public function testToArray(): void
    {
        $agentSocialNetwork = new AgentSocialNetwork();

        $socialNetworkMock = $this->createMock(SocialNetwork::class);
        $socialNetworkMock->method('toArray')->willReturn(['id' => 'social-network-uuid']);
        $agentSocialNetwork->setSocialNetwork($socialNetworkMock);

        $value = '@test';
        $agentSocialNetwork->setValue($value);

        $expectedArray = $agentSocialNetwork->toArray();

        $this->assertSame($expectedArray, [
            'socialNetwork' => [
                'id' => 'social-network-uuid',
            ],
            'value' => $value,
        ]);
    }
}
