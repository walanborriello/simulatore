# GUIDELINE PER CURSOR --- Progetto "Simulatore CFU" (Fasi aggiornate con inversione 4\<-\>5 + gestione branch Git)
127.0.0
> **Repo remoto:** `https://github.com/walanborriello/simulatore`

**Progetto:** Simulatore CFU UniMarconi\
**Dominio host locale:** `127.0.0.1 simulatore.local`\
**Stack:** PHP 8.4 + **IMPORTANTE** Symfony 7.3, MySQL 8, Redis (opzionale),
HTML/CSS/JS, jQuery/Alpine, Vite

------------------------------------------------------------------------

# INDICE

1.  Scopo e destinatari
2.  Requisiti tecnici
3.  Struttura progetto (attesa)
4.  **FASE 1 --- Setup Git & collegamento repository (con esclusioni di
    push e gestione branch)**
5.  **FASE 2 --- Docker (solo locale)**
6.  **FASE 3 --- Index temporanea**
7.  **FASE 4 --- Login con token + gestione studenti/simulazioni**
8.  **FASE 5 --- Pagina `/simulatore` + algoritmo + UI**
9.  **FASE 6 --- Swagger + Unit Test**
10. Database (tabelle esistenti e nuove)
11. Entità Doctrine consigliate
12. Algoritmo (CfuSimulatorService)
13. Controller e flussi (aggiornati alle nuove fasi)
14. Interfaccia e stile (logo & favicon inclusi)
15. Output: le tre tabelle
16. Migrazioni e fixtures
17. Note operative per CURSOR (CRITICO DB)
18. Ottimizzazione Performance (CRITICO)
19. Gestione errori e pagine di stato
20. Comandi utili
21. Nomenclatura variabili di sessione
22. Best practice per il form

------------------------------------------------------------------------

## 1) Scopo e destinatari

Il sistema supporta la **segreteria universitaria** nella simulazione
del riconoscimento CFU per studenti provenienti da altri Atenei.\
Accesso riservato agli operatori, non agli studenti.

------------------------------------------------------------------------

## 2) Requisiti tecnici

-   PHP 8.4+ (ultima compatibile con Symfony 7.3)
-   Symfony 7.3
-   Docker / docker-compose (solo locale, non versionato)
-   MySQL 8.x, phpMyAdmin
-   Redis (cache opzionale)
-   Composer
-   Node.js/npm (vite per asset)

**Credenziali DB** da inserire in `.env` (non versionare):

    DB_USER=unimarconi
    DB_PASSWORD=Tecnolocal123$
    DB_NAME=simulatore
    DB_HOST=db
    DB_PORT=3306
    DATABASE_URL="mysql://unimarconi:Tecnolocal123$@db:3306/simulatore?serverVersion=8.0"
    DEFAULT_URI=http://simulatore.local

Host locale: `127.0.0.1 simulatore.local`

------------------------------------------------------------------------

## 3) Struttura progetto (attesa)

    simulatore/
    ├─ docker/                # SOLO LOCALE (non versionare)
    ├─ config/
    ├─ src/
    │  ├─ Controller/
    │  ├─ Entity/
    │  ├─ Repository/
    │  ├─ Service/
    │  └─ Security/
    ├─ templates/
    │  ├─ layout/base.html.twig
    │  └─ simulatore/index.html.twig
    ├─ public/
    │  ├─ index.php
    │  ├─ favicon.ico          # versionare
    │  └─ logo.png             # versionare
    ├─ migrations/
    ├─ assets/styles/app.css
    ├─ docker-compose.yml      # SOLO LOCALE (non versionare)
    ├─ composer.json
    └─ README.md

------------------------------------------------------------------------

# 4) FASE 1 --- Setup Git & collegamento repository (con esclusioni di push e gestione branch)

### 4.1 Clona e imposta remoti

``` bash
git clone https://github.com/walanborriello/simulatore .
cd simulatore

git config user.name "Tuo Nome"
git config user.email "tuo.email@example.com"
```

### 4.2 Creazione branch

