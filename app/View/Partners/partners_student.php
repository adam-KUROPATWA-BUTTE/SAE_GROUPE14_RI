<?php
/**
 * Partners Student - Contenu uniquement
 *
 * @var string $lang
 * @var string $titre
 * @var Closure(array<string, string>): string $t
 */

ob_start();
?>

    <h1><?= htmlspecialchars($titre) ?></h1>

    <p>
        <?= $t([
            'fr' => 'Veuillez trouver la liste des partenaires d\'AMU en cliquant sur ce lien :',
            'en' => 'Please find the list of AMU\'s partners by clicking on this link:'
        ]) ?>
    </p>

    <p class="lien">
        <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
            Universites-et-reseaux-partenaires
        </a>
    </p>

    <img id="Université_partenaires"
         src="img/<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'University_green.png' : 'University.png' ?>"
         alt="Partner Universities">

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="student"
         style="display:none;">
    </div>

<?php
$content = ob_get_clean();

$title = htmlspecialchars($titre);
$styles = ['styles/partners.css'];
$scripts = [];
$activeMenu = 'partners';
$userRole = 'student';

include __DIR__ . '/../Layout/base.php';