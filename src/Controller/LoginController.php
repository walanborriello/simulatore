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
            return $this->render('error/403.html.twig');
        }
        
        // Controlla se c'è già una sessione attiva
        if ($session->has('user_token')) {
            $currentToken = $session->get('user_token');
            if ($currentToken !== $token) {
                return $this->render('login/session_active.html.twig', [
                    'currentToken' => $currentToken,
                    'newToken' => $token
                ]);
            }
            // Se è lo stesso token, vai direttamente alla homepage
            return $this->redirectToRoute('app_index');
        }
        
        // Validazione token semplice
        if ($this->isValidToken($token)) {
            $session->set('user_token', $token);
            $session->set('user_role', 'segretary');
            
            return $this->redirectToRoute('app_index');
        }
        
        return $this->render('error/403.html.twig');
    }
    
    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request, SessionInterface $session): Response
    {
        $redirectUrl = $request->query->get('redirect');
        $session->clear();
        
        if ($redirectUrl) {
            return $this->redirect($redirectUrl);
        }
        
        return $this->render('logout/info.html.twig');
    }
    
    private function isValidToken(string $token): bool
    {
        // Per ora accettiamo qualsiasi token non vuoto
        // In produzione potresti validare contro un database o servizio esterno
        return !empty($token);
    }
}
