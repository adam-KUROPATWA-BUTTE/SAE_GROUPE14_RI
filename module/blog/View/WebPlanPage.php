<?php
namespace View;

class WebPlanPage
{
    private array $links;

    public function __construct(array $links = [])
    {
        $this->links = $links;
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="styles/web_plan.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
            <title>Plan du site</title>
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img src="img/logo.png" alt="Logo" style="height:100px;">
                <div class="right-buttons"></div>
            </div>
        </header>

        <main style="padding:2em;">
            <h1>Plan du site</h1>
            <ul>
                <?php foreach ($this->links as $link): ?>
                    <li><a href="<?= htmlspecialchars($link['url']) ?>"><?= htmlspecialchars($link['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </main>

        
        <!-- Bulle d'aide en bas à droite -->
        <div id="help-bubble" onclick="toggleHelpPopup()">❓</div>

        <!-- Contenu du popup d'aide -->
        <div id="help-popup">
            <div class="help-popup-header">
                <span>Aide</span>
                <button onclick="toggleHelpPopup()">✖</button>
            </div>
            <div class="help-popup-body">
                <p>Bienvenue ! Comment pouvons-nous vous aider ?</p>
                <ul>
                    <li><a href="index.php?page=help" target="_blank">Page d’aide complète</a></li>
                </ul>
            </div>
        </div>

        <script>
            function toggleHelpPopup() {
                const popup = document.getElementById('help-popup');
                popup.style.display = (popup.style.display === 'block') ? 'none' : 'block';
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
