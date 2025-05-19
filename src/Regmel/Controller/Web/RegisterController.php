<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web;

use App\Controller\Web\AbstractWebController;
use App\Enum\CompanyFrameworkEnum;
use App\Enum\OrganizationTypeEnum;
use App\Enum\UserRolesEnum;
use App\Exception\ValidatorException;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Service\Interface\CityServiceInterface;
use App\Service\Interface\StateServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterController extends AbstractWebController
{
    public const string VIEW_CITY = 'regmel/register/city.html.twig';
    public const string VIEW_COMPANY = 'regmel/register/company.html.twig';

    public const string FORM_CITY = 'register-city';
    public const string FORM_COMPANY = 'register-company';

    public function __construct(
        private readonly RegisterServiceInterface $registerService,
        private readonly StateServiceInterface $stateService,
        private readonly CityServiceInterface $cityService,
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    #[Route('/cadastro/municipio', name: 'regmel_register_city', methods: ['GET', 'POST'])]
    public function registerCity(Request $request): Response
    {
        $snpEmail = $this->parameterBag->get('app.email.address');

        $states = $this->stateService->list();

        $opportunity = $this->registerService->findOpportunityWithActivePhase(OrganizationTypeEnum::MUNICIPIO->value);

        if (false === $opportunity) {
            return $this->render('regmel/register/phase-not-active.html.twig');
        }

        if (Request::METHOD_POST !== $request->getMethod()) {
            return $this->render(self::VIEW_CITY, [
                'form_id' => self::FORM_CITY,
                'opportunities' => $this->registerService->findOpportunitiesBy(OrganizationTypeEnum::MUNICIPIO),
                'states' => $states,
            ]);
        }

        $this->validCsrfToken(self::FORM_CITY, $request);

        $errors = [];

        try {
            $this->registerService->saveOrganization(
                $this->createOrganizationDataForMunicipality($request),
                $request->files->get('joinForm'),
            );
        } catch (ValidatorException $exception) {
            foreach ($exception->getConstraintViolationList() as $violation) {
                $errors[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $this->translator->trans($violation->getMessage()),
                ];
            }
        } catch (UniqueConstraintViolationException $exception) {
            $errors[] = ['message' => $this->translator->trans('view.authentication.error.email_in_use')];
        } catch (Exception $exception) {
            if (str_contains($exception->getMessage(), 'Duplicate entry') || str_contains($exception->getMessage(), 'already registered')) {
                $errors[] = [
                    'message' => $this->translator->trans('organization_duplicate', ['%s' => $snpEmail]),
                ];
            } else {
                $errors[] = [
                    'message' => $exception->getMessage(),
                ];
            }
        }

        if (false === empty($errors)) {
            return $this->render(self::VIEW_CITY, [
                'errors' => $errors,
                'form_id' => self::FORM_CITY,
                'states' => $states,
                'opportunities' => $this->registerService->findOpportunitiesBy(OrganizationTypeEnum::MUNICIPIO),
                'snp_email' => $snpEmail,
            ]);
        }

        $this->addFlash('success', $this->translator->trans('municipality_created'));

        return $this->redirectToRoute('web_home_homepage');
    }

    #[Route('/cadastro/empresa', name: 'regmel_register_company', methods: ['GET', 'POST'])]
    public function registerCompany(Request $request): Response
    {
        $opportunity = $this->registerService->findOpportunityWithActivePhase(OrganizationTypeEnum::EMPRESA->value);

        if (false === $opportunity) {
            return $this->render('regmel/register/phase-not-active.html.twig');
        }

        if ('POST' !== $request->getMethod()) {
            return $this->render(self::VIEW_COMPANY, [
                'opportunities' => $this->registerService->findOpportunitiesBy(OrganizationTypeEnum::EMPRESA),
                'form_id' => self::FORM_COMPANY,
            ]);
        }

        $this->validCsrfToken(self::FORM_COMPANY, $request);

        $errors = [];

        try {
            $this->registerService->saveOrganization(
                $this->createOrganizationDataForCompany($request),
            );
        } catch (ValidatorException $exception) {
            foreach ($exception->getConstraintViolationList() as $violation) {
                $errors[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $this->translator->trans($violation->getMessage()),
                ];
            }
        } catch (Exception $exception) {
            $errors[] = [
                'message' => $exception->getMessage(),
            ];
        }

        if (false === empty($errors)) {
            return $this->render(self::VIEW_COMPANY, [
                'errors' => $errors,
                'form_id' => self::FORM_COMPANY,
                'opportunities' => $this->registerService->findOpportunitiesBy(OrganizationTypeEnum::EMPRESA),
            ]);
        }

        $this->addFlash('success', $this->translator->trans('company_created'));

        return $this->redirectToRoute('web_home_homepage');
    }

    private function createOrganizationDataForCompany(Request $request): array
    {
        $framework = CompanyFrameworkEnum::fromName($request->get('framework'));

        return [
            'organization' => [
                'id' => Uuid::v4(),
                'name' => $request->get('name'),
                'type' => OrganizationTypeEnum::EMPRESA->value,
                'extraFields' => [
                    'tipo' => OrganizationTypeEnum::fromName($request->get('type'))->value,
                    'email' => $request->get('email'),
                    'telefone' => $request->get('phone'),
                    'cnpj' => $request->get('cnpj'),
                    'framework' => $framework?->value,
                    'site' => $request->get('site'),
                ],
            ],
            'user' => [
                'id' => Uuid::v4(),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('userEmail'),
                'password' => $request->get('password'),
                'roles' => [UserRolesEnum::ROLE_COMPANY->value],
                'extraFields' => [
                    'telefone' => $request->get('userPhone'),
                    'cpf' => $request->get('cpf'),
                    'cargo' => $request->get('position'),
                ],
            ],
            'opportunity' => $request->get('opportunity'),
        ];
    }

    private function createOrganizationDataForMunicipality(Request $request): array
    {
        $city = $this->cityService->get($request->get('city'));

        return [
            'organization' => [
                'id' => Uuid::v4(),
                'name' => $city?->getName(),
                'type' => OrganizationTypeEnum::MUNICIPIO->value,
                'extraFields' => [
                    'cityId' => $city?->getId(),
                    'cityCode' => $city?->getCityCode(),
                    'region' => $city?->getState()->getRegion(),
                    'state' => $city?->getState()->getAcronym(),
                    'email' => $request->get('email'),
                    'telefone' => $request->get('phone'),
                    'site' => $request->get('site'),
                    'term_version' => 1,
                    'term_status' => 'awaiting',
                    'hasHousingExperience' => (bool) $request->get('hasHousingExperience'),
                    'hasPlhis' => (bool) $request->get('hasPlhis'),
                ],
            ],
            'user' => [
                'id' => Uuid::v4(),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('userEmail'),
                'password' => $request->get('password'),
                'roles' => [UserRolesEnum::ROLE_MUNICIPALITY->value],
                'extraFields' => [
                    'cpf' => $request->get('cpf'),
                    'cargo' => $request->get('position'),
                ],
            ],
            'opportunity' => $request->get('opportunity'),
        ];
    }

    #[Route('/organizations/check-duplicate', name: 'organization_check_duplicate', methods: ['GET'])]
    public function checkDuplicateOrganization(Request $request): Response
    {
        $name = $request->query->get('name');
        $cityId = $request->query->get('cityId');

        $exists = $this->registerService->isDuplicateOrganization($name, $cityId);

        return $this->json(['exists' => $exists]);
    }
}
