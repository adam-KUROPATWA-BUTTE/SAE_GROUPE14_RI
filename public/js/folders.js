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

document.addEventListener('DOMContentLoaded', function() {
    // Uniquement sur la page de vue d'un dossier
    const btnEnregistrer = document.getElementById('btn-enregistrer');
    const btnModifier = document.getElementById('btn-modifier');

    if (!btnEnregistrer || !btnModifier) {
        return; // Pas sur la bonne page
    }

    const modal = document.getElementById('modal-validation');
    const btnCancel = document.getElementById('btn-modal-cancel');
    const formValidation = document.getElementById('form-validation');
    const formPrincipal = document.querySelector('.creation-form');

    // Récupérer la langue
    const appConfig = document.getElementById('app-config');
    const lang = appConfig ? appConfig.dataset.lang : 'fr';

    // Traductions
    const translations = {
        photo: lang === 'fr' ? 'Photo d\'identité' : 'ID Photo',
        cv: lang === 'fr' ? 'CV' : 'Resume',
        convention: lang === 'fr' ? 'Convention de stage' : 'Internship Agreement',
        lettre_motivation: lang === 'fr' ? 'Lettre de motivation' : 'Motivation Letter',
        conforme: lang === 'fr' ? 'Conforme' : 'Compliant',
        non_conforme: lang === 'fr' ? 'Non conforme' : 'Non-compliant',
        manquant: lang === 'fr' ? 'Manquant' : 'Missing',
        present: lang === 'fr' ? 'Déposé' : 'Uploaded',
        aucun_manquant: lang === 'fr' ? '✅ Aucun document manquant' : '✅ No missing documents'
    };

    // Récupérer les données d'analyse depuis PHP (injectées dans la page)
    const analyseDocuments = window.analyseDocumentsData || {
        manquants: [],
        presents: [],
        statuts: {}
    };

    // Stocker les valeurs originales lors du clic sur "Modifier"
    if (btnModifier) {
        btnModifier.addEventListener('click', function() {
            setTimeout(() => {
                const inputs = formPrincipal.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select');
                inputs.forEach(input => {
                    if (!input.disabled && input.name !== 'numetu') {
                        input.setAttribute('data-original-value', input.value);
                    }
                });
            }, 100);
        });
    }

    // Ouvrir la modale au clic sur "Enregistrer"
    btnEnregistrer.addEventListener('click', function(e) {
        e.preventDefault();

        // Détecter les modifications
        const modifications = detecterModifications();
        afficherModifications(modifications);

        // Afficher les documents
        afficherDocuments();

        // Ouvrir la modale
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    // Fermer la modale
    btnCancel.addEventListener('click', function() {
        fermerModale();
    });

    // Fermer en cliquant sur l'overlay
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            fermerModale();
        }
    });

    function fermerModale() {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    /**
     * Détecte les champs modifiés du formulaire
     */
    function detecterModifications() {
        const modifications = [];
        const inputs = formPrincipal.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select');

        inputs.forEach(input => {
            if (input.disabled) return;
            if (!input.name || input.name === 'numetu' || input.name === 'mobilite_type') return;

            const valeurActuelle = input.value;
            const valeurOriginale = input.getAttribute('data-original-value') ?? '';

            if (valeurActuelle !== valeurOriginale) {
                let label = input.name;
                if (input.id) {
                    const labelEl = formPrincipal.querySelector(`label[for="${input.id}"]`);
                    if (labelEl) label = labelEl.textContent.trim().replace('*', '').trim();
                }

                modifications.push({
                    champ: label,
                    ancienne: valeurOriginale || '(vide)',
                    nouvelle: valeurActuelle || '(vide)'
                });
            }
        });

        return modifications;
    }

    /**
     * Affiche les modifications dans la modale
     */
    function afficherModifications(modifications) {
        const section = document.getElementById('section-modifications');
        const liste = document.getElementById('liste-modifications');

        if (modifications.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';
        liste.innerHTML = modifications.map(m =>
            `<li><strong>${m.champ}</strong> : 
             <span style="color: #dc3545; text-decoration: line-through;">${m.ancienne}</span> 
             → <span style="color: #28a745; font-weight: 600;">${m.nouvelle}</span>
            </li>`
        ).join('');
    }

    /**
     * Affiche les documents manquants et présents
     */
    function afficherDocuments() {
        const manquants = analyseDocuments.manquants || [];
        const presents = analyseDocuments.presents || [];
        const statuts = analyseDocuments.statuts || {};

        // Documents manquants
        const listeManquants = document.getElementById('liste-manquants');
        if (manquants.length === 0) {
            listeManquants.innerHTML = `<p style="color: #28a745; font-weight: 600;">${translations.aucun_manquant}</p>`;
        } else {
            listeManquants.innerHTML = manquants.map(doc => `
                <div class="document-validation-item manquant">
                    <span class="document-name">${translations[doc] || doc}</span>
                    <span class="document-status-badge badge-manquant">${translations.manquant}</span>
                </div>
            `).join('');
        }

        // Documents présents à valider
        // NOUVEAU
        const listePresents = document.getElementById('liste-presents');

// Lire les statuts depuis les boutons actifs dans la vue principale
        const statutsVue = {};
        document.querySelectorAll('.statut-document-buttons').forEach(block => {
            const doc = block.dataset.doc;
            const actif = block.querySelector('.btn-status.active');
            if (actif) statutsVue[doc] = actif.dataset.statut;
        });

// Injecter les statuts comme inputs hidden dans le form modal
        presents.forEach(doc => {
            let input = document.getElementById('statut_modal_' + doc);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'statut_' + doc;
                input.id = 'statut_modal_' + doc;
                formValidation.appendChild(input);
            }
            input.value = statutsVue[doc] || statuts[doc] || '';
        });

        listePresents.innerHTML = presents.map(doc => {
            const s = statutsVue[doc] || statuts[doc] || '';
            const badge = s === 'conforme'
                ? `<span class="document-status-badge" style="background:#28a745;color:white;">✓ ${translations.conforme}</span>`
                : s === 'non_conforme'
                    ? `<span class="document-status-badge" style="background:#dc3545;color:white;">✗ ${translations.non_conforme}</span>`
                    : `<span class="document-status-badge badge-present">${translations.present}</span>`;
            return `
        <div class="document-validation-item present">
            <span class="document-name">${translations[doc] || doc}</span>
            ${badge}
        </div>
    `;
        }).join('');
    }

    // Boutons conforme/non-conforme dans la vue principale
    document.querySelectorAll('.statut-document-buttons .btn-status').forEach(btn => {
        btn.addEventListener('click', function() {
            const doc = this.dataset.doc;
            document.querySelectorAll(`.statut-document-buttons[data-doc="${doc}"] .btn-status`).forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
        });
    });


    // Toast de confirmation
    const msgDiv = document.querySelector('.message');
    if (msgDiv && msgDiv.textContent.trim() !== '') {
        msgDiv.classList.add('message-toast');
        msgDiv.style.display = 'block';
        setTimeout(() => msgDiv.remove(), 3500);
    }

    // Bannière date limite — toggle formulaire
    const btnEditDate = document.getElementById('btn-edit-date-limite');
    const formDateLimite = document.getElementById('form-date-limite');
    const btnCancelDate = document.getElementById('btn-cancel-date');

    if (btnEditDate && formDateLimite) {
        btnEditDate.addEventListener('click', function() {
            formDateLimite.style.display = formDateLimite.style.display === 'none' ? 'block' : 'none';
        });
    }
    if (btnCancelDate) {
        btnCancelDate.addEventListener('click', function() {
            formDateLimite.style.display = 'none';
        });
    }

});