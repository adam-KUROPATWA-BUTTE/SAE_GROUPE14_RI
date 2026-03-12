/* ==========================================================================
   FolderManager.js
========================================================================== */


/* --------------------------------------------------------------------------
   FormManager — main form & edit mode
   -------------------------------------------------------------------------- */

/**
 * Manages the main student form, including mobility-type field visibility
 * and the edit mode toggle (read-only → editable).
 */
class FormManager {

    /**
     * Initialises the mobility-type select listener and the "Edit" button.
     */
    constructor() {
        this._initMobiliteSelect();
        this._initBtnModifier();
    }

    /**
     * Binds the mobility-type select to `changerTypeMobilite` and keeps
     * the hidden mirror input in sync whenever the value changes.
     *
     * @private
     */
    _initMobiliteSelect() {
        const mobiliteSelect = document.getElementById('mobilite_type');
        if (!mobiliteSelect) return;
        this.changerTypeMobilite(mobiliteSelect.value);
        mobiliteSelect.addEventListener('change', (e) => {
            this.changerTypeMobilite(e.target.value);
            // Sync the hidden mirror input when the select changes
            const hidden = document.getElementById('hidden_mobilite_type');
            if (hidden) hidden.value = e.target.value;
        });
    }

    /**
     * Binds the "Edit" button to `activerModification`.
     *
     * @private
     */
    _initBtnModifier() {
        const btnModifier = document.getElementById('btn-modifier');
        if (btnModifier) btnModifier.addEventListener('click', () => this.activerModification());
    }

    /**
     * Shows or hides conditional document blocks based on the selected
     * mobility type.
     *
     * - 'stage'  → shows the internship agreement block.
     * - 'etudes' → shows the motivation letter block.
     * - anything else → hides both blocks.
     *
     * @param {string} type - The selected mobility type value.
     */
    changerTypeMobilite(type) {
        const conventionBlock = document.getElementById('justificatif_convention');
        const lettreBlock     = document.getElementById('lettre_motivation');

        if (conventionBlock) conventionBlock.style.display = 'none';
        if (lettreBlock)     lettreBlock.style.display     = 'none';

        if (type === 'stage'  && conventionBlock) conventionBlock.style.display = 'block';
        if (type === 'etudes' && lettreBlock)     lettreBlock.style.display     = 'block';
    }

    /**
     * Switches the main form from read-only mode to edit mode.
     *
     * - Stores each field's current value in `data-original-value` so that
     *   changes can be detected later.
     * - Removes `readonly` / `disabled` attributes and resets visual styles.
     * - Enables file inputs, document comment textareas, and status buttons.
     * - Swaps the "Edit" button for the "Save" and "Cancel" buttons.
     */
    activerModification() {
        const formPrincipal = document.querySelector('.creation-form');
        if (!formPrincipal) return;

        // Snapshot current values before enabling editing
        formPrincipal.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select').forEach(field => {
            if (field.id !== 'numetu' && field.id !== 'numetu_display') {
                field.setAttribute('data-original-value', field.value);
            }
        });

        // Unlock text inputs
        formPrincipal.querySelectorAll('input').forEach(field => {
            if (field.id !== 'numetu' && field.id !== 'numetu_display') {
                field.removeAttribute('readonly');
                field.disabled = false;
                field.style.backgroundColor = 'white';
                field.style.color = 'black';
                field.classList.remove('input-disabled');
            }
        });

        // Unlock select fields and disable their hidden mirrors
        formPrincipal.querySelectorAll('select').forEach(field => {
            if (field.id !== 'numetu' && field.id !== 'numetu_display') {
                field.disabled = false;
                field.style.backgroundColor = 'white';
                field.style.color = 'black';
                field.classList.remove('input-disabled');
                const mirror = formPrincipal.querySelector(`input[type="hidden"][name="${field.name}"].select-mirror`);
                if (mirror) mirror.disabled = true;
            }
        });

        // Unlock file inputs
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.disabled = false;
            input.classList.remove('input-disabled');
        });

        // Unlock document action textareas and status buttons
        document.querySelectorAll('.doc-actions textarea').forEach(ta => { ta.disabled = false; });
        document.querySelectorAll('.btn-status').forEach(btn => { btn.disabled = false; });

        // Swap action buttons: hide "Edit", show "Save" and "Cancel"
        const btnMod    = document.getElementById('btn-modifier');
        const btnSave   = document.getElementById('btn-enregistrer');
        const btnCancel = document.getElementById('btn-annuler');

        if (btnMod)    btnMod.style.display = 'none';
        if (btnSave)   { btnSave.style.display = 'inline-block';   btnSave.classList.remove('btn-hidden'); }
        if (btnCancel) { btnCancel.style.display = 'inline-block'; btnCancel.classList.remove('btn-hidden'); }
    }
}


