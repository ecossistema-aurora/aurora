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
use App\Exception\Invite\InviteIsExpired;
use App\Exception\Invite\InviteIsNotForYou;
use App\Exception\Invite\InviteResourceNotFoundException;
use App\Exception\UnauthorizedException;
use App\Exception\User\UserResourceNotFoundException;
use App\Repository\Interface\InviteRepositoryInterface;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\EmailServiceInterface;
use App\Service\Interface\InviteServiceInterface;
use App\Service\Interface\UserServiceInterface;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
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

        $invite = new Invite();
        $invite->setId(Uuid::v4());
        $invite->setGuest($agent);
        $invite->setHost($organization);
        $invite->setEmail($email);
        $invite->setExpirationAt($expirationAt);

        $this->repository->save($invite);

        $confirmationUrl = $this->urlGenerator->generate(
            'web_organization_invite_accept',
            ['id' => $organization->getId(), 'invite' => $invite->getId()],
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

    /**
     * @throws UserNotFoundException
     * @throws UnauthorizedException
     * @throws InviteIsNotForYou
     * @throws InviteIsExpired
     */
    public function accept(Uuid $organizationId, Uuid $inviteId, ?User $user): void
    {
        /* @var Invite $invite */
        $invite = $this->repository->findOneBy(['id' => $inviteId, 'host' => $organizationId]);

        if (null === $invite) {
            throw new InviteResourceNotFoundException();
        }

        if ($invite->getExpirationAt() < new DateTime()) {
            throw new InviteIsExpired();
        }

        if (null === $user) {
            if (null === $invite->getGuest()) {
                throw new UserNotFoundException();
            }

            try {
                $this->userService->findOneBy(['email' => $invite->getEmail()]);
            } catch (UserResourceNotFoundException) {
                throw new UserNotFoundException();
            }

            throw new UnauthorizedException();
        }

        $agent = $this->userService->getMainAgent($user);

        $host = $invite->getHost();
        $guest = $invite->getGuest();

        $role = match ($host->getType()) {
            OrganizationTypeEnum::EMPRESA->value => UserRolesEnum::ROLE_COMPANY->value,
            OrganizationTypeEnum::MUNICIPIO->value => UserRolesEnum::ROLE_MUNICIPALITY->value,
            default => UserRolesEnum::ROLE_ADMIN->value,
        };

        $user->addRole($role);

        if (null !== $guest && $guest->getId()->toRfc4122() !== $agent->getId()->toRfc4122()) {
            throw new InviteIsNotForYou();
        }

        $host->addAgent($agent);

        $this->entityManager->persist($host);
        $this->entityManager->persist($user);
        $this->entityManager->remove($invite);

        $this->eventDispatcher->dispatch(new AcceptInviteEvent($invite, $this->security->getUser()), AcceptInviteEvent::class);
        $this->entityManager->flush();
    }

    public function updateGuest(Uuid $inviteId, User $user): void
    {
        $invite = $this->repository->find($inviteId);
        $agent = $user->getAgents()->first();
        $invite->setGuest($agent);
        $this->repository->save($invite);
    }

    public function get(Uuid $inviteId): Invite
    {
        $invite = $this->repository->find($inviteId);

        if (null === $invite) {
            throw new InviteResourceNotFoundException();
        }

        return $invite;
    }
}
