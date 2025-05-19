<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Invite;
use App\Entity\Organization;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;

interface InviteServiceInterface
{
    public function create(Organization $organization, string $name, string $email): Invite;

    public function confirm(Invite $invite, User $user): void;

    public function get(Uuid $id): Invite;

    public function findOneBy(array $params = []): ?Invite;
}
