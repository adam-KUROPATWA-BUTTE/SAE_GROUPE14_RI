/**
 * Class representing the management of student folders.
 * Handles form editing, dynamic fields, filtering, and table interactions.
 */
class FolderManager {
    constructor() {
        this.initFormEvents();
        this.initFilterEvents();
        this.initTableEvents();
        this.initAccordionEvents();
        this.initAccordionPagination(); // Initialisation de la pagination par composante
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
        const selectFilters = document.querySelectorAll('#filter-complet, #date-debut, #date-fin, #filter-composante, #filter-accord');
        selectFilters.forEach(sel => {
            if (sel) {
                sel.addEventListener('change', () => this.appliquerFiltres(true));
            }
        });
    }

    /**
     * Initializes table row clicks to view a student's profile.
     */
    initTableEvents() {
        const rows = document.querySelectorAll('.table-etudiants tbody tr');
        rows.forEach(row => {
            row.addEventListener('click', (e) => {
                const numetu = e.currentTarget.dataset.numetu;
                if (numetu) this.ouvrirFicheEtudiant(numetu);
            });
        });
    }

    /**
     * Gère l'ouverture et la fermeture des barres de composantes
     */
    initAccordionEvents() {
        const barres = document.querySelectorAll('.barre-titre');
        barres.forEach(barre => {
            barre.addEventListener('click', () => {
                const targetId = barre.getAttribute('data-target');
                const contenu = document.getElementById(targetId);
                const fleche = barre.querySelector('.fleche');
                
                if (contenu) contenu.classList.toggle('afficher');
                if (fleche) fleche.classList.toggle('ouverte');
            });
        });
    }

