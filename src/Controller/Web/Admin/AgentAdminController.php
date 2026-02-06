<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\DocumentService\AgentTimelineDocumentService;
use App\Entity\Agent;
use App\Entity\AgentAddress;
use App\Entity\City;
use App\Enum\FlashMessageTypeEnum;
use App\Enum\UserRolesEnum;
use App\Exception\ValidatorException;
use App\Repository\Interface\AddressRepositoryInterface;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\StateServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgentAdminController extends AbstractAdminController
{
    private const string VIEW_ADD = 'agent/create.html.twig';
    private const string VIEW_EDIT = 'agent/edit.html.twig';

    public const string CREATE_FORM_ID = 'add-agent';
    public const string EDIT_FORM_ID = 'edit-agent';

    public function __construct(
        private readonly AgentServiceInterface $service,
        private readonly AgentTimelineDocumentService $documentService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly TranslatorInterface $translator,
        private readonly StateServiceInterface $stateService,
        private readonly AddressRepositoryInterface $addressRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function list(UserInterface $user): Response
    {
        $agents = $this->service->findBy();

        $token = $this->jwtManager->create($user);

        return $this->render('agent/list.html.twig', [
            'agents' => $agents,
            'token' => $token,
        ]);
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function create(Request $request): Response
    {
        if (false === $request->isMethod(Request::METHOD_POST)) {
            return $this->render(self::VIEW_ADD, [
                'form_id' => self::CREATE_FORM_ID,
                'states' => $this->stateService->list(),
                'cities' => [],
            ]);
        }

        $this->validCsrfToken(self::CREATE_FORM_ID, $request);

        $errors = [];

        try {
            $agentData = $this->extractAgentDataFromRequest($request);
            $addressData = $this->extractAddressDataFromRequest($request);

            $agent = $this->service->create($agentData);

            if (!empty($addressData)) {
                $this->createAgentAddress($agent, $addressData);
            }

            $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, $this->translator->trans('view.agent.message.created'));
        } catch (ValidatorException $exception) {
            $errors = $exception->getConstraintViolationList();
        } catch (Exception $exception) {
            $errors = [$exception->getMessage()];
        }

        if (false === empty($errors)) {
            return $this->render(self::VIEW_ADD, [
                'errors' => $errors,
                'form_id' => self::CREATE_FORM_ID,
                'states' => $this->stateService->list(),
                'cities' => [],
            ]);
        }

        return $this->redirectToRoute('admin_agent_list');
    }

    private function extractAgentDataFromRequest(Request $request): array
    {
        return [
            'id' => Uuid::v4(),
            'name' => $request->get('name'),
            'shortBio' => $request->get('shortBio'),
            'longBio' => $request->get('shortBio'),
            'culture' => false,
            'user' => $this->security->getUser()->getId(),
            'fiscalCode' => $request->get('cpf') ?: $request->get('mei'),
            'extraFields' => $this->extractExtraFieldsFromRequest($request),
        ];
    }

    private function extractExtraFieldsFromRequest(Request $request): array
    {
        $extraFields = [];

        $optionalFields = ['social_name', 'full_name', 'public_email', 'private_phone1', 'private_phone2', 'site'];

        foreach ($optionalFields as $field) {
            if ($request->get($field)) {
                $extraFields[$field] = $request->get($field);
            }
        }

        return $extraFields;
    }

    private function extractAddressDataFromRequest(Request $request): array
    {
        if (!$request->get('postal_code') && !$request->get('street')) {
            return [];
        }

        return [
            'zipcode' => $request->get('postal_code') ?: '',
            'street' => $request->get('street') ?: '',
            'number' => $request->get('number') ?: '',
            'neighborhood' => $request->get('neighborhood') ?: '',
            'complement' => $request->get('complement_or_reference_point') ?: '',
            'cityId' => $request->get('address_city'),
        ];
    }

    private function createAgentAddress(Agent $agent, array $addressData): void
    {
        $agentAddress = new AgentAddress();
        $agentAddress->setId(Uuid::v4());
        $agentAddress->setZipcode($addressData['zipcode']);
        $agentAddress->setStreet($addressData['street']);
        $agentAddress->setNumber($addressData['number']);
        $agentAddress->setNeighborhood($addressData['neighborhood']);

        if ($addressData['complement']) {
            $agentAddress->setComplement($addressData['complement']);
        }

        if ($addressData['cityId']) {
            $city = $this->entityManager->getRepository(City::class)->find($addressData['cityId']);
            if ($city) {
                $agentAddress->setCity($city);
            }
        }

        $agentAddress->setOwner($agent);
        $this->addressRepository->save($agentAddress);
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function timeline(?Uuid $id): Response
    {
        $agent = $this->service->get($id);

        $this->denyAccessUnlessGranted('get', $agent);

        $events = $this->documentService->getEventsByEntityId($id);

        return $this->render('agent/timeline.html.twig', [
            'agent' => $agent,
            'events' => $events,
        ]);
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function remove(?Uuid $id): Response
    {
        $agent = $this->service->get($id);

        $this->denyAccessUnlessGranted('remove', $agent);

        $this->service->remove($id);

        $this->addFlash('success', 'view.agent.message.deleted');

        return $this->redirectToRoute('admin_agent_list');
    }

    public function edit(Uuid $id, Request $request): Response
    {
        $agent = $this->service->get($id);

        $this->denyAccessUnlessGranted('edit', $agent);

        if (false === $request->isMethod(Request::METHOD_POST)) {
            return $this->render(self::VIEW_EDIT, [
                'agent' => $agent,
                'form_id' => self::EDIT_FORM_ID,
            ]);
        }

        $this->validCsrfToken(self::EDIT_FORM_ID, $request);
        try {
            $this->service->update($id, [
                'name' => $request->get('name'),
                'shortBio' => $request->request->get('short_description'),
                'longBio' => $request->request->get('long_description'),
            ]);

            $portfolioImages = $request->files->get('portfolioImages') ?? [];
            $portfolioDescriptions = $request->request->all('portfolioDescriptions') ?? [];
            foreach ($portfolioImages as $index => $portfolioImage) {
                $description = $portfolioDescriptions[$index] ?? null;
                $this->service->addPortfolioImage($agent, $portfolioImage, $description);
            }

            $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, $this->translator->trans('view.agent.message.updated'));
        } catch (ValidatorException $exception) {
            $errors = $exception->getConstraintViolationList();
        } catch (Exception $exception) {
            $errors = [$exception->getMessage()];
        }

        if (false === empty($errors)) {
            return $this->render(self::VIEW_EDIT, [
                'agent' => $agent,
                'errors' => $errors,
                'form_id' => self::EDIT_FORM_ID,
            ]);
        }

        return $this->redirectToRoute('admin_agent_edit', ['id' => $id]);
    }

    #[IsGranted(UserRolesEnum::ROLE_USER->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function removePortfolioPhoto(Uuid $id, Uuid $photoId): Response
    {
        try {
            $this->service->removePortfolioImage($id, $photoId);
            $this->addFlash(FlashMessageTypeEnum::SUCCESS->value, $this->translator->trans('photo_removed'));
        } catch (Exception $exception) {
            $this->addFlash(FlashMessageTypeEnum::ERROR->value, $exception->getMessage());
        }

        return $this->redirectToRoute('admin_agent_edit', ['id' => $id]);
    }
}
