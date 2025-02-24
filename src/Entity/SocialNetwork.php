<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SocialNetworkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SocialNetworkRepository::class)]
class SocialNetwork extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[Groups(['social-network.get'])]
    private ?Uuid $id = null;

    #[ORM\Column]
    #[Groups(['social-network.get'])]
    private int $code;

    #[ORM\Column]
    #[Groups(['social-network.get'])]
    private string $baseUrl;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id?->toRfc4122(),
            'code' => $this->code,
            'baseUrl' => $this->baseUrl,
        ];
    }
}