/* --------------------------------------------------------------------------
   FilterManager — search & filters → URL
   -------------------------------------------------------------------------- */

/**
 * Manages the search bar and all filter controls (checkboxes, selects, dates).
 * Applies filters by rebuilding the page URL and navigating to it.
 */
class FilterManager {

    /**
     * Initialises all filter event listeners and pre-selects values from the URL.
     */
    constructor() {
        this._initSearchEvents();
        this._initCheckboxFilters();
        this._initSelectFilters();
    }

    /**
     * Binds the search input (debounced 3 s + Enter key) and the search
     * button to `appliquerFiltres`.
     *
     * @private
     */
    _initSearchEvents() {
        const searchInput = document.getElementById('search');
        const searchBtn   = document.querySelector('#btn-search-loupe');

        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => this.appliquerFiltres(true), 3000);
            });
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.appliquerFiltres(true);
            });
        }

        if (searchBtn) searchBtn.addEventListener('click', () => this.appliquerFiltres(true));
    }

    /**
     * Binds the direction (incoming/outgoing) and zone checkbox groups.
     * Clicking one checkbox in a group unchecks the others (radio-like behaviour).
     *
     * @private
     */
    _initCheckboxFilters() {
        document.querySelectorAll('input[name="entrant_sortant"], input[name="zone"]').forEach(cb => {
            cb.addEventListener('click', (e) => {
                const groupName = e.target.name;
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(other => {
                    if (other !== e.target) other.checked = false;
                });
                this.appliquerFiltres(true);
            });
        });
    }

    /**
     * Binds the completion, date-range, component, and agreement select filters,
     * then pre-selects their values from the current URL query string.
     *
     * @private
     */
    _initSelectFilters() {
        document.querySelectorAll('#filter-complet, #date-debut, #date-fin, #filter-composante, #filter-accord').forEach(sel => {
            if (sel) sel.addEventListener('change', () => this.appliquerFiltres(true));
        });
        this._preselectFromUrl();
    }

    /**
     * Reads filter values from the URL query string and pre-selects the
     * corresponding form controls on page load.
     *
     * @private
     */
    _preselectFromUrl() {
        const params = new URLSearchParams(window.location.search);

        const complet = params.get('complet');
        const filterComplet = document.getElementById('filter-complet');
        if (filterComplet && complet !== null) filterComplet.value = complet;

        const composante = params.get('composante');
        const filterComposante = document.getElementById('filter-composante');
        if (filterComposante && composante !== null) filterComposante.value = composante;

        const accord = params.get('accord');
        const filterAccord = document.getElementById('filter-accord');
        if (filterAccord && accord !== null) filterAccord.value = accord;

        const type = params.get('type');
        if (type) {
            const cb = document.querySelector(`input[name="entrant_sortant"][value="${type}"]`);
            if (cb) cb.checked = true;
        }

        const zone = params.get('zone');
        if (zone) {
            const cb = document.querySelector(`input[name="zone"][value="${zone}"]`);
            if (cb) cb.checked = true;
        }
    }

    /**
     * Reads all active filter values, updates the page URL query string
     * accordingly, and navigates to it.
     *
     * @param {boolean} [resetPage=false] - When true, removes the `p` (page)
     *   parameter so the results reset to page 1.
     */
    appliquerFiltres(resetPage = false) {
        const url = new URL(window.location.href);

        const searchInput = document.getElementById('search');
        if (searchInput && searchInput.value.trim() !== '') url.searchParams.set('search', searchInput.value.trim());
        else url.searchParams.delete('search');

        const typeChecked = document.querySelector('input[name="entrant_sortant"]:checked');
        if (typeChecked) url.searchParams.set('type', typeChecked.value);
        else             url.searchParams.delete('type');

        const zoneChecked = document.querySelector('input[name="zone"]:checked');
        if (zoneChecked) url.searchParams.set('zone', zoneChecked.value);
        else             url.searchParams.delete('zone');

        const completVal = document.getElementById('filter-complet');
        if (completVal && completVal.value !== 'all') url.searchParams.set('complet', completVal.value);
        else                                           url.searchParams.delete('complet');

        const composanteVal = document.getElementById('filter-composante');
        if (composanteVal && composanteVal.value !== 'all') url.searchParams.set('composante', composanteVal.value);
        else                                                 url.searchParams.delete('composante');

        const accordVal = document.getElementById('filter-accord');
        if (accordVal && accordVal.value !== 'all') url.searchParams.set('accord', accordVal.value);
        else                                         url.searchParams.delete('accord');

        if (resetPage) url.searchParams.delete('p');
        window.location.href = url.toString();
    }
}


