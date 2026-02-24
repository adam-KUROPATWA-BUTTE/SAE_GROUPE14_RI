/**
 * messages_admin.js
 * Logique de la page "Messages admin" (lecture, réponse, suppression).
 *
 * Données bootstrappées via #app-config :
 *   data-lang      : 'fr' | 'en'
 *   data-base      : URL de base (ex. "index.php?page=messages-admin")
 *   data-messages  : JSON array des messages (voir vue PHP)
 */

const MessagesAdmin = (function () {
    'use strict';

    /* ── Bootstrap ───────────────────────────────────────────── */
    const cfg      = document.getElementById('app-config').dataset;
    const lang     = cfg.lang;
    const base     = cfg.base;
    const messages = JSON.parse(cfg.messages);

    const subjects = {
        mobility:  lang === 'fr' ? 'Question sur ma mobilité'  : 'Mobility question',
        documents: lang === 'fr' ? 'Documents requis'           : 'Required documents',
        partners:  lang === 'fr' ? 'Universités partenaires'    : 'Partner universities',
        technical: lang === 'fr' ? 'Problème technique'         : 'Technical issue',
        other:     lang === 'fr' ? 'Autre'                      : 'Other',
    };

    /** Map id → message object pour un accès O(1) */
    const msgMap = {};
    messages.forEach(m => { msgMap[m.id] = m; });

    /* ── Helpers ─────────────────────────────────────────────── */
    function esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    /* ── Actions publiques ───────────────────────────────────── */

    /**
     * Charge un message dans le volet de lecture.
     * @param {number} id  - identifiant du message
     * @param {Element} el - élément cliqué dans la liste
     */
    function loadMessage(id, el) {
        document.querySelectorAll('.message-item').forEach(i => i.classList.remove('selected'));
        el.classList.add('selected');
        el.classList.remove('unread');
        const dot = el.querySelector('.unread-dot');
        if (dot) dot.remove();

        const m = msgMap[id];
        if (!m) return;

        const subjectLabel = subjects[m.subject] || m.subject;

        /* ── En-tête ── */
        document.getElementById('readingHeader').innerHTML = `
            <div class="reading-subject">${esc(subjectLabel)}</div>
            <div class="reading-meta">
                <span>
                    <strong>${lang === 'fr' ? 'De' : 'From'} :</strong>
                    ${esc(m.name)} <small>(${esc(m.numEtu)})</small>
                </span>
                <span>
                    <strong>Email :</strong>
                    <a href="mailto:${esc(m.email)}">${esc(m.email)}</a>
                </span>
                <span>
                    <strong>${lang === 'fr' ? 'Date' : 'Date'} :</strong>
                    ${esc(m.createdAt)}
                </span>
            </div>
            <div class="reading-actions">
                ${!m.adminResponse
            ? `<button class="btn-reply" onclick="MessagesAdmin.scrollToReply()">
                           ${lang === 'fr' ? '↩ Répondre' : '↩ Reply'}
                       </button>`
            : ''}
                <button class="btn-delete" onclick="MessagesAdmin.deleteMsg(${m.id})">
                    ${lang === 'fr' ? '🗑 Supprimer' : '🗑 Delete'}
                </button>
            </div>
        `;

        /* ── Corps ── */
        let bodyHtml = `
            <div class="message-text-block">
                ${nl2br(esc(m.message))}
            </div>
        `;

        if (m.adminResponse) {
            bodyHtml += `
                <div class="response-sent-block">
                    <h4>${lang === 'fr' ? 'Votre réponse' : 'Your response'}</h4>
                    <div class="response-text">${nl2br(esc(m.adminResponse))}</div>
                    <small>
                        ${lang === 'fr' ? 'Envoyée le' : 'Sent on'} ${esc(m.respondedAt)}
                    </small>
                </div>
            `;
        } else {
            bodyHtml += `
                <div class="reply-form-block" id="replyForm">
                    <div class="reply-label">
                        ${lang === 'fr' ? "Répondre à l'étudiant" : 'Reply to student'}
                    </div>
                    <form method="POST" action="${base}&action=respond&id=${m.id}&lang=${lang}">
                        <textarea name="response" rows="5" required
                            placeholder="${lang === 'fr' ? 'Votre réponse…' : 'Your response…'}"></textarea>
                        <div class="reply-footer">
                            <button type="submit" class="btn-reply">
                                ${lang === 'fr' ? 'Envoyer' : 'Send'}
                            </button>
                        </div>
                    </form>
                </div>
            `;
        }

        document.getElementById('readingBody').innerHTML = bodyHtml;

        /* ── Affichage du volet ── */
        document.getElementById('readingEmpty').style.display = 'none';
        const rc = document.getElementById('readingContent');
        rc.style.display = 'flex';

        /* ── Marquage "lu" (fire & forget) ── */
        if (!m.isRead) {
            m.isRead = true;
            fetch(`${base}&action=mark-read&id=${m.id}&lang=${lang}`, { method: 'POST' });
        }
    }

    /** Fait défiler jusqu'au formulaire de réponse et donne le focus. */
    function scrollToReply() {
        const form = document.getElementById('replyForm');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth' });
            form.querySelector('textarea').focus();
        }
    }

    /**
     * Supprime un message après confirmation.
     * @param {number} id
     */
    function deleteMsg(id) {
        const msg = lang === 'fr' ? 'Supprimer ce message ?' : 'Delete this message?';
        if (!confirm(msg)) return;

        const f = document.createElement('form');
        f.method  = 'POST';
        f.action  = `${base}&action=delete&id=${id}&lang=${lang}`;
        f.innerHTML = '<input type="hidden" name="action" value="delete">';
        document.body.appendChild(f);
        f.submit();
    }

    /**
     * Change la langue de l'interface.
     * @param {string} newLang - 'fr' | 'en'
     */
    function changeLang(newLang) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', newLang);
        window.location.href = url.toString();
    }

    /* ── API publique ────────────────────────────────────────── */
    return { loadMessage, scrollToReply, deleteMsg, changeLang };
})();