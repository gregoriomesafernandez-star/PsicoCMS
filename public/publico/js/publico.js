document.addEventListener("DOMContentLoaded", () => {

    /* ---------- Acordeon de preguntas frecuentes ---------- */

    document.querySelectorAll(".acordeon-web__cabecera").forEach((cabecera) => {
        cabecera.addEventListener("click", (evento) => {
            evento.preventDefault();

            const pieza = cabecera.closest(".acordeon-web__pieza");

            if (!pieza) {
                return;
            }

            const abierta = cabecera.getAttribute("aria-expanded") === "true";

            cabecera.setAttribute("aria-expanded", abierta ? "false" : "true");
            pieza.classList.toggle("acordeon-web__pieza--abierta", !abierta);
        });
    });

    /* ---------- Scroll suave entre secciones (modo landing) ---------- */

    document.querySelectorAll('a[href*="#"]').forEach((enlace) => {
        enlace.addEventListener("click", (evento) => {
            const destino = enlace.getAttribute("href").split("#")[1];

            if (!destino) {
                return;
            }

            // Solo si el ancla apunta a esta misma pagina.
            const enlaceMismaPagina = enlace.pathname === window.location.pathname
                && enlace.host === window.location.host;

            if (!enlaceMismaPagina) {
                return;
            }

            const seccion = document.getElementById(destino);

            if (!seccion) {
                return;
            }

            evento.preventDefault();
            seccion.scrollIntoView({ behavior: "smooth", block: "start" });
            window.history.replaceState(null, "", "#" + destino);

            const menuMovil = document.querySelector(".layout__nav-mobile");

            if (menuMovil) {
                menuMovil.classList.remove("nav-mobile-active");
            }
        });
    });

    /* ---------- Enlace activo segun la seccion visible ---------- */

    const enlaces = Array.from(document.querySelectorAll(".list-nav__link[href*='#']"));
    const secciones = enlaces
        .map((enlace) => document.getElementById(enlace.getAttribute("href").split("#")[1]))
        .filter(Boolean);

    const marcar = (id) => {
        enlaces.forEach((enlace) => {
            const suyo = enlace.getAttribute("href").endsWith("#" + id);

            enlace.classList.toggle("active", suyo);
            enlace.removeAttribute("aria-current");

            if (suyo) {
                enlace.setAttribute("aria-current", "true");
            }
        });
    };

    // La pagina arranca sin ningun enlace resaltado; solo se marca uno cuando
    // el visitante pulsa en el menu o se mueve por la pagina.
    let huboInteraccion = false;

    /*
     * Al pulsar en el menu, el destino manda hasta que el desplazamiento
     * termina. Sin esto se irian encendiendo una a una todas las secciones por
     * las que pasa la animacion: pulsar "Preguntas" iluminaba de camino
     * Servicios y Blog.
     *
     * El final del desplazamiento se detecta esperando a que dejen de llegar
     * eventos de scroll, que es lo unico que funciona en todos los navegadores.
     */
    let destinoFijado = null;
    let esperaDeParada = null;

    const soltarCuandoPare = () => {
        window.clearTimeout(esperaDeParada);
        esperaDeParada = window.setTimeout(() => {
            destinoFijado = null;
        }, 150);
    };

    enlaces.forEach((enlace) => {
        enlace.addEventListener("click", () => {
            const destino = enlace.getAttribute("href").split("#")[1];

            huboInteraccion = true;
            destinoFijado = destino;
            marcar(destino);
            soltarCuandoPare();
        });
    });

    window.addEventListener("scroll", () => {
        huboInteraccion = true;

        if (destinoFijado !== null) {
            soltarCuandoPare();
        }
    }, { passive: true });

    if (secciones.length > 0 && "IntersectionObserver" in window) {
        const observador = new IntersectionObserver((entradas) => {
            if (!huboInteraccion || destinoFijado !== null) {
                return;
            }

            entradas.forEach((entrada) => {
                if (entrada.isIntersecting) {
                    marcar(entrada.target.id);
                }
            });
        }, { rootMargin: "-45% 0px -50% 0px" });

        secciones.forEach((seccion) => observador.observe(seccion));
    }

});
