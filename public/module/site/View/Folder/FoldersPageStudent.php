<?php
namespace View\Folder;

class FoldersPageStudent
{
    private array $dossier;
    private string $studentId;
    private string $action;
    private string $message;
    private string $lang;

    public function __construct(?array $dossier, string $studentId, string $action, string $message, string $lang)
    {
        $this->dossier = $dossier ?? [];
        $this->studentId = $studentId;
        $this->action = $action;
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $this->t(['fr'=>'Mon dossier','en'=>'My Folder']) ?></title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/folders.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body>
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
                <button class="active" onclick="window.location.href='<?= $this->buildUrl('/folders-student') ?>'"><?= $this->t(['fr'=>'Mon dossier','en'=>'My Folder']) ?></button>
                <button onclick="window.location.href='index.php?page=logout'"><?= $this->t(['fr'=>'Déconnexion','en'=>'Logout']) ?></button>
            </nav>
        </header>
        <main>
            <h1><?= $this->t(['fr'=>'Mon dossier étudiant','en'=>'My Student Folder']) ?></h1>

            <?php if (!empty($this->message)): ?>
                <div class="message"><?= htmlspecialchars($this->message) ?></div>
            <?php endif; ?>

            <?php if (empty($this->dossier)): ?>
                <p><?= $this->t(['fr'=>'Aucun dossier trouvé','en'=>'No folder found']) ?></p>
            <?php else: ?>
                <form method="post"
                      action="index.php?page=update_student&lang=<?= htmlspecialchars($this->lang) ?>"
                      enctype="multipart/form-data"
                      class="creation-form">

                    <!-- Informations personnelles -->
                    <div class="form-section">
                        <label><?= $this->t(['fr'=>'Numéro étudiant','en'=>'Student ID']) ?></label>
                        <input type="text" value="<?= htmlspecialchars($this->studentId) ?>" disabled style="background:#eee;">

                        <label><?= $this->t(['fr'=>'Nom','en'=>'Last Name']) ?></label>
                        <input type="text" value="<?= htmlspecialchars($this->dossier['Nom'] ?? '') ?>" readonly style="background:#f7f7f7;">

                        <label><?= $this->t(['fr'=>'Prénom','en'=>'First Name']) ?></label>
                        <input type="text" value="<?= htmlspecialchars($this->dossier['Prenom'] ?? '') ?>" readonly style="background:#f7f7f7;">

                        <label><?= $this->t(['fr'=>'Email personnel','en'=>'Personal Email']) ?></label>
                        <input type="email" name="email_perso" value="<?= htmlspecialchars($this->dossier['EmailPersonnel'] ?? '') ?>" required>

                        <label><?= $this->t(['fr'=>'Téléphone','en'=>'Phone']) ?></label>
                        <input type="text" name="telephone" value="<?= htmlspecialchars($this->dossier['Telephone'] ?? '') ?>" required>

                        <label><?= $this->t(['fr'=>'Adresse','en'=>'Address']) ?></label>
                        <input type="text" name="adresse" value="<?= htmlspecialchars($this->dossier['Adresse'] ?? '') ?>">

                        <label><?= $this->t(['fr'=>'Code postal','en'=>'Postal Code']) ?></label>
                        <input type="text" name="cp" value="<?= htmlspecialchars($this->dossier['CodePostal'] ?? '') ?>">

                        <label><?= $this->t(['fr'=>'Ville','en'=>'City']) ?></label>
                        <input type="text" name="ville" value="<?= htmlspecialchars($this->dossier['Ville'] ?? '') ?>">
                    </div>

                    <!-- Documents -->
                    <div class="form-section" style="margin-top: 30px;">
                        <h2><?= $this->t(['fr'=>'Mes documents','en'=>'My Documents']) ?></h2>

                        <?php
                        $docs = ['photo'=>'Photo', 'cv'=>'CV'];
                        foreach ($docs as $key => $label):
                            $hasFile = !empty($this->dossier['pieces'][$key]);
                        ?>
                        <div style="margin-bottom: 20px;">
                            <label><?= $this->t(['fr'=>$label,'en'=>$label]) ?></label>
                            <?php if ($hasFile): ?>
                                <div style="margin-top: 10px;">
                                    <a href="data:application/octet-stream;base64,<?= $this->dossier['pieces'][$key] ?>"
                                       download="<?= $key ?>_<?= htmlspecialchars($this->studentId) ?>.<?= $key==='photo'?'jpg':'pdf' ?>"
                                       class="btn-secondary">
                                       <?= $this->t(['fr'=>'📥 Télécharger','en'=>'📥 Download']) ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <p style="color:#999;"><?= $this->t(['fr'=>'Aucun fichier','en'=>'No file']) ?></p>
                            <?php endif; ?>
                            <input type="file" name="<?= $key ?>" accept="<?= $key==='photo'?'image/*':'.pdf' ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-secondary"><?= $this->t(['fr'=>'Enregistrer mes modifications','en'=>'Save changes']) ?></button>
                        <button type="button" class="btn-secondary" onclick="window.location.href='<?= $this->buildUrl('/') ?>'"><?= $this->t(['fr'=>'Annuler','en'=>'Cancel']) ?></button>
                    </div>
                </form>
            <?php endif; ?>
        </main>

        <script>
            function changeLang(lang) {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            }
        </script>
        <footer>
            <p>&copy; 2025 - Aix-Marseille Université</p>
        </footer>
        </body>
        </html>
        <?php
    }
}
