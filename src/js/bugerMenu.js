document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.querySelector('.burger_menu');
    const navMobile = document.querySelector('.nav_mobile');
    const closeBnt = document.querySelector('.closeMenu');

    burgerMenu.addEventListener('click', () => {
        navMobile.classList.add('open');
    });

    closeBnt.addEventListener('click', () => {
        navMobile.classList.remove('open');
    });

    document.addEventListener('click', (e) => {
        if (!navMobile.contains(e.target) && !burgerMenu.contains(e.target) && navMobile.classList.contains('open')){
            navMobile.classList.remove('open');
        }
    });
});