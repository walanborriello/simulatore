<?php

namespace App\Controller;

use App\Entity\StudentProspective;
use App\Entity\StudentManagement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StudentController extends AbstractController
{
    
    #[Route('/student/new', name: 'app_student_new')]
    public function new(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $this->checkToken($session);
        
        $student = new StudentProspective();
        
        if ($request->isMethod('POST')) {
            $currentToken = $session->get('user_token');
            
            $student->setFirstName($request->request->get('firstName', ''));
            $student->setLastName($request->request->get('lastName', ''));
            $student->setEmail($request->request->get('email', ''));
            $student->setCodiceFiscale($request->request->get('codiceFiscale', ''));
            $student->setAteneoProvenienza($request->request->get('ateneoProvenienza', ''));
            $student->setCorsoStudioInteresse($request->request->get('corsoStudioInteresse', ''));
            $student->setPhone($request->request->get('phone', ''));
            $student->setNotes($request->request->get('notes', ''));
            $student->setManagedBy($currentToken);
            $student->setUpdatedAt(new \DateTime());
            
            $em->persist($student);
            $em->flush();
            
            // Log del cambio token per nuovo studente
            $this->logTokenChange($em, $student->getId(), null, $currentToken);
            
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
        
        return $this->render('student/show.html.twig', [
            'student' => $student
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
            $currentToken = $session->get('user_token');
            $oldToken = $student->getManagedBy();
            
            $student->setFirstName($request->request->get('firstName', ''));
            $student->setLastName($request->request->get('lastName', ''));
            $student->setEmail($request->request->get('email', ''));
            $student->setCodiceFiscale($request->request->get('codiceFiscale', ''));
            $student->setAteneoProvenienza($request->request->get('ateneoProvenienza', ''));
            $student->setCorsoStudioInteresse($request->request->get('corsoStudioInteresse', ''));
            $student->setPhone($request->request->get('phone', ''));
            $student->setNotes($request->request->get('notes', ''));
            $student->setManagedBy($currentToken);
            $student->setUpdatedAt(new \DateTime());
            
            $em->flush();
            
            // Log del cambio token se è diverso
            if ($oldToken !== $currentToken) {
                $this->logTokenChange($em, $student->getId(), $oldToken, $currentToken);
            }
            
            $session->set('success_message', 'Studente aggiornato con successo');
            return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
        }
        
        return $this->render('student/edit.html.twig', [
            'student' => $student
        ]);
    }
    
    #[Route('/student/{id}/delete', name: 'app_student_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $this->checkToken($session);
        
        $student = $em->getRepository(StudentProspective::class)->find($id);
        
        if (!$student) {
            throw $this->createNotFoundException('Studente non trovato');
        }
        
        $em->remove($student);
        $em->flush();
        
        $session->set('success_message', 'Studente eliminato con successo');
        return $this->redirectToRoute('app_index');
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
    
    private function logTokenChange(EntityManagerInterface $em, int $studentId, ?string $fromToken, string $toToken): void
    {
        $log = new StudentManagement();
        $log->setStudentId($studentId);
        $log->setFromToken($fromToken);
        $log->setToToken($toToken);
        $log->setModifiedAt(new \DateTime());
        
        $em->persist($log);
        $em->flush();
    }
}
