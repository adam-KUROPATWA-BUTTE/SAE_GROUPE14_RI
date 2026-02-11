<?php
$isTritanopia = (isset($_SESSION['tritanopia']) && $_SESSION['tritanopia'] === true);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t(['fr' => 'Tableau de bord Admin', 'en' => 'Admin Dashboard']) ?></title>
    <link rel="stylesheet" href="styles/folders.css">
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/index.css">
    <link rel="stylesheet" href="styles/chatbot.css">
    <link rel="icon" type="image/png" href="img/favicon.webp"/>
</head>
<body class="<?= $isTritanopia ? 'tritanopie' : '' ?>">

<header>
    <div class="top-bar">
        <img class="logo_amu" src="img/logo.png" alt="Logo">
        <div class="right-buttons">
            <div class="lang-dropdown">
                <button class="dropbtn"><?= htmlspecialchars($lang) ?></button>
                <div class="dropdown-content">
                    <a href="#" onclick="changeLang('fr'); return false;">Français</a>
                    <a href="#" onclick="changeLang('en'); return false;">English</a>
                </div>
            </div>
        </div>
    </div>
    <nav class="menu">
        <button onclick="window.location.href='<?= $buildUrl('index.php?page=home-admin') ?>'"><?= $t(['fr' => 'Accueil','en' => 'Home']) ?></button>
        <button class="active" onclick="window.location.href='<?= $buildUrl('index.php?page=dashboard-admin') ?>'"><?= $t(['fr' => 'Tableau de bord','en' => 'Dashboard']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php?page=partners-admin') ?>'"><?= $t(['fr' => 'Partenaires','en' => 'Partners']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php?page=folders-admin') ?>'"><?= $t(['fr' => 'Dossiers','en' => 'Folders']) ?></button>
        <button onclick="window.location.href='<?= $buildUrl('index.php?page=web_plan-admin') ?>'"><?= $t(['fr' => 'Plan du site','en' => 'Sitemap']) ?></button>
    </nav>
</header>

<main>
    <h1 class="suivi-global"><?= $t(['fr' => 'Suivi Global des Mobilités', 'en' => 'Global Mobility Tracking']) ?></h1>

    <?php if (isset($_SESSION['message'])) : ?>
        <div class="message">
            <?= htmlspecialchars(strval($_SESSION['message'])); ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <form class="filters-container" method="GET" action="index.php">
        <input type="hidden" name="page" value="dashboard-admin">
        <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
        
        <input type="text" name="student" placeholder="<?= $t(['fr' => 'Rechercher...', 'en' => 'Search...']) ?>" value="<?= htmlspecialchars($filters['student']) ?>">
        
        <select name="dept">
            <option value=""><?= $t(['fr' => 'Départements', 'en' => 'Departments']) ?></option>
            <option value="Informatique" <?= $filters['dept'] === 'Informatique' ? 'selected' : '' ?>>Info</option>
            <option value="GEA" <?= $filters['dept'] === 'GEA' ? 'selected' : '' ?>>GEA</option>
            <option value="Biologie" <?= $filters['dept'] === 'Biologie' ? 'selected' : '' ?>>Bio</option>
        </select>
        
        <select name="year">
            <option value=""><?= $t(['fr' => 'Année', 'en' => 'Year']) ?></option>
            <option value="2024-2025" <?= $filters['year'] === '2024-2025' ? 'selected' : '' ?>>24-25</option>
        </select>
        
        <select name="type">
            <option value=""><?= $t(['fr' => 'Type', 'en' => 'Type']) ?></option>
            <option value="Erasmus" <?= $filters['type'] === 'Erasmus' ? 'selected' : '' ?>>Erasmus</option>
            <option value="Stage" <?= $filters['type'] === 'Stage' ? 'selected' : '' ?>>Stage</option>
        </select>
        
        <select name="camp">
            <option value=""><?= $t(['fr' => 'Campagne', 'en' => 'Campaign']) ?></option>
            <option value="Automne 2024" <?= $filters['camp'] === 'Automne 2024' ? 'selected' : '' ?>>Automne 24</option>
        </select>
        
        <input type="text" name="dest" placeholder="<?= $t(['fr' => 'Destination', 'en' => 'Destination']) ?>" value="<?= htmlspecialchars($filters['dest']) ?>">
        
        <button type="submit" class="btn-filter"><?= $t(['fr' => 'Filtrer', 'en' => 'Filter']) ?></button>
    </form>

    <h2><?= $t(['fr' => 'Sortants', 'en' => 'Outgoing']) ?></h2>
    <div class="table-responsive">
        <?php if (empty($outgoing)) : ?>
            <p class="no-files"><?= $t(['fr' => 'Aucun dossier.', 'en' => 'No files.']) ?></p>
        <?php else : ?>
            <table>
                <thead><tr><th>Étudiant</th><th>Dept</th><th>Dest</th><th>Campagne</th><th>Année</th><th>État</th></tr></thead>
                <tbody>
                <?php foreach ($outgoing as $d) :
                    $pct = intval($d['calc_percentage']);
                    $badgeClass = ($pct >= 100) ? 'bg-success' : (($pct > 50) ? 'bg-warning' : 'bg-danger');
                    $label = ($pct >= 100) ? 'Validé' : $pct . '%';
                    $numEtu = strval($d['NumEtu'] ?? '');
                    $detailUrl = "index.php?page=folders-admin&action=view&numetu=" . urlencode($numEtu) . "&lang=" . urlencode($lang);
                    ?>
                    <tr onclick="window.location.href='<?= $detailUrl ?>'" class="clickable-row">
                        <td><strong><?= htmlspecialchars(strval($d['Nom'] ?? '') . ' ' . strval($d['Prenom'] ?? '')) ?></strong><br><small><?= htmlspecialchars($numEtu) ?></small></td>
                        <td><?= htmlspecialchars(strval($d['CodeDepartement'] ?? '')) ?></td>
                        <td><?= htmlspecialchars(strval($d['Zone'] ?? '')) ?></td>
                        <td><?= htmlspecialchars(strval($d['calc_camp'])) ?></td>
                        <td><?= htmlspecialchars(strval($d['calc_annee'])) ?></td>
                        <td>
                            <span class="status-badge <?= $badgeClass ?>"><?= $label ?></span>
                            <?php if (intval($d['IsComplete'] ?? 0) === 0 && !empty($numEtu)) : ?>
                                <a href="index.php?page=send_reminder&numetu=<?= urlencode($numEtu) ?>&lang=<?= $lang ?>" 
                                   class="btn-relance" onclick="event.stopPropagation(); return confirm('Relancer ?')"></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <h2><?= $t(['fr' => 'Entrants', 'en' => 'Incoming']) ?></h2>
    <div class="table-responsive">
        <?php if (empty($incoming)) : ?>
            <p style="text-align:center; color:#666;"><?= $t(['fr' => 'Aucun dossier.', 'en' => 'No files.']) ?></p>
        <?php else : ?>
            <table>
                <thead><tr><th>Étudiant</th><th>Dept</th><th>Type</th><th>Année</th><th>État</th></tr></thead>
                <tbody>
                <?php foreach ($incoming as $d) :
                    $pct = intval($d['calc_percentage']);
                    $badgeClass = ($pct >= 100) ? 'bg-success' : (($pct > 50) ? 'bg-warning' : 'bg-danger');
                    $label = ($pct >= 100) ? 'Validé' : $pct . '%';
                    $numEtu = strval($d['NumEtu'] ?? '');
                    $detailUrl = "index.php?page=folders-admin&action=view&numetu=" . urlencode($numEtu) . "&lang=" . urlencode($lang);
                    ?>
                    <tr onclick="window.location.href='<?= $detailUrl ?>'" class="clickable-row">
                        <td><strong><?= htmlspecialchars(strval($d['Nom'] ?? '') . ' ' . strval($d['Prenom'] ?? '')) ?></strong><br><small><?= htmlspecialchars($numEtu) ?></small></td>
                        <td><?= htmlspecialchars(strval($d['CodeDepartement'] ?? '')) ?></td>
                        <td><?= htmlspecialchars(strval($d['Type'] ?? '')) ?></td>
                        <td><?= htmlspecialchars(strval($d['calc_annee'])) ?></td>
                        <td>
                            <span class="status-badge <?= $badgeClass ?>"><?= $label ?></span>
                            <?php if (intval($d['IsComplete'] ?? 0) === 0 && !empty($numEtu)) : ?>
                                <a href="index.php?page=send_reminder&numetu=<?= urlencode($numEtu) ?>&lang=<?= $lang ?>" 
                                   class="btn-relance" onclick="event.stopPropagation(); return confirm('Relancer ?')"></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<div id="help-bubble" onclick="toggleHelpPopup()">💬</div>
<div id="help-popup" class="chat-popup">
    <div class="help-popup-header">
        <span><?= $t(['fr' => 'Assistant', 'en' => 'Assistant']) ?></span>
        <button onclick="toggleHelpPopup()">✖</button>
    </div>
    <div id="chat-messages" class="chat-messages"></div>
    <div id="quick-actions" class="quick-actions"></div>
</div>

<script src="js/main.js"></script>
<script src="js/chatbot.js"></script>
<footer><p>&copy; 2026 - Aix-Marseille Université.</p></footer>
</body>
</html>