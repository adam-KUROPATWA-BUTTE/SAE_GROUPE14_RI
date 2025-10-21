<?php
namespace View;

class DashboardPage
{
    private array $dossiers;
    private string $lang;

    public function __construct(array $dossiers = [], string $lang = 'fr')
    {
        $this->dossiers = $dossiers;
        $this->lang = $lang;
    }

    // Méthode helper pour construire les URLs avec param lang
    private function buildUrl(string $path): string
    {
        return $path . '?lang=' . urlencode($this->lang);
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tableau de bord</title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img id="logo_amu" src="img/logo.png" alt="Logo" style="height:100px;">
                <div class="right-buttons">
                    <div class="lang-dropdown">
                        <button class="dropbtn" id="current-lang"><?= htmlspecialchars($this->lang) ?></button>
                        <div class="dropdown-content">
                            <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                            <a href="#" onclick="changeLang('en'); return false;">English</a>
                        </div>
                    </div>
                </div>
            </div>
            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'">
                    <?= $this->lang === 'en' ? 'Home' : 'Accueil' ?>
                </button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/dashboard') ?>'">
                    <?= $this->lang === 'en' ? 'Dashboard' : 'Tableau de bord' ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners') ?>'">
                    <?= $this->lang === 'en' ? 'Partners' : 'Partenaires' ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/folders') ?>'">
                    <?= $this->lang === 'en' ? 'Folders' : 'Dossiers' ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'">
                    <?= $this->lang === 'en' ? 'Site Map' : 'Plan du site' ?>
                </button>
            </nav>

        </header>

        <main>
            <h1><?= $this->lang === 'en' ? 'Incomplete Student Files' : 'Dossiers étudiants incomplets' ?></h1>
            <table>
                <thead>
                <tr>
                    <th>NumETu</th>
                    <th><?= $this->lang === 'en' ? 'Last Name' : 'Nom étudiant' ?></th>
                    <th><?= $this->lang === 'en' ? 'First Name' : 'Prénom étudiant' ?></th>
                    <th><?= $this->lang === 'en' ? 'Progress' : 'Avancement' ?></th>
                    <th><?= $this->lang === 'en' ? 'Documents Submitted' : 'Pièces fournies' ?></th>
                    <th><?= $this->lang === 'en' ? 'Last Reminder' : 'Dernière relance' ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($this->dossiers as $dossier):
                    $total = (int)$dossier['total_pieces'];
                    $fournies = (int)$dossier['pieces_fournies'];
                    $pourcentage = $total > 0 ? round(($fournies / $total) * 100) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($dossier['numetu'] ?? '') ?></td>
                        <td><?= htmlspecialchars($dossier['nom']) ?></td>
                        <td><?= htmlspecialchars($dossier['prenom']) ?></td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress" style="width:<?= $pourcentage ?>%"><?= $pourcentage ?>%</div>
                            </div>
                        </td>
                        <td><?= $fournies ?> / <?= $total ?></td>
                        <td><?= htmlspecialchars($dossier['date_derniere_relance']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </main>

        <!-- Bulle d'aide en bas à droite -->
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>

        <!-- Contenu du popup d'aide -->
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->lang === 'en' ? 'Help' : 'Aide' ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->lang === 'en' ? 'Welcome! How can we help you?' : 'Bienvenue ! Comment pouvons-nous vous aider ?' ?></p>
                <ul>
                    <li><a href="index.php?page=help" target="_blank"><?= $this->lang === 'en' ? 'Full help page' : 'Page d’aide complète' ?></a></li>
                </ul>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const menuToggle = document.createElement('button');
                menuToggle.classList.add('menu-toggle');
                menuToggle.innerHTML = '☰';
                document.querySelector('.right-buttons').appendChild(menuToggle);

                const navMenu = document.querySelector('nav.menu');
                menuToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });
            });
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
            }

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
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
}