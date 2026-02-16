document.addEventListener("DOMContentLoaded", () => {
    // 1. Gestion de la Configuration Globale (Langue, Role) via data-attributes
    const configEl = document.getElementById('app-config');
    window.AppConfig = {
        lang: configEl ? configEl.dataset.lang : 'fr',
        role: configEl ? configEl.dataset.role : 'student'
    };

    // 2. Menu Mobile (Hamburger)
    const menuToggle = document.createElement('button');
    menuToggle.classList.add('menu-toggle');
    menuToggle.innerHTML = '☰';

    const rightBtn = document.querySelector('.right-buttons');
    if (rightBtn) {
        rightBtn.appendChild(menuToggle);
    }

    const navMenu = document.querySelector('nav.menu');
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // 3. Dropdown Langue
    const langBtn = document.querySelector('.dropbtn');
    if (langBtn) {
        langBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const dropdown = document.querySelector('.right-buttons'); // ou .lang-dropdown selon ton CSS exact
            if(dropdown) dropdown.classList.toggle('show');
        });
    }

    document.addEventListener('click', () => {
        const dropdown = document.querySelector('.right-buttons');
        if (dropdown) dropdown.classList.remove('show');
    });

    // 4. Liens de changement de langue
    document.querySelectorAll('.dropdown-content a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            // On suppose que le texte du lien ou un attribut permet de déduire la langue, 
            // ou on regarde le onclick original. Ici on simplifie :
            const text = e.target.innerText.toLowerCase();
            const newLang = text.includes('english') || text.includes('en') ? 'en' : 'fr';
            changeLang(newLang);
        });
    });

    // 5. Toggle Tritanopie (Accessibilité)
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        // Initial state check
        if (document.body.classList.contains('tritanopie')) {
            themeToggle.classList.add('active');
        }

        themeToggle.addEventListener('click', function () {
            document.body.classList.toggle('tritanopie');
            this.classList.toggle('active');
            
            const isTritanopia = document.body.classList.contains('tritanopie') ? '1' : '0';
            const url = new URL(window.location.href);
            url.searchParams.set('tritanopia', isTritanopia);
            window.location.href = url.toString();
        });
    }

    // 6. Auto-hide messages success/error
    const messages = document.querySelectorAll('.message, .success-message, .error-message');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000); // Disparait après 5 secondes
    });
});

// Fonction utilitaire globale
function changeLang(lang) {
    const url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}
