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

<?php if ($partner === 'amu'): ?>

    <p>
        <?= $t([
                'fr' => 'Veuillez trouver la liste des destinations d\'AMU en cliquant sur ce lien :',
                'en' => 'Please find the list of AMU\'s destinations by clicking on this link:'
        ]) ?>
    </p>
    <p class="lien">
        <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
            Universites-et-reseaux-partenaires
        </a>
    </p>

<?php else: ?>

    <p>
        <?= $t([
                'fr' => 'Veuillez trouver la liste des destinations de l\'IUT en cliquant sur ce lien :',
                'en' => 'Please find the list of IUT\'s destinations by clicking on this link:'
        ]) ?>
    </p>
    <p class="lien">
        <a href="https://iut.univ-amu.fr/fr/international/partir-etranger#tab-4499" target="_blank">
            Partir à l'étranger avec l'IUT
        </a>
    </p>

<?php endif; ?>
    <img id="Université_partenaires"
         src="img/<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'University_green.png' : 'University.png' ?>"
         alt="Partner Universities">

<?php
$content = ob_get_clean();

$title = htmlspecialchars($titre);
$styles = ['styles/partners.css'];
$scripts = [];
$activeMenu = 'partners';
$userRole = 'student';

include __DIR__ . '/../Layout/base.php';