/**
 * Main application class handling global UI features:
 * responsive menu, language switching, theme toggling, and notifications.
 */
class MainApp {
    constructor() {
        this.initConfig();
        this.initMenu();
        this.initLanguageDropdown();
        this.initThemeToggle();
        this.initAutoDismissMessages();
    }

    /**
     * Parses and stores global app configuration from the DOM.
     */
    initConfig() {
        const configEl = document.getElementById('app-config');
        window.AppConfig = {
            lang: configEl ? configEl.dataset.lang : 'fr',
            role: configEl ? configEl.dataset.role : 'student'
        };
    }

    /**
     * Initializes the responsive hamburger menu for mobile views.
     */
    initMenu() {
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
    }

    /**
     * Initializes the language selector dropdown behavior.
     */
    initLanguageDropdown() {
        const langBtn = document.querySelector('.dropbtn');
        if (langBtn) {
            langBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdown = langBtn.parentElement;
                if(dropdown) dropdown.classList.toggle('show');
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', () => {
            const dropdown = document.querySelector('.lang-dropdown');
            if (dropdown) dropdown.classList.remove('show');
        });
    }

    /**
     * Initializes the tritanopia accessibility theme toggle.
     */
    initThemeToggle() {
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            // Apply initial state
            if (document.body.classList.contains('tritanopie')) {
                themeToggle.classList.add('active');
            }

            themeToggle.addEventListener('click', (e) => {
                document.body.classList.toggle('tritanopie');
                e.currentTarget.classList.toggle('active');

                // Persist state via URL parameters (or session in backend)
                const isTritanopia = document.body.classList.contains('tritanopie') ? '1' : '0';
                const url = new URL(window.location.href);
                url.searchParams.set('tritanopia', isTritanopia);
                window.location.href = url.toString();
            });
        }
    }

    /**
     * Automatically dismisses success/error toast messages after 5 seconds.
     */
    initAutoDismissMessages() {
        const messages = document.querySelectorAll('.message, .success-message, .error-message');
        
        messages.forEach(msg => {
            // Exclude chatbot messages from auto-dismissal
            if(!msg.classList.contains('user-message') && !msg.classList.contains('bot-message')) {
                setTimeout(() => {
                    msg.style.transition = 'opacity 0.5s ease';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                }, 5000);
            }
        });
    }

    /**
     * Changes the application language and reloads the page.
     * @param {string} lang - The language code (e.g., 'fr', 'en').
     */
    changeLang(lang) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', lang);
        window.location.href = url.toString();
    }
}

// Instantiate and provide a fallback for generic onclick calls in HTML
document.addEventListener('DOMContentLoaded', () => {
    window.mainApp = new MainApp();
    
    // Fallback if some HTML still uses onclick="changeLang('en')" directly
    window.changeLang = (lang) => window.mainApp.changeLang(lang);
});