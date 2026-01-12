/* =========================================
   CHATBOT LOGIC
   ========================================= */

const knowledgeBase = {
    // === ADMIN RESPONSES ===
    'admin': {
        'fr': {
            'intro': "Bonjour Admin ! Je peux vous aider à gérer les dossiers.",
            'keywords': {
                'modifier': "Pour <b>modifier un dossier</b> : Cliquez sur le nom de l'étudiant dans sur le tableau de bord ou dans la page dossier.",
                'ajouter dossier': "Allez dans l'onglet 'Dossiers' et cliquez sur le bouton 'Créer' en haut.",
                'partenaire': "Allez dans l'onglet 'Partenaires' pour ajouter une entreprise.",
                'relance': "Les dossiers rouges sont incomplets. Contactez l'étudiant via l'icône Email dans la page du tableau de bord.",
                'avancement': "Vert = Validé, Orange = En cours, Rouge = Incomplet.",
                'default': "Aide : modifier, ajouter dossier, partenaire, relance, avancement."
            }
        },
        'en': {
            'intro': "Hello Admin! I can help you manage mobilities.",
            'keywords': {
                'modifier': "To <b>edit</b>: Click on the student name in the list.",
                'ajouter dossier': "Go to 'Folders' tab and click 'Create'.",
                'partenaire': "Go to 'Partners' tab to add a company.",
                'relance': "Red folders are incomplete. Contact student via Email icon.",
                'avancement': "Green = Done, Orange = In Progress, Red = Incomplete.",
                'default': "Help: edit, add folder, partner, reminder, progress."
            }
        }
    },

    // === STUDENT RESPONSES ===
    'student': {
        'fr': {
            'intro': "Bonjour, je m'appelle Bob ! Je suis là pour t'aider dans ta mobilité.",
            'keywords': {
                'déposer': "Va dans 'Mon Dossier' pour téléverser tes fichiers (CV, Lettre...).",
                'avancé': "Ta progression est indiquée sur ton tableau de bord .",
                'partenaire': "Consulte l'onglet 'Partenaires' pour voir les entreprises disponibles.",
                'convention': "La convention doit être signée et uploadée dans 'Pièces Justificatives'.",
                'default': "Aide : déposer, avancement, partenaire, convention."
            }
        },
        'en': {
            'intro': "Hi, my name is Bob ! I'm here to help with your mobility.",
            'keywords': {
                'déposer': "Go to 'My Folder' to upload documents.",
                'avancé': "Your progress is shown on your dashboard.",
                'partenaire': "Check 'Partners' tab for companies.",
                'convention': "The agreement must be signed and uploaded.",
                'default': "Help: submit, progress, partner, agreement."
            }
        }
    }
};

const currentLang = CHAT_CONFIG.lang;
const userRole = CHAT_CONFIG.role;

document.addEventListener("DOMContentLoaded", () => {
    // Welcome message
    const welcomeMsg = knowledgeBase[userRole][currentLang]['intro'];
    addMessage(welcomeMsg, 'bot');
    generateQuickActions();
});

function toggleHelpPopup()
{
    const popup = document.getElementById('help-popup');
    const isVisible = popup.style.display === 'flex';
    popup.style.display = isVisible ? 'none' : 'flex';
    if (!isVisible) {
        document.getElementById('user-input').focus();
    }
}

function handleKeyPress(e)
{
    if (e.key === 'Enter') {
        sendMessage();
    }
}



function addMessage(text, sender)
{
    const chatContainer = document.getElementById('chat-messages');
    const msgDiv = document.createElement('div');
    msgDiv.classList.add('message', sender === 'user' ? 'user-message' : 'bot-message');
    msgDiv.innerHTML = text;
    chatContainer.appendChild(msgDiv);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function getBotResponse(input)
{
    const lowerInput = input.toLowerCase();
    const roleDict = knowledgeBase[userRole][currentLang]['keywords'];

    for (const key in roleDict) {
        if (key !== 'default' && lowerInput.includes(key)) {
            return roleDict[key];
        }
    }
    return roleDict['default'];
}

function generateQuickActions()
{
    const container = document.getElementById('quick-actions');
    const roleDict = knowledgeBase[userRole][currentLang]['keywords'];

    for (const key in roleDict) {
        if (key === 'default') {
            continue;
        }
        const btn = document.createElement('button');
        btn.innerText = key.charAt(0).toUpperCase() + key.slice(1);
        btn.onclick = () => {
            addMessage(btn.innerText, 'user');
            setTimeout(() => addMessage(roleDict[key], 'bot'), 400);
        };
        container.appendChild(btn);
    }
}