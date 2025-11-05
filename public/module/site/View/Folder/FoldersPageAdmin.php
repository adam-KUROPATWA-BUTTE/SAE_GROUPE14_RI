<?php
namespace View\Folder;
use Model\Folder\FolderAdmin as Folder;

class FoldersPageAdmin
{
    private string $action;
    private array $filters;
    private int $page;
    private int $perPage;
    private string $message;
    private string $lang;
    private ?array $studentData;

    public function __construct(string $action, array $filters, int $page, int $perPage, string $message, string $lang, ?array $studentData = null)
    {
        $this->action = $action;
        $this->filters = $filters;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->message = $message;
        $this->lang = $lang;
        $this->studentData = $studentData;
    }

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        return $path . '?' . http_build_query($params);
    }

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
            <title><?= $this->t(['fr'=>'Gestion des dossiers','en'=>'Folders Management']) ?></title>
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
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr'=>'Accueil','en'=>'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-admin') ?>'"><?= $this->t(['fr'=>'Tableau de bord','en'=>'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners') ?>'"><?= $this->t(['fr'=>'Partenaire','en'=>'Partners']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'"><?= $this->t(['fr'=>'Dossiers','en'=>'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'"><?= $this->t(['fr'=>'Plan du site','en'=>'Site Map']) ?></button>
            </nav>
        </header>
        <main>
            <?php if ($this->action === 'create'): ?>
                <?php $this->renderCreateForm(); ?>
            <?php elseif ($this->action === 'view'): ?>
                <?php $this->renderViewForm(); ?>
            <?php else: ?>
                <?php $this->renderStudentsList(); ?>
            <?php endif; ?>
        </main>
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr'=>'Aide', 'en'=>'Help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->t(['fr'=>'Bienvenue ! Comment pouvons-nous vous aider ?', 'en'=>'Welcome! How can we help you?']) ?></p>
                <ul>
                    <li><a href="/help" target="_blank"><?= $this->t(['fr'=>'Page d’aide complète', 'en'=>'Full help page']) ?></a></li>
                </ul>
            </div>
        </div>
        <script>
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
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
            function filtrerEtudiants() {
                let input = document.getElementById("search");
                let filter = input.value.toLowerCase();
                let rows = document.querySelectorAll("#table-etudiants tbody tr");
                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? "" : "none";
                });
            }
            function changerTypeMobilite(type) {
                document.querySelectorAll('.fichier-obligatoire').forEach(el => el.style.display = 'none');
                if (type === 'stage') {
                    document.getElementById('justificatif_convention').style.display = 'grid';
                } else if (type === 'etudes') {
                    document.getElementById('lettre_motivation').style.display = 'grid';
                }
            }
            function activerModification() {
                document.querySelectorAll('.creation-form input, .creation-form select').forEach(field => {
                    field.disabled = false;
                    field.style.backgroundColor = 'white';
                    field.style.color = 'black';
                });
                document.getElementById('btn-modifier').style.display = 'none';
                document.getElementById('btn-enregistrer').style.display = 'inline-block';
                document.getElementById('btn-annuler').style.display = 'inline-block';
            }
            window.addEventListener('DOMContentLoaded', (event) => {
                const sel = document.getElementById('mobilite_type');
                if (sel) changerTypeMobilite(sel.value);
                document.querySelectorAll('.filters input[type="checkbox"]').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        appliquerFiltres();
                    });
                });
            });
            function appliquerFiltres() {
                const url = new URL(window.location.href);
                const typesChecked = Array.from(document.querySelectorAll('input[name="entrant_sortant"]:checked')).map(cb => cb.value);
                if (typesChecked.length === 1) url.searchParams.set('type', typesChecked[0]);
                else url.searchParams.delete('type');
                const zonesChecked = Array.from(document.querySelectorAll('input[name="zone"]:checked')).map(cb => cb.value);
                if (zonesChecked.length === 1) url.searchParams.set('zone', zonesChecked[0]);
                else url.searchParams.delete('zone');
                window.location.href = url.toString();
            }
        </script>
        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
            <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
                <img src="img/instagram.png" alt="Instagram" style="height:32px;">
            </a>
        </footer>
        </body>
        </html>
        <?php
    }

    private function renderStudentsList(): void
    {
        $etudiants = Folder::getAll();
        $filtered = $this->applyFilters($etudiants);
        $total = count($filtered);
        $totalPages = ceil($total / $this->perPage);
        $offset = ($this->page - 1) * $this->perPage;
        $paginated = array_slice($filtered, $offset, $this->perPage);
        ?>
        <h1><?= $this->t(['fr'=>'Liste des étudiants','en'=>'Students List']) ?></h1>
        <?php if (!empty($this->message)): ?>
        <div class="message"><?= htmlspecialchars($this->message) ?></div>
        <?php endif; ?>
        <div class="student-toolbar">
            <div class="search-container-toolbar">
                <label for="search" class="search-label"><?= $this->t(['fr'=>'Rechercher','en'=>'Search']) ?></label>
                <input type="text" id="search" name="search" placeholder="Nom, prénom, email..." value="<?= htmlspecialchars($this->filters['search']) ?>" onkeyup="filtrerEtudiants()">
            </div>
            <div>
                <button id="btn-creer-dossier" onclick="window.location.href='<?= $this->buildUrl('/folders-admin', ['action' => 'create']) ?>'">
                    <?= $this->t(['fr'=>'+ Créer un dossier','en'=>'+ Create Folder']) ?>
                </button>
            </div>
        </div>
        <div class="filters">
            <label><input type="checkbox" name="entrant_sortant" value="entrant" <?= $this->filters['type'] === 'entrant' ? 'checked' : '' ?>><?= $this->t(['fr'=>'Entrant','en'=>'Incoming']) ?></label>
            <label><input type="checkbox" name="entrant_sortant" value="sortant" <?= $this->filters['type'] === 'sortant' ? 'checked' : '' ?>><?= $this->t(['fr'=>'Sortant','en'=>'Outgoing']) ?></label>
            <label><input type="checkbox" name="zone" value="europe" <?= $this->filters['zone'] === 'europe' ? 'checked' : '' ?>><?= $this->t(['fr'=>'Europe','en'=>'Europe']) ?></label>
            <label><input type="checkbox" name="zone" value="hors_europe" <?= $this->filters['zone'] === 'hors_europe' ? 'checked' : '' ?>><?= $this->t(['fr'=>'Hors-Europe','en'=>'Non-Europe']) ?></label>
            <?php if ($this->hasActiveFilters()): ?>
                <a href="<?= $this->buildUrl('/folders') ?>" class="btn-reset"><?= $this->t(['fr'=>'Réinitialiser','en'=>'Reset']) ?></a>
            <?php endif; ?>
        </div>
        <p class="results-count"><?= $total ?> <?= $this->t(['fr'=>'étudiant(s) trouvé(s)','en'=>'student(s) found']) ?></p>
        <table id="table-etudiants">
            <thead>
                <tr>
                    <th><?= $this->t(['fr'=>'Nom','en'=>'Last Name']) ?></th>
                    <th><?= $this->t(['fr'=>'Prénom','en'=>'First Name']) ?></th>
                    <th><?= $this->t(['fr'=>'Email','en'=>'Email']) ?></th>
                    <th><?= $this->t(['fr'=>'Type','en'=>'Type']) ?></th>
                    <th><?= $this->t(['fr'=>'Zone','en'=>'Zone']) ?></th>
                    <th><?= $this->t(['fr'=>'Pièces','en'=>'Documents']) ?></th>
                    <th><?= $this->t(['fr'=>'Dernière relance','en'=>'Last Follow-up']) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($paginated as $etudiant): ?>
                <tr onclick="ouvrirFicheEtudiant('<?= htmlspecialchars($etudiant['NumEtu'] ?? '') ?>')" style="cursor: pointer;">
                    <td><?= htmlspecialchars($etudiant['Nom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['Prenom']) ?></td>
                    <td><?= htmlspecialchars($etudiant['EmailAMU'] ?? '') ?></td>
                    <td><?= $this->t(['fr' => ($etudiant['Type'] === 'entrant' ? 'Entrant' : 'Sortant'), 'en' => ($etudiant['Type'] === 'entrant' ? 'Incoming' : 'Outgoing')]) ?></td>
                    <td><?= $this->t(['fr' => ($etudiant['Zone'] === 'europe' ? 'Europe' : 'Hors Europe'), 'en' => ($etudiant['Zone'] === 'europe' ? 'Europe' : 'Non-Europe')]) ?></td>
                    <td><?= ($etudiant['pieces_fournies'] ?? 0) ?>/<?= ($etudiant['total_pieces'] ?? 0) ?></td>
                    <td><?= htmlspecialchars($etudiant['date_derniere_relance'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($totalPages > 0): ?>
        <div class="pagination">
            <?php if ($this->page > 1): ?>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl(1) ?>'">«</button>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl($this->page - 1) ?>'">‹</button>
            <?php else: ?>
                <button disabled>«</button>
                <button disabled>‹</button>
            <?php endif; ?>
            <?php for ($i = max(1, $this->page - 2); $i <= min($totalPages, $this->page + 2); $i++): ?>
                <button class="<?= $i === $this->page ? 'active' : '' ?>" onclick="window.location.href='<?= $this->buildPaginationUrl($i) ?>'"><?= $i ?></button>
            <?php endfor; ?>
            <?php if ($this->page < $totalPages): ?>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl($this->page + 1) ?>'">›</button>
                <button onclick="window.location.href='<?= $this->buildPaginationUrl($totalPages) ?>'">»</button>
            <?php else: ?>
                <button disabled>›</button>
                <button disabled>»</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php
    }

    private function renderCreateForm(): void
    {
        ?>
        <h1><?= $this->t(['fr'=>'Créer un nouveau dossier étudiant','en'=>'Create New Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders']) ?>'" class="btn-secondary">
                ← <?= $this->t(['fr'=>'Retour à la liste','en'=>'Back to List']) ?>
            </button>
        </div>
        <form method="post" action="index.php?page=save_student&lang=<?= htmlspecialchars($this->lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu"><?= $this->t(['fr'=>'NumÉtu *','en'=>'Student ID *']) ?></label>
                <input type="text" name="numetu" id="numetu" required>
                <label for="nom"><?= $this->t(['fr'=>'Nom *','en'=>'Last Name *']) ?></label>
                <input type="text" name="nom" id="nom" required>
                <label for="prenom"><?= $this->t(['fr'=>'Prénom *','en'=>'First Name *']) ?></label>
                <input type="text" name="prenom" id="prenom" required>
                <label for="naissance"><?= $this->t(['fr'=>'Né(e) le','en'=>'Date of Birth']) ?></label>
                <input type="date" name="naissance" id="naissance">
                <label for="sexe"><?= $this->t(['fr'=>'Sexe','en'=>'Gender']) ?></label>
                <select name="sexe" id="sexe">
                    <option value="M"><?= $this->t(['fr'=>'Masculin','en'=>'Male']) ?></option>
                    <option value="F"><?= $this->t(['fr'=>'Féminin','en'=>'Female']) ?></option>
                    <option value="Autre"><?= $this->t(['fr'=>'Autre','en'=>'Other']) ?></option>
                </select>
                <label for="adresse"><?= $this->t(['fr'=>'Adresse','en'=>'Address']) ?></label>
                <input type="text" name="adresse" id="adresse">
                <label for="cp"><?= $this->t(['fr'=>'Code postal','en'=>'Postal Code']) ?></label>
                <input type="text" name="cp" id="cp">
                <label for="ville"><?= $this->t(['fr'=>'Ville','en'=>'City']) ?></label>
                <input type="text" name="ville" id="ville">
                <label for="email_perso"><?= $this->t(['fr'=>'Email Personnel *','en'=>'Personal Email *']) ?></label>
                <input type="email" name="email_perso" id="email_perso" required>
                <label for="email_amu"><?= $this->t(['fr'=>'Email AMU','en'=>'AMU Email']) ?></label>
                <input type="email" name="email_amu" id="email_amu">
                <label for="telephone"><?= $this->t(['fr'=>'Téléphone *','en'=>'Phone *']) ?></label>
                <input type="text" name="telephone" id="telephone" required>
                <label for="departement"><?= $this->t(['fr'=>'Code Département','en'=>'Department Code']) ?></label>
                <input type="text" name="departement" id="departement">
                <label for="type"><?= $this->t(['fr'=>'Type *','en'=>'Type *']) ?></label>
                <select name="type" id="type" required>
                    <option value=""><?= $this->t(['fr'=>'-- Choisir --','en'=>'-- Choose --']) ?></option>
                    <option value="entrant"><?= $this->t(['fr'=>'Entrant','en'=>'Incoming']) ?></option>
                    <option value="sortant"><?= $this->t(['fr'=>'Sortant','en'=>'Outgoing']) ?></option>
                </select>
                <label for="zone"><?= $this->t(['fr'=>'Zone *','en'=>'Zone *']) ?></label>
                <select name="zone" id="zone" required>
                    <option value=""><?= $this->t(['fr'=>'-- Choisir --','en'=>'-- Choose --']) ?></option>
                    <option value="europe"><?= $this->t(['fr'=>'Europe','en'=>'Europe']) ?></option>
                    <option value="hors_europe"><?= $this->t(['fr'=>'Hors Europe','en'=>'Non-Europe']) ?></option>
                </select>
                <label for="photo"><?= $this->t(['fr'=>'Photo','en'=>'Photo']) ?></label>
                <input type="file" name="photo" id="photo" accept="image/*">
                <label for="cv"><?= $this->t(['fr'=>'CV','en'=>'CV']) ?></label>
                <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx">
                <label for="mobilite_type"><?= $this->t(['fr'=>'Type de mobilité','en'=>'Mobility Type']) ?></label>
                <select name="mobilite_type" id="mobilite_type" onchange="changerTypeMobilite(this.value)">
                    <option value=""><?= $this->t(['fr'=>'-- Choisir --','en'=>'-- Choose --']) ?></option>
                    <option value="stage"><?= $this->t(['fr'=>'Stage','en'=>'Internship']) ?></option>
                    <option value="etudes"><?= $this->t(['fr'=>'Études','en'=>'Studies']) ?></option>
                </select>
            </div>
            <div class="fichier-obligatoire" id="justificatif_convention" style="display: none;">
                <label><?= $this->t(['fr'=>'Convention de stage','en'=>'Internship Agreement']) ?></label>
                <input type="file" name="convention" accept=".pdf,.doc,.docx">
            </div>
            <div class="fichier-obligatoire" id="lettre_motivation" style="display: none;">
                <label><?= $this->t(['fr'=>'Lettre de motivation','en'=>'Motivation Letter']) ?></label>
                <input type="file" name="lettre_motivation" accept=".pdf,.doc,.docx">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-secondary"><?= $this->t(['fr'=>'Enregistrer','en'=>'Save']) ?></button>
                <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders']) ?>'">
                    <?= $this->t(['fr'=>'Annuler','en'=>'Cancel']) ?>
                </button>
            </div>
        </form>
        <?php
    }

// ✅ MÉTHODE CORRIGÉE - Afficher le formulaire de visualisation/modification
    private function renderViewForm(): void
    {
        if (!$this->studentData) {
            echo '<p>' . $this->t(['fr'=>'Étudiant non trouvé','en'=>'Student not found']) . '</p>';
            return;
        }
        
        $student = $this->studentData;
        ?>
        <h1><?= $this->t(['fr'=>'Dossier étudiant','en'=>'Student Folder']) ?></h1>
        <div class="form-back-button">
            <button onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'" class="btn-secondary">
                ← <?= $this->t(['fr'=>'Retour à la liste','en'=>'Back to List']) ?>
            </button>
        </div>
        
        <form method="post" action="index.php?page=update_student&lang=<?= htmlspecialchars($this->lang) ?>" enctype="multipart/form-data" class="creation-form">
            <div class="form-section">
                <label for="numetu"><?= $this->t(['fr'=>'NumÉtu *','en'=>'Student ID *']) ?></label>
                <input type="text" name="numetu" id="numetu" value="<?= htmlspecialchars($student['NumEtu'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="nom"><?= $this->t(['fr'=>'Nom *','en'=>'Last Name *']) ?></label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($student['Nom'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="prenom"><?= $this->t(['fr'=>'Prénom *','en'=>'First Name *']) ?></label>
                <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($student['Prenom'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="naissance"><?= $this->t(['fr'=>'Né(e) le','en'=>'Date of Birth']) ?></label>
                <input type="date" name="naissance" id="naissance" value="<?= htmlspecialchars($student['DateNaissance'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="sexe"><?= $this->t(['fr'=>'Sexe','en'=>'Gender']) ?></label>
                <select name="sexe" id="sexe" disabled style="background-color: #e0e0e0; color: #666;">
                    <option value="M" <?= ($student['Sexe'] ?? '') === 'M' ? 'selected' : '' ?>><?= $this->t(['fr'=>'Masculin','en'=>'Male']) ?></option>
                    <option value="F" <?= ($student['Sexe'] ?? '') === 'F' ? 'selected' : '' ?>><?= $this->t(['fr'=>'Féminin','en'=>'Female']) ?></option>
                    <option value="Autre" <?= ($student['Sexe'] ?? '') === 'Autre' ? 'selected' : '' ?>><?= $this->t(['fr'=>'Autre','en'=>'Other']) ?></option>
                </select>

                <label for="adresse"><?= $this->t(['fr'=>'Adresse','en'=>'Address']) ?></label>
                <input type="text" name="adresse" id="adresse" value="<?= htmlspecialchars($student['Adresse'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="cp"><?= $this->t(['fr'=>'Code postal','en'=>'Postal Code']) ?></label>
                <input type="text" name="cp" id="cp" value="<?= htmlspecialchars($student['CodePostal'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="ville"><?= $this->t(['fr'=>'Ville','en'=>'City']) ?></label>
                <input type="text" name="ville" id="ville" value="<?= htmlspecialchars($student['Ville'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="email_perso"><?= $this->t(['fr'=>'Email Personnel *','en'=>'Personal Email *']) ?></label>
                <input type="email" name="email_perso" id="email_perso" value="<?= htmlspecialchars($student['EmailPersonnel'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="email_amu"><?= $this->t(['fr'=>'Email AMU','en'=>'AMU Email']) ?></label>
                <input type="email" name="email_amu" id="email_amu" value="<?= htmlspecialchars($student['EmailAMU'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="telephone"><?= $this->t(['fr'=>'Téléphone *','en'=>'Phone *']) ?></label>
                <input type="text" name="telephone" id="telephone" value="<?= htmlspecialchars($student['Telephone'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;" required>

                <label for="departement"><?= $this->t(['fr'=>'Code Département','en'=>'Department Code']) ?></label>
                <input type="text" name="departement" id="departement" value="<?= htmlspecialchars($student['CodeDepartement'] ?? '') ?>" disabled style="background-color: #e0e0e0; color: #666;">

                <label for="type"><?= $this->t(['fr'=>'Type *','en'=>'Type *']) ?></label>
                <select name="type" id="type" disabled style="background-color: #e0e0e0; color: #666;" required>
                    <option value=""><?= $this->t(['fr'=>'-- Choisir --','en'=>'-- Choose --']) ?></option>
                    <option value="entrant" <?= ($student['Type'] ?? '') === 'entrant' ? 'selected' : '' ?>><?= $this->t(['fr'=>'Entrant','en'=>'Incoming']) ?></option>
                    <option value="sortant" <?= ($student['Type'] ?? '') === 'sortant' ? 'selected' : '' ?>><?= $this->t(['fr'=>'Sortant','en'=>'Outgoing']) ?></option>
                </select>

                <label for="zone"><?= $this->t(['fr'=>'Zone *','en'=>'Zone *']) ?></label>
                <select name="zone" id="zone" disabled style="background-color: #e0e0e0; color: #666;" required>
                    <option value=""><?= $this->t(['fr'=>'-- Choisir --','en'=>'-- Choose --']) ?></option>
                    <option value="europe" <?= ($student['Zone'] ?? '') === 'europe' ? 'selected' : '' ?>><?= $this->t(['fr'=>'Europe','en'=>'Europe']) ?></option>
                    <option value="hors_europe" <?= ($student['Zone'] ?? '') === 'hors_europe' ? 'selected' : '' ?>><?= $this->t(['fr'=>'Hors Europe','en'=>'Non-Europe']) ?></option>
                </select>
            </div>

            <!-- ✅ SECTION PIÈCES JUSTIFICATIVES -->
            <div class="form-section" style="margin-top: 30px;">
                <h2><?= $this->t(['fr'=>'Pièces Justificatives','en'=>'Supporting Documents']) ?></h2>
                
                <!-- Photo -->
                <div style="margin-bottom: 20px;">
                    <label><?= $this->t(['fr'=>'Photo','en'=>'Photo']) ?></label>
                    <?php if (!empty($student['pieces']['photo'])): ?>
                        <div style="margin-top: 10px;">
                            <img src="data:image/jpeg;base64,<?= $student['pieces']['photo'] ?>" 
                                alt="Photo" 
                                style="max-width: 200px; max-height: 200px; border: 1px solid #ccc; border-radius: 5px;">
                            <br>
                            <a href="data:image/jpeg;base64,<?= $student['pieces']['photo'] ?>" 
                            download="photo_<?= htmlspecialchars($student['NumEtu']) ?>.jpg" 
                            class="btn-secondary" 
                            style="margin-top: 10px; display: inline-block;">
                                <?= $this->t(['fr'=>'📥 Télécharger','en'=>'📥 Download']) ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <p style="color: #999;"><?= $this->t(['fr'=>'Aucune photo disponible','en'=>'No photo available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="photo" id="photo" accept="image/*" disabled style="background-color: #e0e0e0; color: #666; margin-top: 10px;">
                </div>

                <!-- CV -->
                <div style="margin-bottom: 20px;">
                    <label><?= $this->t(['fr'=>'CV','en'=>'CV']) ?></label>
                    <?php if (!empty($student['pieces']['cv'])): ?>
                        <div style="margin-top: 10px;">
                            <p>✅ <?= $this->t(['fr'=>'CV disponible','en'=>'CV available']) ?></p>
                            <a href="data:application/pdf;base64,<?= $student['pieces']['cv'] ?>" 
                            download="cv_<?= htmlspecialchars($student['NumEtu']) ?>.pdf" 
                            class="btn-secondary">
                                <?= $this->t(['fr'=>'📥 Télécharger le CV','en'=>'📥 Download CV']) ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <p style="color: #999;"><?= $this->t(['fr'=>'Aucun CV disponible','en'=>'No CV available']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="cv" id="cv" accept=".pdf,.doc,.docx" disabled style="background-color: #e0e0e0; color: #666; margin-top: 10px;">
                </div>

                <!-- Convention de stage -->
                <?php if (!empty($student['pieces']['convention'])): ?>
                <div style="margin-bottom: 20px;">
                    <label><?= $this->t(['fr'=>'Convention de stage','en'=>'Internship Agreement']) ?></label>
                    <div style="margin-top: 10px;">
                        <p>✅ <?= $this->t(['fr'=>'Convention disponible','en'=>'Agreement available']) ?></p>
                        <a href="data:application/pdf;base64,<?= $student['pieces']['convention'] ?>" 
                        download="convention_<?= htmlspecialchars($student['NumEtu']) ?>.pdf" 
                        class="btn-secondary">
                            <?= $this->t(['fr'=>'📥 Télécharger','en'=>'📥 Download']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Lettre de motivation -->
                <?php if (!empty($student['pieces']['lettre_motivation'])): ?>
                <div style="margin-bottom: 20px;">
                    <label><?= $this->t(['fr'=>'Lettre de motivation','en'=>'Motivation Letter']) ?></label>
                    <div style="margin-top: 10px;">
                        <p>✅ <?= $this->t(['fr'=>'Lettre disponible','en'=>'Letter available']) ?></p>
                        <a href="data:application/pdf;base64,<?= $student['pieces']['lettre_motivation'] ?>" 
                        download="lettre_<?= htmlspecialchars($student['NumEtu']) ?>.pdf" 
                        class="btn-secondary">
                            <?= $this->t(['fr'=>'📥 Télécharger','en'=>'📥 Download']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Statut du dossier -->
                <div style="margin-top: 20px; padding: 15px; background-color: <?= ($student['IsComplete'] ?? 0) ? '#d4edda' : '#fff3cd' ?>; border-radius: 5px;">
                    <strong><?= $this->t(['fr'=>'Statut du dossier :','en'=>'Folder status:']) ?></strong>
                    <?= ($student['IsComplete'] ?? 0) 
                        ? $this->t(['fr'=>'✅ Complet','en'=>'✅ Complete']) 
                        : $this->t(['fr'=>'⚠️ Incomplet','en'=>'⚠️ Incomplete']) ?>
                    <br><br>
                    <button type="button" 
                            onclick="window.location.href='index.php?page=toggle_complete&numetu=<?= urlencode($student['NumEtu'] ?? '') ?>&lang=<?= htmlspecialchars($this->lang) ?>'"
                            class="btn-secondary"
                            style="margin-top: 10px;">
                        <?= ($student['IsComplete'] ?? 0) 
                            ? $this->t(['fr'=>'Marquer comme incomplet','en'=>'Mark as incomplete']) 
                            : $this->t(['fr'=>'Marquer comme complet','en'=>'Mark as complete']) ?>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <!-- Bouton Modifier -->
                <button type="button" id="btn-modifier" class="btn-danger" onclick="activerModification()">
                    <?= $this->t(['fr'=>'Modifier','en'=>'Edit']) ?>
                </button>

                <!-- Bouton Enregistrer (caché par défaut) -->
                <button type="submit" id="btn-enregistrer" class="btn-secondary" style="display: none;">
                    <?= $this->t(['fr'=>'Enregistrer','en'=>'Save']) ?>
                </button>

                <!-- Bouton Annuler -->
                <button type="button" id="btn-annuler" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'" style="display: none;">
                    <?= $this->t(['fr'=>'Annuler','en'=>'Cancel']) ?>
                </button>

                <!-- Bouton Retour -->
                <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'">
                    <?= $this->t(['fr'=>'Retour','en'=>'Back']) ?>
                </button>
            </div>
        </form>
        <?php
    }

    private function applyFilters(array $etudiants): array
    {
        $filtered = $etudiants;
        if ($this->filters['type'] !== 'all') {
            $filtered = array_filter($filtered, fn($e) => ($e['type'] ?? '') === $this->filters['type']);
        }
        if ($this->filters['zone'] !== 'all') {
            $filtered = array_filter($filtered, fn($e) => ($e['zone'] ?? '') === $this->filters['zone']);
        }
        if (!empty($this->filters['search'])) {
            $search = strtolower($this->filters['search']);
            $filtered = array_filter($filtered, function($e) use ($search) {
                return strpos(strtolower($e['nom'] ?? ''), $search) !== false
                        || strpos(strtolower($e['prenom'] ?? ''), $search) !== false
                        || strpos(strtolower($e['email'] ?? ''), $search) !== false;
            });
        }
        return array_values($filtered);
    }

    private function buildPaginationUrl(int $page): string
    {
        $params = array_merge($this->filters, [
            'p' => $page,
            'page' => 'folders-admin'
        ]);
        return 'index.php?' . http_build_query($params);
    }


    private function hasActiveFilters(): bool
    {
        return $this->filters['type'] !== 'all'
                || $this->filters['zone'] !== 'all'
                || !empty($this->filters['search']);
    }
}
