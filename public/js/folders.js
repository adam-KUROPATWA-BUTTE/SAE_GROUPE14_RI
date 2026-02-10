document.addEventListener("DOMContentLoaded", () => {
    
    /* --- 1. Gestion des formulaires (Admin & Student) --- */
    
    // Changement dynamique (Stage vs Études)
    const mobiliteSelect = document.getElementById('mobilite_type');
    if (mobiliteSelect) {
        // Initial check
        changerTypeMobilite(mobiliteSelect.value);
        // Event listener
        mobiliteSelect.addEventListener('change', function() {
            changerTypeMobilite(this.value);
        });
    }

    // Bouton Modifier (Admin)
    const btnModifier = document.getElementById('btn-modifier');
    if (btnModifier) {
        btnModifier.addEventListener('click', activerModification);
    }


    /* --- 2. Gestion des Filtres et Recherche (Admin Liste) --- */

    // Recherche avec touche Entrée
    const searchInput = document.getElementById('search');
    const searchBtn = document.querySelector('.btn-search');
    
    if (searchInput) {
        // Debounce simple
        let timeout = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                 // Optionnel : recherche automatique au bout de 3s
                 appliquerFiltres(true); 
            }, 3000);
        });

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') appliquerFiltres(true);
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', () => appliquerFiltres(true));
    }

    // Checkboxes filtres (Entrant/Sortant, Zones...)
    const checkboxes = document.querySelectorAll('input[name="entrant_sortant"], input[name="zone"]');
    checkboxes.forEach(cb => {
        cb.addEventListener('click', function() {
            // Comportement "Radio" pour les checkboxes du même groupe
            const groupName = this.name;
            document.querySelectorAll(`input[name="${groupName}"]`).forEach(other => {
                if (other !== this) other.checked = false;
            });
            appliquerFiltres(true);
        });
    });

    // Selects filtres
    const selectFilters = document.querySelectorAll('#filter-complet, #date-debut, #date-fin');
    selectFilters.forEach(sel => {
        sel.addEventListener('change', () => appliquerFiltres(true));
    });

    /* --- 3. Click sur ligne tableau --- */
    const rows = document.querySelectorAll('#table-etudiants tbody tr');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            // On cherche l'ID dans un attribut data ou on le déduit (ici méthode simplifiée via attribut onclick simulé)
            // L'idéal serait <tr data-numetu="xxx">
            // Si vous gardez le onclick dans le HTML pour l'instant, ce bloc n'est pas nécessaire.
            // Mais pour nettoyer complètement :
            const numetu = this.dataset.numetu;
            if (numetu) ouvrirFicheEtudiant(numetu);
        });
    });
});

/* --- Fonctions Logiques --- */

function changerTypeMobilite(type) {
    const conventionBlock = document.getElementById('justificatif_convention');
    const lettreBlock = document.getElementById('lettre_motivation');

    if (conventionBlock) conventionBlock.style.display = 'none';
    if (lettreBlock) lettreBlock.style.display = 'none';

    if (type === 'stage') {
        if (conventionBlock) conventionBlock.style.display = 'block';
    } else if (type === 'etudes') {
        if (lettreBlock) lettreBlock.style.display = 'block';
    }
}

function activerModification() {
    document.querySelectorAll('.creation-form input, .creation-form select').forEach(field => {
        // On ne réactive pas l'ID étudiant
        if (field.id !== 'numetu' && field.id !== 'numetu_display') {
            field.disabled = false;
            field.style.backgroundColor = 'white';
            field.style.color = 'black';
            field.classList.remove('input-disabled');
        }
    });
    
    const btnMod = document.getElementById('btn-modifier');
    const btnSave = document.getElementById('btn-enregistrer');
    const btnCancel = document.getElementById('btn-annuler');
    const fileInputs = document.querySelectorAll('input[type="file"]');

    if (btnMod) btnMod.style.display = 'none';
    if (btnSave) btnSave.style.display = 'inline-block';
    if (btnSave) btnSave.classList.remove('btn-hidden');
    if (btnCancel) btnCancel.style.display = 'inline-block';
    if (btnCancel) btnCancel.classList.remove('btn-hidden');

    fileInputs.forEach(input => {
        input.disabled = false;
        input.classList.remove('input-disabled');
    });
}

function ouvrirFicheEtudiant(numetu) {
    const url = new URL(window.location.href);
    url.searchParams.set('action', 'view');
    url.searchParams.set('numetu', numetu);
    window.location.href = url.toString();
}

function appliquerFiltres(resetPage = false) {
    const url = new URL(window.location.href);
    
    // Recherche textuelle
    const searchInput = document.getElementById('search');
    if (searchInput && searchInput.value.trim() !== '') {
        url.searchParams.set('search', searchInput.value.trim());
    } else {
        url.searchParams.delete('search');
    }

    // Checkboxes Type
    const typeChecked = document.querySelector('input[name="entrant_sortant"]:checked');
    if (typeChecked) url.searchParams.set('type', typeChecked.value);
    else url.searchParams.delete('type');

    // Checkboxes Zone
    const zoneChecked = document.querySelector('input[name="zone"]:checked');
    if (zoneChecked) url.searchParams.set('zone', zoneChecked.value);
    else url.searchParams.delete('zone');

    // Select Statut
    const completVal = document.getElementById('filter-complet');
    if (completVal && completVal.value !== 'all') url.searchParams.set('complet', completVal.value);
    else url.searchParams.delete('complet');

    // Reset pagination
    if (resetPage) url.searchParams.set('p', 1);

    window.location.href = url.toString();
}