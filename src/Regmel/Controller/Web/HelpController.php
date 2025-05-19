<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelpController extends AbstractController
{
    #[Route('/saiba-mais', name: 'regmel_about', methods: ['GET', 'POST'])]
    public function about(): Response
    {
        return $this->render('regmel/help/about.html.twig');
    }
}
