# GUIDELINE PER CURSOR --- Progetto "Simulatore CFU" (Fasi aggiornate con inversione 4\<-\>5 + gestione branch Git)
127.0.0
> **Repo remoto:** `https://github.com/walanborriello/simulatore`

**Progetto:** Simulatore CFU UniMarconi\
**Dominio host locale:** `127.0.0.1 simulatore.local`\
**Stack:** PHP 8.4 + Symfony 7.3, MySQL 8, Redis (opzionale),
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
- \[ \] App raggiungibile **SOLO** su `http://simulatore.local`

------------------------------------------------------------------------

# 6) FASE 3 --- Index temporanea

-   Piccola pagina di benvenuto per verificare pipeline build e asset.\
-   **Niente logica di simulazione** qui.

*Checklist Fase 3:* - \[ \] Route `/` attiva (Index temporanea)\
- \[ \] Stile base e palette confermati

------------------------------------------------------------------------

# 7) FASE 4 --- Login con token + gestione studenti/simulazioni

-   `/login?token=...` obbligatorio → salva `user_token`
    (`user_role=segretary`) in sessione, altrimenti **403** stilizzata.\
-   Homepage `/`: **SOLO** tabella studenti (paginazione 10).\
-   Pagine `newStudent`, `editStudent`, `showStudent` con integrazione
    simulatore e storico simulazioni (salvataggio con token in
    sessione + log cambi token).

*Checklist Fase 4:* - \[ \] Blocco accesso se manca `user_token`\
- \[ \] CRUD studenti + associazione simulazioni\
- \[ \] Log cambio token su simulazioni

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

-   Palette: gradiente `linear-gradient(0deg, #379975 40px, #FFF 0%)`,
    titoli `#E57552`, testo `#444444`, footer `#444444` (testo bianco).\
-   **Logo:** `public/logo.png` (dimensione originale, clic → `/`).\
-   **Favicon:** `public/favicon.ico` con
    `<link rel="icon" type="image/x-icon" href="/favicon.ico">`.\
-   Responsive: hamburger menu, transizioni scroll.\
-   Errori form: bordo rosso + messaggio sotto campo + scroll al primo
    errore.

------------------------------------------------------------------------

## 15) Output: le tre tabelle

1.  **Dettaglio** (convalide puntuali, priorità, note)\
2.  **Riepilogo** (riconosciuti, integrativi, stato)\
3.  **Rimanenze** (residui e motivazioni) --- tutte con paginazione.

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

-   Select2 per CDL/SSD; sezione discipline visibile solo con CDL
    selezionato.\
-   Validazione lato client solo al click "Calcola simulazione";
    messaggi specifici per campo.\
-   Righe dinamiche (min 3 iniziali + add/remove).\
-   GIF di loading uniforme, overlay bloccante.

------------------------------------------------------------------------

**Conclusione**\
Le fasi ora sono:\
- **F1** repo & policy push (con branch Git) → **F2** Docker (solo
locale) → **F3** index → **F4** login+studenti → **F5** simulatore →
**F6** swagger+unit test.\
In ogni fase si lavora sempre in un branch dedicato nato da `master`.
Conferma → merge in `staging`.
