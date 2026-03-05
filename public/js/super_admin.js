/**
 * super_admin.js
 * Gestion de l'interface super administrateur.
 * Toutes les fonctions sont regroupées dans l'objet SuperAdmin.
 */

const SuperAdmin = (() => {

    // Label de suppression injecté par PHP dans la vue (window.SA_DELETE_LABEL)
    function confirmDelete(login) {
        const label = (typeof window.SA_DELETE_LABEL !== 'undefined')
            ? window.SA_DELETE_LABEL
            : 'Supprimer le compte';
        return confirm(label + ' ' + login + ' ?');
    }

    function toggleRoleFields(role) {
        const deptField      = document.getElementById('deptField');
        const siteField      = document.getElementById('siteField');
        const coordTypeField = document.getElementById('coordTypeField');
        const deptSelect     = document.getElementById('departement');
        const siteSelect     = document.getElementById('site');
        const roleHidden     = document.getElementById('roleHiddenAdmin');

        if (role === 'admin') {
            siteField.style.display      = '';
            deptField.style.display      = 'none';
            coordTypeField.style.display = 'none';
            siteSelect.required          = true;
            deptSelect.required          = false;
            roleHidden.value             = 'admin';
            roleHidden.disabled          = false;
            document.querySelectorAll('input[name="role"]').forEach(r => {
                r.required = false;
                r.checked  = false;
            });
        } else {
            siteField.style.display      = 'none';
            deptField.style.display      = '';
            coordTypeField.style.display = '';
            siteSelect.required          = false;
            deptSelect.required          = true;
            roleHidden.value             = '';
            roleHidden.disabled          = true;
            document.querySelectorAll('input[name="role"]').forEach(r => r.required = true);
        }
    }

    function generatePassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
        const regex = /^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{12,}$/;
        let pwd = '';

        do {
            pwd = '';
            for (let i = 0; i < 12; i++) {
                pwd += chars.charAt(Math.floor(Math.random() * chars.length));
            }
        } while (!regex.test(pwd));

        const el = document.getElementById('password');
        if (el) el.value = pwd;
    }

    function _showMsg(el, color, text) {
        if (!el) return;
        el.style.color   = color;
        el.textContent   = text;
        el.style.display = '';
    }

    function addDepartment() {
        const input  = document.getElementById('newDeptInput');
        const msg    = document.getElementById('deptMsg');
        const select = document.getElementById('departement');
        if (!input) return;

        const code = input.value.trim().toUpperCase();
        if (!code) { _showMsg(msg, 'red', 'Veuillez saisir un code.'); return; }

        for (let opt of select.options) {
            if (opt.value === code) { _showMsg(msg, 'orange', 'Déjà existant.'); return; }
        }

        const hidden = document.getElementById('hiddenDeptValue');
        const form   = document.getElementById('addDeptForm');
        if (hidden && form) { hidden.value = code; form.submit(); }
    }

    function addSite() {
        const input  = document.getElementById('newSiteInput');
        const msg    = document.getElementById('siteMsg');
        const select = document.getElementById('site');
        if (!input) return;

        const name = input.value.trim();
        if (!name) { _showMsg(msg, 'red', 'Veuillez saisir un nom.'); return; }

        for (let opt of select.options) {
            if (opt.value === name) { _showMsg(msg, 'orange', 'Déjà existant.'); return; }
        }

        const hidden = document.getElementById('hiddenSiteValue');
        const form   = document.getElementById('addSiteForm');
        if (hidden && form) { hidden.value = name; form.submit(); }
    }

    return {
        toggleRoleFields,
        generatePassword,
        confirmDelete,
        addDepartment,
        addSite,
    };
})();