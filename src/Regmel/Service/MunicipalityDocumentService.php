<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\Enum\StatusProposalEnum;
use App\Regmel\Service\Interface\MunicipalityDocumentServiceInterface;
use App\Regmel\Service\Interface\MunicipalityServiceInterface;
use App\Repository\Interface\OrganizationRepositoryInterface;
use App\Service\Interface\EmailServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use DateTimeImmutable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class MunicipalityDocumentService implements MunicipalityDocumentServiceInterface
{
    public function __construct(
        private OrganizationServiceInterface $organizationService,
        private OrganizationRepositoryInterface $organizationRepository,
        private EmailServiceInterface $emailService,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private MunicipalityServiceInterface $municipalityService,
    ) {
    }

    public function decision(Uuid $municipalityId, bool $approved, string $reason): void
    {
        $municipality = $this->organizationService->get($municipalityId);
        $extraFields = $municipality->getExtraFields();
        $extraFields['term_status'] = $approved ? 'approved' : 'rejected';
        $extraFields['term_reason'] = $reason;
        $extraFields['term_updated_at'] = (new DateTimeImmutable())->format('d/m/Y H:i:s');

        $municipality->setExtraFields($extraFields);
        $this->organizationRepository->save($municipality);

        $this->municipalityService->updateProposals($municipality, StatusProposalEnum::ENVIADA, StatusProposalEnum::RECEBIDA);
    }

    public function sendEmailDecision(Uuid $municipalityId, bool $approved, string $reason): void
    {
        $municipality = $this->organizationService->get($municipalityId);

        $emails = $municipality->getAgents()->map(fn ($agent) => $agent->getUser()?->getEmail())->toArray();
        $emails[] = $municipality->getExtraFields()['email'] ?? null;

        $subject = $approved ? $this->translator->trans('term_approved') : $this->translator->trans('term_rejected');
        $htmlTemplate = $approved ? '_emails/notifications/municipality/document_approved.html.twig' : '_emails/notifications/municipality/document_rejected.html.twig';

        $loginPage = $this->urlGenerator->generate('web_auth_login', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->emailService->sendTemplatedEmail(
            array_filter($emails),
            $subject,
            $htmlTemplate,
            [
                'municipality' => $municipality->getName(),
                'reason' => $reason,
                'loginPage' => $loginPage,
            ]
        );
    }
}
