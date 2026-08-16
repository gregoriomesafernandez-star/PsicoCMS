

window.addEventListener("load", () => {
    const layoutBg = document.querySelector('.layout__background');
    const btnPlay = document.querySelector('.characteristics__btn-play');
    const frameEl = document.querySelector('.characteristics__container-frame');
    const btnClose = document.querySelector('.container-frame__btn-close');
    const videoContainer = document.querySelector('.characteristics__video-container');


    btnPlay.addEventListener('click', function () {
        layoutBg.classList.add('showBg');
        frameEl.classList.add('showVideo');
    });

    btnClose.addEventListener('click', function () {
        layoutBg.classList.remove('showBg');
        frameEl.classList.remove('showVideo');
        videoPause();
    });

    function videoPause () {
        let iframe = videoContainer.querySelector('iframe');
        let video = videoContainer.querySelector('video');
        
        if (iframe) {
            let iframeSrc = iframe.src;
            iframe.src = iframeSrc;
        }
        if (video) {
            video.pause();
        }
    }

});
