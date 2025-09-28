<?php

namespace App\Controller;

use App\Entity\StudentProspective;
use App\Entity\Simulation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StudentController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(EntityManagerInterface $em, SessionInterface $session, Request $request): Response
    {
        $this->checkToken($session);
        
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $students = $em->getRepository(StudentProspective::class)
            ->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
            
        $totalStudents = $em->getRepository(StudentProspective::class)
            ->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
            
        $totalPages = ceil($totalStudents / $limit);
        
        return $this->render('student/index.html.twig', [
            'students' => $students,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalStudents' => $totalStudents
        ]);
    }
    
    #[Route('/student/new', name: 'app_student_new')]
    public function new(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $this->checkToken($session);
        
        $student = new StudentProspective();
        
        if ($request->isMethod('POST')) {
            $student->setFirstName($request->request->get('firstName', ''));
            $student->setLastName($request->request->get('lastName', ''));
            $student->setEmail($request->request->get('email', ''));
            $student->setPhone($request->request->get('phone', ''));
            $student->setNotes($request->request->get('notes', ''));
            $student->setUpdatedAt(new \DateTime());
            
            $em->persist($student);
            $em->flush();
            
            $session->set('success_message', 'Studente creato con successo');
            return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
        }
        
        return $this->render('student/new.html.twig', [
            'student' => $student
        ]);
    }
    
    #[Route('/student/{id}', name: 'app_student_show', requirements: ['id' => '\d+'])]
    public function show(int $id, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $this->checkToken($session);
        
        $student = $em->getRepository(StudentProspective::class)->find($id);
        
        if (!$student) {
            throw $this->createNotFoundException('Studente non trovato');
        }
        
        $simulations = $em->getRepository(Simulation::class)
            ->findBy(['student' => $student], ['createdAt' => 'DESC']);
        
        return $this->render('student/show.html.twig', [
            'student' => $student,
            'simulations' => $simulations
        ]);
    }
    
    #[Route('/student/{id}/edit', name: 'app_student_edit', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $this->checkToken($session);
        
        $student = $em->getRepository(StudentProspective::class)->find($id);
        
        if (!$student) {
            throw $this->createNotFoundException('Studente non trovato');
        }
        
        if ($request->isMethod('POST')) {
            $student->setFirstName($request->request->get('firstName', ''));
            $student->setLastName($request->request->get('lastName', ''));
            $student->setEmail($request->request->get('email', ''));
            $student->setPhone($request->request->get('phone', ''));
            $student->setNotes($request->request->get('notes', ''));
            $student->setUpdatedAt(new \DateTime());
            
            $em->flush();
            
            $session->set('success_message', 'Studente aggiornato con successo');
            return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
        }
        
        return $this->render('student/edit.html.twig', [
            'student' => $student
        ]);
    }
    
    private function checkToken(SessionInterface $session): void
    {
        if (!$session->has('user_token') || !$session->has('user_role')) {
            throw new AccessDeniedHttpException('Accesso negato. Token mancante.');
        }
        
        if ($session->get('user_role') !== 'segretary') {
            throw new AccessDeniedHttpException('Accesso negato. Ruolo non valido.');
        }
    }
}
