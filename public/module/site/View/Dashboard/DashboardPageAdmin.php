<?php
namespace View\Dashboard;

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
        return $path . '?lang=' . urlencode($this->lang);
    }

        private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->lang === 'en' ? 'Admin Dashboard' : 'Tableau de bord (Admin)' ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body>
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
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr'=>'Accueil','en'=>'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard-admin') ?>'"><?= $this->t(['fr'=>'Tableau de bord','en'=>'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/partners') ?>'"><?= $this->t(['fr'=>'Partenaire','en'=>'Partners']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/folders-admin') ?>'"><?= $this->t(['fr'=>'Dossiers','en'=>'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'"><?= $this->t(['fr'=>'Plan du site','en'=>'Site Map']) ?></button>
            </nav>
        </header>

        <main>
            <h1><?= $this->lang === 'en' ? 'Incomplete Student Files' : 'Dossiers étudiants incomplets' ?></h1>

            <?php if (empty($this->dossiers)): ?>
                <p style="text-align:center;color:#666;">
                    <?= $this->lang === 'en' ? 'No incomplete files found.' : 'Aucun dossier incomplet trouvé.' ?>
                </p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>NumEtu</th>
                        <th><?= $this->lang === 'en' ? 'Last Name' : 'Nom' ?></th>
                        <th><?= $this->lang === 'en' ? 'First Name' : 'Prénom' ?></th>
                        <th><?= $this->lang === 'en' ? 'Progress' : 'Avancement' ?></th>
                        <th><?= $this->lang === 'en' ? 'Documents Submitted' : 'Pièces fournies' ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($this->dossiers as $d): ?>
                        <?php
                        $nom = $d['Nom'] ?? $d['nom'] ?? '';
                        $prenom = $d['Prenom'] ?? $d['prenom'] ?? '';
                        $total = $d['total_pieces'] ?? 0;
                        $fournies = $d['pieces_fournies'] ?? 0;
                        $pourcentage = $total > 0 ? round(($fournies / $total) * 100) : 0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($d['NumEtu'] ?? '') ?></td>
                            <td><?= htmlspecialchars($nom) ?></td>
                            <td><?= htmlspecialchars($prenom) ?></td>
                            <td>
                                <div class="progress-bar"><div class="progress" style="width:<?= $pourcentage ?>%"><?= $pourcentage ?>%</div></div>
                            </td>
                            <td><?= "$fournies / $total" ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2025 - Aix-Marseille Université</p>
        </footer>
        <script>
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
        </script>
        </body>
        </html>
        <?php
    }
}
