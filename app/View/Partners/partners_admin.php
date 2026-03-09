<?php
/**
 * Partners Admin - Contenu uniquement
 *
 * @var string $lang
 * @var string $titre
 * @var Closure(array<string, string>): string $t
 * @var bool $success
 * @var string|null $errorMessage
 */

ob_start();
?>

    <h1><?= htmlspecialchars($titre) ?></h1>

<?php if ($success) : ?>
    <p id="success-message" class="success-message">
        <?= $t(['fr' => 'Partenaire ajouté avec succès.', 'en' => 'Partner successfully added.']) ?>
    </p>
<?php elseif (!empty($errorMessage)) : ?>
    <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
<?php endif; ?>

    <div class="partners-actions">
        <button class="btn-add-partner">
            <span class="btn-plus">+</span>
            <?= $t(['fr' => 'Ajouter', 'en' => 'Add']) ?>
        </button>
        <div id="partner-form-container" class="partner-form hidden">
            <form method="post" action="">
                <div class="form-group">
                    <label for="name"><?= $t(['fr' => 'Continent', 'en' => 'Continent']) ?></label>
                    <input type="text" id="name" name="name" required placeholder="<?= $t(['fr' => 'Ex: Europe', 'en' => 'Ex: Europe']) ?>">
                </div>

                <div class="form-group">
                    <label for="country"><?= $t(['fr' => 'Pays', 'en' => 'Country']) ?></label>
                    <input type="text" id="country" name="country" required placeholder="<?= $t(['fr' => 'Ex: France', 'en' => 'Ex: France']) ?>">
                </div>

                <div class="form-group">
                    <label for="city"><?= $t(['fr' => 'Ville', 'en' => 'City']) ?></label>
                    <input type="text" id="city" name="city" required placeholder="<?= $t(['fr' => 'Ex: Marseille', 'en' => 'Ex: Marseille']) ?>">
                </div>

                <div class="form-group">
                    <label for="institution"><?= $t(['fr' => 'Universités et institutions', 'en' => 'Universities and institutions']) ?></label>
                    <input type="text" id="institution" name="institution" required placeholder="<?= $t(['fr' => 'Ex: Aix-Marseille Université', 'en' => 'Ex: Aix-Marseille University']) ?>">
                </div>

                <div class="form-group">
                    <label for="type"><?= $t(['fr' => 'Type', 'en' => 'Type']) ?></label>
                    <select id="type" name="type" required>
                        <option value=""><?= $t(['fr' => '-- Choisir --', 'en' => '-- Select --']) ?></option>
                        <option value="amu">AMU</option>
                        <option value="iut">IUT</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save"><?= $t(['fr' => 'Enregistrer', 'en' => 'Save']) ?></button>
                    <button type="button" class="btn-cancel"><?= $t(['fr' => 'Annuler', 'en' => 'Cancel']) ?></button>
                </div>
            </form>
        </div>
    </div>

    <p><?= $t([
            'fr' => 'Veuillez trouver la liste des partenaires d\'AMU et IUT en cliquant sur ces liens :',
            'en' => 'Please find the list of AMU and IUT\'s partners by clicking on these links:'
        ]) ?></p>
    <p class="lien">
        <a href="https://www.univ-amu.fr/fr/public/universites-et-reseaux-partenaires" target="_blank">
            Universites-et-reseaux-partenaires
        </a>
        <br>
        <a href="https://iut.univ-amu.fr/fr/international/partir-etranger#tab-4499" target="_blank">
            Partir à l'étranger avec l'IUT
        </a>
    </p>

    <img id="Université_partenaires"
         src="img/<?= isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] ? 'University_green.png' : 'University.png' ?>"
         alt="Partner Universities">

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="admin"
         style="display:none;">
    </div>

<?php
$content = ob_get_clean();

$title = htmlspecialchars($titre);
$styles = ['styles/partners.css'];
$scripts = ['js/partner.js'];
$activeMenu = 'partners';
$userRole = 'admin';

include __DIR__ . '/../Layout/base.php';