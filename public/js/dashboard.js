/* =========================================
   DASHBOARD UI LOGIC
   ========================================= */

function changeLang(lang) {
    const url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

document.addEventListener("DOMContentLoaded", () => {
    // Mobile Menu Toggle
    const menuToggle = document.createElement('button');
    menuToggle.classList.add('menu-toggle');
    menuToggle.innerHTML = '☰';
    
    const rightBtn = document.querySelector('.right-buttons');
    if(rightBtn) rightBtn.appendChild(menuToggle);

    const navMenu = document.querySelector('nav.menu');
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
});