<?php

namespace App\Service;

use App\Entity\ZcfuCdl;
use App\Entity\ZcfuDis;
use App\Entity\ZcfuOfferta;
use App\Entity\ZcfuRegole;
use App\Entity\ZcfuRiconoscibile;
use Doctrine\ORM\EntityManagerInterface;

class CfuSimulatorService
{
    private EntityManagerInterface $em;
    private array $offerta = [];
    private array $regole = [];
    private array $riconoscibili = [];
    private array $discipline = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function simulate(array $input, ?int $studentId = null): array
    {
        $cdl = $input['cdl'] ?? '';
        $discipline = $input['discipline'] ?? [];

        if (empty($cdl) || empty($discipline)) {
            throw new \InvalidArgumentException('CDL e discipline sono obbligatori');
        }

        try {
            // Carica dati in memoria per performance
            $this->loadDataInMemory($cdl);
        } catch (\Exception $e) {
            throw new \Exception("Errore nel caricamento dati per CDL {$cdl}: " . $e->getMessage());
        }

        // Inizializza array per l'algoritmo
        $needed = [];
        $assigned = [];
        $available = [];

        // Popola needed con offerta obbligatoria (rosa=0)
        foreach ($this->offerta as $off) {
            if ($off['rosa'] == 0) {
                $needed[$off['OFF_ID']] = $off['CFU'];
                $assigned[$off['OFF_ID']] = 0;
            }
        }

        // Popola available con discipline esterne
        foreach ($discipline as $disc) {
            $ssd = $disc['ssd'] ?? '';
            $cfu = (int)($disc['cfu'] ?? 0);
            $nome = $disc['nome'] ?? '';

            if ($ssd && $cfu > 0) {
                $available[] = [
                    'ssd' => $ssd,
                    'cfu' => $cfu,
                    'nome' => $nome,
                    'used' => 0
                ];
            }
        }

        // Esegui algoritmo di simulazione
        $results = $this->executeSimulation($needed, $assigned, $available);

        return [
            'detail' => $results['detail'],
            'summary' => $results['summary'],
            'leftovers' => $results['leftovers'],
            'simulationId' => null // Sarà impostato quando salvato nel DB
        ];
    }

    private function loadDataInMemory(string $cdl): void
    {
        // Carica TUTTI i dati in memoria per performance (caricamento preventivo)
        
        // Carica TUTTE le offerte (non solo per CDL specifico)
        $offertaRepo = $this->em->getRepository(ZcfuOfferta::class);
        $offertaData = $offertaRepo->createQueryBuilder('o')
            ->getQuery()
            ->getArrayResult();

        $this->offerta = [];
        foreach ($offertaData as $off) {
            if ($off['CDL'] === $cdl) {
                $this->offerta[$off['OFF_ID']] = $off;
            }
        }
        
        if (empty($this->offerta)) {
            throw new \Exception("Nessuna offerta trovata per CDL: {$cdl}");
        }

        // Carica TUTTE le regole
        $regoleRepo = $this->em->getRepository(ZcfuRegole::class);
        $regoleData = $regoleRepo->createQueryBuilder('r')
            ->getQuery()
            ->getArrayResult();

        $this->regole = [];
        foreach ($regoleData as $reg) {
            $this->regole[$reg['ID_off']][$reg['ID_ric']] = $reg['priorita'];
        }

        // Carica TUTTI i riconoscibili (non solo per CDL specifico)
        $riconoscibiliRepo = $this->em->getRepository(ZcfuRiconoscibile::class);
        $riconoscibiliData = $riconoscibiliRepo->createQueryBuilder('r')
            ->getQuery()
            ->getArrayResult();

        $this->riconoscibili = [];
        foreach ($riconoscibiliData as $ric) {
            if ($ric['CDL'] === $cdl) {
                $this->riconoscibili[$ric['ID_ric']] = $ric;
            }
        }

        // Carica TUTTE le discipline
        $disRepo = $this->em->getRepository(ZcfuDis::class);
        $disData = $disRepo->createQueryBuilder('d')
            ->getQuery()
            ->getArrayResult();

        $this->discipline = [];
        foreach ($disData as $dis) {
            $this->discipline[$dis['DIS_ID']] = $dis;
        }
    }

