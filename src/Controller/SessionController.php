<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SessionController extends AbstractController
{
    #[Route('/clear-session-message', name: 'app_clear_session_message', methods: ['POST'])]
    public function clearSessionMessage(SessionInterface $session): JsonResponse
    {
        // Rimuove i messaggi dalla sessione
        $session->remove('success_message');
        $session->remove('error_message');
        
        return new JsonResponse(['status' => 'success']);
    }
}
