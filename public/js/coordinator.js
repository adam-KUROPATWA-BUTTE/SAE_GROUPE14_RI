/**
 * JS pour la page coordinateur d'étude
 * Gère les filtres dynamiques et la recherche
 */
document.addEventListener('DOMContentLoaded', function () {

    const BASE_URL = 'index.php?page=coordinateur-etude';
    const lang     = document.getElementById('app-config')?.dataset?.lang || 'fr';

    // ── Filtre zone conditionnel (visible seulement si Sortant coché) ──
    const sortantCb = document.querySelector('input[name="entrant_sortant"][value="sortant"]');
    const zoneGroup = document.getElementById('zone-filter-group');

    if (sortantCb && zoneGroup) {
        sortantCb.addEventListener('change', function () {
            zoneGroup.style.display = this.checked ? '' : 'none';
            if (!this.checked) {
                zoneGroup.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
            }
            applyFilters();
        });
    }

    // ── Application des filtres → redirect URL ──
    function applyFilters() {
        const params = new URLSearchParams({ page: 'coordinateur-etude', lang });

        // Type (entrant/sortant)
        const typeCb = document.querySelector('input[name="entrant_sortant"]:checked');
        if (typeCb) params.set('type', typeCb.value);

        // Zone (seulement si sortant)
        if (sortantCb?.checked) {
            const zoneCb = document.querySelector('input[name="zone"]:checked');
            if (zoneCb) params.set('zone', zoneCb.value);
        }

        // Statut
        const complet = document.getElementById('filter-complet');
        if (complet && complet.value !== 'all') params.set('complet', complet.value);

        // Recherche
        const search = document.getElementById('search');
        if (search?.value.trim()) params.set('search', search.value.trim());

        window.location.href = 'index.php?' + params.toString();
    }

    // Écoute tous les checkboxes de filtre
    document.querySelectorAll('.filters input[type=checkbox]').forEach(cb => {
        cb.addEventListener('change', applyFilters);
    });

    // Écoute le select statut
    const complet = document.getElementById('filter-complet');
    if (complet) complet.addEventListener('change', applyFilters);

    // Bouton loupe
    const btnSearch = document.getElementById('btn-search-loupe');
    if (btnSearch) btnSearch.addEventListener('click', applyFilters);

    // Touche Entrée dans le champ recherche
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') applyFilters();
        });
    }
});