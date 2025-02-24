<?php

declare(strict_types=1);

namespace App\DataFixtures\Entity;

use App\Enum\SocialNetworkEnum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class SocialNetworkFixtures extends AbstractFixture
{
    public const string SOCIAL_NETWORK_ID_PREFIX = 'social-network';
    public const string SOCIAL_NETWORK_ID_1 = '5c151480-6230-4b57-8276-d7a8667a8ad5';
    public const string SOCIAL_NETWORK_ID_2 = '62d69b0a-543b-470e-bf15-53df16223dd5';
    public const string SOCIAL_NETWORK_ID_3 = 'a1a4d897-8319-47c5-98d9-516db20db52e';
    public const string SOCIAL_NETWORK_ID_4 = 'c5901550-77dd-4479-a9af-165556e740d0';
    public const string SOCIAL_NETWORK_ID_5 = '2bb86482-d2f9-4728-ad8f-e76af872ccbe';
    public const string SOCIAL_NETWORK_ID_6 = '849eca3e-9436-4fec-97f5-40b3c5a1243b';
    public const string SOCIAL_NETWORK_ID_7 = '95181741-93cc-4d85-be76-6330254ce8c6';
    public const string SOCIAL_NETWORK_ID_8 = '9c747003-ca61-405e-99bb-359f4fb0e8ea';
    public const string SOCIAL_NETWORK_ID_9 = '321752bc-cc65-417b-a350-b52e49d40776';

    public const array SOCIAL_NETWORKS = [
        [
            'id' => self::SOCIAL_NETWORK_ID_1,
            'code' => SocialNetworkEnum::FACEBOOK,
            'baseUrl' => 'https://facebook.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_2,
            'code' => SocialNetworkEnum::INSTAGRAM,
            'baseUrl' => 'https://instagra.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_3,
            'code' => SocialNetworkEnum::LINKEDIN,
            'baseUrl' => 'https://linkedin.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_4,
            'code' => SocialNetworkEnum::PINTEREST,
            'baseUrl' => 'https://pinterest.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_5,
            'code' => SocialNetworkEnum::SPOTIFY,
            'baseUrl' => 'https://spotify.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_6,
            'code' => SocialNetworkEnum::VIMEO,
            'baseUrl' => 'https://vimeo.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_7,
            'code' => SocialNetworkEnum::TIKTOK,
            'baseUrl' => 'https://tiktok.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_8,
            'code' => SocialNetworkEnum::X,
            'baseUrl' => 'https://x.com/',
        ],
        [
            'id' => self::SOCIAL_NETWORK_ID_9,
            'code' => SocialNetworkEnum::YOUTUBE,
            'baseUrl' => 'https://youtube.com/',
        ],
    ];

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected TokenStorageInterface $tokenStorage,
    ) {
        parent::__construct($entityManager, $tokenStorage);
    }

    public function load(ObjectManager $manager): void
    {
    }
}
