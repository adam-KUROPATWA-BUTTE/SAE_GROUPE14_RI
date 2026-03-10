<?php
/**
 * Footer - Pied de page
 */

if (!isset($userRole)) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $userRole = 'admin';
    } elseif (isset($_SESSION['numetu'])) {
        $userRole = 'student';
    } else {
        $userRole = null;
    }
}


?>
<footer>
    <p>&copy; 2026 - Aix-Marseille Université</p>

    <div class="footer-links">
        <a href="index.php?page=mentions-legales&lang=<?= htmlspecialchars($lang ?? 'fr') ?>" class="footer-sitemap-btn no-icon">
            <?= $t(['fr' => 'Mentions Légales & RGPD', 'en' => 'Legal Notice & GDPR']) ?>
        </a>

        <?php if ($userRole === 'admin' || $userRole === 'student'): ?>
            <a href="index.php?page=web_plan&lang=<?= htmlspecialchars($lang ?? 'fr') ?>" class="footer-sitemap-btn">
                <?= htmlspecialchars($t(['fr' => 'Plan du site 🗺️', 'en' => 'Site Map 🗺️'])) ?>
            </a>
        <?php endif; ?>

    </div>
</footer>