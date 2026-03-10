<?php
/**
 * Footer - Pied de page
 */

// Déterminer le userRole depuis la session si non défini
if (!isset($userRole)) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $userRole = 'admin';
    } elseif (isset($_SESSION['numetu'])) {
        $userRole = 'student';
    } else {
        $userRole = null;
    }
}

// Créer la fonction de traduction si elle n'existe pas
if (!isset($t)) {
    $lang = $_SESSION['lang'] ?? 'fr';
    $t = function(array $translations) use ($lang) {
        return $translations[$lang] ?? $translations['fr'] ?? '';
    };
}
?>
<footer>
    <p>&copy; 2026 - Aix-Marseille Université</p>

    <div class="footer-links">
        <?php if ($userRole === 'admin' || $userRole === 'student'): ?>
            <a href="index.php?page=web_plan&lang=<?= htmlspecialchars($lang ?? 'fr') ?>"
               class="footer-sitemap-btn">
                <?= htmlspecialchars($t(['fr' => 'Plan du site', 'en' => 'Site Map'])) ?>
            </a>
            <span class="footer-separator">|</span>
        <?php endif; ?>

    </div>
</footer>