<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\Entity\Event;
use App\Enum\ExportFormatEnum;
use App\Service\InscriptionEventExportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class InscriptionEventController extends AbstractController
{
    public function __construct(
        private readonly InscriptionEventExportService $exportService
    ) {
    }

    public function download(Event $event, ExportFormatEnum $format): Response
    {
        return $this->exportService->export($event, $format);
    }
}
