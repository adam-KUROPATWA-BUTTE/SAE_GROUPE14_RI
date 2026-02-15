<?php
/**
 * Footer commun
 *
 * @var string $userRole - 'admin' ou 'student'
 */
?>
<footer>
    <p>&copy; 2026 - Aix-Marseille Université.</p>

    <?php if ($userRole === 'student'): ?>
        <a href="https://www.instagram.com/relationsinternationales_amu/" target="_blank">
            <img class="insta" src="img/instagram.png" alt="Instagram">
        </a>
    <?php endif; ?>
</footer>