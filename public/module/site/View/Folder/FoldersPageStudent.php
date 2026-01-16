<?php

// phpcs:disable Generic.Files.LineLength

namespace View\Folder;

/**
 * Class FoldersPageStudent
 *
 * View responsible for displaying the student's personal folder page.
 * Includes the form to view and edit personal information and documents.
 */
class FoldersPageStudent
{
    /** @var array<string, mixed> Student folder data (including personal info and pieces) */
    private array $dossier;

    /** @var string Student identifier (NumEtu) */
    private string $studentId;

    // Note: $action removed from properties because it is never read.
    // It remains in the constructor signature for compatibility.

    /** @var string Flash message (success/error) */
    private string $message;

    /** @var string Current language code */
    private string $lang;

    /**
     * Constructor.
     *
     * @param array<string, mixed>|null $dossier   Student folder data.
     * @param string                    $studentId Student identifier (NumEtu).
     * @param string                    $message   Flash message (success/error).
     * @param string                    $lang      Current language.
     */
    public function __construct(?array $dossier, string $studentId, string $message, string $lang)
    {
        $this->dossier = $dossier ?? [];
        $this->studentId = $studentId;
        $this->message = $message;
        $this->lang = $lang;
    }

    /**
     * Translates a string based on the current language.
     *
     * @param array{fr: string, en: string} $frEn Associative array with 'fr' and 'en' translations.
     * @return string The translated string.
     */
    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    /**
     * Builds a URL safely handling query parameters.
     *
     * @param string               $path   The base path.
     * @param array<string, mixed> $params Query parameters to append.
     * @return string The constructed URL.
     */
    private function buildUrl(string $path, array $params = []): string
    {
        $params['lang'] = $this->lang;
        $separator = (strpos($path, '?') === false) ? '?' : '&';
        return $path . $separator . http_build_query($params);
    }

