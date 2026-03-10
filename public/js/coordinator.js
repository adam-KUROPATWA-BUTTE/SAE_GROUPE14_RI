class CoordinatorManager {
    constructor() {
        this.lang = document.getElementById('app-config')?.dataset?.lang || 'fr';
        this.init();
    }

    init() {
        this.sortantCb = document.querySelector('input[name="entrant_sortant"][value="sortant"]');
        this.zoneGroup = document.getElementById('zone-filter-group');
        this.bindEvents();
    }

    bindEvents() {
        // ── Filtre zone conditionnel ──
        if (this.sortantCb && this.zoneGroup) {
            this.sortantCb.addEventListener('change', () => {
                this.zoneGroup.style.display = this.sortantCb.checked ? '' : 'none';
                if (!this.sortantCb.checked) {
                    this.zoneGroup.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
                }
                this.applyFilters();
            });
        }

        // ── Checkboxes et Selects ──
        document.querySelectorAll('.filters input[type=checkbox]').forEach(cb => {
            cb.addEventListener('change', () => this.applyFilters());
        });

        const complet = document.getElementById('filter-complet');
        if (complet) complet.addEventListener('change', () => this.applyFilters());

        // ── Recherche ──
        const btnSearch = document.getElementById('btn-search-loupe');
        if (btnSearch) btnSearch.addEventListener('click', () => this.applyFilters());

        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') this.applyFilters();
            });
        }
    }

    applyFilters() {
        const params = new URLSearchParams({ page: 'coordinateur-etude', lang: this.lang });

        // Type (entrant/sortant)
        const typeCb = document.querySelector('input[name="entrant_sortant"]:checked');
        if (typeCb) params.set('type', typeCb.value);

        // Zone (seulement si sortant)
        if (this.sortantCb?.checked) {
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
}

// Déclenche l'initialisation quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => new CoordinatorManager());