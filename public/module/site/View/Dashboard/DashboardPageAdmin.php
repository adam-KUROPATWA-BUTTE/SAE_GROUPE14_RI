<?php

namespace View\Dashboard;

/**
 * Class DashboardPageAdmin
 *
 * Renders the HTML for the Administrator's Global Dashboard.
 * Includes advanced filtering logic, status calculation, and responsive tables.
 */
class DashboardPageAdmin
{
    private array $dossiers;
    private string $lang;

    public function __construct(array $dossiers = [], string $lang = 'fr')
    {
        $this->dossiers = $dossiers;
        $this->lang = $lang;
    }

    private function buildUrl(string $path): string
    {
        return $path . (strpos($path, '?') !== false ? '&' : '?') . 'lang=' . urlencode($this->lang);
    }

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    public function render(): void
    {
        // --- 1. Retrieve Filters ---
        $searchStudent = strtolower($_GET['student'] ?? '');
        $filterDept    = $_GET['dept'] ?? '';
        $filterType    = $_GET['type'] ?? '';
        $filterYear    = $_GET['year'] ?? '';
        $filterDest    = $_GET['dest'] ?? '';
        $filterCamp    = $_GET['camp'] ?? '';

        // --- 2. Process Data ---
        $outgoing = [];
        $incoming = [];

        foreach ($this->dossiers as $d) {
            $nom        = $d['Nom'] ?? '';
            $prenom     = $d['Prenom'] ?? '';
            $numEtu     = $d['NumEtu'] ?? '';
            $dept       = $d['CodeDepartement'] ?? '';
            $type       = $d['Type'] ?? '';
            $zone       = $d['Zone'] ?? ''; 
            $annee      = $d['Annee'] ?? '2024-2025'; 
            $campagne   = $d['Campagne'] ?? 'Automne 2024'; 

            // --- A. Filtering Logic ---
            if ($searchStudent) {
                $fullName = strtolower("$nom $prenom $numEtu");
                if (strpos($fullName, $searchStudent) === false) continue;
            }
            if ($filterDept && $dept !== $filterDept) continue;
            if ($filterType && $type !== $filterType) continue;
            if ($filterYear && $annee !== $filterYear) continue;
            if ($filterDest && strpos(strtolower($zone), strtolower($filterDest)) === false) continue;
            if ($filterCamp && $campagne !== $filterCamp) continue;

            // --- B. Color Code Logic (Correction here) ---
            $isComplete = $d['IsComplete'] ?? 0;
            $piecesJson = $d['PiecesJustificatives'] ?? '';
            $pieces = (!empty($piecesJson)) ? json_decode($piecesJson, true) : [];
            
            $totalRequired = 4; 
            $countProvided = is_array($pieces) ? count($pieces) : 0;
            
            if ($isComplete == 1) {
                $percentage = 100;
            } else {
                $percentage = ($totalRequired > 0) ? round(($countProvided / $totalRequired) * 100) : 0;
                if ($percentage > 100) $percentage = 100;
            }

            $d['calc_percentage'] = $percentage;
            $d['calc_annee']      = $annee;
            $d['calc_camp']       = $campagne;

            // --- C. Sort Incoming/Outgoing ---
            if (stripos($type, 'incoming') !== false || stripos($type, 'entrant') !== false) {
                $incoming[] = $d;
            } else {
                $outgoing[] = $d;
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->t(['fr' => 'Tableau de bord Admin', 'en' => 'Admin Dashboard']) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
            <link rel="stylesheet" href="styles/dashboard.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body class="<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true ? 'tritanopie' : '' ?>">
        <header>
            <div class="top-bar">
                <img class="logo_amu" src="img/logo.png" alt="Logo">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr' => 'Accueil','en' => 'Home']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('index.php?page=dashboard-admin') ?>'"><?= $this->t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners-admin') ?>'"><?= $this->t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'"><?= $this->t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
            </nav>
        </header>

        <main>
            <h1><?= $this->t(['fr' => 'Suivi Global des Mobilités', 'en' => 'Global Mobility Tracking']) ?></h1>

            <form class="filters-container" method="GET" action="index.php">
                <input type="hidden" name="page" value="dashboard-admin">
                <input type="hidden" name="lang" value="<?= $this->lang ?>">

                <input type="text" name="student" placeholder="<?= $this->t(['fr' => 'Rechercher étudiant...', 'en' => 'Search student...']) ?>" value="<?= htmlspecialchars($searchStudent) ?>">
                
                <select name="dept">
                    <option value=""><?= $this->t(['fr' => 'Tous Départements', 'en' => 'All Departments']) ?></option>
                    <option value="Informatique" <?= $filterDept == 'Informatique' ? 'selected' : '' ?>>Informatique</option>
                    <option value="GEA" <?= $filterDept == 'GEA' ? 'selected' : '' ?>>GEA</option>
                    <option value="Biologie" <?= $filterDept == 'Biologie' ? 'selected' : '' ?>>Biologie</option>
                </select>

                <select name="year">
                    <option value=""><?= $this->t(['fr' => 'Toutes Années', 'en' => 'All Years']) ?></option>
                    <option value="2024-2025" <?= $filterYear == '2024-2025' ? 'selected' : '' ?>>2024-2025</option>
                    <option value="2025-2026" <?= $filterYear == '2025-2026' ? 'selected' : '' ?>>2025-2026</option>
                </select>

                <select name="type">
                    <option value=""><?= $this->t(['fr' => 'Tous Types', 'en' => 'All Types']) ?></option>
                    <option value="Erasmus" <?= $filterType == 'Erasmus' ? 'selected' : '' ?>>Erasmus</option>
                    <option value="Stage" <?= $filterType == 'Stage' ? 'selected' : '' ?>>Stage</option>
                </select>

                <select name="camp">
                    <option value=""><?= $this->t(['fr' => 'Toutes Campagnes', 'en' => 'All Campaigns']) ?></option>
                    <option value="Automne 2024" <?= $filterCamp == 'Automne 2024' ? 'selected' : '' ?>>Automne 2024</option>
                    <option value="Hiver 2025" <?= $filterCamp == 'Hiver 2025' ? 'selected' : '' ?>>Hiver 2025</option>
                </select>

                <input type="text" name="dest" placeholder="<?= $this->t(['fr' => 'Destination...', 'en' => 'Destination...']) ?>" value="<?= htmlspecialchars($filterDest) ?>">

                <button type="submit" class="btn-filter"><?= $this->t(['fr' => 'Filtrer', 'en' => 'Filter']) ?></button>
            </form>

            <h2><?= $this->t(['fr' => 'Mobilités Sortantes', 'en' => 'Outgoing Mobilities']) ?></h2>
            <div class="table-responsive">
                <?php if (empty($outgoing)) : ?>
                    <p style="text-align:center; color:#666;"><?= $this->t(['fr' => 'Aucun dossier sortant trouvé.', 'en' => 'No outgoing files found.']) ?></p>
                <?php else : ?>
                    <table>
                        <thead>
                        <tr>
                            <th><?= $this->t(['fr' => 'Étudiant', 'en' => 'Student']) ?></th>
                            <th><?= $this->t(['fr' => 'Département', 'en' => 'Dept']) ?></th>
                            <th><?= $this->t(['fr' => 'Destination', 'en' => 'Destination']) ?></th>
                            <th><?= $this->t(['fr' => 'Campagne', 'en' => 'Campaign']) ?></th>
                            <th><?= $this->t(['fr' => 'Année', 'en' => 'Year']) ?></th>
                            <th><?= $this->t(['fr' => 'État', 'en' => 'Status']) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($outgoing as $d) : 
                            $pct = $d['calc_percentage'];
                            if ($pct >= 100) {
                                $badgeClass = 'bg-success'; // Green
                                $label = $this->t(['fr' => 'Validé', 'en' => 'Done']);
                            } elseif ($pct > 50) {
                                $badgeClass = 'bg-warning'; // Orange
                                $label = $pct . '%';
                            } else {
                                $badgeClass = 'bg-danger';  // Red
                                $label = $pct . '%';
                            }
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($d['Nom'] . ' ' . $d['Prenom']) ?></strong><br>
                                    <small><?= htmlspecialchars($d['NumEtu'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($d['CodeDepartement'] ?? '') ?></td>
                                <td><?= htmlspecialchars($d['Zone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($d['calc_camp'] ?? '') ?></td>
                                <td><?= htmlspecialchars($d['calc_annee'] ?? '') ?></td>
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>">
                                        <?= $label ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <h2><?= $this->t(['fr' => 'Mobilités Entrantes', 'en' => 'Incoming Mobilities']) ?></h2>
            <div class="table-responsive">
                <?php if (empty($incoming)) : ?>
                    <p style="text-align:center; color:#666;"><?= $this->t(['fr' => 'Aucun dossier entrant trouvé.', 'en' => 'No incoming files found.']) ?></p>
                <?php else : ?>
                    <table>
                        <thead>
                        <tr>
                            <th><?= $this->t(['fr' => 'Étudiant', 'en' => 'Student']) ?></th>
                            <th><?= $this->t(['fr' => 'Département', 'en' => 'Dept']) ?></th>
                            <th><?= $this->t(['fr' => 'Type', 'en' => 'Type']) ?></th>
                            <th><?= $this->t(['fr' => 'Année', 'en' => 'Year']) ?></th>
                            <th><?= $this->t(['fr' => 'État', 'en' => 'Status']) ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($incoming as $d) : 
                            $pct = $d['calc_percentage'];
                            if ($pct >= 100) { $badgeClass = 'bg-success';$label = $this->t(['fr' => 'Validé', 'en' => 'Done']); }
                            elseif ($pct > 50) { $badgeClass = 'bg-warning'; $label = $pct . '%'; }
                            else { $badgeClass = 'bg-danger'; $label = $pct . '%'; }
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($d['Nom'] . ' ' . $d['Prenom']) ?></strong><br>
                                    <small><?= htmlspecialchars($d['NumEtu'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($d['CodeDepartement'] ?? '') ?></td>
                                <td><?= htmlspecialchars($d['Type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($d['calc_annee'] ?? '') ?></td>
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>">
                                        <?= $label ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université</p>
        </footer>

        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr' => 'Aide', 'en' => 'Help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->t(['fr' => 'Besoin d\'aide ?', 'en' => 'Need help?']) ?></p>
            </div>
        </div>

        <script>
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
            }

            document.addEventListener("DOMContentLoaded", () => {
                const menuToggle = document.createElement('button');
                menuToggle.classList.add('menu-toggle');
                menuToggle.innerHTML = '☰';
                
                const rightBtn = document.querySelector('.right-buttons');
                if(rightBtn) rightBtn.appendChild(menuToggle);

                const navMenu = document.querySelector('nav.menu');
                menuToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });
            });
        </script>
        </body>
        </html>
        <?php
    }
}