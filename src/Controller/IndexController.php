<?php

namespace App\Controller;

use App\Entity\StudentProspective;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em, SessionInterface $session, Request $request): Response
    {
        if (!$session->has('user_token')) {
            return $this->redirectToRoute('app_login');
        }
        
        $this->checkToken($session);
        
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Filtri
        $nome = $request->query->get('nome', '');
        $cognome = $request->query->get('cognome', '');
        $email = $request->query->get('email', '');
        $codiceFiscale = $request->query->get('codiceFiscale', '');
        $soloMiei = $request->query->get('soloMiei', false);
        
        $qb = $em->getRepository(StudentProspective::class)->createQueryBuilder('s');
        
        if ($nome) {
            $qb->andWhere('s.firstName LIKE :nome')
               ->setParameter('nome', '%' . $nome . '%');
        }
        
        if ($cognome) {
            $qb->andWhere('s.lastName LIKE :cognome')
               ->setParameter('cognome', '%' . $cognome . '%');
        }
        
        if ($email) {
            $qb->andWhere('s.email LIKE :email')
               ->setParameter('email', '%' . $email . '%');
        }
        
        if ($codiceFiscale) {
            $qb->andWhere('s.codiceFiscale LIKE :codiceFiscale')
               ->setParameter('codiceFiscale', '%' . $codiceFiscale . '%');
        }
        
        if ($soloMiei) {
            $qb->andWhere('s.managedBy = :userToken')
               ->setParameter('userToken', $session->get('user_token'));
        }
        
        $qb->orderBy('s.createdAt', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit);
        
        $students = $qb->getQuery()->getResult();
        
        // Count totale per paginazione
        $countQb = $em->getRepository(StudentProspective::class)->createQueryBuilder('s');
        
        if ($nome) {
            $countQb->andWhere('s.firstName LIKE :nome')
                    ->setParameter('nome', '%' . $nome . '%');
        }
        
        if ($cognome) {
            $countQb->andWhere('s.lastName LIKE :cognome')
                    ->setParameter('cognome', '%' . $cognome . '%');
        }
        
        if ($email) {
            $countQb->andWhere('s.email LIKE :email')
                    ->setParameter('email', '%' . $email . '%');
        }
        
        if ($codiceFiscale) {
            $countQb->andWhere('s.codiceFiscale LIKE :codiceFiscale')
                    ->setParameter('codiceFiscale', '%' . $codiceFiscale . '%');
        }
        
        if ($soloMiei) {
            $countQb->andWhere('s.managedBy = :userToken')
                    ->setParameter('userToken', $session->get('user_token'));
        }
        
        $totalStudents = $countQb->select('COUNT(s.id)')->getQuery()->getSingleScalarResult();
            
        $totalPages = ceil($totalStudents / $limit);
        
        // Gestisci messaggio di successo
        $successMessage = $request->query->get('success');
        if ($successMessage) {
            $session->set('success_message', $successMessage);
        }
        
        return $this->render('student/index.html.twig', [
            'students' => $students,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalStudents' => $totalStudents
        ]);
    }
    
    private function checkToken(SessionInterface $session): void
    {
        if (!$session->has('user_token') || !$session->has('user_role')) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Accesso negato. Token mancante.');
        }
        
        if ($session->get('user_role') !== 'segretary') {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Accesso negato. Ruolo non valido.');
        }
    }

}
