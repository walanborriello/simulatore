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
        
        // Prima ottieni l'orientamento per questo CDL
        $cdlRepo = $this->em->getRepository(\App\Entity\ZcfuCdl::class);
        $cdlEntities = $cdlRepo->createQueryBuilder('c')
            ->where('c.cdl = :cdl')
            ->setParameter('cdl', $cdl)
            ->getQuery()
            ->getResult();
        
        $cdlEntity = !empty($cdlEntities) ? $cdlEntities[0] : null;
        $oriId = $cdlEntity ? $cdlEntity->getIdOri() : null;
        
        // Carica TUTTE le offerte per questo CDL (sia ORI_ID=0 che ORI_ID specifico)
        // ORI_ID=0 = orientamento generale, ORI_ID specifico = orientamento del CDL
        $offertaRepo = $this->em->getRepository(ZcfuOfferta::class);
        $qb = $offertaRepo->createQueryBuilder('o')
            ->where('o.cdl = :cdl')
            ->setParameter('cdl', $cdl);
        
        if ($oriId !== null) {
            $qb->andWhere('(o.oriId = 0 OR o.oriId = :oriId)')
               ->setParameter('oriId', $oriId);
        } else {
            $qb->andWhere('o.oriId = 0');
        }
        
        $offertaEntities = $qb->getQuery()->getResult();

        $this->offerta = [];
        foreach ($offertaEntities as $off) {
            $this->offerta[$off->getOffId()] = [
                'OFF_ID' => $off->getOffId(),
                'ORI_ID' => $off->getOriId(),
                'DIS_ID' => $off->getDisId(),
                'rosa' => $off->getRosa(),
                'maxCFU' => $off->getMaxCFU(),
                'TAF' => $off->getTaf(),
                'CFU' => $off->getCfu(),
                'ANNO' => $off->getAnno(),
                'AA' => $off->getAa(),
                'CDL' => $off->getCdl()
            ];
        }
        
        if (empty($this->offerta)) {
            throw new \Exception("Nessuna offerta trovata per CDL: {$cdl}");
        }

        // Carica TUTTE le regole
        $regoleRepo = $this->em->getRepository(ZcfuRegole::class);
        $regoleEntities = $regoleRepo->createQueryBuilder('r')
            ->getQuery()
            ->getResult();

        $this->regole = [];
        foreach ($regoleEntities as $reg) {
            $this->regole[$reg->getIdOff()][$reg->getIdRic()] = $reg->getPriorita();
        }

        // Carica TUTTI i riconoscibili (non solo per CDL specifico)
        $riconoscibiliRepo = $this->em->getRepository(ZcfuRiconoscibile::class);
        $riconoscibiliEntities = $riconoscibiliRepo->createQueryBuilder('r')
            ->where('r.cdl = :cdl')
            ->setParameter('cdl', $cdl)
            ->getQuery()
            ->getResult();

        $this->riconoscibili = [];
        foreach ($riconoscibiliEntities as $ric) {
            $this->riconoscibili[$ric->getIdRic()] = [
                'ID_ric' => $ric->getIdRic(),
                'riconoscibile' => $ric->getRiconoscibile(),
                'CDL' => $ric->getCdl()
            ];
        }

        // Carica TUTTE le discipline
        $disRepo = $this->em->getRepository(ZcfuDis::class);
        $disEntities = $disRepo->createQueryBuilder('d')
            ->getQuery()
            ->getResult();

        $this->discipline = [];
        foreach ($disEntities as $dis) {
            $this->discipline[$dis->getDisId()] = [
                'DIS_ID' => $dis->getDisId(),
                'disciplina' => $dis->getDisciplina(),
                'ssd' => $dis->getSsd()
            ];
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
                    'note' => $recognized['note'] ?? '',
                    'stato' => $remaining == 0 ? 'tot' : ($recognized['cfu'] > 0 ? 'parziale' : 'non')
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
        $groupResults = [];

        foreach ($group as $off) {
            $totalRequired += $off['CFU'];
        }

        // Prima passata: prova a distribuire CFU disponibili con logica ottimizzata
        $groupResults = $this->optimizeRosaGroupDistribution($group, $available);
        
        // Calcola totale assegnato
        foreach ($groupResults as $result) {
            $totalAssigned += $result['recognized']['cfu'];
        }

        // ROLLBACK: Se il gruppo non soddisfa i requisiti minimi, annulla tutto
        // Per gruppi rosa, richiediamo almeno il 70% dei CFU totali per considerarlo valido
        $minRequired = $totalRequired * 0.7; // Almeno 70% dei CFU richiesti per gruppi a scelta
        if ($totalAssigned < $minRequired) {
            // Rollback: ripristina i CFU utilizzati
            foreach ($groupResults as $result) {
                $this->rollbackRecognition($result['recognized'], $available);
            }
            
            // Aggiungi tutte le discipline del gruppo come non riconosciute
            foreach ($group as $off) {
                $disciplineName = $this->discipline[$off['DIS_ID']]['disciplina'] ?? 'Disciplina sconosciuta';
                
                $detail[] = [
                    'disciplina_unimarconi' => $disciplineName,
                    'cfu_richiesti' => $off['CFU'],
                    'disciplina_esterna' => '',
                    'cfu_assegnati' => 0,
                    'priorita' => 0,
                    'note' => 'Gruppo non soddisfa requisiti minimi (rollback)',
                    'stato' => 'non'
                ];

                $summary[] = [
                    'disciplina_unimarconi' => $disciplineName,
                    'cfu_richiesti' => $off['CFU'],
                    'cfu_riconosciuti' => 0,
                    'integrativi_richiesti' => $off['CFU'],
                    'stato' => 'non'
                ];
            }
        } else {
            // Gruppo soddisfa i requisiti, mantieni i risultati
            foreach ($groupResults as $result) {
                $off = $result['off'];
                $disciplineName = $result['disciplineName'];
                $recognized = $result['recognized'];

                $detail[] = [
                    'disciplina_unimarconi' => $disciplineName,
                    'cfu_richiesti' => $off['CFU'],
                    'disciplina_esterna' => $recognized['nome'] ?? '',
                    'cfu_assegnati' => $recognized['cfu'],
                    'priorita' => $recognized['priorita'] ?? 0,
                    'note' => $recognized['note'] ?? '',
                    'stato' => $recognized['cfu'] == $off['CFU'] ? 'tot' : ($recognized['cfu'] > 0 ? 'parziale' : 'non')
                ];

                $summary[] = [
                    'disciplina_unimarconi' => $disciplineName,
                    'cfu_richiesti' => $off['CFU'],
                    'cfu_riconosciuti' => $recognized['cfu'],
                    'integrativi_richiesti' => max(0, $off['CFU'] - $recognized['cfu']),
                    'stato' => $recognized['cfu'] == $off['CFU'] ? 'tot' : ($recognized['cfu'] > 0 ? 'parziale' : 'non')
                ];
            }
        }
    }

    private function optimizeRosaGroupDistribution(array $group, array &$available): array
    {
        $groupResults = [];
        
        // Crea una copia del gruppo per non modificare l'originale
        $sortedGroup = $group;
        
        // Ordina le discipline del gruppo per priorità (CFU richiesti, poi priorità regole)
        usort($sortedGroup, function($a, $b) {
            // Prima per CFU richiesti (discendente)
            if ($a['CFU'] != $b['CFU']) {
                return $b['CFU'] - $a['CFU'];
            }
            // Poi per priorità regole (ascendente - priorità 0 prima di 1)
            $prioA = $this->getMinPriorityForOffer($a['OFF_ID']);
            $prioB = $this->getMinPriorityForOffer($b['OFF_ID']);
            return $prioA - $prioB;
        });
        
        // Distribuisci CFU seguendo l'ordine ottimizzato
        foreach ($sortedGroup as $off) {
            $offId = $off['OFF_ID'];
            $disciplineName = $this->discipline[$off['DIS_ID']]['disciplina'] ?? 'Disciplina sconosciuta';
            
            $recognized = $this->findRecognition($offId, $available);
            
            $groupResults[] = [
                'off' => $off,
                'disciplineName' => $disciplineName,
                'recognized' => $recognized
            ];
        }
        
        return $groupResults;
    }
    
    private function getMinPriorityForOffer(int $offId): int
    {
        if (!isset($this->regole[$offId])) {
            return 999; // Priorità bassa se non ci sono regole
        }
        
        $priorities = array_values($this->regole[$offId]);
        return min($priorities);
    }

    private function rollbackRecognition(array $recognized, array &$available): void
    {
        if ($recognized['cfu'] > 0 && !empty($recognized['nome'])) {
            // Trova e ripristina i CFU utilizzati
            foreach ($available as &$avail) {
                if ($avail['nome'] === $recognized['nome']) {
                    $avail['used'] = max(0, $avail['used'] - $recognized['cfu']);
                    break;
                }
            }
        }
    }
}
