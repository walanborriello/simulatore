<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/welcome', name: 'app_welcome')]
    public function welcome(): Response
    {
        return $this->render('index/simple.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
