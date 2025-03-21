<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\Interface\UserServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class UserApiController extends AbstractApiController
{
    public function __construct(
        private readonly UserServiceInterface $service
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        $user = $this->service->create($request->toArray());

        return $this->json($user, status: Response::HTTP_CREATED, context: ['groups' => 'user.get']);
    }

    public function update(Uuid $id, Request $request): JsonResponse
    {
        $user = $this->service->update($id, $request->toArray());

        return $this->json($user, context: ['groups' => 'user.get']);
    }
}
