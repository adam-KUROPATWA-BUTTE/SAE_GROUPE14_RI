<?php

namespace View\Folder;

use Model\Folder\FolderAdmin as Folder;

/**
 * Class FoldersPageAdmin
 *
 * Handles the HTML rendering for the administrative folder management page.
 * Includes lists, creation forms, and view/edit details forms.
 */
class FoldersPageAdmin
{
    private string $action;
    private array $filters;
    private int $page;
    private int $perPage;
    private string $message;
    private string $lang;
    private ?array $studentData;
    private array $paginatedData;
    private int $totalCount;
    private int $totalPages;

    /**
     * Constructor.
     *
     * @param string     $action      Current action (list, create, view).
     * @param array      $filters     Active filters for the list.
     * @param int        $page        Current page number.
     * @param int        $perPage     Items per page.
     * @param string     $message     Flash message to display (success/error).
     * @param string     $lang        Current language ('fr' or 'en').
     * @param array|null $studentData Data of a specific student (for view/edit mode).
     */
    public function __construct(
            string $action,
            array $filters,
            int $page,
            int $perPage,
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
        $this->perPage = $perPage;
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
     * @param array $frEn Array ['fr' => '...', 'en' => '...'].
     * @return string The translated string.
     */
    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    /**
     * Builds a URL with current parameters and language.
     *
     * @param string $path   Base path.
     * @param array  $params Query parameters.
     * @return string The complete URL.
     */
    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        return $path . '?' . http_build_query($params);
    }

