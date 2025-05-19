<?php

declare(strict_types=1);

namespace App\Regmel\Service\Interface;

use App\Entity\Initiative;
use App\Entity\Organization;
use App\Enum\StatusProposalEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

interface ProposalServiceInterface
{
    public function saveProposal(Organization $company, array $data, ?UploadedFile $map = null, ?UploadedFile $project): Initiative;

    public function updateStatusProposal(Uuid $id, StatusProposalEnum $status, string $reason): void;

    public function exportProjectFiles(array $proposals): string;

    public function exportMapFiles(array $proposals): string;
}
