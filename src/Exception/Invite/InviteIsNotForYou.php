<?php

declare(strict_types=1);

namespace App\Exception\Invite;

use RuntimeException;
use Throwable;

class InviteIsNotForYou extends RuntimeException
{
    public function __construct(?Throwable $previous = null)
    {
        $message = 'The invite is not for you.';

        parent::__construct($message, 0, $previous);
    }
}
