/* =========================================
   CHATBOT LOGIC
   ========================================= */

const knowledgeBase = {
    'admin': {
        'fr': {
            'intro': "Bonjour Admin ! Je peux vous aider à gérer les dossiers.",
            'keywords': {
                'Modifier': "Pour <b>modifier un dossier</b> : Cliquez sur le nom de l'étudiant dans sur le tableau de bord ou dans la page dossier.",
                'Ajouter dossier': "Allez dans l'onglet 'Dossiers' et cliquez sur le bouton 'Créer' en haut.",
                'Partenaire': "Allez dans l'onglet 'Partenaires' pour ajouter une entreprise.",
                'Relance': "Les dossiers rouges sont incomplets. Contactez l'étudiant via l'icône Email dans la page du tableau de bord.",
                'Avancement': "Vert = Validé, Orange = En cours, Rouge = Incomplet.",
                'default': "Aide : modifier, ajouter dossier, partenaire, relance, avancement."
            }
        },
        'en': {
            'intro': "Hello Admin! I can help you manage mobilities.",
            'keywords': {
                'Modify': "To <b>edit</b>: Click on the student name in the list.",
                'Add Folder': "Go to 'Folders' tab and click 'Create'.",
                'Partner': "Go to 'Partners' tab to add a company.",
                'Relaunch': "Red folders are incomplete. Contact student via Email icon.",
                'Advancement': "Green = Done, Orange = In Progress, Red = Incomplete.",
                'default': "Help: edit, add folder, partner, reminder, progress."
            }
        }
    },
    'student': {
        'fr': {
            'intro': "Bonjour, je m'appelle Bob ! Je suis là pour t'aider dans ta mobilité.",
            'keywords': {
                'Déposer': "Va dans 'Mon Dossier' pour téléverser tes fichiers (CV, Lettre...).",
                'Avancé': "Ta progression est indiquée sur ton tableau de bord .",
                'Partenaire': "Consulte l'onglet 'Partenaires' pour voir les entreprises disponibles.",
                'Convention': "La convention doit être signée et uploadée dans 'Pièces Justificatives'.",
                'default': "Aide : déposer, avancement, partenaire, convention."
            }
        },
        'en': {
            'intro': "Hi, my name is Bob ! I'm here to help with your mobility.",
            'keywords': {
                'Deposit': "Go to 'My Folder' to upload documents.",
                'Advance': "Your progress is shown on your dashboard.",
                'Partner': "Check 'Partners' tab for companies.",
                'Convention': "The agreement must be signed and uploaded.",
                'default': "Help: submit, progress, partner, agreement."
            }
        }
    }
};

document.addEventListener("DOMContentLoaded", () => {
    // Récupération sécurisée de la config depuis main.js ou fallback
    const configEl = document.getElementById('app-config');
    const currentLang = configEl ? configEl.dataset.lang : 'fr';
    const userRole = configEl ? configEl.dataset.role : 'student';

    // Gestion de l'ouverture du popup
    const bubble = document.getElementById('help-bubble');
    const popup = document.getElementById('help-popup');
    const closeBtn = document.querySelector('#help-popup button'); // Bouton X

    if (bubble && popup) {
        bubble.addEventListener('click', () => {
            const isHidden = (window.getComputedStyle(popup).display === 'none');
            popup.style.display = isHidden ? 'flex' : 'none';
            if (isHidden) {
                // Initialiser le chat si vide
                const chatContainer = document.getElementById('chat-messages');
                if (chatContainer && chatContainer.innerHTML === '') {
                    const welcomeMsg = knowledgeBase[userRole][currentLang]['intro'];
                    addMessage(welcomeMsg, 'bot');
                    generateQuickActions(userRole, currentLang);
                }
            }
        });
    }

    if (closeBtn && popup) {
        closeBtn.addEventListener('click', () => {
            popup.style.display = 'none';
        });
    }
});

function addMessage(text, sender) {
    const chatContainer = document.getElementById('chat-messages');
    if (!chatContainer) return;
    
    const msgDiv = document.createElement('div');
    msgDiv.classList.add('message', sender === 'user' ? 'user-message' : 'bot-message');
    msgDiv.innerHTML = text;
    chatContainer.appendChild(msgDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function generateQuickActions(role, lang) {
    const container = document.getElementById('quick-actions');
    if (!container) return;

    const roleDict = knowledgeBase[role][lang]['keywords'];

    for (const key in roleDict) {
        if (key === 'default') continue;
        const btn = document.createElement('button');
        btn.innerText = key.charAt(0).toUpperCase() + key.slice(1);
        btn.onclick = () => {
            addMessage(btn.innerText, 'user');
            setTimeout(() => addMessage(roleDict[key], 'bot'), 400);
        };
        container.appendChild(btn);
    }
}