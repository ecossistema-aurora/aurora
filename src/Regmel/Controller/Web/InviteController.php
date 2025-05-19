<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web;

use App\Controller\Web\AbstractWebController;
use App\Entity\User;
use App\Enum\FlashMessageTypeEnum;
use App\Enum\OrganizationTypeEnum;
use App\Exception\Agent\AgentAlreadyMemberException;
use App\Exception\Invite\InviteIsNotForYou;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Service\Interface\InviteServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\UserServiceInterface;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class InviteController extends AbstractWebController
{
    public const string VIEW_INVITE_LOGIN = 'regmel/invite/login.html.twig';
    public const string VIEW_INVITE_REGISTER = 'regmel/invite/register.html.twig';
    public const string FORM_INVITE_LOGIN = 'invite-login';
    public const string FORM_INVITE_REGISTER = 'invite-register';
    public const string LOGIN = 'login';
    public const string REGISTER = 'register';

    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly TranslatorInterface $translator,
        private readonly UserServiceInterface $userService,
        private readonly RegisterServiceInterface $registerService,
        private readonly InviteServiceInterface $inviteService,
    ) {
    }

    #[Route('/convites/empresas/{id}/convidar/agente', name: 'admin_regmel_company_invite_agent', methods: ['POST'])]
    #[Route('/convites/municipios/{id}/convidar/agente', name: 'admin_regmel_municipality_invite_agent', methods: ['POST'])]
    public function sendInvite(Uuid $id, Request $request): Response
    {
        $type = match ($request->attributes->get('_route')) {
            'admin_regmel_company_invite_agent' => OrganizationTypeEnum::EMPRESA->value,
            'admin_regmel_municipality_invite_agent' => OrganizationTypeEnum::MUNICIPIO->value,
        };

        $organizationDetailsRoute = $this->getRedirectRoute($request->attributes->get('_route'));

        $name = $request->request->get('name');
        $email = $request->request->get('email');

        $error = [];

        if (false === is_string($name)) {
            $error[] = $this->translator->trans('view.authentication.error.first_name_length');
        }

        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = $this->translator->trans('view.authentication.error.invalid_email');
        }

        if ($this->getUser()->getEmail() === $email) {
            $error[] = $this->translator->trans('this_member_belongs_to_organization');
        }

        if ([] === $error) {
            $organization = $this->organizationService->findOneBy([
                'id' => $id,
                'type' => $type,
            ]);

            try {
                $this->inviteService->create($organization, $name, $email);
                $this->addFlash('success', $this->translator->trans('invite_sent'));
            } catch (AgentAlreadyMemberException) {
                $error[] = $this->translator->trans('this_member_belongs_to_organization');
            }
        }

        foreach ($error as $errorMessage) {
            $this->addFlash('error', $errorMessage);
        }

        return $this->redirectToRoute($organizationDetailsRoute, ['id' => $id->toRfc4122()]);
    }

    #[Route('/convites/empresas/{token}/aceitar', name: 'accept_company_invitation', methods: ['GET', 'POST'])]
    #[Route('/convites/municipios/{token}/aceitar', name: 'accept_municipality_invitation', methods: ['GET', 'POST'])]
    public function acceptInvite(string $token, Request $request): Response
    {
        $invite = $this->inviteService->findOneBy(['token' => $token]);

        if (null === $invite || $invite->getExpirationAt() < new DateTime()) {
            $this->addFlash(FlashMessageTypeEnum::ERROR->value, $this->translator->trans('invite_invalid'));

            return $this->redirectToRoute('web_home_homepage');
        }

        $user = $this->getUser();

        $typeForm = $request->get('type');

        $organizationDetailsRoute = $this->getRedirectRoute($request->attributes->get('_route'));

        if (null !== $user) {
            try {
                /* @var User $user */
                $this->inviteService->confirm($invite, $user);
            } catch (InviteIsNotForYou) {
                $this->addFlash(FlashMessageTypeEnum::ERROR->value, $this->translator->trans('invite_not_belongs_to_you'));

                return $this->redirectToRoute($organizationDetailsRoute, [
                    'id' => $invite->getHost()->getId()->toRfc4122(),
                ]);
            }

            $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, $this->translator->trans('invite_accept'));

            return $this->redirectToRoute($organizationDetailsRoute, [
                'id' => $invite->getHost()->getId()->toRfc4122(),
            ]);
        }

        if (Request::METHOD_GET === $request->getMethod()) {
            if (null === $invite->getGuest()) {
                return $this->render(self::VIEW_INVITE_REGISTER, [
                    'form_id' => self::FORM_INVITE_REGISTER,
                    'id' => $invite->getHost()->getId()->toRfc4122(),
                ]);
            }

            return $this->render(self::VIEW_INVITE_LOGIN, [
                'form_id' => self::FORM_INVITE_LOGIN,
                'id' => $invite->getHost()->getId()->toRfc4122(),
            ]);
        }

        if (self::LOGIN === $typeForm) {
            $email = $request->get('email');
            $password = $request->get('password');

            $user = $this->userService->findOneBy(['id' => $invite->getGuest()->getUser()->getId(), 'email' => $email]);

            if (false === $this->userService->authenticate($user, $password)) {
                $this->addFlash(FlashMessageTypeEnum::ERROR->value, $this->translator->trans('invalid_credentials'));

                return $this->render(self::VIEW_INVITE_LOGIN, [
                    'form_id' => self::FORM_INVITE_LOGIN,
                    'id' => $invite->getHost()->getId()->toRfc4122(),
                ]);
            }

            $this->inviteService->confirm($invite, $user);
        }

        if (self::REGISTER === $typeForm) {
            $user = $this->registerService->saveUser([
                'id' => Uuid::v4(),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                'extraFields' => [
                    'cpf' => $request->get('cpf'),
                    'cargo' => $request->get('position'),
                ],
            ]);

            $this->inviteService->confirm($invite, $user);
        }

        $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, $this->translator->trans('invite_accept'));

        return $this->redirectToRoute($organizationDetailsRoute, [
            'id' => $invite->getHost()->getId()->toRfc4122(),
        ]);
    }

    private function getRedirectRoute(string $url): string
    {
        return match ($url) {
            'admin_regmel_company_invite_agent',
            'accept_company_invitation' => 'admin_regmel_company_details',
            'admin_regmel_municipality_invite_agent',
            'accept_municipality_invitation' => 'admin_regmel_municipality_details',
        };
    }
}