    /**
     * Main method to render the HTML page.
     *
     * @return void
     */
    public function render(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_GET['lang'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $this->lang = (string)($_SESSION['lang'] ?? 'fr');

        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = $_GET['tritanopia'] === '1';
        }
        $tritanopia = !empty($_SESSION['tritanopia']);

        // --- Determine View Mode: CREATE or UPDATE ---
        $isCreateMode = empty($this->dossier);
        $formAction = $isCreateMode ? 'create_folder' : 'update_my_folder';

        // Extract pieces safely to avoid "Undefined array key" warnings
        /** @var array<string, mixed> $pieces */
        $pieces = $this->dossier['pieces'] ?? [];

        // Auto-detect mobility type for display logic (only if updating)
        $detectedType = '';
        if (!$isCreateMode) {
            if (!empty($pieces['convention'])) {
                $detectedType = 'stage';
            } elseif (!empty($pieces['lettre_motivation'])) {
                $detectedType = 'etudes';
            }
        }

        // Prepare strict variables for HTML output (Level 9 compliance)
        $valNom = htmlspecialchars(strval($this->dossier['Nom'] ?? ''));
        $valPrenom = htmlspecialchars(strval($this->dossier['Prenom'] ?? ''));
        $valDate = htmlspecialchars(strval($this->dossier['DateNaissance'] ?? ''));
        $valSexe = strval($this->dossier['Sexe'] ?? '');
        $valEmailP = htmlspecialchars(strval($this->dossier['EmailPersonnel'] ?? ''));
        $valEmailA = htmlspecialchars(strval($this->dossier['EmailAMU'] ?? ''));
        $valTel = htmlspecialchars(strval($this->dossier['Telephone'] ?? ''));
        $valAdresse = htmlspecialchars(strval($this->dossier['Adresse'] ?? ''));
        $valCP = htmlspecialchars(strval($this->dossier['CodePostal'] ?? ''));
        $valVille = htmlspecialchars(strval($this->dossier['Ville'] ?? ''));
        $valDept = htmlspecialchars(strval($this->dossier['CodeDepartement'] ?? ''));
        $valType = strval($this->dossier['Type'] ?? '');
        $valZone = strval($this->dossier['Zone'] ?? '');

        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->t(['fr' => 'Mon dossier','en' => 'My Folder']) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
            <link rel="stylesheet" href="styles/chatbot.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body class="<?= $tritanopia ? 'tritanopie' : '' ?>">
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
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'home']) ?>'">
                    <?= $this->t(['fr' => 'Accueil', 'en' => 'Home']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'dashboard-student']) ?>'">
                    <?= $this->t(['fr' => 'Mon Tableau de bord', 'en' => 'My Dashboard']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'partners-student']) ?>'">
                    <?= $this->t(['fr' => 'Partenaires', 'en' => 'Partners']) ?>
                </button>
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'folders-student']) ?>'">
                    <?= $this->t(['fr' => 'Mon Dossier', 'en' => 'My Folder']) ?>
                </button>
                <button onclick="window.location.href='<?= $this->buildUrl('/web_plan-student') ?>'"><?= $this->t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>

            </nav>
        </header>
        <main>
            <h1><?= $this->t(['fr' => 'Mon dossier étudiant','en' => 'My Student Folder']) ?></h1>

            <?php if (!empty($this->message)) : ?>
                <div class="message"><?= htmlspecialchars($this->message) ?></div>
            <?php endif; ?>

            <form method="post"
                  action="<?= $this->buildUrl('index.php', ['page' => $formAction]) ?>"
                  enctype="multipart/form-data"
                  class="creation-form">

                <div class="form-section">
                    <label><?= $this->t(['fr' => 'Numéro étudiant','en' => 'Student ID']) ?></label>
                    <input type="text" value="<?= htmlspecialchars($this->studentId) ?>" readonly style="background:#eee;">
                    <input type="hidden" name="numetu" value="<?= htmlspecialchars($this->studentId) ?>">

                    <label><?= $this->t(['fr' => 'Nom *','en' => 'Last Name *']) ?></label>
                    <input type="text" name="nom" value="<?= $valNom ?>" 
                           <?= $isCreateMode ? 'required' : 'readonly style="background:#f7f7f7;"' ?>>

                    <label><?= $this->t(['fr' => 'Prénom *','en' => 'First Name *']) ?></label>
                    <input type="text" name="prenom" value="<?= $valPrenom ?>" 
                           <?= $isCreateMode ? 'required' : 'readonly style="background:#f7f7f7;"' ?>>

                    <label><?= $this->t(['fr' => 'Date de naissance','en' => 'Date of Birth']) ?></label>
                    <input type="date" name="naissance" value="<?= $valDate ?>"
                           <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

                    <label><?= $this->t(['fr' => 'Sexe','en' => 'Gender']) ?></label>
                    <select name="sexe" id="sexe" <?= $isCreateMode ? '' : 'disabled style="background:#f7f7f7;"' ?>>
                        <option value="M" <?= $valSexe === 'M' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Masculin','en' => 'Male']) ?></option>
                        <option value="F" <?= $valSexe === 'F' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Féminin','en' => 'Female']) ?></option>
                        <option value="Autre" <?= $valSexe === 'Autre' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Autre','en' => 'Other']) ?></option>
                    </select>

                    <label><?= $this->t(['fr' => 'Email personnel *','en' => 'Personal Email *']) ?></label>
                    <input type="email" name="email_perso" value="<?= $valEmailP ?>" required>

                    <label><?= $this->t(['fr' => 'Email AMU','en' => 'AMU Email']) ?></label>
                    <input type="email" name="email_amu" value="<?= $valEmailA ?>"
                           <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

                    <label><?= $this->t(['fr' => 'Téléphone *','en' => 'Phone *']) ?></label>
                    <input type="text" name="telephone" value="<?= $valTel ?>" required>

                    <label><?= $this->t(['fr' => 'Adresse','en' => 'Address']) ?></label>
                    <input type="text" name="adresse" value="<?= $valAdresse ?>">

                    <label><?= $this->t(['fr' => 'Code postal','en' => 'Postal Code']) ?></label>
                    <input type="text" name="cp" value="<?= $valCP ?>">

                    <label><?= $this->t(['fr' => 'Ville','en' => 'City']) ?></label>
                    <input type="text" name="ville" value="<?= $valVille ?>">
                    
                    <label><?= $this->t(['fr' => 'Code Département','en' => 'Department Code']) ?></label>
                    <input type="text" name="departement" value="<?= $valDept ?>"
                           <?= $isCreateMode ? '' : 'readonly style="background:#f7f7f7;"' ?>>

                    <label for="type"><?= $this->t(['fr' => 'Type *','en' => 'Type *']) ?></label>
                    <select name="type" id="type" <?= $isCreateMode ? 'required' : 'disabled style="background:#f7f7f7;"' ?>>
                        <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                        <option value="entrant" <?= $valType === 'entrant' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Entrant','en' => 'Incoming']) ?></option>
                        <option value="sortant" <?= $valType === 'sortant' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Sortant','en' => 'Outgoing']) ?></option>
                    </select>

                    <label for="zone"><?= $this->t(['fr' => 'Zone *','en' => 'Zone *']) ?></label>
                    <select name="zone" id="zone" <?= $isCreateMode ? 'required' : 'disabled style="background:#f7f7f7;"' ?>>
                        <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                        <option value="europe" <?= $valZone === 'europe' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Europe','en' => 'Europe']) ?></option>
                        <option value="hors_europe" <?= $valZone === 'hors_europe' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Hors Europe','en' => 'Non-Europe']) ?></option>
                    </select>

                    <label for="mobilite_type"><?= $this->t(['fr' => 'Type de mobilité','en' => 'Mobility Type']) ?></label>
                    <select name="mobilite_type" id="mobilite_type" onchange="changerTypeMobilite(this.value)" 
                        <?= $isCreateMode ? '' : 'disabled style="background:#f7f7f7;"' ?>>
                        <option value=""><?= $this->t(['fr' => '-- Choisir --','en' => '-- Choose --']) ?></option>
                        <option value="stage" <?= $detectedType === 'stage' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Stage','en' => 'Internship']) ?></option>
                        <option value="etudes" <?= $detectedType === 'etudes' ? 'selected' : '' ?>><?= $this->t(['fr' => 'Études','en' => 'Studies']) ?></option>
                    </select>
                </div>

                <div class="form-section" style="margin-top: 30px;">
                    <h2><?= $this->t(['fr' => 'Mes documents','en' => 'My Documents']) ?></h2>

                    <?php
                    $docs = ['photo' => 'Photo', 'cv' => 'CV'];
                    foreach ($docs as $key => $label) :
                        $hasFile = !empty($pieces[$key]);
                        ?>
                    <div style="margin-bottom: 20px;">
                        <label><?= $this->t(['fr' => $label,'en' => $label]) ?></label>
                        <?php if ($hasFile) : ?>
                            <div style="margin-top: 10px;">
                                <a href="data:application/octet-stream;base64,<?= strval($pieces[$key]) ?>"
                                   download="<?= $key ?>_<?= htmlspecialchars($this->studentId) ?>.<?= $key === 'photo' ? 'jpg' : 'pdf' ?>"
                                   class="btn-secondary">
                                   <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <p style="color:#999;"><?= $this->t(['fr' => 'Aucun fichier','en' => 'No file']) ?></p>
                        <?php endif; ?>
                        <input type="file" name="<?= $key ?>" accept="<?= $key === 'photo' ? 'image/*' : '.pdf' ?>">
                    </div>
                    <?php endforeach; ?>

                    <div id="justificatif_convention" style="display: none; margin-bottom: 20px;">
                        <label><?= $this->t(['fr' => 'Convention de stage','en' => 'Internship Agreement']) ?></label>
                        <?php if (!empty($pieces['convention'])) : ?>
                             <div style="margin-top: 10px;">
                                <a href="data:application/pdf;base64,<?= strval($pieces['convention']) ?>" download="convention.pdf" class="btn-secondary">
                                    <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="convention" accept=".pdf,.doc,.docx">
                    </div>

                    <div id="lettre_motivation" style="display: none; margin-bottom: 20px;">
                        <label><?= $this->t(['fr' => 'Lettre de motivation','en' => 'Motivation Letter']) ?></label>
                        <?php if (!empty($pieces['lettre_motivation'])) : ?>
                             <div style="margin-top: 10px;">
                                <a href="data:application/pdf;base64,<?= strval($pieces['lettre_motivation']) ?>" download="lettre.pdf" class="btn-secondary">
                                    <?= $this->t(['fr' => 'Télécharger','en' => 'Download']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="lettre_motivation" accept=".pdf,.doc,.docx">
                    </div>

                </div>

                <div class="form-actions">
                    <?php if ($isCreateMode) : ?>
                        <button type="submit" class="btn-secondary">
                            <?= $this->t(['fr' => 'Déposer ma demande','en' => 'Submit my application']) ?>
                        </button>
                    <?php else : ?>
                        <button type="submit" class="btn-secondary">
                            <?= $this->t(['fr' => 'Enregistrer mes modifications','en' => 'Save changes']) ?>
                        </button>
                    <?php endif; ?>
                    
                    <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('index.php', ['page' => 'home']) ?>'">
                        <?= $this->t(['fr' => 'Annuler','en' => 'Cancel']) ?>
                    </button>
                </div>
            </form>
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
        <script src="js/chatbot.js"></script>
        <script>
            document.getElementById('current-lang').addEventListener('click', function(event) {
                event.stopPropagation();
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.toggle('show');
            });

            document.addEventListener('click', function() {
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.remove('show');
            });

            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }

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

            window.addEventListener('DOMContentLoaded', (event) => {
                const sel = document.getElementById('mobilite_type');
                if (sel) changerTypeMobilite(sel.value);
            });
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
}