/* --------------------------------------------------------------------------
   TableManager — row clicks → student record
   -------------------------------------------------------------------------- */

/**
 * Handles click events on the student list table rows.
 * Clicking any row navigates to the corresponding student's record.
 */
class TableManager {

    /**
     * Attaches a click listener to every row in the student table.
     * The student number is read from the row's `data-numetu` attribute.
     */
    constructor() {
        document.querySelectorAll('.table-etudiants tbody tr').forEach(row => {
            row.addEventListener('click', (e) => {
                const numetu = e.currentTarget.dataset.numetu;
                if (numetu) this.ouvrirFicheEtudiant(numetu);
            });
        });
    }

    /**
     * Navigates to the student record page by appending `action=view`
     * and `numetu` to the current URL.
     *
     * @param {string} numetu - The student number to open.
     */
    ouvrirFicheEtudiant(numetu) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'view');
        url.searchParams.set('numetu', numetu);
        window.location.href = url.toString();
    }
}


/* --------------------------------------------------------------------------
   AccordionManager — toggle + internal pagination per section
   -------------------------------------------------------------------------- */

/**
 * Manages collapsible section accordions and their internal pagination.
 * Each section with more rows than `itemsPerPage` gets its own page controls.
 */
class AccordionManager {

    /**
     * @param {number} [itemsPerPage=10] - Maximum number of table rows
     *   visible per page inside each accordion section.
     */
    constructor(itemsPerPage = 10) {
        this.itemsPerPage = itemsPerPage;
        this._initToggle();
        this._initPagination();
    }

    /**
     * Binds click events to every accordion header bar.
     * Clicking a bar toggles the visibility of its target content panel
     * and rotates the arrow indicator.
     *
     * @private
     */
    _initToggle() {
        document.querySelectorAll('.barre-titre').forEach(barre => {
            barre.addEventListener('click', () => {
                const contenu = document.getElementById(barre.getAttribute('data-target'));
                const fleche  = barre.querySelector('.fleche');
                if (contenu) contenu.classList.toggle('afficher');
                if (fleche)  fleche.classList.toggle('ouverte');
            });
        });
    }

