/**
 * Class representing the management of student folders.
 * Handles form editing, dynamic fields, filtering, and table interactions.
 */
class FolderManager {
    constructor() {
        this.initFormEvents();
        this.initFilterEvents();
        this.initTableEvents();
    }

    /**
     * Initializes events related to the creation/edition forms.
     */
    initFormEvents() {
        // Dynamic change (Internship vs Studies)
        const mobiliteSelect = document.getElementById('mobilite_type');
        if (mobiliteSelect) {
            this.changerTypeMobilite(mobiliteSelect.value);
            mobiliteSelect.addEventListener('change', (e) => {
                this.changerTypeMobilite(e.target.value);
            });
        }

        // Edit button for admins
        const btnModifier = document.getElementById('btn-modifier');
        if (btnModifier) {
            btnModifier.addEventListener('click', () => this.activerModification());
        }
    }

    /**
     * Initializes events related to the search and filtering system.
     */
    initFilterEvents() {
        const searchInput = document.getElementById('search');
        const searchBtn = document.querySelector('.btn-search');
        
        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', () => {
                clearTimeout(timeout);
                // Auto-search after 3 seconds of inactivity
                timeout = setTimeout(() => {
                     this.appliquerFiltres(true); 
                }, 3000);
            });

            // Search on Enter key
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.appliquerFiltres(true);
            });
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', () => this.appliquerFiltres(true));
        }

        // Checkboxes filters (Incoming/Outgoing, Zones)
        const checkboxes = document.querySelectorAll('input[name="entrant_sortant"], input[name="zone"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('click', (e) => {
                // Radio-like behavior for checkboxes of the same group
                const groupName = e.target.name;
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(other => {
                    if (other !== e.target) other.checked = false;
                });
                this.appliquerFiltres(true);
            });
        });

        // Dropdown filters
        const selectFilters = document.querySelectorAll('#filter-complet, #date-debut, #date-fin');
        selectFilters.forEach(sel => {
            sel.addEventListener('change', () => this.appliquerFiltres(true));
        });
    }

    /**
     * Initializes table row clicks to view a student's profile.
     */
    initTableEvents() {
        const rows = document.querySelectorAll('#table-etudiants tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', (e) => {
                const numetu = e.currentTarget.dataset.numetu;
                if (numetu) this.ouvrirFicheEtudiant(numetu);
            });
        });
    }

    /**
     * Toggles the display of document upload fields based on mobility type.
     * @param {string} type - 'stage' (internship) or 'etudes' (studies).
     */
    changerTypeMobilite(type) {
        const conventionBlock = document.getElementById('justificatif_convention');
        const lettreBlock = document.getElementById('lettre_motivation');

        if (conventionBlock) conventionBlock.style.display = 'none';
        if (lettreBlock) lettreBlock.style.display = 'none';

        if (type === 'stage' && conventionBlock) {
            conventionBlock.style.display = 'block';
        } else if (type === 'etudes' && lettreBlock) {
            lettreBlock.style.display = 'block';
        }
    }

    /**
     * Activates the form fields for editing (Admin side).
     */
    activerModification() {
        document.querySelectorAll('.creation-form input, .creation-form select').forEach(field => {
            // Do not reactivate the student ID field
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
        
        if (btnSave) {
            btnSave.style.display = 'inline-block';
            btnSave.classList.remove('btn-hidden');
        }
        
        if (btnCancel) {
            btnCancel.style.display = 'inline-block';
            btnCancel.classList.remove('btn-hidden');
        }

        fileInputs.forEach(input => {
            input.disabled = false;
            input.classList.remove('input-disabled');
        });
    }

    /**
     * Redirects to a student's profile view.
     * @param {string} numetu - The student's ID number.
     */
    ouvrirFicheEtudiant(numetu) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'view');
        url.searchParams.set('numetu', numetu);
        window.location.href = url.toString();
    }

    /**
     * Collects all filter values and updates the URL to reload the filtered list.
     * @param {boolean} resetPage - Whether to reset pagination to page 1.
     */
    appliquerFiltres(resetPage = false) {
        const url = new URL(window.location.href);
        
        // Text search
        const searchInput = document.getElementById('search');
        if (searchInput && searchInput.value.trim() !== '') {
            url.searchParams.set('search', searchInput.value.trim());
        } else {
            url.searchParams.delete('search');
        }

        // Type checkboxes
        const typeChecked = document.querySelector('input[name="entrant_sortant"]:checked');
        if (typeChecked) url.searchParams.set('type', typeChecked.value);
        else url.searchParams.delete('type');

        // Zone checkboxes
        const zoneChecked = document.querySelector('input[name="zone"]:checked');
        if (zoneChecked) url.searchParams.set('zone', zoneChecked.value);
        else url.searchParams.delete('zone');

        // Status dropdown
        const completVal = document.getElementById('filter-complet');
        if (completVal && completVal.value !== 'all') url.searchParams.set('complet', completVal.value);
        else url.searchParams.delete('complet');

        // Reset pagination
        if (resetPage) url.searchParams.set('p', 1);

        window.location.href = url.toString();
    }
}

// Instantiate globally
document.addEventListener('DOMContentLoaded', () => {
    window.folderManager = new FolderManager();
});