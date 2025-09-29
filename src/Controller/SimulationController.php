<?php

namespace App\Controller;

use App\Entity\Simulation;
use App\Entity\StudentProspective;
use App\Entity\StudentManagement;
use App\Service\CfuSimulatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SimulationController extends AbstractController
{
    public function __construct(
        private CfuSimulatorService $simulatorService,
        private EntityManagerInterface $em
    ) {}

    #[Route('/student/{studentId}/simulation/{simulationId}', name: 'app_simulation_show', requirements: ['studentId' => '\d+', 'simulationId' => '\d+'])]
    public function show(int $studentId, int $simulationId, EntityManagerInterface $em, SessionInterface $session, Request $request): Response
    {
        $this->checkToken($session);
        
        // Verifica che lo studente esista
        $student = $em->getRepository(StudentProspective::class)->find($studentId);
        if (!$student) {
            throw $this->createNotFoundException('Studente non trovato');
        }
        
        // Verifica che la simulazione esista e appartenga allo studente
        $simulation = $em->getRepository(Simulation::class)->find($simulationId);
        if (!$simulation || $simulation->getStudentId() !== $studentId) {
            throw $this->createNotFoundException('Simulazione non trovata');
        }
        
        // Ottieni il messaggio di successo dalla sessione per mostrarlo
        $sessionSuccessMessage = $session->get('success_message');
        if ($sessionSuccessMessage) {
            $session->remove('success_message'); // Rimuovi il messaggio dopo averlo ottenuto
        }
        
        // Carica i corsi di laurea disponibili per il simulatore
        $cdlRepo = $em->getRepository(\App\Entity\ZcfuCdl::class);
        $cdlData = $cdlRepo->createQueryBuilder('c')
            ->select('c.cdl, c.orient')
            ->orderBy('c.cdl', 'ASC')
            ->addOrderBy('c.orient', 'ASC')
            ->getQuery()
            ->getArrayResult();
        
        // Carica le SSD per il CDL della simulazione
        $ssdData = [];
        if ($simulation->getCdl()) {
            $riconoscibiliRepo = $em->getRepository(\App\Entity\ZcfuRiconoscibile::class);
            $ssdData = $riconoscibiliRepo->createQueryBuilder('r')
                ->where('r.cdl = :cdl')
                ->setParameter('cdl', $simulation->getCdl())
                ->getQuery()
                ->getArrayResult();
        }
        
        // Debug: log dei dati della simulazione
        error_log("🔍 SIMULATION DEBUG - ID: " . $simulation->getId());
        error_log("🔍 CDL: " . $simulation->getCdl());
        error_log("🔍 Input Data: " . json_encode($simulation->getInputData()));
        error_log("🔍 Detail Results: " . json_encode($simulation->getDetailResults()));
        error_log("🔍 Summary Results: " . json_encode($simulation->getSummaryResults()));
        error_log("🔍 Leftover Results: " . json_encode($simulation->getLeftoverResults()));
        
        return $this->render('simulation/show.html.twig', [
            'student' => $student,
            'simulation' => $simulation,
            'cdlOptions' => $cdlData,
            'ssdOptions' => $ssdData,
            'successMessage' => $sessionSuccessMessage
        ]);
    }

    #[Route('/api/simulation/{id}', name: 'app_get_simulation', methods: ['GET'])]
    public function getSimulation(int $id, SessionInterface $session): JsonResponse
    {
        $this->checkToken($session);
        
        try {
            $simulation = $this->em->getRepository(Simulation::class)->find($id);
            
            if (!$simulation) {
                return new JsonResponse(['error' => 'Simulazione non trovata'], 404);
            }
            
            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $simulation->getId(),
                    'studentId' => $simulation->getStudentId(),
                    'cdl' => $simulation->getCdl(),
                    'inputData' => $simulation->getInputData(),
                    'outputDetail' => $simulation->getDetailResults(),
                    'outputSummary' => $simulation->getSummaryResults(),
                    'outputLeftovers' => $simulation->getLeftoverResults(),
                    'totalCfuRecognized' => $simulation->getTotalCfuRecognized(),
                    'totalCfuRequired' => $simulation->getTotalCfuRequired(),
                    'totalCfuIntegrative' => $simulation->getTotalCfuIntegrative(),
                    'createdAt' => $simulation->getCreatedAt()->format('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Errore nel recupero della simulazione',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/simulation/{id}', name: 'app_update_simulation', methods: ['PUT'])]
    public function updateSimulation(int $id, Request $request, SessionInterface $session): JsonResponse
    {
        $this->checkToken($session);
        
        try {
            $simulation = $this->em->getRepository(Simulation::class)->find($id);
            
            if (!$simulation) {
                return new JsonResponse(['error' => 'Simulazione non trovata'], 404);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse(['error' => 'Dati JSON non validi'], 400);
            }
            
            // Validazione input
            $validation = $this->validateInput($data);
            if (!$validation['valid']) {
                return new JsonResponse(['error' => 'Input non valido', 'details' => $validation['errors']], 400);
            }
            
            // Esegui simulazione
            $result = $this->simulatorService->simulate($data);
            
            // Aggiorna la simulazione esistente
            $simulation->setCdl($data['cdl']);
            $simulation->setInputData($data['discipline']);
            $simulation->setDetailResults($result['detail']);
            $simulation->setSummaryResults($result['summary']);
            $simulation->setLeftoverResults($result['leftovers']);
            $simulation->setTotalCfuRecognized($result['summary']['totalCfuRecognized'] ?? 0);
            $simulation->setTotalCfuRequired($result['summary']['totalCfuRequired'] ?? 0);
            $simulation->setTotalCfuIntegrative($result['summary']['totalCfuIntegrative'] ?? 0);
            $simulation->setUpdatedAt(new \DateTime());
            
            $this->em->flush();
            
            // Log dell'azione edit_simulation
            $this->logStudentAction($this->em, $simulation->getStudentId(), $session->get('user_token'), 'edit_simulation', $simulation->getId());
            
            // Imposta il messaggio di successo nella sessione
            $session->set('success_message', '✅ Simulazione aggiornata con successo!');
            
            return new JsonResponse([
                'success' => true,
                'data' => $result,
                'simulationId' => $simulation->getId()
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Errore durante l\'aggiornamento della simulazione',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    private function validateInput(array $data): array
    {
        $errors = [];
        
        if (empty($data['cdl'])) {
            $errors[] = 'CDL è obbligatorio';
        }
        
        if (empty($data['discipline']) || !is_array($data['discipline'])) {
            $errors[] = 'Almeno una disciplina è obbligatoria';
        } else {
            foreach ($data['discipline'] as $index => $discipline) {
                if (empty($discipline['ssd'])) {
                    $errors[] = "SSD obbligatorio per disciplina " . ($index + 1);
                }
                if (empty($discipline['nome'])) {
                    $errors[] = "Nome disciplina obbligatorio per disciplina " . ($index + 1);
                }
                if (empty($discipline['cfu']) || !is_numeric($discipline['cfu']) || $discipline['cfu'] <= 0) {
                    $errors[] = "CFU validi obbligatori per disciplina " . ($index + 1);
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    private function logStudentAction(EntityManagerInterface $em, int $studentId, string $currentToken, string $action, ?int $simulationId = null): void
    {
        $log = new StudentManagement();
        $log->setStudentId($studentId);
        $log->setCurrentToken($currentToken);
        $log->setAction($action);
        $log->setSimulationId($simulationId);
        $log->setModifiedAt(new \DateTime());
        
        $em->persist($log);
        $em->flush();
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