    /**
     * Sets up page-by-page navigation inside each accordion section.
     * Sections with fewer rows than `itemsPerPage` have their pagination
     * container hidden.
     *
     * @private
     */
    _initPagination() {
        document.querySelectorAll('.section-composante').forEach((section) => {
            const tbody               = section.querySelector('.table-etudiants tbody');
            const paginationContainer = section.querySelector('.accordion-pagination');
            if (!tbody || !paginationContainer) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            if (rows.length <= this.itemsPerPage) { paginationContainer.style.display = 'none'; return; }

            const totalPages = Math.ceil(rows.length / this.itemsPerPage);

            /**
             * Displays the given page and re-renders the pagination buttons.
             *
             * @param {number} page - 1-based page number to display.
             */
            const showPage = (page) => {
                rows.forEach((row, index) => {
                    row.style.display = (index >= (page - 1) * this.itemsPerPage && index < page * this.itemsPerPage) ? '' : 'none';
                });
                renderButtons(page);
            };

            /**
             * Renders previous, numbered, and next pagination buttons.
             * Stops click events from bubbling up to the accordion toggle.
             *
             * @param {number} currentPage - The currently active page.
             */
            const renderButtons = (currentPage) => {
                paginationContainer.innerHTML = '';

                const btnPrev = document.createElement('button');
                btnPrev.textContent = '‹';
                btnPrev.disabled    = currentPage === 1;
                btnPrev.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); showPage(currentPage - 1); });
                paginationContainer.appendChild(btnPrev);

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    if (i === currentPage) btn.classList.add('active');
                    btn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); showPage(i); });
                    paginationContainer.appendChild(btn);
                }

                const btnNext = document.createElement('button');
                btnNext.textContent = '›';
                btnNext.disabled    = currentPage === totalPages;
                btnNext.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); showPage(currentPage + 1); });
                paginationContainer.appendChild(btnNext);
            };

            showPage(1);
        });
    }
}


/* --------------------------------------------------------------------------
   DocumentManager — AJAX confirm/upload, global status, status buttons, upload unlock
   -------------------------------------------------------------------------- */

/**
 * Handles all document-related interactions:
 * - Activating status buttons (accepted / refused / pending).
 * - Unlocking document action controls when a new file is selected.
 * - Saving individual document status and comment via AJAX (with optional file upload).
 * - Saving the global folder status via AJAX.
 */
class DocumentManager {

    /**
     * Initialises status button highlighting and file-upload unlock behaviour.
     */
    constructor() {
        this._initStatutButtons();
        this._initFileUploadEvents();
    }

