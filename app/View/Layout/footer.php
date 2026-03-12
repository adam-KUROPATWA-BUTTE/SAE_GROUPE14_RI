<?php
/**
 * Footer - Pied de page
 *
 * @var string|null $userRole
 * @var string $lang
 * @var callable $t
 */

$userRole ??= (
isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'
    ? 'admin'
    : (isset($_SESSION['numetu']) ? 'student' : null)
);
?>
<footer>
    <p>&copy; 2026 - Aix-Marseille Université</p>

    <div class="footer-links">
        <a href="index.php?page=mentions-legales&lang=<?= htmlspecialchars($lang) ?>" class="footer-sitemap-btn no-icon">
            <?= $t(['fr' => 'Mentions Légales & RGPD', 'en' => 'Legal Notice & GDPR']) ?>
        </a>

        <?php if ($userRole === 'admin' || $userRole === 'student'): ?>
            <a href="index.php?page=web_plan&lang=<?= htmlspecialchars($lang) ?>" class="footer-sitemap-btn">
                <?= htmlspecialchars($t(['fr' => 'Plan du site 🗺️', 'en' => 'Site Map 🗺️'])) ?>
            </a>
        <?php endif; ?>
    </div>
</footer>