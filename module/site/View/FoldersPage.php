<?php
namespace View;

class FoldersPage
{
    private array $voeux;
    private string $message;
    private string $lang;

    public function __construct(array $voeux = [], string $message = '', string $lang = 'fr')
    {
        $this->voeux = $voeux;
        $this->message = $message;
        $this->lang = $lang;
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
        <body>
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo" style="height:100px;">
                <div class="lang-dropdown" style="float:right; margin-top: 30px; margin-right: 20px;">
                    <button class="dropbtn" id="current-lang"><?= htmlspecialchars($this->lang) ?></button>
                    <div class="dropdown-content">
                        <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                        <a href="#" onclick="changeLang('en'); return false;">English</a>
                    </div>
                </div>
            </div>
            <nav class="menu">
                <button onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr'=>'Accueil','en'=>'Home']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/dashboard') ?>'"><?= $this->t(['fr'=>'Tableau de bord','en'=>'Dashboard']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/settings') ?>'"><?= $this->t(['fr'=>'Paramétrage','en'=>'Settings']) ?></button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/folders') ?>'"><?= $this->t(['fr'=>'Dossiers','en'=>'Folders']) ?></button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan') ?>'"><?= $this->t(['fr'=>'Plan du site','en'=>'Site Map']) ?></button>
            </nav>
            <div class="sub-menu" style="display:flex; gap:10px; margin-top:20px;">
                <button onclick="window.location.href='<?= $this->buildUrl('/folders') ?>'"><?= $this->t(['fr'=>'Les étudiants','en'=>'Students']) ?></button>
            </div>
        </header>

        <main>
            <h1><?= $this->t(['fr'=>'Fiche Étudiant','en'=>'Student Record']) ?></h1>

            <?php if (!empty($this->message)): ?>
                <div class="message">
                    <?= htmlspecialchars($this->message) ?>
                </div>
            <?php endif; ?>

            <div class="student-toolbar">
                <div>
                    <label for="search"><?= $this->t(['fr'=>'Rechercher','en'=>'Search']) ?></label>
                    <input type="text" id="search" name="search">
                </div>
                <div class="nav-buttons">
                    <button title="<?= $this->t(['fr'=>'Premier','en'=>'First']) ?>">&laquo;</button>
                    <button title="<?= $this->t(['fr'=>'Précédent','en'=>'Previous']) ?>">&lt;</button>
                    <button title="<?= $this->t(['fr'=>'Suivant','en'=>'Next']) ?>">&gt;</button>
                    <button title="<?= $this->t(['fr'=>'Dernier','en'=>'Last']) ?>">&raquo;</button>
                    <span style="margin-left:10px; font-style: italic; cursor: pointer;"><?= $this->t(['fr'=>'Enregistrer la fiche','en'=>'Save Record']) ?></span>
                </div>
            </div>

            <form method="post" action="save_student.php" enctype="multipart/form-data">
                <div class="form-section">
                    <label for="numetu"><?= $this->t(['fr'=>'NumÉtu','en'=>'Student ID']) ?></label>
                    <input type="text" name="numetu" id="numetu" required>

                    <label for="nom"><?= $this->t(['fr'=>'Nom','en'=>'Last Name']) ?></label>
                    <input type="text" name="nom" id="nom" required>

                    <label for="prenom"><?= $this->t(['fr'=>'Prénom','en'=>'First Name']) ?></label>
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

                    <label for="email_perso"><?= $this->t(['fr'=>'Email Personnel','en'=>'Personal Email']) ?></label>
                    <input type="email" name="email_perso" id="email_perso">

                    <label for="email_amu"><?= $this->t(['fr'=>'Email AMU','en'=>'AMU Email']) ?></label>
                    <input type="email" name="email_amu" id="email_amu">

                    <label for="telephone"><?= $this->t(['fr'=>'Téléphone','en'=>'Phone']) ?></label>
                    <input type="text" name="telephone" id="telephone">

                    <label for="departement"><?= $this->t(['fr'=>'Code Département','en'=>'Department Code']) ?></label>
                    <input type="text" name="departement" id="departement">

                    <label for="photo"><?= $this->t(['fr'=>'Photo','en'=>'Photo']) ?></label>
                    <input type="file" name="photo" id="photo" accept="image/*">
                </div>
            </form>

            <h2><?= $this->t(['fr'=>"Liste des vœux de l'étudiant",'en'=>"Student's Wishes List"]) ?></h2>
            <table>
                <thead>
                <tr>
                    <th><?= $this->t(['fr'=>'N° vœu','en'=>'Wish #']) ?></th>
                    <th><?= $this->t(['fr'=>'Code campagne','en'=>'Campaign Code']) ?></th>
                    <th><?= $this->t(['fr'=>'Départ','en'=>'Departure']) ?></th>
                    <th><?= $this->t(['fr'=>'Retour','en'=>'Return']) ?></th>
                    <th><?= $this->t(['fr'=>'Destination','en'=>'Destination']) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($this->voeux)): ?>
                    <?php foreach ($this->voeux as $voeu): ?>
                        <tr>
                            <td><?= htmlspecialchars($voeu['id']) ?></td>
                            <td><?= htmlspecialchars($voeu['codecampagne']) ?></td>
                            <td><?= htmlspecialchars($voeu['depart']) ?></td>
                            <td><?= htmlspecialchars($voeu['retour']) ?></td>
                            <td><?= htmlspecialchars($voeu['destination']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5"><?= $this->t(['fr'=>'Aucun vœu enregistré','en'=>'No wishes recorded']) ?></td></tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="voeux-actions">
                <button type="button" onclick="window.location.href='<?= $this->buildUrl('new_voeu.php') ?>'"><?= $this->t(['fr'=>'Nouveau vœu','en'=>'New Wish']) ?></button>
                <button type="button" onclick="location.reload()"><?= $this->t(['fr'=>'Actualiser la liste','en'=>'Refresh List']) ?></button>
            </div>
        </main>

        <!-- Bulle d'aide en bas à droite -->
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>

        <!-- Contenu du popup d'aide -->
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr'=>'Aide','en'=>'Help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->t(['fr'=>'Bienvenue ! Comment pouvons-nous vous aider ?','en'=>'Welcome! How can we help you?']) ?></p>
                <ul>
                    <li><a href="index.php?page=help" target="_blank"><?= $this->t(['fr'=>'Page d’aide complète','en'=>'Full help page']) ?></a></li>
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
        </script>
        <footer>
            <p>&copy; 2025 - Aix-Marseille Université.</p>
        </footer>
        </body>
        </html>
        <?php
    }
}
