<?php

// phpcs:disable Generic.Files.LineLength

namespace View\Folder;

/**
 * Class FoldersPageAdmin
 *
 * Handles the HTML rendering for the administrative folder management page.
 * Includes lists, creation forms, and view/edit details forms.
 */
class FoldersPageAdmin
{
    /** @var string Current action (list, create, view) */
    private string $action;

    /** @var array<string, mixed> Active filters for the list */
    private array $filters;

    /** @var int Current page number */
    private int $page;

    // Note: $perPage removed as property because it was unused in logic,
    // but kept in constructor for compatibility.

    /** @var string Flash message to display */
    private string $message;

    /** @var string Current language code */
    private string $lang;

    /** @var array<string, mixed>|null Data of a specific student */
    private ?array $studentData;

    /** @var array<int, array<string, mixed>> List of paginated students */
    private array $paginatedData;

    /** @var int Total number of records */
    private int $totalCount;

    /** @var int Total number of pages */
    private int $totalPages;

    /**
     * Constructor.
     *
     * @param string                           $action        Current action.
     * @param array<string, mixed>             $filters       Active filters.
     * @param int                              $page          Current page.
     * @param string                           $message       Flash message.
     * @param string                           $lang          Language code.
     * @param array<string, mixed>|null        $studentData   Specific student data.
     * @param array<int, array<string, mixed>> $paginatedData List of students for the current page.
     * @param int                              $totalCount    Total records.
     * @param int                              $totalPages    Total pages.
     */
    public function __construct(
        string $action,
        array $filters,
        int $page,
        // $perPage a été supprimé ici
        string $message,
        string $lang,
        ?array $studentData = null,
        array $paginatedData = [],
        int $totalCount = 0,
        int $totalPages = 0
    ) {
        $this->action = $action;
        $this->filters = $filters;
        $this->page = $page;
        $this->message = $message;
        $this->lang = $lang;
        $this->studentData = $studentData;
        $this->paginatedData = $paginatedData;
        $this->totalCount = $totalCount;
        $this->totalPages = $totalPages;
    }

    /**
     * Helper to translate strings based on the current language.
     *
     * @param array{fr: string, en: string} $frEn Array containing translations.
     * @return string The translated string.
     */
    private function t(array $frEn): string
    {
        return ($this->lang === 'en') ? $frEn['en'] : $frEn['fr'];
    }

