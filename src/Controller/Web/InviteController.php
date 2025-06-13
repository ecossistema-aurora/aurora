<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Enum\FlashMessageTypeEnum;
use App\Exception\Invite\InviteIsExpired;
use App\Exception\Invite\InviteIsNotForYou;
use App\Exception\UnauthorizedException;
use App\Service\Interface\InviteServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
        } catch (InviteIsNotForYou) {
            $this->addFlash(FlashMessageTypeEnum::ERROR->value, $this->translator->trans('invite_not_belongs_to_you'));

            return $this->redirectToRoute('web_home_homepage');
        } catch (InviteIsExpired) {
            $this->addFlash(FlashMessageTypeEnum::ERROR->value, $this->translator->trans('invite_expired'));

            return $this->redirectToRoute('web_home_homepage');
        } catch (UniqueConstraintViolationException) {
            $this->addFlash(FlashMessageTypeEnum::ERROR->value, 'Já voi rezistrado macho');

            return $this->redirectToRoute('web_home_homepage');
        } catch (UnauthorizedException) {
            $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, 'Mar menino, avexa teu login ai vaso');

            return $this->redirectToRoute('web_auth_login', [
                'target_path_invite' => $request->getUri(),
            ]);
        } catch (UserNotFoundException) {
            $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, 'Ei fresco, faz teu rezistro');

            return $this->redirectToRoute('web_auth_register', [
                'target_path_invite' => $request->getUri(),
            ]);
        }

        $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, $this->translator->trans('invite_accept'));

        return $this->redirectToRoute('admin_organization_get', [
            'id' => $request->get('invite'),
        ]);
    }
}
