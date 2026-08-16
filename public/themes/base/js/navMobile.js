

window.addEventListener("load", () => {

    const btnMobile = document.querySelector('.nav__btn-menu-mobile');
    const menuMobile = document.querySelector('.layout__nav-mobile');
    const btnClose = document.querySelector('.header__btn-close');

    btnMobile.addEventListener('click', function () {
        menuMobile.classList.toggle('show');
    });

    btnClose.addEventListener('click', function () {
        menuMobile.classList.toggle('show');
    });

    window.addEventListener('resize', function () {
        menuMobile.classList.remove('show');
    });
});
