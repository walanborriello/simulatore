<?php

namespace App\Controller;

use App\Entity\Simulation;
use App\Service\CfuSimulatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SimulatoreController extends AbstractController
{
    private CfuSimulatorService $simulatorService;
    private EntityManagerInterface $em;

    public function __construct(CfuSimulatorService $simulatorService, EntityManagerInterface $em)
    {
        $this->simulatorService = $simulatorService;
        $this->em = $em;
    }


    #[Route('/api/simulate', name: 'app_simulate', methods: ['POST'])]
    public function simulate(Request $request, SessionInterface $session): JsonResponse
    {
        $this->checkToken($session);

        try {
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

            // Salva simulazione solo se esplicitamente richiesto
            $simulationId = null;
            if (isset($data['saveSimulation']) && $data['saveSimulation'] === true && isset($data['studentId']) && $data['studentId']) {
                $simulation = $this->saveSimulation($data, $result, $session->get('user_token'));
                $simulationId = $simulation->getId();
                $result['simulationId'] = $simulationId;
                
                // PUNTO 4: Log dell'azione simulated
                $this->logStudentAction($this->em, $data['studentId'], $session->get('user_token'), 'simulated', $simulationId);
                
                // Imposta il messaggio di successo nella sessione
                // Se studentId è presente, significa che è stato salvato anche lo studente
                if (isset($data['studentId']) && $data['studentId']) {
                    $session->set('success_message', '✅ Studente e simulazione salvati con successo!');
                } else {
                    $session->set('success_message', '✅ Simulazione salvata con successo!');
                }
            }

            return new JsonResponse([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Errore durante la simulazione',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    #[Route('/api/cdl', name: 'app_get_cdl', methods: ['GET'])]
    public function getCdl(): JsonResponse
    {
        $cdlRepo = $this->em->getRepository(\App\Entity\ZcfuCdl::class);
        $cdlData = $cdlRepo->createQueryBuilder('c')
            ->select('c.cdl, c.orient')
            ->getQuery()
            ->getArrayResult();

        $cdlList = [];
        foreach ($cdlData as $cdl) {
            $cdlList[] = [
                'id' => $cdl['cdl'],
                'text' => $cdl['cdl'] . ' - ' . $cdl['orient']
            ];
        }

        return new JsonResponse($cdlList);
    }

    #[Route('/api/ssd/{cdl}', name: 'app_get_ssd', methods: ['GET'])]
    public function getSsd(string $cdl): JsonResponse
    {
        $riconoscibiliRepo = $this->em->getRepository(\App\Entity\ZcfuRiconoscibile::class);
        $riconoscibiliData = $riconoscibiliRepo->createQueryBuilder('r')
            ->where('r.cdl = :cdl')
            ->setParameter('cdl', $cdl)
            ->getQuery()
            ->getArrayResult();

        $ssdList = [];
        foreach ($riconoscibiliData as $ric) {
            $ssdList[] = [
                'id' => $ric['riconoscibile'],
                'text' => $ric['riconoscibile']
            ];
        }

        return new JsonResponse($ssdList);
    }

    #[Route('/api/ssd-all', name: 'app_get_all_ssd', methods: ['GET'])]
    public function getAllSsd(): JsonResponse
    {
        // Pre-carica tutti i dati SSD per tutti i CDL per evitare AJAX lente
        $riconoscibiliRepo = $this->em->getRepository(\App\Entity\ZcfuRiconoscibile::class);
        $riconoscibiliData = $riconoscibiliRepo->createQueryBuilder('r')
            ->select('r.cdl, r.riconoscibile')
            ->orderBy('r.cdl', 'ASC')
            ->addOrderBy('r.riconoscibile', 'ASC')
            ->getQuery()
            ->getArrayResult();

        // Organizza i dati per CDL
        $ssdByCdl = [];
        foreach ($riconoscibiliData as $ric) {
            $cdl = $ric['cdl'];
            if (!isset($ssdByCdl[$cdl])) {
                $ssdByCdl[$cdl] = [];
            }
            $ssdByCdl[$cdl][] = [
                'id' => $ric['riconoscibile'],
                'text' => $ric['riconoscibile']
            ];
        }

        return new JsonResponse($ssdByCdl);
    }

    #[Route('/api/simulation/{id}/delete', name: 'app_delete_simulation', methods: ['DELETE'])]
    public function deleteSimulation(int $id, SessionInterface $session): JsonResponse
    {
        try {
            $this->checkToken($session);

            $simulation = $this->em->getRepository(\App\Entity\Simulation::class)->find($id);
            
            if (!$simulation) {
                return new JsonResponse(['error' => 'Simulazione non trovata'], 404);
            }

            // Log dell'eliminazione prima di eliminare
            $this->logStudentAction($this->em, $simulation->getStudentId(), $session->get('user_token'), 'simulation_deleted', $id);

            // Elimina la simulazione
            $this->em->remove($simulation);
            $this->em->flush();

            // Imposta il messaggio di successo nella sessione
            $session->set('success_message', '✅ Simulazione eliminata con successo!');

            return new JsonResponse([
                'success' => true,
                'message' => 'Simulazione eliminata con successo'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Errore durante l\'eliminazione',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    private function validateInput(array $data): array
    {
        $errors = [];

        if (empty($data['cdl'])) {
            $errors['cdl'] = 'Seleziona un corso di laurea valido per procedere con la simulazione.';
        }

        if (empty($data['discipline']) || !is_array($data['discipline'])) {
            $errors['discipline'] = 'Inserisci almeno una disciplina esterna.';
        } else {
            foreach ($data['discipline'] as $index => $disc) {
                if (empty($disc['ssd'])) {
                    $errors["discipline_{$index}_ssd"] = 'Seleziona il settore scientifico disciplinare della materia che hai sostenuto.';
                }
                if (empty($disc['cfu']) || $disc['cfu'] < 1 || $disc['cfu'] > 30) {
                    $errors["discipline_{$index}_cfu"] = 'Inserisci il numero di crediti formativi universitari (CFU) della disciplina (1-30).';
                }
                if (empty($disc['nome'])) {
                    $errors["discipline_{$index}_nome"] = 'Inserisci il nome completo della disciplina che hai sostenuto.';
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function saveSimulation(array $input, array $result, string $userToken): Simulation
    {
        $simulation = new Simulation();
        $simulation->setStudentId($input['studentId']);
        $simulation->setCdl($input['cdl']);
        $simulation->setInputData($input['discipline']);
        $simulation->setDetailResults($result['detail']);
        $simulation->setSummaryResults($result['summary']);
        $simulation->setLeftoverResults($result['leftovers']);
        $simulation->setManagedBy($userToken);

        // Calcola totali
        $totalRecognized = 0;
        $totalRequired = 0;
        $totalIntegrative = 0;

        foreach ($result['summary'] as $summary) {
            $totalRequired += $summary['cfu_richiesti'];
            $totalRecognized += $summary['cfu_riconosciuti'];
            $totalIntegrative += $summary['integrativi_richiesti'];
        }

        $simulation->setTotalCfuRequired($totalRequired);
        $simulation->setTotalCfuRecognized($totalRecognized);
        $simulation->setTotalCfuIntegrative($totalIntegrative);

        $this->em->persist($simulation);
        $this->em->flush();

        return $simulation;
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
    
    private function logStudentAction(EntityManagerInterface $em, int $studentId, string $currentToken, string $action, ?int $simulationId = null): void
    {
        $log = new \App\Entity\StudentManagement();
        $log->setStudentId($studentId);
        $log->setCurrentToken($currentToken);
        $log->setAction($action);
        $log->setSimulationId($simulationId);
        $log->setModifiedAt(new \DateTime());
        
        $em->persist($log);
        $em->flush();
    }
    
    /**
     * PUNTO 3: Traccia il cambio token quando un utente diverso gestisce una simulazione
     */
    private function logTokenChange(EntityManagerInterface $em, int $studentId, string $fromToken, string $toToken, ?int $simulationId = null): void
    {
        $log = new \App\Entity\StudentManagement();
        $log->setStudentId($studentId);
        $log->setCurrentToken($fromToken); // Il vecchio token va in current_token
        $log->setToToken($toToken); // Il nuovo token va in to_token
        $log->setAction('change_token');
        $log->setSimulationId($simulationId);
        $log->setModifiedAt(new \DateTime());
        
        $em->persist($log);
        $em->flush();
    }
    
}
