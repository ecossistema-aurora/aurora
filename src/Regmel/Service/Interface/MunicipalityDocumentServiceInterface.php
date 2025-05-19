<?php

declare(strict_types=1);

namespace App\Regmel\Service\Interface;

use Symfony\Component\Uid\Uuid;

interface MunicipalityDocumentServiceInterface
{
    public function decision(Uuid $municipalityId, bool $approved, string $reason): void;

    public function sendEmailDecision(Uuid $municipalityId, bool $approved, string $reason): void;
}
