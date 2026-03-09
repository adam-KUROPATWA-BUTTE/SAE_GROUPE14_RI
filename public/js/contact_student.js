class ContactStudentManager {
    toggleMessages() {
        const panel = document.getElementById('messagesPanel');
        const arrow = document.getElementById('msgArrow');
        if (!panel) return;
        
        const isOpen = panel.style.display !== 'none';
        panel.style.display = isOpen ? 'none' : 'block';
        if (arrow) arrow.textContent = isOpen ? '▼' : '▲';
    }

    toggleReplyForm(id) {
        const form = document.getElementById('replyForm' + id);
        if (!form) return;
        
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
}

const ContactStudent = new ContactStudentManager();

window.toggleMessages = () => ContactStudent.toggleMessages();
window.toggleReplyForm = (id) => ContactStudent.toggleReplyForm(id);