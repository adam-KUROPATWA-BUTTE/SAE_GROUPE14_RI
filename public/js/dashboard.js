class DashboardManager {
    constructor() {
        // Vous pourrez ajouter ici des initialisations si le tableau de bord se complexifie
        console.log("DashboardManager initialisé");
    }

    /**
     * Ouvre ou ferme un menu déroulant (accordéons)
     * @param {string} section - Le nom de la section (ex: 'sortants', 'entrants')
     */
    toggleAccordion(section) {
        const contenu = document.getElementById('contenu-' + section);
        const fleche = document.getElementById('fleche-' + section);

        // Vérification de sécurité au cas où l'élément n'existe pas
        if (!contenu || !fleche) return;

        if (contenu.classList.contains('afficher')) {
            contenu.classList.remove('afficher');
            fleche.classList.remove('ouverte');
        } else {
            contenu.classList.add('afficher');
            fleche.classList.add('ouverte');
        }
    }
}


window.dashboardManager = new DashboardManager();