    /**
     * Ensures that clicking a status button marks only that button as active
     * within its document group.
     *
     * @private
     */
    _initStatutButtons() {
        document.querySelectorAll('.statut-document-buttons .btn-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const doc = this.dataset.doc;
                document.querySelectorAll(`.statut-document-buttons[data-doc="${doc}"] .btn-status`).forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }

    /**
     * Unlocks the review controls (radio buttons, comment textarea, confirm
     * button) for a document item once a new file has been selected.
     *
     * @private
     */
    _initFileUploadEvents() {
        document.querySelectorAll('.doc-review-item input[type="file"]').forEach(fileInput => {
            fileInput.addEventListener('change', function () {
                if (!this.files || this.files.length === 0) return;
                const item = this.closest('.doc-review-item');
                if (!item) return;
                item.querySelectorAll('input[type="radio"], textarea, .btn-confirm-doc').forEach(el => {
                    el.disabled = false;
                    el.classList.remove('input-disabled');
                });
                const docActions = item.querySelector('.doc-actions');
                if (docActions) docActions.classList.remove('disabled-area');
            });
        });
    }

    /**
     * Saves the review status and comment for a single document via AJAX.
     * If a new file has been selected it is uploaded in the same request,
     * and the page reloads automatically on success.
     *
     * @async
     * @param {string} numEtu  - The student number owning the document.
     * @param {string} docType - The document type key (e.g. 'cv', 'langues').
     */
    async confirmDocument(numEtu, docType) {
        const container = document.querySelector(`.doc-review-item[data-doctype="${docType}"]`);
        if (!container) return;

        const checkedRadio  = container.querySelector(`input[name="status_${docType}"]:checked`);
        const status        = checkedRadio ? checkedRadio.value : 'pending';
        const comment       = container.querySelector(`textarea[name="comment_${docType}"]`)?.value ?? '';
        const indicator     = document.getElementById(`indicator_${docType}`);
        const btn           = container.querySelector('.btn-confirm-doc');
        const fileInputName = docType === 'langues' ? 'langues_file' : docType;
        const fileInput     = container.querySelector(`input[type="file"][name="${fileInputName}"]`);
        const hasNewFile    = fileInput && fileInput.files && fileInput.files.length > 0;

        if (btn)       btn.disabled = true;
        if (indicator) { indicator.textContent = "Saving…"; indicator.style.color = "orange"; }

        if (hasNewFile) {
            // Upload the new file together with status and comment
            const formData = new FormData();
            formData.append('numetu',   numEtu);
            formData.append('doc_type', docType);
            formData.append('status',   status);
            formData.append('comment',  comment);
            formData.append('file',     fileInput.files[0]);

            try {
                const result = await (await fetch('index.php?page=update_document_status', { method: 'POST', body: formData })).json();
                if (indicator) {
                    indicator.textContent = result.success ? "Saved ✓" : (result.message || "Server error");
                    indicator.style.color = result.success ? "green" : "red";
                }
                // Reload to reflect the new file in the UI
                if (result.success) setTimeout(() => window.location.reload(), 800);
            } catch {
                if (indicator) { indicator.textContent = "Network error"; indicator.style.color = "red"; }
            }

            setTimeout(() => { if (indicator) indicator.textContent = ""; if (btn) btn.disabled = false; }, 3000);
            return;
        }

        // No new file — update status and comment only
        const formData = new FormData();
        formData.append('numetu',   numEtu);
        formData.append('doc_type', docType);
        formData.append('status',   status);
        formData.append('comment',  comment);

        try {
            const result = await (await fetch('index.php?page=update_document_status', { method: 'POST', body: formData })).json();
            if (indicator) {
                indicator.textContent = result.success ? "Saved ✓" : "Server error";
                indicator.style.color = result.success ? "green" : "red";
            }
        } catch {
            if (indicator) { indicator.textContent = "Network error"; indicator.style.color = "red"; }
        }

        setTimeout(() => { if (indicator) indicator.textContent = ""; if (btn) btn.disabled = false; }, 3000);
    }

    /**
     * Saves the global folder status selected in the status dropdown via AJAX.
     *
     * @async
     * @param {string} numEtu - The student number whose global status is being updated.
     */
    async updateGlobalStatus(numEtu) {
        const statusSelect = document.getElementById('global_status_select');
        const indicator    = document.getElementById('global_status_indicator');
        if (!statusSelect) return;

        if (indicator) { indicator.textContent = "Saving…"; indicator.style.color = "orange"; }

        const formData = new FormData();
        formData.append('numetu', numEtu);
        formData.append('status', statusSelect.value);

        try {
            const result = await (await fetch('index.php?page=update_global_status', { method: 'POST', body: formData })).json();
            if (indicator) {
                indicator.textContent = result.success ? "Status updated ✓" : "Error";
                indicator.style.color = result.success ? "green" : "red";
            }
        } catch {
            if (indicator) { indicator.textContent = "Network error"; indicator.style.color = "red"; }
        }

        setTimeout(() => { if (indicator) indicator.textContent = ""; }, 3000);
    }
}


/* --------------------------------------------------------------------------
   DateLimiteManager — deadline form toggle
   -------------------------------------------------------------------------- */

/**
 * Toggles the visibility of the deadline editing form.
 * The form opens when the edit button is clicked and closes when
 * the cancel button is clicked.
 */
class DateLimiteManager {

