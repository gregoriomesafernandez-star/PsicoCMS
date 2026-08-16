window.addEventListener("load", () => {
    const btnTop = document.querySelector('.container-go-top__btn-go-top');


    window.addEventListener('scroll', function(){
        if (window.scrollY > 400) {
            btnTop.style.display = 'block';
        }else{
            btnTop.style.display = 'none';
        }
    });

    btnTop.addEventListener('click', function(){
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
});