    /**
     * Main render method. Outputs the HTML structure.
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
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
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
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-admin') ?>'"><?= $this->t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners-admin') ?>'"><?= $this->t(['fr' => 'Partenaire','en' => 'Partners']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'"><?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Site Map']) ?></button>
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

        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr' => 'Aide', 'en' => 'Help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->t(['fr' => 'Bienvenue ! Comment pouvons-nous vous aider ?', 'en' => 'Welcome! How can we help you?']) ?></p>
                <ul>
                    <li><a href="/help" target="_blank"><?= $this->t(['fr' => 'Page d’aide complète', 'en' => 'Full help page']) ?></a></li>
                </ul>
            </div>
        </div>

        <script>
            /**
             * Toggle the help popup visibility.
             */
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
            }

            /**
             * Change the interface language.
             */
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

            /**
             * Redirect to student details view.
             */
            function ouvrirFicheEtudiant(numetu) {
                const url = new URL(window.location.href);
                url.searchParams.set('action', 'view');
                url.searchParams.set('numetu', numetu);
                window.location.href = url.toString();
            }

            /**
             * Client-side search filtering (simple table filter).
             */
            function filtrerEtudiants() {
                let input = document.getElementById("search");
                let filter = input.value.toLowerCase();
                let rows = document.querySelectorAll("#table-etudiants tbody tr");
                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? "" : "none";
                });
            }

                    /**
            * Effectue une recherche côté serveur et revient à la page 1
            */
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
            /**
             * Recherche avec debounce (attend 800ms après la dernière frappe)
             */
            let rechercheTimeout;
            function rechercherAvecDebounce() {
                clearTimeout(rechercheTimeout);
                rechercheTimeout = setTimeout(() => {
                    rechercherEtRevenirPage1();
                }, 3000);
            }



            /**
             * Toggles visibility of specific document fields based on mobility type.
             * Shows 'Convention' for Internships, 'Motivation Letter' for Studies.
             */
            function changerTypeMobilite(type) {
                // Hide both by default
                const conventionBlock = document.getElementById('justificatif_convention');
                const lettreBlock = document.getElementById('lettre_motivation');

                if(conventionBlock) conventionBlock.style.display = 'none';
                if(lettreBlock) lettreBlock.style.display = 'none';

                // Show appropriate block
                if (type === 'stage') {
                    if(conventionBlock) conventionBlock.style.display = 'block';
                } else if (type === 'etudes') {
                    if(lettreBlock) lettreBlock.style.display = 'block';
                }
            }

            /**
             * Enables input fields for editing in the View form.
             */
            function activerModification() {
                document.querySelectorAll('.creation-form input, .creation-form select').forEach(field => {
                    // Do not enable NumEtu visually as it is the primary key, but ensure it is sent via hidden field
                    if (field.id !== 'numetu') {
                        field.disabled = false;
                        field.style.backgroundColor = 'white';
                        field.style.color = 'black';
                    }
                });

                // Toggle action buttons
                document.getElementById('btn-modifier').style.display = 'none';
                document.getElementById('btn-enregistrer').style.display = 'inline-block';
                document.getElementById('btn-annuler').style.display = 'inline-block';
            }

            // Initialize scripts on page load
            window.addEventListener('DOMContentLoaded', (event) => {
                // Initialize mobility type display
                const sel = document.getElementById('mobilite_type');
                if (sel) changerTypeMobilite(sel.value);

                const typeCheckboxes = document.querySelectorAll('input[name="entrant_sortant"]');
                typeCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('click', function(e) {
                        // Empêcher le comportement par défaut temporairement
                        const wasChecked = this.checked;

                        // Décocher toutes les autres checkboxes du même groupe AVANT
                        typeCheckboxes.forEach(other => {
                            if (other !== this) {
                                other.checked = false;
                            }
                        });

                        // Si on clique sur une case déjà cochée, la décocher
                        if (!wasChecked && this.checked) {
                            // Ne rien faire, elle est déjà cochée
                        }

                        // Appliquer les filtres après avoir géré l'exclusivité
                        appliquerFiltres();
                    });
                });


                const zoneCheckboxes = document.querySelectorAll('input[name="zone"]');
                zoneCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('click', function(e) {
                        // Empêcher le comportement par défaut temporairement
                        const wasChecked = this.checked;

                        // Décocher toutes les autres checkboxes du même groupe AVANT
                        zoneCheckboxes.forEach(other => {
                            if (other !== this) {
                                other.checked = false;
                            }
                        });

                        // Appliquer les filtres après avoir géré l'exclusivité
                        appliquerFiltres();
                    });
                });

                const filterComplet = document.getElementById('filter-complet');
                if (filterComplet) {
                    filterComplet.addEventListener('change', function() {
                        appliquerFiltres();
                    });
                }

                const dateDebut = document.getElementById('date-debut');
                if (dateDebut) {
                    dateDebut.addEventListener('change', function() {
                        appliquerFiltres();
                    });
                }

                const dateFin = document.getElementById('date-fin');
                if (dateFin) {
                    dateFin.addEventListener('change', function() {
                        appliquerFiltres();
                    });
                }
            });

            /**
             * Applies filters by reloading the page with query parameters.
             */
            function appliquerFiltres() {
                const url = new URL(window.location.href);

                // Handle Checkbox Filters (Type) - Exclusive behavior
                const typesChecked = Array.from(document.querySelectorAll('input[name="entrant_sortant"]:checked')).map(cb => cb.value);
                if (typesChecked.length > 0) {
                    // Si plusieurs cochées, garder seulement la dernière cliquée
                    url.searchParams.set('type', typesChecked[typesChecked.length - 1]);
                } else {
                    url.searchParams.delete('type');
                }

                // Handle Checkbox Filters (Zone) - Exclusive behavior
                const zonesChecked = Array.from(document.querySelectorAll('input[name="zone"]:checked')).map(cb => cb.value);
                if (zonesChecked.length > 0) {
                    // Si plusieurs cochées, garder seulement la dernière cliquée
                    url.searchParams.set('zone', zonesChecked[zonesChecked.length - 1]);
                } else {
                    url.searchParams.delete('zone');
                }

                // Handle Select Filter (Completeness)
                const completVal = document.getElementById('filter-complet');
                if (completVal && completVal.value !== 'all') {
                    url.searchParams.set('complet', completVal.value);
                } else {
                    url.searchParams.delete('complet');
                }

                // Handle Date Filters
                const dateDebut = document.getElementById('date-debut');
                if (dateDebut && dateDebut.value) {
                    url.searchParams.set('date_debut', dateDebut.value);
                } else {
                    url.searchParams.delete('date_debut');
                }

                const dateFin = document.getElementById('date-fin');
                if (dateFin && dateFin.value) {
                    url.searchParams.set('date_fin', dateFin.value);
                } else {
                    url.searchParams.delete('date_fin');
                }

                // Reset to page 1 when applying filters
                url.searchParams.set('p', 1);

                window.location.href = url.toString();
            }


        </script>
        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
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
            <div class="search-container-toolbar" style="display: flex; align-items: center; gap: 10px;">
                <label for="search" class="search-label"><?= $this->t(['fr' => 'Rechercher','en' => 'Search']) ?></label>
                <input type="text" id="search" name="search" placeholder="Nom, prénom, email..." value="<?= htmlspecialchars($this->filters['search'] ?? '') ?>" oninput="rechercherAvecDebounce()" onkeypress="if(event.key === 'Enter') rechercherEtRevenirPage1()">
                <button type="button" onclick="rechercherEtRevenirPage1()" style="padding: 7px; cursor: pointer; background: #2b91bb; color: white; border: none; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <img src="img/loupe.png" style="width: 20px; height: 20px;">
                </button>
            </div>
            <div>
                <button id="btn-creer-dossier" onclick="window.location.href='<?= $this->buildUrl('/folders-admin', ['action' => 'create']) ?>'">
                    <?= $this->t(['fr' => '+ Créer un dossier','en' => '+ Create Folder']) ?>
                </button>
            </div>
        </div>

        <div class="filters-container" style="background: #f4f4f4; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <p style="margin-top: 0; font-weight: bold;"><?= $this->t(['fr' => 'Filtres','en' => 'Filters']) ?></p>

            <div class="filters" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center;">

                <div class="filter-group">
                    <label><input type="checkbox" name="entrant_sortant" value="entrant" <?= ($this->filters['type'] ?? '') === 'entrant' ? 'checked' : '' ?>><?= $this->t(['fr' => 'Entrant','en' => 'Incoming']) ?></label>
                    <label><input type="checkbox" name="entrant_sortant" value="sortant" <?= ($this->filters['type'] ?? '') === 'sortant' ? 'checked' : '' ?>><?= $this->t(['fr' => 'Sortant','en' => 'Outgoing']) ?></label>
                </div>

                <div class="filter-group">
                    <label><input type="checkbox" name="zone" value="europe" <?= ($this->filters['zone'] ?? '') === 'europe' ? 'checked' : '' ?>><?= $this->t(['fr' => 'Europe','en' => 'Europe']) ?></label>
                    <label><input type="checkbox" name="zone" value="hors_europe" <?= ($this->filters['zone'] ?? '') === 'hors_europe' ? 'checked' : '' ?>><?= $this->t(['fr' => 'Hors-Europe','en' => 'Non-Europe']) ?></label>
                </div>

                <div class="filter-group">
                    <label for="filter-complet"><?= $this->t(['fr' => 'Statut :','en' => 'Status:']) ?></label>
                    <select id="filter-complet" onchange="appliquerFiltres()" style="padding: 5px;">
                        <option value="all" <?= ($this->filters['complet'] ?? 'all') === 'all' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Tous','en' => 'All']) ?></option>
                        <option value="1" <?= ($this->filters['complet'] ?? '') === '1' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Complet ✅','en' => 'Complete ✅']) ?></option>
                        <option value="0" <?= ($this->filters['complet'] ?? '') === '0' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Incomplet ⚠️','en' => 'Incomplete ⚠️']) ?></option>
                    </select>
                </div>

                <div class="filter-group">


                <?php if ($this->hasActiveFilters()) : ?>
                    <a href="<?= $this->buildUrl('/folders-admin') ?>" class="btn-reset" style="color: red; text-decoration: underline; margin-left: auto;">
                        <?= $this->t(['fr' => '✖ Réinitialiser les filtres','en' => '✖ Reset filters']) ?>
                    </a>
                <?php endif; ?>
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
                // Determine Mobility Type (Stage/études) based on existing files in JSON
                $pieces = json_decode($etudiant['PiecesJustificatives'] ?? '{}', true);
                $mobilityType = '-';
                if (!empty($pieces['convention'])) {
                    $mobilityType = $this->t(['fr' => 'Stage', 'en' => 'Internship']);
                } elseif (!empty($pieces['lettre_motivation'])) {
                    $mobilityType = $this->t(['fr' => 'Études', 'en' => 'Studies']);
                }
                ?>
                <tr onclick="ouvrirFicheEtudiant('<?= htmlspecialchars($etudiant['NumEtu'] ?? '') ?>')" style="cursor: pointer;">
                    <td><?= htmlspecialchars($etudiant['Nom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['Prenom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['DateNaissance'] ?? '') ?></td>
                    <td><?= $this->t(['fr' => ($etudiant['Type'] === 'entrant' ? 'Entrant' : 'Sortant'), 'en' => ($etudiant['Type'] === 'entrant' ? 'Incoming' : 'Outgoing')]) ?></td>
                    <td><?= $this->t(['fr' => ($etudiant['Zone'] === 'europe' ? 'Europe' : 'Hors Europe'), 'en' => ($etudiant['Zone'] === 'europe' ? 'Europe' : 'Non-Europe')]) ?></td>
                    <td><?= htmlspecialchars($mobilityType) ?></td>
                    <td>
                        <?= ($etudiant['IsComplete'] ?? 0) == 1
                                ? '<span style="color:green">Complet</span>'
                                : '<span style="color:orange">Incomplet</span>' ?>
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
     */
    private function renderCreateForm(): void
    {
        ?>
        <h1><?= $this->t(['fr' => 'Créer un nouveau dossier étudiant','en' => 'Create New Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders']) ?>'" class="btn-secondary">
                ← <?= $this->t(['fr' => 'Retour à la liste','en' => 'Back to List']) ?>
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

            <div class="fichier-obligatoire" id="justificatif_convention" style="display: none;">
                <label><?= $this->t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>
                <input type="file" name="convention" accept=".pdf,.doc,.docx">
            </div>
            <div class="fichier-obligatoire" id="lettre_motivation" style="display: none;">
                <label><?= $this->t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>
                <input type="file" name="lettre_motivation" accept=".pdf,.doc,.docx">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-secondary"><?= $this->t(['fr' => 'Enregistrer','en' => 'Save']) ?></button>
                <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders']) ?>'">
                    <?= $this->t(['fr' => 'Annuler','en' => 'Cancel']) ?>
                </button>
            </div>
        </form>
        <?php
    }

    /**
     * Renders the detailed view of a student folder (Edit Mode).
     */
    private function renderViewForm(): void
    {
        if (!$this->studentData) {
            echo '<p>' . $this->t(['fr' => 'Étudiant non trouvé','en' => 'Student not found']) . '</p>';
            return;
        }

        $student = $this->studentData;

        // Determine if it is 'stage' or 'studies' based on existing files.
        $detectedType = '';
        if (!empty($student['pieces']['convention'])) {
            $detectedType = 'stage';
        } elseif (!empty($student['pieces']['lettre_motivation'])) {
            $detectedType = 'etudes';
        }
        ?>
        <h1><?= $this->t(['fr' => 'Dossier étudiant','en' => 'Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'" class="btn-secondary">
                ← <?= $this->t(['fr' => 'Retour à la liste','en' => 'Back to List']) ?>
            </button>
        </div>

        <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($this->lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu"><?= $this->t(['fr' => 'NumÉtu *','en' => 'Student ID *']) ?></label>
                <input type="text" id="numetu_display" value="<?= htmlspecialchars($student['NumEtu'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">
                <input type="hidden" name="numetu" id="numetu" value="<?= htmlspecialchars($student['NumEtu'] ?? '') ?>">

                <label for="nom"><?= $this->t(['fr' => 'Nom *','en' => 'Last Name *']) ?></label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($student['Nom'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="prenom"><?= $this->t(['fr' => 'Prénom *','en' => 'First Name *']) ?></label>
                <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($student['Prenom'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="naissance"><?= $this->t(['fr' => 'Né(e) le','en' => 'Date of Birth']) ?></label>
                <input type="date" name="naissance" id="naissance" value="<?= htmlspecialchars($student['DateNaissance'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="sexe"><?= $this->t(['fr' => 'Sexe','en' => 'Gender']) ?></label>
                <select name="sexe" id="sexe" disabled style="background-color: #e0e0e0; color: #666;">
                    <option value="M" <?= ($student['Sexe'] ?? '') === 'M' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Masculin','en' => 'Male']) ?></option>
                    <option value="F" <?= ($student['Sexe'] ?? '') === 'F' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Féminin','en' => 'Female']) ?></option>
                    <option value="Autre" <?= ($student['Sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Autre','en' => 'Other']) ?></option>
                </select>

                <label for="adresse"><?= $this->t(['fr' => 'Adresse','en' => 'Address']) ?></label>
                <input type="text" name="adresse" id="adresse" value="<?= htmlspecialchars($student['Adresse'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="cp"><?= $this->t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
                <input type="text" name="cp" id="cp" value="<?= htmlspecialchars($student['CodePostal'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="ville"><?= $this->t(['fr' => 'Ville','en' => 'City']) ?></label>
                <input type="text" name="ville" id="ville" value="<?= htmlspecialchars($student['Ville'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="email_perso"><?= $this->t(['fr' => 'Email Personnel *','en' => 'Personal Email *']) ?></label>
                <input type="email" name="email_perso" id="email_perso" value="<?= htmlspecialchars($student['EmailPersonnel'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="email_amu"><?= $this->t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
                <input type="email" name="email_amu" id="email_amu" value="<?= htmlspecialchars($student['EmailAMU'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="telephone"><?= $this->t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
                <input type="text" name="telephone" id="telephone" value="<?= htmlspecialchars($student['Telephone'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="departement"><?= $this->t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
                <input type="text" name="departement" id="departement" value="<?= htmlspecialchars($student['CodeDepartement'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="type"><?= $this->t(['fr' => 'Type *','en' => 'Type *']) ?></label>
                <select name="type" id="type" disabled style="background-color: #e0e0e0; color: #666;" required>
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="entrant" <?= ($student['Type'] ?? '') === 'entrant' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Entrant','en' => 'Incoming']) ?></option>
                    <option value="sortant" <?= ($student['Type'] ?? '') === 'sortant' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Sortant','en' => 'Outgoing']) ?></option>
                </select>

                <label for="zone"><?= $this->t(['fr' => 'Zone *','en' => 'Zone *']) ?></label>
                <select name="zone" id="zone" disabled style="background-color: #e0e0e0; color: #666;" required>
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="europe" <?= ($student['Zone'] ?? '') === 'europe' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Europe','en' => 'Europe']) ?></option>
                    <option value="hors_europe" <?= ($student['Zone'] ?? '') === 'hors_europe' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Hors Europe','en' => 'Non-Europe']) ?></option>
                </select>

                <label for="mobilite_type"><?= $this->t(['fr' => 'Type de mobilité','en' => 'Mobility Type']) ?></label>
                <select name="mobilite_type" id="mobilite_type" onchange="changerTypeMobilite(this.value)" disabled style="background-color: #e0e0e0; color: #666;">
                    <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                    <option value="stage" <?= $detectedType === 'stage' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Stage','en' => 'Internship']) ?></option>
                    <option value="etudes" <?= $detectedType === 'etudes' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Études','en' => 'Studies']) ?></option>
                </select>
            </div>

            <div class="form-section" style="margin-top: 30px;">
                <h2><?= $this->t(['fr' => 'Pièces Justificatives','en' => 'Supporting Documents']) ?></h2>

                <div style="margin-bottom: 20px;">
                    <label><?= $this->t(['fr' => 'Photo','en' => 'Photo']) ?></label>
                    <?php if (!empty($student['pieces']['photo'])) : ?>
                        <div style="margin-top: 10px;">
                            <img src="data:image/jpeg;base64,<?= $student['pieces']['photo'] ?>"
                                 alt="Photo"
                                 style="max-width: 200px; max-height: 200px; border: 1px solid #ccc; border-radius: 5px;">
                            <br>
                            <a href="data:image/jpeg;base64,<?= $student['pieces']['photo'] ?>"
                               download="photo_<?= htmlspecialchars($student['NumEtu']) ?>.jpg"
                               class="btn-secondary"
                               style="margin-top: 10px; display: inline-block;">
                                <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p style="color: #999;"><?= $this->t(['fr' => 'Aucune photo disponible','en' => 'No photo available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="photo" id="photo" accept="image/*" disabled style="background-color: #e0e0e0; color: #666; margin-top: 10px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label><?= $this->t(['fr' => 'CV','en' => 'CV']) ?></label>
                    <?php if (!empty($student['pieces']['cv'])) : ?>
                        <div style="margin-top: 10px;">
                            <p style="color: #008000FF;"> <?= $this->t(['fr' => 'CV disponible','en' => 'CV available']) ?></p>
                            <a href="data:application/pdf;base64,<?= $student['pieces']['cv'] ?>"
                               download="cv_<?= htmlspecialchars($student['NumEtu']) ?>.pdf"
                               class="btn-secondary">
                                <?= $this->t(['fr' => 'Télécharger le CV','en' => 'Download CV']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p style="color: #999;"><?= $this->t(['fr' => 'Aucun CV disponible','en' => 'No CV available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx" disabled style="background-color: #e0e0e0; color: #666; margin-top: 10px;">
                </div>

                <div id="justificatif_convention" style="margin-bottom: 20px; display: none;">
                    <label><?= $this->t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>

                    <?php if (!empty($student['pieces']['convention'])) : ?>
                        <div style="margin-top: 10px;">
                            <p style="color: #008000FF;"> <?= $this->t(['fr' => 'Convention disponible','en' => 'Agreement available']) ?></p>
                            <a href="data:application/pdf;base64,<?= $student['pieces']['convention'] ?>"
                               download="convention_<?= htmlspecialchars($student['NumEtu']) ?>.pdf"
                               class="btn-secondary">
                                <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p style="color: #999;"><?= $this->t(['fr' => 'Aucune convention disponible','en' => 'No agreement available']) ?></p>
                    <?php endif; ?>

                    <input type="file" name="convention" id="convention" accept=".pdf,.doc,.docx" disabled style="background-color: #e0e0e0; color: #666; margin-top: 10px;">
                </div>

                <div id="lettre_motivation" style="margin-bottom: 20px; display: none;">
                    <label><?= $this->t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>

                    <?php if (!empty($student['pieces']['lettre_motivation'])) : ?>
                        <div style="margin-top: 10px;">
                            <p>✅ <?= $this->t(['fr' => 'Lettre disponible','en' => 'Letter available']) ?></p>
                            <a href="data:application/pdf;base64,<?= $student['pieces']['lettre_motivation'] ?>"
                               download="lettre_<?= htmlspecialchars($student['NumEtu']) ?>.pdf"
                               class="btn-secondary">
                                <?= $this->t(['fr' => '📥 Télécharger','en' => '📥 Download']) ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <p style="color: #999;"><?= $this->t(['fr' => 'Aucune lettre disponible','en' => 'No letter available']) ?></p>
                    <?php endif; ?>

                    <input type="file" name="lettre_motivation" id="lettre_motivation_file" accept=".pdf,.doc,.docx" disabled style="background-color: #e0e0e0; color: #666; margin-top: 10px;">
                </div>



                <div style="margin-top: 20px; padding: 15px; background-color: <?= ($student['IsComplete'] ?? 0) ? '#d4edda' : '#fff3cd' ?>; border-radius: 5px;">
                    <strong><?= $this->t(['fr' => 'Statut du dossier :','en' => 'Folder status:']) ?></strong>
                    <span style="color: <?= ($student['IsComplete'] ?? 0) ? '#008000FF' : '#FFA500FF' ?>;">
                        <?= ($student['IsComplete'] ?? 0)
                                ? $this->t(['fr' => 'Complet','en' => 'Complete'])
                                : $this->t(['fr' => 'Incomplet','en' => 'Incomplete']) ?>
                    </span>
                </div>

                    <br><br>
                    <button type="button"
                            onclick="window.location.href='index.php?page=toggle_complete&numetu=<?= urlencode($student['NumEtu'] ?? '') ?>&lang=<?= htmlspecialchars($this->lang) ?>'"
                            class="btn-secondary"
                            style="margin-top: 10px;">
                        <?= ($student['IsComplete'] ?? 0)
                                ? $this->t(['fr' => 'Marquer comme incomplet','en' => 'Mark as incomplete'])
                                : $this->t(['fr' => 'Marquer comme complet','en' => 'Mark as complete']) ?>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="btn-modifier" class="btn-danger" onclick="activerModification()">
                    <?= $this->t(['fr' => 'Modifier','en' => 'Edit']) ?>
                </button>

                <button type="submit" id="btn-enregistrer" class="btn-secondary" style="display: none;">
                    <?= $this->t(['fr' => 'Enregistrer','en' => 'Save']) ?>
                </button>

                <button type="button" id="btn-annuler" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'" style="display: none;">
                    <?= $this->t(['fr' => 'Annuler','en' => 'Cancel']) ?>
                </button>

                <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'">
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
        return 'index.php?' . http_build_query($params);
    }

    /**
     * Checks if any filter is currently active.
     *
     * @return bool True if filters are active.
     */
    private function hasActiveFilters(): bool
    {
        return ($this->filters['type'] ?? 'all') !== 'all'
                || ($this->filters['zone'] ?? 'all') !== 'all'
                || ($this->filters['complet'] ?? 'all') !== 'all'
                || !empty($this->filters['date_debut'])
                || !empty($this->filters['date_fin'])
                || !empty($this->filters['search']);
    }
}