    /**
     * Binds the edit and cancel buttons to show/hide the deadline form.
     */
    constructor() {
        const btnEditDate    = document.getElementById('btn-edit-date-limite');
        const formDateLimite = document.getElementById('form-date-limite');
        const btnCancelDate  = document.getElementById('btn-cancel-date');

        if (btnEditDate && formDateLimite) {
            btnEditDate.addEventListener('click', () => {
                formDateLimite.style.display = formDateLimite.style.display === 'none' ? 'block' : 'none';
            });
        }
        if (btnCancelDate && formDateLimite) {
            btnCancelDate.addEventListener('click', () => { formDateLimite.style.display = 'none'; });
        }
    }
}


/* --------------------------------------------------------------------------
   ValidationModalManager — confirmation modal before form submission
   -------------------------------------------------------------------------- */

/**
 * Manages the validation modal that summarises pending field changes and
 * document statuses before the user confirms the form submission.
 */
class ValidationModalManager {

    /**
     * Initialises the modal and binds all related event listeners.
     */
    constructor() {
        this._init();
    }

    /**
     * Sets up the "Save" button, modal open/close logic, field-change
     * detection, document status display, and form synchronisation.
     *
     * @private
     */
    _init() {
        const btnEnregistrer = document.getElementById('btn-enregistrer');
        const modal          = document.getElementById('modal-validation');
        const btnModalCancel = document.getElementById('btn-modal-cancel');
        const formPrincipal  = document.querySelector('.creation-form');
        const formValidation = document.getElementById('form-validation');

        if (!btnEnregistrer || !modal || !formPrincipal) return;

        // Determine UI language from the app config element (defaults to 'fr')
        const lang         = document.getElementById('app-config')?.dataset.lang ?? 'fr';
        const translations = {
            photo:             lang === 'fr' ? "Photo d'identité"          : 'ID Photo',
            cv:                lang === 'fr' ? 'CV'                         : 'Resume',
            convention:        lang === 'fr' ? 'Convention de stage'        : 'Internship Agreement',
            lettre_motivation: lang === 'fr' ? 'Lettre de motivation'       : 'Motivation Letter',
            langues:           lang === 'fr' ? 'Attestation de langues'     : 'Language Certificate',
            conforme:          lang === 'fr' ? 'Accepté'                    : 'Accepted',
            non_conforme:      lang === 'fr' ? 'Refusé'                     : 'Refused',
            manquant:          lang === 'fr' ? 'Manquant'                   : 'Missing',
            present:           lang === 'fr' ? 'Déposé'                     : 'Uploaded',
            aucun_manquant:    lang === 'fr' ? '✅ Aucun document manquant'  : '✅ No missing documents',
        };

        // Document analysis data injected by the server (missing, present, statuses)
        const analyseDocuments = window.analyseDocumentsData || { manquants: [], presents: [], statuts: {} };

        // Snapshot original field values so changes can be detected
        formPrincipal.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select').forEach(field => {
            if (field.id !== 'numetu' && field.id !== 'numetu_display') {
                field.setAttribute('data-original-value', field.value);
            }
        });

        // Open modal when "Save" is clicked
        btnEnregistrer.addEventListener('click', (e) => {
            e.preventDefault();
            this._afficherModifications(this._detecterModifications(formPrincipal));
            this._afficherDocuments(analyseDocuments, translations);
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Close modal via cancel button, backdrop click, or Escape key
        if (btnModalCancel) btnModalCancel.addEventListener('click', () => this._fermerModale(modal));
        modal.addEventListener('click', (e) => { if (e.target === modal) this._fermerModale(modal); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) this._fermerModale(modal);
        });

        // Sync main form data into the validation form before submitting
        if (formValidation) {
            formValidation.addEventListener('submit', (e) => {
                e.preventDefault();
                this._syncFormToModal(formPrincipal, formValidation);
                formValidation.submit();
            });
        }

        // Auto-dismiss flash messages after 3.5 seconds
        const msgDiv = document.querySelector('.message');
        if (msgDiv && msgDiv.textContent.trim() !== '') {
            msgDiv.style.display = 'block';
            setTimeout(() => msgDiv.remove(), 3500);
        }
    }

