<?php

declare(strict_types=1);

namespace App\ExceptionResolver;

use Exception;
use Symfony\Component\HttpFoundation\Request;

interface ExceptionResolverInterface
{
    public function resolve(Request $request, Exception $exception): array;
}
