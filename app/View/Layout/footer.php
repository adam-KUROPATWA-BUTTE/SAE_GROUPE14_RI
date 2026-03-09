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
        <?php if ($userRole === 'admin' || $userRole === 'student'): ?>
            <a href="index.php?page=web_plan&lang=<?= htmlspecialchars($lang ?? 'fr') ?>"
               class="footer-sitemap-btn">
                <?= htmlspecialchars($t(['fr' => 'Plan du site', 'en' => 'Site Map'])) ?>
            </a>
            <span class="footer-separator">|</span>
        <?php endif; ?>

        <a href="https://www.instagram.com/relationsinternationales_amu/"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Instagram">
            <img class="insta" src="img/instagram.png" alt="Instagram">
        </a>
    </div>
</footer>