    /**
     * Closes the validation modal and restores normal page scrolling.
     *
     * @private
     * @param {HTMLElement} modal - The modal element to close.
     */
    _fermerModale(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    /**
     * Copies all relevant fields from the main form into the validation form
     * as hidden inputs so they are submitted together.
     * Previously synced fields are removed first to avoid duplicates.
     * Disabled selects are replaced by the value of their hidden mirror input.
     *
     * @private
     * @param {HTMLElement} formPrincipal - The source (main) form element.
     * @param {HTMLElement} formModal     - The target (validation) form element.
     */
    _syncFormToModal(formPrincipal, formModal) {
        formModal.querySelectorAll('.synced-field').forEach(el => el.remove());

        formPrincipal.querySelectorAll('input:not([type="file"]), select, textarea').forEach(field => {
            if (!field.name) return;
            if (formModal.querySelector(`[name="${field.name}"]`)) return;

            let value = '';
            if (field.tagName === 'SELECT') {
                if (field.disabled) {
                    // Read from hidden mirror when select is disabled
                    const mirror = formPrincipal.querySelector(`input[type="hidden"][name="${field.name}"]`);
                    value = mirror ? mirror.value : '';
                } else {
                    value = field.options[field.selectedIndex]?.value ?? '';
                }
            } else {
                value = field.value;
            }

            // DEBUG — remove after verification
            if (field.name === 'mobilite_type') {
                console.log('mobilite_type synced value:', value);
            }

            const hidden = document.createElement('input');
            hidden.type  = 'hidden';
            hidden.name  = field.name;
            hidden.value = value;
            hidden.classList.add('synced-field');
            formModal.appendChild(hidden);
        });
    }

    /**
     * Compares each editable field's current value against the snapshot
     * stored in `data-original-value` and returns a list of changes.
     *
     * @private
     * @param {HTMLElement} formPrincipal - The main form containing the fields.
     * @returns {{ champ: string, ancienne: string, nouvelle: string }[]}
     *   Array of objects describing each detected change.
     */
    _detecterModifications(formPrincipal) {
        const modifications = [];

        formPrincipal.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select').forEach(input => {
            if (input.disabled) return;
            if (!input.name || input.name === 'numetu' || input.name === 'mobilite_type') return;

            const valeurOriginale = input.getAttribute('data-original-value');
            if (valeurOriginale === null) return;

            const valeurActuelle = input.value;
            if (valeurActuelle === valeurOriginale) return;

            // Resolve a human-readable label from the associated <label> element
            let label = input.name;
            if (input.id) {
                const labelEl = formPrincipal.querySelector(`label[for="${input.id}"]`);
                if (labelEl) label = labelEl.textContent.trim().replace('*', '').trim();
            }

            modifications.push({ champ: label, ancienne: valeurOriginale || '(empty)', nouvelle: valeurActuelle || '(empty)' });
        });

