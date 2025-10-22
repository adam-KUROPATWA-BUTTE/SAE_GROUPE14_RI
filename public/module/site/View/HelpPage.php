<?php
namespace View;

class HelpPage
{
    private array $faq;

    public function __construct(array $faq = [])
    {
        $this->faq = $faq;
    }

    public function render(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Aide - Service des Relations Internationales AMU</title>
            <link rel="stylesheet" href="styles/index.css">
            <link rel="stylesheet" href="styles/help.css">
            <link rel="icon" type="image/png" href="img/favicon.webp"/>
        </head>
        <body>
        <header>
            <div class="top-bar">
                <img id="logo_amu" src="img/logo.png" alt="Logo" style="height:100px;">
                <div class="right-buttons"></div>
            </div>
            <nav class="menu">
                <button onclick="window.location.href='/'">Accueil</button>
                <button onclick="window.location.href='/dashboard'">Tableau de bord</button>
                <button onclick="window.location.href='/partners'">Partenaires</button>
                <button onclick="window.location.href='/folders'">Dossiers</button>
                <button onclick="window.location.href='/web_plan'">Plan du site</button>
            </nav>
        </header>

        <main>
            <h1>Aide pour les administrateurs</h1>
            <p>Bienvenue dans l’espace d’aide destiné aux administrateurs du service des relations internationales d’AMU.</p>
            <h2>Fonctionnalités principales</h2>
            <ul>
                <li><strong>Gestion des utilisateurs :</strong> Ajouter, modifier ou supprimer des comptes utilisateurs.</li>
                <li><strong>Gestion des universités partenaires :</strong> Ajouter, modifier ou supprimer des universités dans la base.</li>
                <li><strong>Consultation des dossiers :</strong> Accéder à tous les dossiers étudiants et suivre leur avancement.</li>
                <li><strong>Paramétrage :</strong> Modifier les paramètres du service (contacts, préférences, etc.).</li>
                <li><strong>Réinitialisation de mot de passe :</strong> Aider les utilisateurs à réinitialiser leur mot de passe si besoin.</li>
            </ul>

            <h2>Questions fréquentes</h2>
            <ul>
                <?php foreach ($this->faq as $item): ?>
                    <li>
                        <strong><?= htmlspecialchars($item['question']) ?></strong><br>
                        <?= $item['answer'] ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h2>Contact</h2>
            <p>Pour toute question ou problème, contactez le responsable du service des relations internationales :<br>
                <strong>relations-internationales@amu.fr</strong></p>
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
            document.addEventL<footer>
            <p>&copy; <?= date('Y') ?> - Aix-Marseille Université.</p>
        </footer>istener("DOMContentLoaded", () => {
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