    /**
     * Builds a URL with current parameters and language.
     *
     * @param string               $path   Base path.
     * @param array<string, mixed> $params Query parameters.
     * @return string The complete URL.
     */
    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        $separator = (strpos($path, '?') === false) ? '?' : '&';
        return $path . $separator . http_build_query($params);
    }

    /**
     * Main render method. Outputs the HTML structure.
     *
     * @return void
     */
    public function render(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->t(['fr' => 'Gestion des dossiers','en' => 'Folders Management']) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
            <link rel="stylesheet" href="styles/chatbot.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body class="<?= (isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true) ? 'tritanopie' : '' ?>">
        
        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="Logo">
                <div class="lang-dropdown">
                    <button class="dropbtn" id="current-lang"><?= htmlspecialchars($this->lang) ?></button>
                    <div class="dropdown-content">
                        <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                        <a href="#" onclick="changeLang('en'); return false;">English</a>
                    </div>
                </div>
            </div>
            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'home-admin']) ?>'">
                    <?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'dashboard-admin']) ?>'">
                    <?= $this->t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'partners-admin']) ?>'">
                    <?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?>
                </button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                    <?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'web_plan-admin']) ?>'">
                    <?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?>
                </button>
            </nav>
        </header>

        <main>
            <?php if ($this->action === 'create') : ?>
                <?php $this->renderCreateForm(); ?>
            <?php elseif ($this->action === 'view') : ?>
                <?php $this->renderViewForm(); ?>
            <?php else : ?>
                <?php $this->renderStudentsList(); ?>
            <?php endif; ?>
        </main>

        <div id="help-bubble" onclick="toggleHelpPopup()">💬</div>
        <div id="help-popup" class="chat-popup">
            <div class="help-popup-header">
                <span>Assistant</span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div id="quick-actions" class="quick-actions"></div>
        </div>

        <script>
            const CHAT_CONFIG = {
                lang: '<?= $this->lang ?>',
                role: '<?= (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'admin' : 'student' ?>'
            };
        </script>
        
        <script src="js/chatbot.js?v=<?= time() ?>"></script>
        
        <script>
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                const isHidden = (window.getComputedStyle(popup).display === 'none');
                
                if (isHidden) {
                    popup.style.display = 'flex';
                    if (typeof scrollToBottom === 'function') {
                        scrollToBottom();
                    }
                } else {
                    popup.style.display = 'none';
                }
            }

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

            function ouvrirFicheEtudiant(numetu) {
                const url = new URL(window.location.href);
                url.searchParams.set('action', 'view');
                url.searchParams.set('numetu', numetu);
                window.location.href = url.toString();
            }

            function rechercherEtRevenirPage1() {
                const searchInput = document.getElementById('search');
                if (!searchInput) return;

                const url = new URL(window.location.href);
                if (searchInput.value.trim() !== '') {
                    url.searchParams.set('search', searchInput.value.trim());
                    url.searchParams.set('p', 1);
                } else {
                    url.searchParams.delete('search');
                }
                window.location.href = url.toString();
            }

            let rechercheTimeout;
            function rechercherAvecDebounce() {
                clearTimeout(rechercheTimeout);
                rechercheTimeout = setTimeout(() => {
                    rechercherEtRevenirPage1();
                }, 3000);
            }

            function changerTypeMobilite(type) {
                const conventionBlock = document.getElementById('justificatif_convention');
                const lettreBlock = document.getElementById('lettre_motivation');

                if(conventionBlock) conventionBlock.style.display = 'none';
                if(lettreBlock) lettreBlock.style.display = 'none';

                if (type === 'stage') {
                    if(conventionBlock) conventionBlock.style.display = 'block'; 
                } else if (type === 'etudes') {
                    if(lettreBlock) lettreBlock.style.display = 'block';
                }
            }

            function activerModification() {
                document.querySelectorAll('.creation-form input, .creation-form select').forEach(field => {
                    if (field.id !== 'numetu' && field.id !== 'numetu_display') {
                        field.disabled = false;
                        field.style.backgroundColor = 'white';
                        field.style.color = 'black';
                    }
                });
                const btnMod = document.getElementById('btn-modifier');
                const btnSave = document.getElementById('btn-enregistrer');
                const btnCancel = document.getElementById('btn-annuler');
                
                if(btnMod) btnMod.style.display = 'none';
                if(btnSave) btnSave.style.display = 'inline-block';
                if(btnCancel) btnCancel.style.display = 'inline-block';
            }

            window.addEventListener('DOMContentLoaded', () => {
                const sel = document.getElementById('mobilite_type');
                if (sel) changerTypeMobilite(sel.value);

                const typeCheckboxes = document.querySelectorAll('input[name="entrant_sortant"]');
                typeCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('click', function() {
                        typeCheckboxes.forEach(other => { if (other !== this) other.checked = false; });
                        appliquerFiltres();
                    });
                });

                const zoneCheckboxes = document.querySelectorAll('input[name="zone"]');
                zoneCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('click', function() {
                        zoneCheckboxes.forEach(other => { if (other !== this) other.checked = false; });
                        appliquerFiltres();
                    });
                });

                const filterComplet = document.getElementById('filter-complet');
                if (filterComplet) filterComplet.addEventListener('change', appliquerFiltres);

                const dateDebut = document.getElementById('date-debut');
                if (dateDebut) dateDebut.addEventListener('change', appliquerFiltres);

                const dateFin = document.getElementById('date-fin');
                if (dateFin) dateFin.addEventListener('change', appliquerFiltres);
            });

            function appliquerFiltres() {
                const url = new URL(window.location.href);

                const typesChecked = Array.from(document.querySelectorAll('input[name="entrant_sortant"]:checked')).map(cb => cb.value);
                if (typesChecked.length > 0) url.searchParams.set('type', typesChecked[typesChecked.length - 1]);
                else url.searchParams.delete('type');

                const zonesChecked = Array.from(document.querySelectorAll('input[name="zone"]:checked')).map(cb => cb.value);
                if (zonesChecked.length > 0) url.searchParams.set('zone', zonesChecked[zonesChecked.length - 1]);
                else url.searchParams.delete('zone');

                const completVal = document.getElementById('filter-complet');
                if (completVal && completVal.value !== 'all') url.searchParams.set('complet', completVal.value);
                else url.searchParams.delete('complet');

                const dateDebut = document.getElementById('date-debut');
                if (dateDebut && dateDebut.value) url.searchParams.set('date_debut', dateDebut.value);
                else url.searchParams.delete('date_debut');

                const dateFin = document.getElementById('date-fin');
                if (dateFin && dateFin.value) url.searchParams.set('date_fin', dateFin.value);
                else url.searchParams.delete('date_fin');

                url.searchParams.set('p', 1);
                window.location.href = url.toString();
            }
        </script>
        <footer>
            <p>&copy; 2026 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img class="insta" src="img/instagram.png" alt="Instagram">
            </a>
        </footer>
        </body>
        </html>
        <?php
    }

    /**
     * Renders the list of students (Table view).
     *
     * @return void
     */
    private function renderStudentsList(): void
    {
        $etudiants = $this->paginatedData;
        $total = $this->totalCount;
        $totalPages = $this->totalPages;

        ?>
        <h1><?= $this->t(['fr' => 'Liste des étudiants','en' => 'Students List']) ?></h1>

        <?php if (!empty($this->message)) : ?>
        <div class="message"><?= htmlspecialchars($this->message) ?></div>
        <?php endif; ?>

        <div class="student-toolbar">
            <div class="search-container-toolbar">
                <label for="search" class="search-label"><?= $this->t(['fr' => 'Rechercher','en' => 'Search']) ?></label>
                <input type="text" id="search" name="search" placeholder="Nom, prénom, email..." 
                       value="<?= htmlspecialchars(strval($this->filters['search'] ?? '')) ?>" 
                       oninput="rechercherAvecDebounce()" 
                       onkeypress="if(event.key === 'Enter') rechercherEtRevenirPage1()">
                <button type="button" class="btn-search" onclick="rechercherEtRevenirPage1()">
                    <img src="img/loupe.png" alt="Rechercher">
                </button>
            </div>
            <div>
                <button id="btn-creer-dossier" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-admin', 'action' => 'create']) ?>'">
                    <?= $this->t(['fr' => '+ Créer un dossier','en' => '+ Create Folder']) ?>
                </button>
            </div>
        </div>

        <div class="filters-container">
            <p class="filters-title"><?= $this->t(['fr' => 'Filtres','en' => 'Filters']) ?></p>

            <div class="filters">
                <div class="filter-group">
                    <label>
                        <input type="checkbox" name="entrant_sortant" value="entrant" <?= (strval($this->filters['type'] ?? '')) === 'entrant' ? 'checked' : '' ?>>
                        <?= $this->t(['fr' => 'Entrant','en' => 'Incoming']) ?>
                    </label>
                    <label>
                        <input type="checkbox" name="entrant_sortant" value="sortant" <?= (strval($this->filters['type'] ?? '')) === 'sortant' ? 'checked' : '' ?>>
                        <?= $this->t(['fr' => 'Sortant','en' => 'Outgoing']) ?>
                    </label>
                </div>

                <div class="filter-group">
                    <label>
                        <input type="checkbox" name="zone" value="europe" <?= (strval($this->filters['zone'] ?? '')) === 'europe' ? 'checked' : '' ?>>
                        <?= $this->t(['fr' => 'Europe','en' => 'Europe']) ?>
                    </label>
                    <label>
                        <input type="checkbox" name="zone" value="hors_europe" <?= (strval($this->filters['zone'] ?? '')) === 'hors_europe' ? 'checked' : '' ?>>
                        <?= $this->t(['fr' => 'Hors-Europe','en' => 'Non-Europe']) ?>
                    </label>
                </div>

                <div class="filter-group">
                    <label for="filter-complet"><?= $this->t(['fr' => 'Statut :','en' => 'Status:']) ?></label>
                    <select id="filter-complet" onchange="appliquerFiltres()">
                        <option value="all" <?= (strval($this->filters['complet'] ?? 'all')) === 'all' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Tous','en' => 'All']) ?></option>
                        <option value="1" <?= (strval($this->filters['complet'] ?? '')) === '1' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Complet','en' => 'Complete']) ?></option>
                        <option value="0" <?= (strval($this->filters['complet'] ?? '')) === '0' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Incomplet','en' => 'Incomplete']) ?></option>
                    </select>
                </div>

                <div class="filter-group">
                    <?php if ($this->hasActiveFilters()) : ?>
                        <a href="<?= $this->buildUrl('index.php', ['page' => 'folders-admin']) ?>" class="btn-reset">
                            <?= $this->t(['fr' => 'Réinitialiser les filtres','en' => 'Reset filters']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <p class="results-count"><?= $total ?> <?= $this->t(['fr' => 'étudiant(s) trouvé(s)','en' => 'student(s) found']) ?></p>

        <table id="table-etudiants">
            <thead>
            <tr>
                <th><?= $this->t(['fr' => 'Nom','en' => 'Last Name']) ?></th>
                <th><?= $this->t(['fr' => 'Prénom','en' => 'First Name']) ?></th>
                <th><?= $this->t(['fr' => 'Né(e) le','en' => 'Birth Date']) ?></th>
                <th><?= $this->t(['fr' => 'Type','en' => 'Type']) ?></th>
                <th><?= $this->t(['fr' => 'Zone','en' => 'Zone']) ?></th>
                <th><?= $this->t(['fr' => 'Mobilité', 'en' => 'Mobility']) ?></th>
                <th><?= $this->t(['fr' => 'Statut','en' => 'Status']) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($etudiants as $etudiant) : ?>
                <?php
                // Safe JSON decoding
                $rawPieces = strval($etudiant['PiecesJustificatives'] ?? '{}');
                $decoded = json_decode($rawPieces, true);

                // Explicit type hint to ensure PHPStan knows this is an array
                /** @var array<string, mixed> $pieces */
                $pieces = is_array($decoded) ? $decoded : [];

                $mobilityType = '-';
                if (isset($pieces['convention']) && !empty($pieces['convention'])) {
                    $mobilityType = $this->t(['fr' => 'Stage', 'en' => 'Internship']);
                } elseif (isset($pieces['lettre_motivation']) && !empty($pieces['lettre_motivation'])) {
                    $mobilityType = $this->t(['fr' => 'Études', 'en' => 'Studies']);
                }

                $numEtu = strval($etudiant['NumEtu'] ?? '');
                $nom = strval($etudiant['Nom'] ?? '');
                $prenom = strval($etudiant['Prenom'] ?? '');
                $type = strval($etudiant['Type'] ?? '');
                $zone = strval($etudiant['Zone'] ?? '');
                // Correction Level 9: intval casting
                $isComplete = intval($etudiant['IsComplete'] ?? 0);
                ?>
                <tr onclick="ouvrirFicheEtudiant('<?= htmlspecialchars($numEtu) ?>')">
                    <td><?= htmlspecialchars($nom) ?></td>
                    <td><?= htmlspecialchars($prenom) ?></td>
                    <td><?= htmlspecialchars(strval($etudiant['DateNaissance'] ?? '')) ?></td>
                    <td><?= $this->t(['fr' => ($type === 'entrant' ? 'Entrant' : 'Sortant'), 'en' => ($type === 'entrant' ? 'Incoming' : 'Outgoing')]) ?></td>
                    <td><?= $this->t(['fr' => ($zone === 'europe' ? 'Europe' : 'Hors Europe'), 'en' => ($zone === 'europe' ? 'Europe' : 'Non-Europe')]) ?></td>
                    <td><?= htmlspecialchars($mobilityType) ?></td>
                    <td>
                        <?= $isComplete === 1
                                ? '<span class="status-complete">Complet</span>'
                                : '<span class="status-incomplete">Incomplet</span>' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 0) : ?>
        <div class="pagination">
            <?php if ($this->page > 1) : ?>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl(1) ?>'">«</button>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl($this->page - 1) ?>'">‹</button>
            <?php else : ?>
                <button disabled>«</button>
                <button disabled>‹</button>
            <?php endif; ?>
            
            <?php for ($i = max(1, $this->page - 2); $i <= min($totalPages, $this->page + 2); $i++) : ?>
                <button class="<?= $i === $this->page ? 'active' : '' ?>" onclick="window.location.href='<?= $this->buildPaginationUrl($i) ?>'"><?= $i ?></button>
            <?php endfor; ?>
            
            <?php if ($this->page < $totalPages) : ?>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl($this->page + 1) ?>'">›</button>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl($totalPages) ?>'">»</button>
            <?php else : ?>
                <button disabled>›</button>
                <button disabled>»</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php
    }

    /**
     * Renders the form to create a new student folder.
     *
     * @return void
     */
    private function renderCreateForm(): void
    {
        ?>
        <h1><?= $this->t(['fr' => 'Créer un nouveau dossier étudiant','en' => 'Create New Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-admin']) ?>'" class="btn-secondary">
                <?= $this->t(['fr' => 'Retour à la liste','en' => 'Back to List']) ?>
            </button>
        </div>
        <form method="post" action="index.php?page=save_student&lang=<?= htmlspecialchars($this->lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu"><?= $this->t(['fr' => 'NumÉtu *','en' => 'Student ID *']) ?></label>
                <input type="text" name="numetu" id="numetu" required>
                
                <label for="nom"><?= $this->t(['fr' => 'Nom *','en' => 'Last Name *']) ?></label>
                <input type="text" name="nom" id="nom" required>
                
                <label for="prenom"><?= $this->t(['fr' => 'Prénom *','en' => 'First Name *']) ?></label>
                <input type="text" name="prenom" id="prenom" required>
                
                <label for="naissance"><?= $this->t(['fr' => 'Né(e) le','en' => 'Date of Birth']) ?></label>
                <input type="date" name="naissance" id="naissance">
                
                <label for="sexe"><?= $this->t(['fr' => 'Sexe','en' => 'Gender']) ?></label>
                <select name="sexe" id="sexe">
                    <option value="M"><?= $this->t(['fr' => 'Masculin','en' => 'Male']) ?></option>
                    <option value="F"><?= $this->t(['fr' => 'Féminin','en' => 'Female']) ?></option>
                    <option value="Autre"><?= $this->t(['fr' => 'Autre','en' => 'Other']) ?></option>
                </select>
                
                <label for="adresse"><?= $this->t(['fr' => 'Adresse','en' => 'Address']) ?></label>
                <input type="text" name="adresse" id="adresse">
                
                <label for="cp"><?= $this->t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
                <input type="text" name="cp" id="cp">
                
                <label for="ville"><?= $this->t(['fr' => 'Ville','en' => 'City']) ?></label>
                <input type="text" name="ville" id="ville">
                
                <label for="email_perso"><?= $this->t(['fr' => 'Email Personnel *','en' => 'Personal Email *']) ?></label>
                <input type="email" name="email_perso" id="email_perso" required>
                
                <label for="email_amu"><?= $this->t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
                <input type="email" name="email_amu" id="email_amu">
                
                <label for="telephone"><?= $this->t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
                <input type="text" name="telephone" id="telephone" required>
                
                <label for="departement"><?= $this->t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
                <input type="text" name="departement" id="departement">
                
                <label for="type"><?= $this->t(['fr' => 'Type *','en' => 'Type *']) ?></label>
                <select name="type" id="type" required>
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="entrant"><?= $this->t(['fr' => 'Entrant','en' => 'Incoming']) ?></option>
                    <option value="sortant"><?= $this->t(['fr' => 'Sortant','en' => 'Outgoing']) ?></option>
                </select>
                
                <label for="zone"><?= $this->t(['fr' => 'Zone *','en' => 'Zone *']) ?></label>
                <select name="zone" id="zone" required>
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="europe"><?= $this->t(['fr' => 'Europe','en' => 'Europe']) ?></option>
                    <option value="hors_europe"><?= $this->t(['fr' => 'Hors Europe','en' => 'Non-Europe']) ?></option>
                </select>

                <label for="photo"><?= $this->t(['fr' => 'Photo','en' => 'Photo']) ?></label>
                <input type="file" name="photo" id="photo" accept="image/*">

                <label for="cv"><?= $this->t(['fr' => 'CV','en' => 'CV']) ?></label>
                <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx">

                <label for="mobilite_type"><?= $this->t(['fr' => 'Type de mobilité','en' => 'Mobility Type']) ?></label>
                <select name="mobilite_type" id="mobilite_type" onchange="changerTypeMobilite(this.value)">
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="stage"><?= $this->t(['fr' => 'Stage','en' => 'Internship']) ?></option>
                    <option value="etudes"><?= $this->t(['fr' => 'Études','en' => 'Studies']) ?></option>
                </select>
            </div>

            <div class="fichier-obligatoire" id="justificatif_convention">
                <label><?= $this->t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>
                <input type="file" name="convention" accept=".pdf,.doc,.docx">
            </div>
            <div class="fichier-obligatoire" id="lettre_motivation">
                <label><?= $this->t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>
                <input type="file" name="lettre_motivation" accept=".pdf,.doc,.docx">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-secondary"><?= $this->t(['fr' => 'Enregistrer','en' => 'Save']) ?></button>
                <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                    <?= $this->t(['fr' => 'Annuler','en' => 'Cancel']) ?>
                </button>
            </div>
        </form>
        <?php
    }

    /**
     * Renders the detailed view of a student folder (Edit Mode).
     *
     * @return void
     */
    private function renderViewForm(): void
    {
        if (!$this->studentData) {
            echo '<p>' . $this->t(['fr' => 'Étudiant non trouvé','en' => 'Student not found']) . '</p>';
            return;
        }

        $student = $this->studentData;

        // Extract pieces safely
        /** @var array<string, mixed> $pieces */
        $pieces = (isset($student['pieces']) && is_array($student['pieces']))
            ? $student['pieces']
            : [];

        // Determine if it is 'stage' or 'studies' based on existing files.
        $detectedType = '';
        if (isset($pieces['convention']) && !empty($pieces['convention'])) {
            $detectedType = 'stage';
        } elseif (isset($pieces['lettre_motivation']) && !empty($pieces['lettre_motivation'])) {
            $detectedType = 'etudes';
        }

        // CORRECTION LEVEL 9: Preparing strict string values for input values
        // PHPStan strictly forbids passing mixed to htmlspecialchars.
        $valNom = htmlspecialchars(strval($student['Nom'] ?? ''));
        $valPrenom = htmlspecialchars(strval($student['Prenom'] ?? ''));
        $valDate = htmlspecialchars(strval($student['DateNaissance'] ?? ''));
        $valSexe = strval($student['Sexe'] ?? ''); // Not htmlspecialchars because used in attributes with known safe values
        $valAdresse = htmlspecialchars(strval($student['Adresse'] ?? ''));
        $valCP = htmlspecialchars(strval($student['CodePostal'] ?? ''));
        $valVille = htmlspecialchars(strval($student['Ville'] ?? ''));
        $valEmailP = htmlspecialchars(strval($student['EmailPersonnel'] ?? ''));
        $valEmailA = htmlspecialchars(strval($student['EmailAMU'] ?? ''));
        $valTel = htmlspecialchars(strval($student['Telephone'] ?? ''));
        $valDept = htmlspecialchars(strval($student['CodeDepartement'] ?? ''));
        $valType = strval($student['Type'] ?? '');
        $valZone = strval($student['Zone'] ?? '');
        $numEtu = htmlspecialchars(strval($student['NumEtu'] ?? ''));
        ?>
        <h1><?= $this->t(['fr' => 'Dossier étudiant','en' => 'Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-admin']) ?>'" class="btn-secondary">
                <?= $this->t(['fr' => 'Retour à la liste','en' => 'Back to List']) ?>
            </button>
        </div>

        <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($this->lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu"><?= $this->t(['fr' => 'NumÉtu *','en' => 'Student ID *']) ?></label>
                <input type="text" id="numetu_display" value="<?= $numEtu ?>" disabled class="input-disabled">
                <input type="hidden" name="numetu" id="numetu" value="<?= $numEtu ?>">

                <label for="nom"><?= $this->t(['fr' => 'Nom *','en' => 'Last Name *']) ?></label>
                <input type="text" name="nom" id="nom" value="<?= $valNom ?>" disabled class="input-disabled" required>

                <label for="prenom"><?= $this->t(['fr' => 'Prénom *','en' => 'First Name *']) ?></label>
                <input type="text" name="prenom" id="prenom" value="<?= $valPrenom ?>" disabled class="input-disabled" required>

                <label for="naissance"><?= $this->t(['fr' => 'Né(e) le','en' => 'Date of Birth']) ?></label>
                <input type="date" name="naissance" id="naissance" value="<?= $valDate ?>" disabled class="input-disabled">

                <label for="sexe"><?= $this->t(['fr' => 'Sexe','en' => 'Gender']) ?></label>
                <select name="sexe" id="sexe" disabled class="input-disabled">
                    <option value="M" <?= $valSexe === 'M' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Masculin','en' => 'Male']) ?></option>
                    <option value="F" <?= $valSexe === 'F' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Féminin','en' => 'Female']) ?></option>
                    <option value="Autre" <?= $valSexe === 'Autre' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Autre','en' => 'Other']) ?></option>
                </select>

                <label for="adresse"><?= $this->t(['fr' => 'Adresse','en' => 'Address']) ?></label>
                <input type="text" name="adresse" id="adresse" value="<?= $valAdresse ?>" disabled class="input-disabled">

                <label for="cp"><?= $this->t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
                <input type="text" name="cp" id="cp" value="<?= $valCP ?>" disabled class="input-disabled">

                <label for="ville"><?= $this->t(['fr' => 'Ville','en' => 'City']) ?></label>
                <input type="text" name="ville" id="ville" value="<?= $valVille ?>" disabled class="input-disabled">

                <label for="email_perso"><?= $this->t(['fr' => 'Email Personnel *','en' => 'Personal Email *']) ?></label>
                <input type="email" name="email_perso" id="email_perso" value="<?= $valEmailP ?>" disabled class="input-disabled" required>

                <label for="email_amu"><?= $this->t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
                <input type="email" name="email_amu" id="email_amu" value="<?= $valEmailA ?>" disabled class="input-disabled">

                <label for="telephone"><?= $this->t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
                <input type="text" name="telephone" id="telephone" value="<?= $valTel ?>" disabled class="input-disabled" required>

                <label for="departement"><?= $this->t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
                <input type="text" name="departement" id="departement" value="<?= $valDept ?>" disabled class="input-disabled">

                <label for="type"><?= $this->t(['fr' => 'Type *','en' => 'Type *']) ?></label>
                <select name="type" id="type" disabled class="input-disabled" required>
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="entrant" <?= $valType === 'entrant' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Entrant','en' => 'Incoming']) ?></option>
                    <option value="sortant" <?= $valType === 'sortant' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Sortant','en' => 'Outgoing']) ?></option>
                </select>

                <label for="zone"><?= $this->t(['fr' => 'Zone *','en' => 'Zone *']) ?></label>
                <select name="zone" id="zone" disabled class="input-disabled" required>
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="europe" <?= $valZone === 'europe' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Europe','en' => 'Europe']) ?></option>
                    <option value="hors_europe" <?= $valZone === 'hors_europe' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Hors Europe','en' => 'Non-Europe']) ?></option>
                </select>

                <label for="mobilite_type"><?= $this->t(['fr' => 'Type de mobilité','en' => 'Mobility Type']) ?></label>
                <select name="mobilite_type" id="mobilite_type" onchange="changerTypeMobilite(this.value)" disabled class="input-disabled">
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="stage" <?= $detectedType === 'stage' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Stage','en' => 'Internship']) ?></option>
                    <option value="etudes" <?= $detectedType === 'etudes' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Études','en' => 'Studies']) ?></option>
                </select>
            </div>

            <div class="form-section documents-section">
                <h2><?= $this->t(['fr' => 'Pièces Justificatives','en' => 'Supporting Documents']) ?></h2>

                <div class="document-block">
                    <label><?= $this->t(['fr' => 'Photo','en' => 'Photo']) ?></label>
                    <?php if (isset($pieces['photo']) && !empty($pieces['photo'])) : ?>
                        <div class="document-preview">
                            <img src="data:image/jpeg;base64,<?= strval($pieces['photo']) ?>" alt="Photo" class="photo-preview">
                            <br>
                            <a href="data:image/jpeg;base64,<?= strval($pieces['photo']) ?>" download="photo_<?= $numEtu ?>.jpg" class="btn-download">
                                <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p class="no-document"><?= $this->t(['fr' => 'Aucune photo disponible','en' => 'No photo available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="photo" id="photo" accept="image/*" disabled class="input-disabled file-input-margin">
                </div>

                <div class="document-block">
                    <label><?= $this->t(['fr' => 'CV','en' => 'CV']) ?></label>
                    <?php if (isset($pieces['cv']) && !empty($pieces['cv'])) : ?>
                        <div class="document-preview">
                            <p class="document-available"><?= $this->t(['fr' => 'CV disponible','en' => 'CV available']) ?></p>
                            <a href="data:application/pdf;base64,<?= strval($pieces['cv']) ?>" download="cv_<?= $numEtu ?>.pdf" class="btn-download">
                                <?= $this->t(['fr' => 'Télécharger le CV','en' => 'Download CV']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p class="no-document"><?= $this->t(['fr' => 'Aucun CV disponible','en' => 'No CV available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx" disabled class="input-disabled file-input-margin">
                </div>

                <div id="justificatif_convention" class="document-block">
                    <label><?= $this->t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>
                    <?php if (isset($pieces['convention']) && !empty($pieces['convention'])) : ?>
                        <div class="document-preview">
                            <p class="document-available"><?= $this->t(['fr' => 'Convention disponible','en' => 'Agreement available']) ?></p>
                            <a href="data:application/pdf;base64,<?= strval($pieces['convention']) ?>" download="convention_<?= $numEtu ?>.pdf" class="btn-download">
                                <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p class="no-document"><?= $this->t(['fr' => 'Aucune convention disponible','en' => 'No agreement available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="convention" id="convention" accept=".pdf,.doc,.docx" disabled class="input-disabled file-input-margin">
                </div>

                <div id="lettre_motivation" class="document-block">
                    <label><?= $this->t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>
                    <?php if (isset($pieces['lettre_motivation']) && !empty($pieces['lettre_motivation'])) : ?>
                        <div class="document-preview">
                            <p class="document-available"><?= $this->t(['fr' => 'Lettre disponible','en' => 'Letter available']) ?></p>
                            <a href="data:application/pdf;base64,<?= strval($pieces['lettre_motivation']) ?>" download="lettre_<?= $numEtu ?>.pdf" class="btn-download">
                                <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p class="no-document"><?= $this->t(['fr' => 'Aucune lettre disponible','en' => 'No letter available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="lettre_motivation" id="lettre_motivation_file" accept=".pdf,.doc,.docx" disabled class="input-disabled file-input-margin">
                </div>

                <?php
                // CORRECTION LEVEL 9: intval casting
                $isComplete = intval($student['IsComplete'] ?? 0);
                ?>
                <div class="status-block status-<?= $isComplete ? 'complete' : 'incomplete' ?>">
                    <strong><?= $this->t(['fr' => 'Statut du dossier :','en' => 'Folder status:']) ?></strong>
                    <span class="status-text-<?= $isComplete ? 'complete' : 'incomplete' ?>">
                    <?= $isComplete
                            ? $this->t(['fr' => 'Complet','en' => 'Complete'])
                            : $this->t(['fr' => 'Incomplet','en' => 'Incomplete']) ?>
                </span>
                    <br><br>
                    <button type="button" onclick="window.location.href='index.php?page=toggle_complete&numetu=<?= urlencode($numEtu) ?>&lang=<?= htmlspecialchars($this->lang) ?>'" class="btn-secondary btn-toggle-status">
                        <?= $isComplete
                                ? $this->t(['fr' => 'Marquer comme incomplet','en' => 'Mark as incomplete'])
                                : $this->t(['fr' => 'Marquer comme complet','en' => 'Mark as complete']) ?>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="btn-modifier" class="btn-danger" onclick="activerModification()">
                    <?= $this->t(['fr' => 'Modifier','en' => 'Edit']) ?>
                </button>

                <button type="submit" id="btn-enregistrer" class="btn-secondary btn-hidden">
                    <?= $this->t(['fr' => 'Enregistrer','en' => 'Save']) ?>
                </button>

                <button type="button" id="btn-annuler" class="btn-secondary btn-hidden" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                    <?= $this->t(['fr' => 'Annuler','en' => 'Cancel']) ?>
                </button>

                <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-admin']) ?>'">
                    <?= $this->t(['fr' => 'Retour','en' => 'Back']) ?>
                </button>
            </div>
        </form>
        <?php
    }

    /**
     * Builds pagination URL preserving current filters.
     *
     * @param int $page Target page number.
     * @return string URL.
     */
    private function buildPaginationUrl(int $page): string
    {
        $params = array_merge($this->filters, [
                'p' => $page,
                'page' => 'folders-admin'
        ]);
        // Remove empty filters to keep URL clean
        $params = array_filter($params, fn($v) => !empty($v) || $v === 0 || $v === '0');

        return 'index.php?' . http_build_query($params);
    }

    /**
     * Checks if any filter is currently active.
     *
     * @return bool True if filters are active.
     */
    private function hasActiveFilters(): bool
    {
        return (strval($this->filters['type'] ?? 'all')) !== 'all'
                || (strval($this->filters['zone'] ?? 'all')) !== 'all'
                || (strval($this->filters['complet'] ?? 'all')) !== 'all'
                || !empty($this->filters['date_debut'])
                || !empty($this->filters['date_fin'])
                || !empty($this->filters['search']);
    }
}