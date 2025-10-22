<?php
namespace View;

class WebPlanPage
{
    private array $links;
    private string $lang;

    public function __construct(array $links = [], string $lang = 'fr')
    {
        $this->links = $links;
        $this->lang = $lang;
    }

    private function t(array $frEn): string
    {
        return $this->lang === 'en' ? $frEn['en'] : $frEn['fr'];
    }

    private function buildUrl(string $url): string
    {
        // On ajoute lang au paramètre d'URL existant
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $sep . 'lang=' . urlencode($this->lang);
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="<?= htmlspecialchars($this->lang) ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/web_plan.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
            <title><?= $this->t(['fr'=>'Plan du site', 'en'=>'Site Map']) ?></title>
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo">
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
        </header>

        <main style="padding:2em;">
            <h1><?= $this->t(['fr'=>'Plan du site', 'en'=>'Site Map']) ?></h1>
            <ul>
                <?php foreach ($this->links as $link): ?>
                    <li><a href="<?= htmlspecialchars($this->buildUrl($link['url'])) ?>"><?= 
                        htmlspecialchars($this->t([
                            'fr' => $link['label'], // labels en dur en fr dans Model, on peut compléter en dur ici si besoin
                            'en' => $this->translateLabel($link['label'])
                        ])) 
                    ?></a></li>
                <?php endforeach; ?>
            </ul>



        </main>

        <!-- Bulle d'aide en bas à droite -->
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>

        <!-- Contenu du popup d'aide -->
        <div id="help-popup">
            <div class="help-popup-header">
                <span><?= $this->t(['fr'=>'Aide', 'en'=>'Help']) ?></span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p><?= $this->t(['fr'=>'Bienvenue ! Comment pouvons-nous vous aider ?', 'en'=>'Welcome! How can we help you?']) ?></p>
                <ul>
                    <li><a href="index.php?page=help" target="_blank"><?= $this->t(['fr'=>'Page d’aide complète', 'en'=>'Full help page']) ?></a></li>
                </ul>
            </div>
        </div>

        <script>
            document.getElementById('current-lang').addEventListener('click', function(event) {
                event.stopPropagation(); // empêcher la propagation au document
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.toggle('show');
            });

            // Fermer le dropdown si clic ailleurs sur la page
            document.addEventListener('click', function() {
                const rightButtons = document.querySelector('.right-buttons');
                rightButtons.classList.remove('show');
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

    private function translateLabel(string $label): string
    {
        // Mappage simple FR -> EN
        $map = [
            'Accueil' => 'Home',
            'Tableau de bord' => 'Dashboard',
            'Partenaires' => 'Partners',
            'Dossiers' => 'Folders',
            'Plan du site' => 'Site Map',
            'Connexion / Inscription' => 'Login / Register',
        ];

        return $map[$label] ?? $label; // fallback si label inconnu
    }
}