    /**
     * Pagination indépendante pour chaque composante (Client-side)
     */
    initAccordionPagination() {
        const itemsPerPage = 10; // Change ce chiffre si tu veux plus/moins d'étudiants par page
        const sections = document.querySelectorAll('.section-composante');

        sections.forEach((section) => {
            const tbody = section.querySelector('.table-etudiants tbody');
            const paginationContainer = section.querySelector('.accordion-pagination');
            
            if (!tbody || !paginationContainer) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Si pas assez de lignes, on cache la pagination
            if (rows.length <= itemsPerPage) {
                paginationContainer.style.display = 'none';
                return;
            }

            const totalPages = Math.ceil(rows.length / itemsPerPage);

            const showPage = (page) => {
                // Afficher/Masquer les lignes
                rows.forEach((row, index) => {
                    row.style.display = (index >= (page - 1) * itemsPerPage && index < page * itemsPerPage) ? '' : 'none';
                });

                // Reconstruire les boutons
                renderButtons(page);
            };

            const renderButtons = (currentPage) => {
                paginationContainer.innerHTML = '';
                
                // Bouton Précédent
                const btnPrev = document.createElement('button');
                btnPrev.textContent = '‹';
                btnPrev.disabled = currentPage === 1;
                btnPrev.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); showPage(currentPage - 1); });
                paginationContainer.appendChild(btnPrev);

                // Boutons Numéros de page
                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    if (i === currentPage) btn.classList.add('active');
                    btn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); showPage(i); });
                    paginationContainer.appendChild(btn);
                }

                // Bouton Suivant
                const btnNext = document.createElement('button');
                btnNext.textContent = '›';
                btnNext.disabled = currentPage === totalPages;
                btnNext.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); showPage(currentPage + 1); });
                paginationContainer.appendChild(btnNext);
            };

            // Initialiser la première page
            showPage(1);
        });
    }

    /**
     * Toggles the display of document upload fields based on mobility type.
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
        if (btnSave) { btnSave.style.display = 'inline-block'; btnSave.classList.remove('btn-hidden'); }
        if (btnCancel) { btnCancel.style.display = 'inline-block'; btnCancel.classList.remove('btn-hidden'); }

        fileInputs.forEach(input => {
            input.disabled = false;
            input.classList.remove('input-disabled');
        });
    }

    /**
     * Redirects to a student's profile view.
     */
    ouvrirFicheEtudiant(numetu) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'view');
        url.searchParams.set('numetu', numetu);
        window.location.href = url.toString();
    }

    /**
     * Collects all filter values and updates the URL to reload the filtered list.
     */
    appliquerFiltres(resetPage = false) {
        const url = new URL(window.location.href);
        
        const searchInput = document.getElementById('search');
        if (searchInput && searchInput.value.trim() !== '') url.searchParams.set('search', searchInput.value.trim());
        else url.searchParams.delete('search');

        const typeChecked = document.querySelector('input[name="entrant_sortant"]:checked');
        if (typeChecked) url.searchParams.set('type', typeChecked.value);
        else url.searchParams.delete('type');

        const zoneChecked = document.querySelector('input[name="zone"]:checked');
        if (zoneChecked) url.searchParams.set('zone', zoneChecked.value);
        else url.searchParams.delete('zone');

        const completVal = document.getElementById('filter-complet');
        if (completVal && completVal.value !== 'all') url.searchParams.set('complet', completVal.value);
        else url.searchParams.delete('complet');

        const composanteVal = document.getElementById('filter-composante');
        if (composanteVal && composanteVal.value !== 'all') url.searchParams.set('composante', composanteVal.value);
        else url.searchParams.delete('composante');

        const accordVal = document.getElementById('filter-accord');
        if (accordVal && accordVal.value !== 'all') url.searchParams.set('accord', accordVal.value);
        else url.searchParams.delete('accord');

        // Note: On supprime 'p' car la pagination globale n'est plus utilisée avec ce système
        url.searchParams.delete('p');

        window.location.href = url.toString();
    }

    /**
     * Envoie la validation d'une pièce précise au serveur sans recharger la page
     */
    async confirmDocument(numEtu, docType) {
        const container = document.querySelector(`.doc-review-item[data-doctype="${docType}"]`);
        if (!container) return;

        const status = container.querySelector(`input[name="status_${docType}"]:checked`)?.value || 'accepted';
        const comment = container.querySelector(`textarea[name="comment_${docType}"]`).value;
        const indicator = container.querySelector(`#indicator_${docType}`);
        const btn = container.querySelector('.btn-confirm-doc');

        btn.disabled = true;
        indicator.textContent = "Sauvegarde en cours...";
        indicator.style.color = "orange";

        const formData = new FormData();
        formData.append('numetu', numEtu);
        formData.append('doc_type', docType);
        formData.append('status', status);
        formData.append('comment', comment);

        try {
            const response = await fetch('index.php?page=update_document_status', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                indicator.textContent = "Enregistré";
                indicator.style.color = "green";
            } else {
                indicator.textContent = "Erreur serveur";
                indicator.style.color = "red";
            }
        } catch (error) {
            indicator.textContent = "Erreur de réseau";
            indicator.style.color = "red";
        }

        setTimeout(() => { indicator.textContent = ""; btn.disabled = false; }, 3000);
    }

    /**
     * NOUVEAU : Met à jour le statut global du dossier via AJAX
     */
    async updateGlobalStatus(numEtu) {
        const statusSelect = document.getElementById('global_status_select');
        const indicator = document.getElementById('global_status_indicator');
        if (!statusSelect) return;

        const newStatus = statusSelect.value;
        
        indicator.textContent = "Sauvegarde en cours...";
        indicator.style.color = "orange";

        const formData = new FormData();
        formData.append('numetu', numEtu);
        formData.append('status', newStatus);

        try {
            const response = await fetch('index.php?page=update_global_status', { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                indicator.textContent = "Statut mis à jour";
                indicator.style.color = "green";
            } else {
                indicator.textContent = "Erreur";
                indicator.style.color = "red";
            }
        } catch (error) {
            indicator.textContent = "Erreur réseau";
            indicator.style.color = "red";
        }

        setTimeout(() => { indicator.textContent = ""; }, 3000);
    }
}

// Instantiate globally
document.addEventListener('DOMContentLoaded', () => {
    window.folderManager = new FolderManager();
});