document.addEventListener("DOMContentLoaded", () => {
    const configEl = document.getElementById('app-config');
    window.AppConfig = {
        lang: configEl ? configEl.dataset.lang : 'fr',
        role: configEl ? configEl.dataset.role : 'student'
    };

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

    const langBtn = document.querySelector('.dropbtn');
    if (langBtn) {
        langBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const dropdown = langBtn.parentElement;
            if(dropdown) dropdown.classList.toggle('show');
        });
    }

    document.addEventListener('click', () => {
        const dropdown = document.querySelector('.lang-dropdown');
        if (dropdown) dropdown.classList.remove('show');
    });

    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
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

    const messages = document.querySelectorAll('.message, .success-message, .error-message');
    messages.forEach(msg => {
        setTimeout(() => {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 500);
        }, 5000);
    });
});

function changeLang(lang) {
    const url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

