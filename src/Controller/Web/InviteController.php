<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Enum\FlashMessageTypeEnum;
use App\Exception\Invite\InviteIsExpired;
use App\Exception\Invite\InviteIsNotForYou;
use App\Exception\UnauthorizedException;
use App\Service\Interface\InviteServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class InviteController extends AbstractWebController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly InviteServiceInterface $inviteService,
    ) {
    }

    public function accept(Request $request): Response
    {
        $user = $this->getUser();

        try {
            $this->inviteService->accept(
                Uuid::fromString($request->get('id')),
                Uuid::fromString($request->get('invite')),
                $user
            );
        } catch (Exception $exception) {
            [$message, $route, $params] = match (get_class($exception)) {
                InviteIsNotForYou::class => [
                    $this->translator->trans('invite_not_belongs_to_you'),
                    'web_home_homepage',
                    [],
                ],
                InviteIsExpired::class => [
                    $this->translator->trans('invite_expired'),
                    'web_home_homepage',
                    [],
                ],
                UniqueConstraintViolationException::class => [
                    $this->translator->trans('user_already_belongs_the_organization'),
                    'web_home_homepage',
                    [],
                ],
                UnauthorizedException::class => [
                    $this->translator->trans('you_must_login_now'),
                    'web_auth_login',
                    ['target_path_invite' => $request->getUri()],
                ],
                UserNotFoundException::class => [
                    $this->translator->trans('you_must_register_now'),
                    'web_auth_register',
                    ['target_path_invite' => $request->getUri()],
                ],
                default => throw $exception
            };

            $this->addFlash(
                in_array(get_class($exception), [UnauthorizedException::class, UserNotFoundException::class])
                    ? FlashMessageTypeEnum::SUCCESS->value
                    : FlashMessageTypeEnum::ERROR->value,
                $message
            );

            return $this->redirectToRoute($route, $params);
        }

        $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, $this->translator->trans('invite_accept'));

        return $this->redirectToRoute('admin_organization_get', [
            'id' => $request->get('invite'),
        ]);
    }
}