All'inizio del progetto devono esistere **4 branch principali**: 1.
`master` → branch **default**, sorgente da cui si staccano gli altri
branch.\
2. `preprod` → da `master`, usato per pre-produzione.\
3. `staging` → da `master`, usato come ambiente di test integrato.\
4. `fase-1-repo-setup` (o branch della fase corrente) → branch di lavoro
attuale, sempre staccato da `master`.

Creazione branch:

``` bash
git checkout -b master
git checkout -b preprod master
git checkout -b staging master
git checkout -b fase-1-repo-setup master
```

> ⚠️ Ogni branch nuovo di fase deve essere creato sempre da `master`.\
> ✅ A fine fase, dopo conferma, il contenuto del branch di lavoro viene
> **portato su `staging`** (merge).\
> Gli altri merge (`staging → preprod → master`) vanno gestiti
> **manualmente**.

### 4.3 .gitignore (CRITICO)

    /vendor/
    /var/
    /node_modules/
    /public/build/
    /public/*.map

    .env
    .env.*
    !.env.dist

    /docker/
    docker-compose*.yml
    docker-compose*.yaml
    docker/**

    .idea/
    .vscode/
    *.log
    .DS_Store
    Thumbs.db

### 4.4 Commit iniziale (solo file custom/app)

``` bash
git add .
git status   # verifica esclusioni
git commit -m "Fase 1: setup repo + .gitignore (esclusi Docker, .env e build)"
git push -u origin fase-1-repo-setup
```

------------------------------------------------------------------------

# 5) FASE 2 --- Docker (solo locale)

-   Prepara i container **in locale** (MySQL, PHP-FPM, Nginx,
    phpMyAdmin, Redis opzionale).\
-   **NON** committare i file Docker (già esclusi da `.gitignore`).\
-   Importa `schema.sql` **in locale**.

*Checklist Fase 2:* - \[ \] `docker-compose up -d --build` ok\
- \[ \] DB importato da `schema.sql`\
- \[ \] App raggiungibile **SOLO** su `http://simulatore.local` e **NON** su `http://localhost` 

------------------------------------------------------------------------

# 6) FASE 3 --- Index temporanea

-   Piccola pagina di benvenuto per verificare pipeline build e asset.\
-   **Niente logica di simulazione** qui.

*Checklist Fase 3:* - \[ \] Route `/` attiva (Index temporanea)\
- \[ \] Stile base e palette confermati

------------------------------------------------------------------------

# 7) FASE 4 --- Login con token + gestione studenti/simulazioni

-   `/login?token=...` obbligatorio → salva `user_token`
    (`user_role=segretary`) in sessione e fai redirect in homepage, altrimenti **403** stilizzata.\ 
-   Homepage `/`: **SOLO** tabella studenti (paginazione 10).\
-   Pagine `newStudent`, `editStudent`, `showStudent`. 
-   In homepage il tasto `aggiungi studente` deve essere posizionato in alto a destra del body.
-   In homepage devono esserci i filtri della tabella degli studenti che viene mostrata e che ci siano anche i filtri per `nome`, `cognome`, `email`, `codice fiscale` degli studenti e un flag per filtrare solo gli studenti che abbiano il `managedBy` dell'utente loggato e quindi della variabile salvata in sessione `user_token` 
-   La form deve contenere `nome`, `cognome`, `data di registrazione`, `email` con verifica validazione email, `codice fiscale` con validazione validità e deve essere univoco, `ateneo di provenienza`, `corso di studio di interesse`, `telefono` e questi devono essere tutti campi obbligatori e salvare in `managedBy` il token dell'utente loggato.
- Nel caso in cui un utente loggato prende in gestione uno studente con token differente allora in fase di salvataggio studente viene aggiornato il campo `managedBy` col nuovo token.
- Pulsante elimina studente nella tabella in home nella colonna azioni e nel visualizza.


*Checklist Fase 4:* - \[ \] Blocco accesso se manca `user_token`\
- \[ \] CRUD studenti + associazione simulazioni (che verrà implementata in fase 5 quindi non implementare nulla e se lo hai fatto elimina)\
- \[ \] Tabella a db `students_managements` cambio token su simulazione e/o salvataggio dello studente in fase di modifica o nuova creazione (che verrà implementata in fase 5 quindi non implementare nulla e se lo hai fatto elimina) se chi sta facendo la modifica dello studente ha un token differente da quello associato allo studente

------------------------------------------------------------------------

# 8) FASE 5 --- Pagina `/simulatore` + algoritmo + UI

-   Implementa form con selezione CDL e righe discipline (min 3 +
    aggiunta dinamica).\
-   Implementa
    `CfuSimulatorService::simulate(array $input, ?int $studentId=null)`.\
-   Output in **tre tabelle** (dettaglio, riepilogo, rimanenze).\
-   Validazioni e UX come da specifiche.\
-   Performance: niente query nei loop, pre-caricamento mappe, eventuale
    Redis.

## Passi operativi approccio al simulatore
    1. Nella pagina di creazione studente aggiungere un div che contiene il simulatore.
    2. Una volta aggiunta l'anagrafica e fatta la simulazione si può procedere col salvataggio dell'anagrafica, della simulazione e di tracciare nella tabella `students_management`.
    3. Nella pagina di dettagli dello studente, oltre a esserci l'anagrafica, dovrà essere presente anche una tabella per le simulazioni fatte per lo studente selezionato (ogni studente può avere n simulazioni). La tabella manterrà lo stesso stile delle altre tabelle con paginazione e massimo 5 elementi per volta. 
    4. Sempre nella pagina di dettaglio, dovrà esserci in alto a destra un bottone "Simulatore" che cliccandolo apre lo stesso simulatore presente nel div di "Aggiungi studente", **SOLO** in questa pagina devi aggiungere questo div, che ti permettere di fare la simulazione e salvarla allo studente che stiamo visualizzando.

## Passi operativi form simulatore
    1. Validare input.  
    2. Caricare offerta (`zcfu_offerta`) per CDL scelto, distinguendo **obbligatorie** (rosa=0) e **rose** (rosa>0).  
    3. Caricare regole (`zcfu_regole`) in memoria.  
    4. Inizializzare array `needed[ID_off]`, `assigned[ID_off]=0`, `available[ID_ric]`.  
    5. Matching obbligatorie → priorità 0, poi priorità 1.  
    6. Avvio simulazione → l'algoritmo calcola convalide su **obbligatorie** (rosa=0) e poi **gruppi a scelta** (rosa > 0) con priorità regole (0, poi 1) e **rollback** delle parziali non soddisfacenti.  
    7. Generare Tabella 1 (dettaglio).  
    8. Generare Tabella 2 (riepilogo: totali riconosciuti, integrativi richiesti).  
    9. Generare Tabella 3 (rimanenze: CFU residui).  
    10. Salvare in DB (opzionale).  

*Checklist Fase 5:* - \[ \] Endpoint `POST /api/simulate` restituisce
JSON coerente\
- \[ \] UI `/simulatore` con 3 tabelle e paginazione\
- \[ \] Palette/stile conformi

**CRITICO**: Le API devono avere come `baseurl` ciò che viene definito nel file .env nella variabile `DEFAULT_URI` quindi, ad esempio, se `DEFAULT_URI = 'simulatore.local'` allora l'API punterà a `http://simulatore.local/api/simulate`

------------------------------------------------------------------------

# 9) FASE 6 --- Swagger + Unit Test

-   Genera documentazione Swagger per API (simulate, riconoscibili,
    ecc.).\
-   Unit test per algoritmo e controller principali.

*Checklist Fase 6:* - \[ \] Doc API disponibile\
- \[ \] PHPUnit green su service e controller chiave

------------------------------------------------------------------------

## 10) Database (estratto `schema.sql`)

-   `zcfu_CDL (ID, CDL, ID_ORI, Orient)`\
-   `zcfu_dis (DIS_ID, disciplina, ssd)`\
-   `zcfu_offerta (OFF_ID, ORI_ID, DIS_ID, rosa, maxCFU, TAF, CFU, ANNO, AA, CDL)`\
-   `zcfu_regole (ID_off, ID_ric, priorita)`\
-   `zcfu_riconoscibile (ID_ric, riconoscibile, CDL)`

⚠️ Usare esattamente i nomi colonna minuscoli.

Tabelle nuove: `students_prospective`, `simulations`, eventuale
`simulation_log_token`.

Struttura `students_prospective`
-   `firstName`
-   `lastName`
-   `email`
-   `phone`
-   `notes`
-   `createdAt`
-   `updatedAt`
-   `codiceFiscale`
-   `ateneoProvenienza`
-   `corsoStudioInteresse`
-   `managedBy`

Struttura `students_managements`
-   `id` Autoincrement
-   `studentId`
-   `fromToken`
-   `toToken`
-   `modifiedAt` currante datetime

------------------------------------------------------------------------

## 11) Entità Doctrine consigliate

-   `ZcfuCdl`, `ZcfuDis`, `ZcfuOfferta`, `ZcfuRegole`,
    `ZcfuRiconoscibile`\
-   `StudentProspective`, `Simulation`, `SimulationLogToken`

------------------------------------------------------------------------

## 12) Algoritmo (CfuSimulatorService)

-   Obbligatorie (`rosa=0`) prima, poi gruppi (`rosa>0`).\
-   Priorità regole: `0` prima, poi `1`, con **rollback** se parziali
    non soddisfano i requisiti.\
-   Per obbligatorie includere sempre `ORI_ID=0` insieme
    all'orientamento selezionato.\
-   Evitare query nei loop, usare transazioni e audit log.

Output: `detail`, `summary`, `leftovers`, `simulationId`.

------------------------------------------------------------------------

## 13) Controller e flussi (aggiornati)

-   **F4**: `LoginController`, `IndexController`, `StudentController`,
    `ShowStudentController` (token obbligatorio, homepage solo
    studenti).\
-   **F5**: `SimulatoreController` con `simulatore()` e `simulate()`.

------------------------------------------------------------------------

## 14) Interfaccia e stile (logo & favicon)

- Grafica accattivante
- Font family: 'Raleway', sans-serif
- tag h1: `36px`
- tag p: `18px`
- tag a: `#E57552`
- tag a:hover: sottolineato
- bottoni hover: cursor pointer
- breadcrump: `16px`
- header: background `linear-gradient(0deg, #379975 40px, #FFF 0%)`
- Gradient primario: `linear-gradient(0deg, #379975 40px, #FFF 0%)`  
- Titoli: `#E57552`  
- Colore testo body: `#444444` 
- Colore bottoni conferma azione: `#379975`  
- Footer: `#444444` con testo `#fff`  
- Header e footer generico per tutto il sito
- Definisci bene gli spazi tra elementi (Non accavallare i componenti grafici). Vorrei un header abbastanza alto che mostri correttamente il logo, ben definito, tieni presente che hai il padding nel background quindi aggiungi il padding all'altezza dell'header e non degli elementi al suo interno.
Cerca di evitare errori grafici. Il brand-text ben definito che non sia di disturbo anche su mobile che rischia di avere un font-size troppo grande.
**PER I CAMPI NELLE FORM NON MOSTRARE ALTRI TIPI DI ERRORI**
- Non modificare la lunghezza delle select.
-   **Logo:** `logo.png` (dimensione originale, clic → `/`).\
-   **Favicon:** `favicon.ico` con
    `<link rel="icon" type="image/x-icon" href="/favicon.ico">`.\
-   **IMPORTANTE** il portale deve essere **ASSOLUTAMENTE** Responsive: hamburger menu, transizioni scroll.\
-   Errori form: bordo rosso + messaggio sotto campo + scroll al primo
    errore.
-   header **GENERICO PER TUTTE LE PAGINE DEL SITO** con logo a sinistra cliccabile che ti porti in homepage e a destra il tasto di logout che se cliccato ti fa redirect verso una pagina che ti informa che per loggarsi devi avere un token e che non abbia altre scritte vicino

## **IMPORTANTE - Gif di Loading**
    - **Gif di Loading**: Deve essere mostrata OVUNQUE nel portale quando si fa un'operazione che richiede tempo di elaborazione (submit, salvataggio, eliminazione, caricamento)
    - **Caratteristiche**: Considerare la gif di loading (magari gli metti il logo al centro ben visibile, almeno 100px) solo se cliccato il pulsante di simulazione o se richiede tempo di elaborazione nelle chiamate ajax o di click sul salvataggio nelle varie form del portale. Il loading dovrà avere uno sfondo opaco bianco che non dia la possibilità di click negli elementi sottostanti. Non inserire altre frasi o messaggi durante il loading.
    **IMPORTANTE: DEVE ESSERE UGUALE IN TUTTO IL SITO**

## Stile per fase 5
    ### Gestione error messages
        - Al click del pulsante "Calcola Simulazione", se un campo presenta errore di validazione, deve essere mostrato:
        1. Bordo rosso attorno al campo
        2. Messaggio di errore specifico sotto il campo stesso
        3. Questo vale per TUTTI i campi del form: select CDL, select SSD, input CFU, input nome disciplina
        4. I messaggi di errore devono essere specifici per ogni tipo di campo e spiegare chiaramente cosa manca
        5. Fai un transition scroll (lento) verso il primo campo che presenta l'errore
        6. I messaggi di errore devono essere nascosti quando:
            - L'utente clicca su un campo input text
            - L'utente cambia il valore di una select (evento change)
            - Il campo diventa valido (ha un valore e supera la validazione)
        7. I messaggi di errore devono essere mostrati solo al click del pulsante "Calcola Simulazione", non durante la digitazione e solo sulle form visibili. Quelle non visibili **NON** devi fare validazione, non voglio vedere messaggi di errori.
        8. Ogni campo deve avere il proprio messaggio di errore specifico:
            - CDL: "Seleziona un corso di laurea valido per procedere con la simulazione."
            - SSD: "Seleziona il settore scientifico disciplinare della materia che hai sostenuto."
            - CFU: "Inserisci il numero di crediti formativi universitari (CFU) della disciplina (1-30)."
            - Nome: "Inserisci il nome completo della disciplina che hai sostenuto."
        9. Lo scroll automatico deve avvenire SOLO quando si clicca "Calcola Simulazione" e ci sono errori di validazione. NON deve avvenire scroll quando si seleziona un SSD o si cambia il valore di una select durante la normale compilazione del form e al change delle option di "Seleziona corso di laurea" fai una transaction (lenta) scroll verso il div che contiene il titolo della form sottostante.
        10. I messaggi di errore sul singolo campo devono sparire in caso di click sul campo stesso o in caso di change del valore della select (usa la change di select2 altrimenti non lo riconoscerai mai).
        11. Non mi devi assolutamente cambiare la lunghezza delle select, devono essere al 100% dello spazio disponibile nemmeno quando mi mostri gli errori.

    ### Gestione form discipline
        - La sezione "Discipline Esterne da Riconoscere" deve essere inizialmente nascosta
        - Quando si seleziona un CDL, la sezione deve diventare visibile E le select SSD devono essere abilitate e popolate
        - Le select SSD devono essere abilitate fin dall'inizio, non disabilitate
        - Quando si aggiunge una nuova riga di disciplina, le select SSD devono essere già popolate con i dati del CDL selezionato
        - La validazione deve avvenire SOLO se la sezione è visibile

    ### Gestione select SSD
        - Le select SSD devono essere sempre abilitate (non disabilitate)
        - Quando si seleziona un CDL, tutte le select SSD (esistenti e future) devono essere popolate
        - Quando si aggiunge una nuova riga, la select SSD deve essere già popolata con i dati del CDL corrente
        - Le select SSD devono mostrare "Seleziona SSD" come placeholder quando non c'è CDL selezionato

**IMPORTANTE E CRITICO**: Avendo lo stesso simulatore in due punti diversi ossia nel div di crea studente e pagina dedicata, **devi** mantenere lo **STESSO STILE**.

------------------------------------------------------------------------

## 15) Output: le tre tabelle

- **Tabella 1 — Dettaglio riconoscimenti**: disciplina Unimarconi, CFU richiesti, disciplina esterna, CFU assegnati, priorità, note.  
- **Tabella 2 — Riepilogo**: disciplina Unimarconi, CFU richiesti, CFU riconosciuti, integrativi richiesti, stato (tot/parziale/non).  
- **Tabella 3 — Rimanenze**: disciplina esterna, CFU residui, motivazione (no rule, maxCFU superato, ecc.).  
### Stile tabelle
    - Le tre tabelle dovranno avere uno stile chiaro, sfruttare il 100% dello spazio disponibile e magari per tutte le righe sceglierei due colori `#fff` e qualcosa tendente al grigio chiaro per fare contrasto per differenziarle l'una dall'altra.
    - Aggiungi la paginazione a queste tre tabelle, dando un massimo di 5 righe in visualizzazione.
    - Mantieni la stessa lunghezza per ogni view nella paginazione, se magari ti trovi un testo troppo lungo allora manda a capo.


------------------------------------------------------------------------

## 16) Migrazioni e fixtures

-   Migrazioni per nuove tabelle senza toccare `zcfu_*`.\
-   Fixtures di test (5 studenti, 10 simulazioni).

------------------------------------------------------------------------

## 17) Note operative per CURSOR (CRITICO DB)

-   Mai eliminare tabelle importate da `schema.sql`.\
-   `schema.sql` è **sacro**. Se purge → reimport immediato.\
-   Usare `doctrine:schema:validate`, **non** `schema:update --force` se
    elimina `zcfu_*`.

------------------------------------------------------------------------

## 18) Ottimizzazione Performance (CRITICO)

-  **IMPORTANTE** Per il simulatore carica le tabelle in diversi array cosi evitiamo chiamate AJAX lente.
-   Lazy loading riconoscibili via AJAX; cache Redis opzionale.\
-   Indici: `zcfu_riconoscibile(CDL)`,
    `zcfu_riconoscibile(riconoscibile)`, `zcfu_dis(ssd)`.\
-   PHP: `max_execution_time=60`, `memory_limit=512M`.\
-   Evitare JOIN pesanti in pagine non simulatore (lazy services).

------------------------------------------------------------------------

## 19) Gestione errori e pagine di stato

-   Template personalizzati `403` (no token) e `404`.\
-   Stile coerente, nessun bottone nelle pagine di errore.

------------------------------------------------------------------------

## 20) Comandi utili

``` bash
# Fase 1
git clone https://github.com/walanborriello/simulatore .
cd simulatore
git checkout -b master
git checkout -b preprod master
git checkout -b staging master
git checkout -b fase-1-repo-setup master
git add . && git commit -m "F1: setup repo e .gitignore" && git push -u origin fase-1-repo-setup

# Fase 2 (solo locale, non versionare Docker)
docker-compose up -d --build
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load  # solo dopo import schema.sql
```

------------------------------------------------------------------------

## 21) Nomenclatura variabili di sessione

-   `user_token`, `user_role` (= segretary)\
-   `student_firstname`, `student_lastname`, `student_email`\
-   `success_message`, `error_message`

------------------------------------------------------------------------

## 22) Best practice per il form

-   Select2 con search per CDL/SSD; sezione discipline visibile solo con CDL
    selezionato e che abbia dei risultati.\
-   Validazione lato client solo al click "Calcola simulazione";
    messaggi specifici per campo.\
-   Righe dinamiche (min 3 iniziali + add/remove).\
-   Utilizza gli events di select2 e non quelli standard javascript altrimenti non noterà la change.

------------------------------------------------------------------------

**Conclusione**\
Le fasi ora sono:\
- **F1** repo & policy push (con branch Git) → **F2** Docker (solo
locale) → **F3** index → **F4** login+studenti → **F5** simulatore →
**F6** swagger+unit test.\
In ogni fase si lavora sempre in un branch dedicato nato da `master`.
Conferma → merge in `staging`.