        return modifications;
    }

    /**
     * Renders the list of detected field changes inside the modal.
     * Hides the section entirely when there are no modifications.
     *
     * @private
     * @param {{ champ: string, ancienne: string, nouvelle: string }[]} modifications
     *   Array of change descriptors returned by `_detecterModifications`.
     */
    _afficherModifications(modifications) {
        const section = document.getElementById('section-modifications');
        const liste   = document.getElementById('liste-modifications');
        if (!section || !liste) return;

        if (modifications.length === 0) { section.style.display = 'none'; return; }

        section.style.display = 'block';
        liste.innerHTML = modifications.map(m =>
            `<li>
        <strong>${m.champ}</strong> :
        <span class="valeur-ancienne">${m.ancienne}</span>
        → <span class="valeur-nouvelle">${m.nouvelle}</span>
     </li>`
        ).join('');
    }

    /**
     * Populates the document summary section of the modal with missing and
     * present documents, reflecting the statuses currently selected in the UI.
     * Also injects hidden inputs into the validation form for each present document.
     *
     * @private
     * @param {{ manquants: string[], presents: string[], statuts: Object }} analyseDocuments
     *   Server-side document analysis data.
     * @param {Object} translations - Localised label map for document types and statuses.
     */
    _afficherDocuments(analyseDocuments, translations) {
        const manquants = analyseDocuments.manquants || [];
        const presents  = analyseDocuments.presents  || [];
        const statuts   = analyseDocuments.statuts   || {};

        // Read statuses currently selected in the review UI (may differ from server data)
        const statutsVue = {};
        document.querySelectorAll('.doc-review-item').forEach(item => {
            const doc          = item.dataset.doctype;
            const checkedRadio = doc ? item.querySelector(`input[name="status_${doc}"]:checked`) : null;
            if (doc && checkedRadio) statutsVue[doc] = checkedRadio.value;
        });

        const formValidation = document.getElementById('form-validation');

        // Remove stale status hidden inputs before re-injecting
        document.querySelectorAll('[id^="statut_modal_"]').forEach(el => el.remove());

        // Inject one hidden input per present document into the validation form
        presents.forEach(doc => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'statut_' + doc;
            input.id    = 'statut_modal_' + doc;
            input.value = statutsVue[doc] || statuts[doc] || 'pending';
            if (formValidation) formValidation.appendChild(input);
        });

        // Render missing document list (or a success message if none)
        const listeManquants = document.getElementById('liste-manquants');
        if (listeManquants) {
            listeManquants.innerHTML = manquants.length === 0
                ? `<p>${translations.aucun_manquant}</p>`
                : manquants.map(doc => `
                    <div class="document-validation-item manquant">
                        <span class="document-name">${translations[doc] || doc}</span>
                        <span class="document-status-badge badge-manquant">${translations.manquant}</span>
                    </div>`
                ).join('');
        }

        // Render present document list with their review status badges
        const listePresents = document.getElementById('liste-presents');
        if (listePresents) {
            listePresents.innerHTML = presents.map(doc => {
                const s     = statutsVue[doc] || statuts[doc] || '';
                const badge = s === 'accepted'
                    ? `<span class="document-status-badge">✅ ${translations.conforme}</span>`
                    : s === 'refused'
                        ? `<span class="document-status-badge" >❌ ${translations.non_conforme}</span>`
                        : `<span class="document-status-badge badge-present">${translations.present}</span>`;
                return `
                    <div class="document-validation-item present">
                        <span class="document-name">${translations[doc] || doc}</span>
                        ${badge}
                    </div>`;
            }).join('');
        }
    }
}


/* --------------------------------------------------------------------------
   FolderManager — orchestrates all classes
   -------------------------------------------------------------------------- */

/**
 * Top-level controller that instantiates and wires together all sub-managers.
 * Exposes proxy methods so HTML event attributes can call document actions
 * directly via `window.folderManager`.
 */
class FolderManager {

    /**
     * Creates instances of all sub-managers.
     */
    constructor() {
        this._form       = new FormManager();
        this._filter     = new FilterManager();
        this._table      = new TableManager();
        this._accordion  = new AccordionManager();
        this._document   = new DocumentManager();
        this._modal      = new ValidationModalManager();
        this._dateLimite = new DateLimiteManager();
    }

    /**
     * Proxy — called from HTML: `folderManager.confirmDocument(numEtu, docType)`
     *
     * @param {string} numEtu   - The student number.
     * @param {string} docType  - The document type key.
     * @returns {Promise<void>}
     */
    confirmDocument(numEtu, docType) {
        return this._document.confirmDocument(numEtu, docType);
    }

    /**
     * Proxy — called from HTML: `folderManager.updateGlobalStatus(numEtu)`
     *
     * @param {string} numEtu - The student number.
     * @returns {Promise<void>}
     */
    updateGlobalStatus(numEtu) {
        return this._document.updateGlobalStatus(numEtu);
    }
}


document.addEventListener('DOMContentLoaded', () => {
    window.folderManager = new FolderManager();
});