<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'agent_social_network')]
class AgentSocialNetwork
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Agent::class, inversedBy: 'socialNetworks')]
    #[ORM\JoinColumn(nullable: false)]
    private Agent $agent;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: SocialNetwork::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SocialNetwork $socialNetwork;

    #[ORM\Column]
    #[Groups(['agent.get.item'])]
    private string $value;

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function setAgent(Agent $agent): void
    {
        $this->agent = $agent;
    }

    public function getSocialNetwork(): SocialNetwork
    {
        return $this->socialNetwork;
    }

    public function setSocialNetwork(SocialNetwork $socialNetwork): void
    {
        $this->socialNetwork = $socialNetwork;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function toArray(): array
    {
        return [
            'socialNetwork' => $this->socialNetwork->toArray(),
            'value' => $this->value,
        ];
    }
}
