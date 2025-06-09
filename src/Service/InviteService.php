<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Invite;
use App\Entity\Organization;
use App\Entity\User;
use App\Enum\OrganizationTypeEnum;
use App\Enum\UserRolesEnum;
use App\Event\Invite\AcceptInviteEvent;
use App\Event\Invite\SendInviteEvent;
use App\Exception\Agent\AgentAlreadyMemberException;
use App\Exception\Invite\InviteIsNotForYou;
use App\Repository\Interface\InviteRepositoryInterface;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\EmailServiceInterface;
use App\Service\Interface\InviteServiceInterface;
use App\Service\Interface\UserServiceInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class InviteService extends AbstractEntityService implements InviteServiceInterface
{
    private const string TIME_TO_EXPIRATION = '+24 hours';

    public function __construct(
        private AgentServiceInterface $agentService,
        private InviteRepositoryInterface $repository,
        private UserServiceInterface $userService,
        private Security $security,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private EmailServiceInterface $emailService,
        private TranslatorInterface $translator,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct(
            $this->security,
            $this->serializer,
            $this->validator,
            $this->entityManager,
            Invite::class,
        );
    }

    public function send(Organization $organization, string $name, string $email): Invite
    {
        $agent = $this->agentService->getMainAgentByEmail($email);

        if (null !== $agent && true === $organization->hasAgent($agent)) {
            throw new AgentAlreadyMemberException();
        }

        $expirationAt = new DateTimeImmutable(self::TIME_TO_EXPIRATION);
        $token = Uuid::v4();

        $invite = new Invite();
        $invite->setId(Uuid::v4());
        $invite->setGuest($agent);
        $invite->setHost($organization);
        $invite->setToken($token);
        $invite->setExpirationAt($expirationAt);

        $this->repository->save($invite);

        $confirmationUrl = $this->urlGenerator->generate(
            'admin_organization_invite_accept',
            ['id' => $organization->getId(), 'invite' => $invite->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->emailService->sendTemplatedEmail(
            [$email],
            $this->translator->trans('invite_to_organization'),
            '_emails/agent-invitation.html.twig',
            [
                'name' => $name,
                'organization' => $organization->getName(),
                'confirmationUrl' => $confirmationUrl,
            ]
        );

        $this->eventDispatcher->dispatch(new SendInviteEvent($invite, $this->security->getUser()), SendInviteEvent::class);

        return $this->repository->save($invite);
    }

    public function accept(Invite $invite, User $user): void
    {
        $agent = $this->userService->getMainAgent($user);

        $host = $invite->getHost();
        $guest = $invite->getGuest();

        $role = match ($host->getType()) {
            OrganizationTypeEnum::EMPRESA->value => UserRolesEnum::ROLE_COMPANY->value,
            OrganizationTypeEnum::MUNICIPIO->value => UserRolesEnum::ROLE_MUNICIPALITY->value,
        };

        $user->addRole($role);

        if (null !== $guest && $guest->getId()->toRfc4122() !== $agent->getId()->toRfc4122()) {
            throw new InviteIsNotForYou();
        }

        $host->addAgent($agent);

        $this->entityManager->persist($host);
        $this->entityManager->persist($user);
        $this->entityManager->remove($invite);

        $this->manualLogin($user);
        $this->eventDispatcher->dispatch(new AcceptInviteEvent($invite, $this->security->getUser()), AcceptInviteEvent::class);
        $this->entityManager->flush();
        $this->manualLogout();
    }

    public function get(Uuid $id): Invite
    {
        return $this->repository->find($id);
    }

    public function findOneBy(array $params = []): ?Invite
    {
        return $this->repository->findOneBy($params);
    }

    private function manualLogin(User $user): void
    {
        $token = new UsernamePasswordToken($user, 'web');
        $this->tokenStorage->setToken($token);
    }

    private function manualLogout(): void
    {
        $this->tokenStorage->setToken(null);
    }
}
