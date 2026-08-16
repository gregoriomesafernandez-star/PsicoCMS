

window.addEventListener("load", () => {

    const nav = document.querySelector('.layout__nav');

    window.addEventListener('scroll', function(){
        if (window.scrollY > 100) {
            nav.classList.add('fixed');
        }else{
            nav.classList.remove('fixed');
        }
    });


});
