class FolderManager {
    constructor() {
        this.initFormEvents();
        this.initFilterEvents();
        this.initTableEvents();
        this.initAccordionEvents();
        this.initAccordionPagination();
        this.initValidationModal();
        this.initStatutDocumentButtons();
        this.initDateLimiteBanniere();
    }

    initFormEvents() {
        const mobiliteSelect = document.getElementById('mobilite_type');
        if (mobiliteSelect) {
            this.changerTypeMobilite(mobiliteSelect.value);
            mobiliteSelect.addEventListener('change', (e) => {
                this.changerTypeMobilite(e.target.value);
            });
        }

        const btnModifier = document.getElementById('btn-modifier');
        if (btnModifier) {
            btnModifier.addEventListener('click', () => this.activerModification());
        }
    }

    changerTypeMobilite(type) {
        const conventionBlock = document.getElementById('justificatif_convention');
        const lettreBlock     = document.getElementById('lettre_motivation');

        if (conventionBlock) conventionBlock.style.display = 'none';
        if (lettreBlock)     lettreBlock.style.display     = 'none';

        if (type === 'stage'  && conventionBlock) conventionBlock.style.display = 'block';
        if (type === 'etudes' && lettreBlock)     lettreBlock.style.display     = 'block';
    }

    activerModification() {
        const formPrincipal = document.querySelector('.creation-form');
        if (!formPrincipal) return;

        formPrincipal.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select').forEach(field => {
            if (field.id !== 'numetu' && field.id !== 'numetu_display') {
                field.setAttribute('data-original-value', field.value);
            }
        });

        formPrincipal.querySelectorAll('input, select').forEach(field => {
            if (field.id !== 'numetu' && field.id !== 'numetu_display') {
                field.disabled = false;
                field.style.backgroundColor = 'white';
                field.style.color = 'black';
                field.classList.remove('input-disabled');
            }
        });

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.disabled = false;
            input.classList.remove('input-disabled');
        });

        document.querySelectorAll('.doc-actions textarea').forEach(ta => {
            ta.disabled = false;
        });

        document.querySelectorAll('.btn-status').forEach(btn => {
            btn.disabled = false;
        });

        const btnMod    = document.getElementById('btn-modifier');
        const btnSave   = document.getElementById('btn-enregistrer');
        const btnCancel = document.getElementById('btn-annuler');

        if (btnMod)    btnMod.style.display = 'none';
        if (btnSave)   { btnSave.style.display = 'inline-block';   btnSave.classList.remove('btn-hidden'); }
        if (btnCancel) { btnCancel.style.display = 'inline-block'; btnCancel.classList.remove('btn-hidden'); }
    }

    initFilterEvents() {
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

        if (searchBtn) {
            searchBtn.addEventListener('click', () => this.appliquerFiltres(true));
        }

        document.querySelectorAll('input[name="entrant_sortant"], input[name="zone"]').forEach(cb => {
            cb.addEventListener('click', (e) => {
                const groupName = e.target.name;
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(other => {
                    if (other !== e.target) other.checked = false;
                });
                this.appliquerFiltres(true);
            });
        });

        document.querySelectorAll('#filter-complet, #date-debut, #date-fin, #filter-composante, #filter-accord').forEach(sel => {
            if (sel) sel.addEventListener('change', () => this.appliquerFiltres(true));
        });
    }

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

        url.searchParams.delete('p');
        window.location.href = url.toString();
    }

    initTableEvents() {
        document.querySelectorAll('.table-etudiants tbody tr').forEach(row => {
            row.addEventListener('click', (e) => {
                const numetu = e.currentTarget.dataset.numetu;
                if (numetu) this.ouvrirFicheEtudiant(numetu);
            });
        });
    }

    ouvrirFicheEtudiant(numetu) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'view');
        url.searchParams.set('numetu', numetu);
        window.location.href = url.toString();
    }

    initAccordionEvents() {
        document.querySelectorAll('.barre-titre').forEach(barre => {
            barre.addEventListener('click', () => {
                const targetId = barre.getAttribute('data-target');
                const contenu  = document.getElementById(targetId);
                const fleche   = barre.querySelector('.fleche');
                if (contenu) contenu.classList.toggle('afficher');
                if (fleche)  fleche.classList.toggle('ouverte');
            });
        });
    }

    initAccordionPagination() {
        const itemsPerPage = 10;
        document.querySelectorAll('.section-composante').forEach((section) => {
            const tbody               = section.querySelector('.table-etudiants tbody');
            const paginationContainer = section.querySelector('.accordion-pagination');
            if (!tbody || !paginationContainer) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));
            if (rows.length <= itemsPerPage) {
                paginationContainer.style.display = 'none';
                return;
            }

            const totalPages = Math.ceil(rows.length / itemsPerPage);

            const showPage = (page) => {
                rows.forEach((row, index) => {
                    row.style.display = (index >= (page - 1) * itemsPerPage && index < page * itemsPerPage) ? '' : 'none';
                });
                renderButtons(page);
            };

            const renderButtons = (currentPage) => {
                paginationContainer.innerHTML = '';

                const btnPrev = document.createElement('button');
                btnPrev.textContent = '‹';
                btnPrev.disabled = currentPage === 1;
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
                btnNext.disabled = currentPage === totalPages;
                btnNext.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); showPage(currentPage + 1); });
                paginationContainer.appendChild(btnNext);
            };

            showPage(1);
        });
    }

    async confirmDocument(numEtu, docType) {
        const container = document.querySelector(`.doc-review-item[data-doctype="${docType}"]`);
        if (!container) return;

        const activeBtn = container.querySelector('.btn-status.active');
        const status    = activeBtn ? activeBtn.dataset.statut : 'pending';
        const comment   = container.querySelector(`textarea[name="comment_${docType}"]`)?.value ?? '';
        const indicator = document.getElementById(`indicator_${docType}`);
        const btn       = container.querySelector('.btn-confirm-doc');

        if (btn) btn.disabled = true;
        if (indicator) { indicator.textContent = "Sauvegarde en cours..."; indicator.style.color = "orange"; }

        const formData = new FormData();
        formData.append('numetu',   numEtu);
        formData.append('doc_type', docType);
        formData.append('status',   status);
        formData.append('comment',  comment);

        try {
            const response = await fetch('index.php?page=update_document_status', { method: 'POST', body: formData });
            const result   = await response.json();
            if (indicator) {
                indicator.textContent = result.success ? "Enregistré ✓" : "Erreur serveur";
                indicator.style.color = result.success ? "green"        : "red";
            }
        } catch {
            if (indicator) { indicator.textContent = "Erreur réseau"; indicator.style.color = "red"; }
        }

        setTimeout(() => {
            if (indicator) indicator.textContent = "";
            if (btn) btn.disabled = false;
        }, 3000);
    }

    async updateGlobalStatus(numEtu) {
        const statusSelect = document.getElementById('global_status_select');
        const indicator    = document.getElementById('global_status_indicator');
        if (!statusSelect) return;

        const newStatus = statusSelect.value;
        if (indicator) { indicator.textContent = "Sauvegarde en cours..."; indicator.style.color = "orange"; }

        const formData = new FormData();
        formData.append('numetu', numEtu);
        formData.append('status', newStatus);

        try {
            const response = await fetch('index.php?page=update_global_status', { method: 'POST', body: formData });
            const result   = await response.json();
            if (indicator) {
                indicator.textContent = result.success ? "Statut mis à jour ✓" : "Erreur";
                indicator.style.color = result.success ? "green"               : "red";
            }
        } catch {
            if (indicator) { indicator.textContent = "Erreur réseau"; indicator.style.color = "red"; }
        }

        setTimeout(() => { if (indicator) indicator.textContent = ""; }, 3000);
    }

    initStatutDocumentButtons() {
        document.querySelectorAll('.statut-document-buttons .btn-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const doc = this.dataset.doc;
                document.querySelectorAll(`.statut-document-buttons[data-doc="${doc}"] .btn-status`).forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
    }

    initDateLimiteBanniere() {
        const btnEditDate    = document.getElementById('btn-edit-date-limite');
        const formDateLimite = document.getElementById('form-date-limite');
        const btnCancelDate  = document.getElementById('btn-cancel-date');

        if (btnEditDate && formDateLimite) {
            btnEditDate.addEventListener('click', () => {
                formDateLimite.style.display = formDateLimite.style.display === 'none' ? 'block' : 'none';
            });
        }
        if (btnCancelDate && formDateLimite) {
            btnCancelDate.addEventListener('click', () => {
                formDateLimite.style.display = 'none';
            });
        }
    }

    initValidationModal() {
        const btnEnregistrer = document.getElementById('btn-enregistrer');
        const modal          = document.getElementById('modal-validation');
        const btnModalCancel = document.getElementById('btn-modal-cancel');
        const formPrincipal  = document.querySelector('.creation-form');
        const formValidation = document.getElementById('form-validation');

        if (!btnEnregistrer || !modal || !formPrincipal) return;

        const appConfig = document.getElementById('app-config');
        const lang      = appConfig ? appConfig.dataset.lang : 'fr';

        const translations = {
            photo:             lang === 'fr' ? "Photo d'identité"       : 'ID Photo',
            cv:                lang === 'fr' ? 'CV'                      : 'Resume',
            convention:        lang === 'fr' ? 'Convention de stage'     : 'Internship Agreement',
            lettre_motivation: lang === 'fr' ? 'Lettre de motivation'    : 'Motivation Letter',
            langues:           lang === 'fr' ? 'Attestation de langues'  : 'Language Certificate',
            conforme:          lang === 'fr' ? 'Accepté'                 : 'Accepted',
            non_conforme:      lang === 'fr' ? 'Refusé'                  : 'Refused',
            manquant:          lang === 'fr' ? 'Manquant'                : 'Missing',
            present:           lang === 'fr' ? 'Déposé'                  : 'Uploaded',
            aucun_manquant:    lang === 'fr' ? '✅ Aucun document manquant' : '✅ No missing documents',
        };

        const analyseDocuments = window.analyseDocumentsData || { manquants: [], presents: [], statuts: {} };


        btnEnregistrer.addEventListener('click', (e) => {
            e.preventDefault();

            const modifications = this._detecterModifications(formPrincipal);

            if (modifications.length === 0) {
                // Pas de modification → soumission directe, sans popup
                formPrincipal.submit();
                return;
            }

            this._afficherModifications(modifications);
            this._afficherDocuments(analyseDocuments, translations);
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        if (btnModalCancel) {
            btnModalCancel.addEventListener('click', () => this._fermerModale(modal));
        }
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this._fermerModale(modal);
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                this._fermerModale(modal);
            }
        });

        if (formValidation) {
            formValidation.addEventListener('submit', (e) => {
                e.preventDefault();
                this._syncFormToModal(formPrincipal, formValidation);
                formValidation.submit();
            });
        }

        const msgDiv = document.querySelector('.message');
        if (msgDiv && msgDiv.textContent.trim() !== '') {
            msgDiv.style.display = 'block';
            setTimeout(() => msgDiv.remove(), 3500);
        }
    }

    _fermerModale(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }


    _syncFormToModal(formPrincipal, formModal) {
        // Nettoyer les anciens champs copiés
        formModal.querySelectorAll('.synced-field').forEach(el => el.remove());

        formPrincipal.querySelectorAll('input:not([type="file"]), select, textarea').forEach(field => {
            if (!field.name || field.disabled) return;
            if (formModal.querySelector(`[name="${field.name}"]`)) return; // déjà présent

            const hidden = document.createElement('input');
            hidden.type  = 'hidden';
            hidden.name  = field.name;
            hidden.value = field.tagName === 'SELECT'
                ? (field.options[field.selectedIndex]?.value ?? '')
                : field.value;
            hidden.classList.add('synced-field');
            formModal.appendChild(hidden);
        });
    }

    _detecterModifications(formPrincipal) {
        const modifications = [];

        formPrincipal.querySelectorAll('input:not([type="file"]):not([type="hidden"]), select').forEach(input => {
            if (input.disabled) return;
            if (!input.name || input.name === 'numetu' || input.name === 'mobilite_type') return;

            const valeurOriginale = input.getAttribute('data-original-value');
            if (valeurOriginale === null) return; // champ non débloqué

            const valeurActuelle = input.value;
            if (valeurActuelle === valeurOriginale) return; // pas de changement

            let label = input.name;
            if (input.id) {
                const labelEl = formPrincipal.querySelector(`label[for="${input.id}"]`);
                if (labelEl) label = labelEl.textContent.trim().replace('*', '').trim();
            }

            modifications.push({
                champ:    label,
                ancienne: valeurOriginale || '(vide)',
                nouvelle: valeurActuelle  || '(vide)',
            });
        });

        return modifications;
    }

    _afficherModifications(modifications) {
        const section = document.getElementById('section-modifications');
        const liste   = document.getElementById('liste-modifications');
        if (!section || !liste) return;

        if (modifications.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';
        liste.innerHTML = modifications.map(m =>
            `<li>
                <strong>${m.champ}</strong> :
                <span style="color:#dc3545;text-decoration:line-through;">${m.ancienne}</span>
                → <span style="color:#28a745;font-weight:600;">${m.nouvelle}</span>
             </li>`
        ).join('');
    }

    _afficherDocuments(analyseDocuments, translations) {
        const manquants = analyseDocuments.manquants || [];
        const presents  = analyseDocuments.presents  || [];
        const statuts   = analyseDocuments.statuts   || {};

        const statutsVue = {};
        document.querySelectorAll('.statut-document-buttons').forEach(block => {
            const doc   = block.dataset.doc;
            const actif = block.querySelector('.btn-status.active');
            if (actif) statutsVue[doc] = actif.dataset.statut;
        });

        const formValidation = document.getElementById('form-validation');
        presents.forEach(doc => {
            let input = document.getElementById('statut_modal_' + doc);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'statut_' + doc;
                input.id   = 'statut_modal_' + doc;
                if (formValidation) formValidation.appendChild(input);
            }
            input.value = statutsVue[doc] || statuts[doc] || '';
        });

        // Documents manquants
        const listeManquants = document.getElementById('liste-manquants');
        if (listeManquants) {
            listeManquants.innerHTML = manquants.length === 0
                ? `<p style="color:#28a745;font-weight:600;">${translations.aucun_manquant}</p>`
                : manquants.map(doc => `
                    <div class="document-validation-item manquant">
                        <span class="document-name">${translations[doc] || doc}</span>
                        <span class="document-status-badge badge-manquant">${translations.manquant}</span>
                    </div>`
                ).join('');
        }

        const listePresents = document.getElementById('liste-presents');
        if (listePresents) {
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
                    </div>`;
            }).join('');
        }
    }
}


document.addEventListener('DOMContentLoaded', () => {
    window.folderManager = new FolderManager();
});