    private function executeSimulation(array $needed, array $assigned, array $available): array
    {
        $detail = [];
        $summary = [];
        $leftovers = [];

        // Prima fase: gestisci obbligatorie (rosa=0)
        foreach ($this->offerta as $off) {
            if ($off['rosa'] == 0) {
                $offId = $off['OFF_ID'];
                $requiredCfu = $off['CFU'];
                $disciplineName = $this->discipline[$off['DIS_ID']]['disciplina'] ?? 'Disciplina sconosciuta';

                // Cerca riconoscimenti per questa offerta
                $recognized = $this->findRecognition($offId, $available);

                $assigned[$offId] = $recognized['cfu'];
                $remaining = $requiredCfu - $recognized['cfu'];

                // Aggiungi a dettaglio
                $detail[] = [
                    'disciplina_unimarconi' => $disciplineName,
                    'cfu_richiesti' => $requiredCfu,
                    'disciplina_esterna' => $recognized['nome'] ?? '',
                    'cfu_assegnati' => $recognized['cfu'],
                    'priorita' => $recognized['priorita'] ?? 0,
                    'note' => $recognized['note'] ?? ''
                ];

                // Aggiungi a riepilogo
                $summary[] = [
                    'disciplina_unimarconi' => $disciplineName,
                    'cfu_richiesti' => $requiredCfu,
                    'cfu_riconosciuti' => $recognized['cfu'],
                    'integrativi_richiesti' => max(0, $remaining),
                    'stato' => $remaining == 0 ? 'tot' : ($recognized['cfu'] > 0 ? 'parziale' : 'non')
                ];
            }
        }

        // Seconda fase: gestisci gruppi a scelta (rosa>0)
        $rosaGroups = [];
        foreach ($this->offerta as $off) {
            if ($off['rosa'] > 0) {
                $rosaGroups[$off['rosa']][] = $off;
            }
        }

        foreach ($rosaGroups as $rosaId => $group) {
            $this->processRosaGroup($group, $available, $detail, $summary);
        }

        // Terza fase: calcola rimanenze
        foreach ($available as $avail) {
            if ($avail['used'] < $avail['cfu']) {
                $leftovers[] = [
                    'disciplina_esterna' => $avail['nome'],
                    'cfu_residui' => $avail['cfu'] - $avail['used'],
                    'motivazione' => 'Non utilizzati'
                ];
            }
        }

        return [
            'detail' => $detail,
            'summary' => $summary,
            'leftovers' => $leftovers
        ];
    }

    private function findRecognition(int $offId, array &$available): array
    {
        $bestMatch = ['cfu' => 0, 'nome' => '', 'priorita' => 999, 'note' => ''];

        // Cerca nelle regole per questa offerta
        if (isset($this->regole[$offId])) {
            foreach ($this->regole[$offId] as $ricId => $priorita) {
                if (isset($this->riconoscibili[$ricId])) {
                    $riconoscibile = $this->riconoscibili[$ricId];
                    
                    // Cerca disponibilità per questo SSD
                    foreach ($available as &$avail) {
                        if ($avail['ssd'] === $riconoscibile['riconoscibile'] && $avail['used'] < $avail['cfu']) {
                            $availableCfu = $avail['cfu'] - $avail['used'];
                            $offertaCfu = $this->offerta[$offId]['CFU'];
                            $maxCfu = $this->offerta[$offId]['maxCFU'] ?? $offertaCfu;
                            
                            $toUse = min($availableCfu, $maxCfu);
                            
                            if ($toUse > 0 && ($priorita < $bestMatch['priorita'] || 
                                ($priorita == $bestMatch['priorita'] && $toUse > $bestMatch['cfu']))) {
                                
                                $bestMatch = [
                                    'cfu' => $toUse,
                                    'nome' => $avail['nome'],
                                    'priorita' => $priorita,
                                    'note' => "SSD: {$riconoscibile['riconoscibile']}"
                                ];
                                
                                $avail['used'] += $toUse;
                            }
                        }
                    }
                }
            }
        }

        return $bestMatch;
    }

    private function processRosaGroup(array $group, array &$available, array &$detail, array &$summary): void
    {
        $totalRequired = 0;
        $totalAssigned = 0;

        foreach ($group as $off) {
            $totalRequired += $off['CFU'];
        }

        // Distribuisci CFU disponibili tra le discipline del gruppo
        foreach ($group as $off) {
            $offId = $off['OFF_ID'];
            $requiredCfu = $off['CFU'];
            $disciplineName = $this->discipline[$off['DIS_ID']]['disciplina'] ?? 'Disciplina sconosciuta';

            $recognized = $this->findRecognition($offId, $available);
            $totalAssigned += $recognized['cfu'];

            // Aggiungi a dettaglio
            $detail[] = [
                'disciplina_unimarconi' => $disciplineName,
                'cfu_richiesti' => $requiredCfu,
                'disciplina_esterna' => $recognized['nome'] ?? '',
                'cfu_assegnati' => $recognized['cfu'],
                'priorita' => $recognized['priorita'] ?? 0,
                'note' => $recognized['note'] ?? ''
            ];

            // Aggiungi a riepilogo
            $summary[] = [
                'disciplina_unimarconi' => $disciplineName,
                'cfu_richiesti' => $requiredCfu,
                'cfu_riconosciuti' => $recognized['cfu'],
                'integrativi_richiesti' => max(0, $requiredCfu - $recognized['cfu']),
                'stato' => $recognized['cfu'] == $requiredCfu ? 'tot' : ($recognized['cfu'] > 0 ? 'parziale' : 'non')
            ];
        }
    }
}
