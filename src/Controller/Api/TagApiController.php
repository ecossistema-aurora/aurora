<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Helper\EntityIdNormalizerHelper;
use App\Service\Interface\TagServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;

final class TagApiController extends AbstractApiController
{
    public function __construct(
        private readonly TagServiceInterface $service,
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        $tag = $this->service->create($request->toArray());

        return $this->json($tag, Response::HTTP_CREATED, context: ['groups' => ['tag.get', 'tag.get.item']]);
    }

    public function get(?Uuid $id): JsonResponse
    {
        $tag = $this->service->get($id);

        return $this->json($tag, context: ['groups' => ['tag.get', 'tag.get.item']]);
    }

    public function list(): JsonResponse
    {
        return $this->json($this->service->list(), context: [
            'groups' => 'tag.get',
            AbstractNormalizer::CALLBACKS => [
                'parent' => [EntityIdNormalizerHelper::class, 'normalizeEntityId'],
            ],
        ]);
    }

    public function update(?Uuid $id, Request $request): JsonResponse
    {
        $tag = $this->service->update($id, $request->toArray());

        return $this->json($tag, Response::HTTP_OK, context: ['groups' => ['tag.get', 'tag.get.item']]);
    }

    public function remove(?Uuid $id): JsonResponse
    {
        $this->service->remove($id);

        return $this->json(data: [], status: Response::HTTP_NO_CONTENT);
    }
}
