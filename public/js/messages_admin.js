/**
 * Class representing the admin messages management.
 * Handles reading, replying, and deleting messages.
 */
class MessagesAdmin {
    constructor() {
        const cfg = document.getElementById('app-config')?.dataset || {};
        this.lang = cfg.lang || 'fr';
        this.base = cfg.base || '';
        this.messages = cfg.messages ? JSON.parse(cfg.messages) : [];

        this.subjects = {
            mobility:  this.lang === 'fr' ? 'Question sur ma mobilité'  : 'Mobility question',
            documents: this.lang === 'fr' ? 'Documents requis'           : 'Required documents',
            partners:  this.lang === 'fr' ? 'Universités partenaires'    : 'Partner universities',
            technical: this.lang === 'fr' ? 'Problème technique'         : 'Technical issue',
            other:     this.lang === 'fr' ? 'Autre'                      : 'Other',
        };

        this.msgMap = {};
        this.messages.forEach(m => { this.msgMap[m.id] = m; });
    }

    esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    loadMessage(id, el) {
        document.querySelectorAll('.message-item').forEach(i => i.classList.remove('selected'));
        el.classList.add('selected');
        el.classList.remove('unread');

        const dot = el.querySelector('.unread-dot');
        if (dot) dot.remove();

        const m = this.msgMap[id];
        if (!m) return;

        const subjectLabel = this.subjects[m.subject] || m.subject;

        // ── Header ──
        document.getElementById('readingHeader').innerHTML = `
            <div class="reading-subject">${this.esc(subjectLabel)}</div>
            <div class="reading-meta">
                <span>
                    <strong>${this.lang === 'fr' ? 'De' : 'From'}:</strong>
                    ${this.esc(m.name)} <small>(${this.esc(m.numEtu)})</small>
                </span>
                <span>
                    <strong>Email:</strong>
                    <a href="mailto:${this.esc(m.email)}">${this.esc(m.email)}</a>
                </span>
                <span>
                    <strong>${this.lang === 'fr' ? 'Date' : 'Date'}:</strong>
                    ${this.esc(m.createdAt)}
                </span>
            </div>
            <div class="reading-actions">
                ${!m.adminResponse
            ? `<button class="btn-reply" onclick="window.messagesAdmin.scrollToReply()">
                           ${this.lang === 'fr' ? '↩ Répondre' : '↩ Reply'}
                       </button>`
            : ''}
                <button class="btn-delete" onclick="window.messagesAdmin.deleteMsg(${m.id})">
                    ${this.lang === 'fr' ? '🗑 Supprimer' : '🗑 Delete'}
                </button>
            </div>
        `;

        // ── Body ──
        let bodyHtml = `
            <div class="message-text-block">
                ${this.nl2br(this.esc(m.message))}
            </div>
        `;

        if (m.adminResponse) {
            // Réponse admin
            bodyHtml += `
                <div class="response-sent-block">
                    <h4>${this.lang === 'fr' ? 'Votre réponse' : 'Your response'}</h4>
                    <div class="response-text">${this.nl2br(this.esc(m.adminResponse))}</div>
                    <small>${this.lang === 'fr' ? 'Envoyée le' : 'Sent on'} ${this.esc(m.respondedAt)}</small>
                </div>
            `;

            // Réponse étudiant si elle existe
            if (m.studentReply) {
                bodyHtml += `
                    <div class="student-reply-block">
                        <h4>${this.lang === 'fr' ? "Réponse de l'étudiant" : "Student's reply"}</h4>
                        <div class="student-reply-text">${this.nl2br(this.esc(m.studentReply))}</div>
                        <small>${this.lang === 'fr' ? 'Reçue le' : 'Received on'} ${this.esc(m.studentRepliedAt)}</small>
                    </div>
                `;
            }

        } else {
            // Formulaire de réponse admin
            bodyHtml += `
                <div class="reply-form-block" id="replyForm">
                    <div class="reply-label">
                        ${this.lang === 'fr' ? "Répondre à l'étudiant" : 'Reply to student'}
                    </div>
                    <form method="POST" action="${this.base}&action=respond&id=${m.id}&lang=${this.lang}">
                        <textarea name="response" rows="5" required
                            placeholder="${this.lang === 'fr' ? 'Votre réponse…' : 'Your response…'}"></textarea>
                        <div class="reply-footer">
                            <button type="submit" class="btn-reply">
                                ${this.lang === 'fr' ? 'Envoyer' : 'Send'}
                            </button>
                        </div>
                    </form>
                </div>
            `;
        }

        document.getElementById('readingBody').innerHTML = bodyHtml;
        document.getElementById('readingEmpty').style.display = 'none';
        document.getElementById('readingContent').style.display = 'flex';

        if (!m.isRead) {
            m.isRead = true;
            fetch(`${this.base}&action=mark-read&id=${m.id}&lang=${this.lang}`, { method: 'POST' });
        }
    }

    scrollToReply() {
        const form = document.getElementById('replyForm');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth' });
            form.querySelector('textarea').focus();
        }
    }

    deleteMsg(id) {
        const msg = this.lang === 'fr' ? 'Supprimer ce message ?' : 'Delete this message?';
        if (!confirm(msg)) return;

        const f = document.createElement('form');
        f.method  = 'POST';
        f.action  = `${this.base}&action=delete&id=${id}&lang=${this.lang}`;
        f.innerHTML = '<input type="hidden" name="action" value="delete">';
        document.body.appendChild(f);
        f.submit();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.messagesAdmin = new MessagesAdmin();
});