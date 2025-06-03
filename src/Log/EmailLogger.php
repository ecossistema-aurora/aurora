<?php

declare(strict_types=1);

namespace App\Log;

use App\Log\Interface\EmailLoggerInterface;
use Psr\Log\LoggerInterface;

final class EmailLogger implements EmailLoggerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function logEmailNotSent(string $email, string $messageError): void
    {
        $this->logger->error(
            'Could not send email to user: {email}. Error: {error}',
            [
                'email' => $email,
                'error' => $messageError,
            ]
        );
    }
}
