<?php
/**
 * WebPlan Student - Contenu uniquement
 *
 * @var string $lang
 * @var Closure(array<string, string>): string $t
 * @var Closure(string, array<string, mixed>=): string $buildUrl
 * @var array<int, array{url: string, label: string}> $links
 * @var Closure(string): string $translateLabel
 */

ob_start();
?>

    <h1><?= $t(['fr' => 'Plan du site', 'en' => 'Site Map']) ?></h1>

    <ul>
        <?php foreach ($links as $link) :
            $url = strval($link['url']);
            $label = strval($link['label']);
            ?>
            <li>
                <a href="<?= htmlspecialchars($buildUrl($url)) ?>">
                    <?= htmlspecialchars($t([
                        'fr' => $label,
                        'en' => $translateLabel($label)
                    ])) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div id="app-config"
         data-lang="<?= htmlspecialchars($lang) ?>"
         data-role="student"
         style="display:none;">
    </div>

<?php
$content = ob_get_clean();

$title = $t(['fr' => 'Plan du site', 'en' => 'Site Map']);
$styles = ['styles/web_plan.css'];
$scripts = [];
$activeMenu = 'web_plan';
$userRole = 'student';

include __DIR__ . '/../Layout/base_minimal.php';