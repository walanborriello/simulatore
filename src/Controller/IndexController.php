<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(SessionInterface $session): Response
    {
        if (!$session->has('user_token')) {
            return $this->redirectToRoute('app_login');
        }
        return $this->redirectToRoute('app_student_index');
    }

    #[Route('/welcome', name: 'app_welcome')]
    public function welcome(): Response
    {
        return $this->render('index/simple.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
