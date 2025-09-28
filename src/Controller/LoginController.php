<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, SessionInterface $session): Response
    {
        $token = $request->query->get('token');
        
        if (!$token) {
            return $this->render('login/error.html.twig', [
                'error' => 'Token mancante. Accesso negato.'
            ]);
        }
        
        // Validazione token (qui potresti aggiungere logica più complessa)
        if ($this->isValidToken($token)) {
            $session->set('user_token', $token);
            $session->set('user_role', 'segretary');
            
            return $this->redirectToRoute('app_index');
        }
        
        return $this->render('login/error.html.twig', [
            'error' => 'Token non valido. Accesso negato.'
        ]);
    }
    
    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();
        return $this->redirectToRoute('app_login');
    }
    
    private function isValidToken(string $token): bool
    {
        // Per ora accettiamo qualsiasi token non vuoto
        // In produzione potresti validare contro un database o servizio esterno
        return !empty($token) && strlen($token) >= 8;
    }
}
