

window.addEventListener("load", () => {

    const bannerImg = document.querySelector('.banner__container-img');
    const bannerSandwich = document.querySelector('.banner__sandwich');
    const bannerTitle = document.querySelector('.banner__title');
    const bannerAppointment  = document.querySelector('.banner__appointment');

    bannerImg.classList.add('showBanner')
    bannerSandwich.classList.add('showSandwich')
    bannerTitle.classList.add('showText')
    bannerAppointment.classList.add('showText')

});
