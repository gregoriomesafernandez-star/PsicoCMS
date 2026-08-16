document.addEventListener("DOMContentLoaded", () => {

    const sidebar = document.getElementById("sidebar");
    const velo = document.querySelector(".panel__velo");
    const abrir = document.querySelector("[data-abrir-menu]");
    const cerrar = document.querySelectorAll("[data-cerrar-menu]");

    const alternarMenu = (visible) => {
        sidebar.classList.toggle("sidebar--abierta", visible);
        velo.hidden = !visible;
        document.body.style.overflow = visible ? "hidden" : "";
    };

    if (abrir) {
        abrir.addEventListener("click", (evento) => {
            evento.preventDefault();
            alternarMenu(true);
        });
    }

    cerrar.forEach((elemento) => {
        elemento.addEventListener("click", (evento) => {
            evento.preventDefault();
            alternarMenu(false);
        });
    });

    document.addEventListener("keydown", (evento) => {
        if (evento.key === "Escape" && sidebar.classList.contains("sidebar--abierta")) {
            alternarMenu(false);
        }
    });

    const CLAVE_GRUPOS = "psicocms.menu.grupos";

    const leerGrupos = () => {
        try {
            return JSON.parse(localStorage.getItem(CLAVE_GRUPOS)) || {};
        } catch (error) {
            return {};
        }
    };

    const guardarGrupos = (estado) => {
        localStorage.setItem(CLAVE_GRUPOS, JSON.stringify(estado));
    };

    const estado = leerGrupos();

    document.querySelectorAll(".menu__desplegable").forEach((boton) => {
        const lista = document.getElementById(boton.getAttribute("aria-controls"));
        const clave = lista.id;
        const tieneActivo = lista.querySelector(".menu__enlace--activo") !== null;

        if (!tieneActivo && Object.prototype.hasOwnProperty.call(estado, clave)) {
            const abierto = estado[clave];
            boton.setAttribute("aria-expanded", abierto ? "true" : "false");
            lista.hidden = !abierto;
        }

        boton.addEventListener("click", (evento) => {
            evento.preventDefault();

            const abierto = boton.getAttribute("aria-expanded") === "true";
            boton.setAttribute("aria-expanded", abierto ? "false" : "true");
            lista.hidden = abierto;

            estado[clave] = !abierto;
            guardarGrupos(estado);
        });
    });

    document.querySelectorAll(".menu__enlace--inactivo").forEach((enlace) => {
        enlace.addEventListener("click", (evento) => evento.preventDefault());
    });

    const desplegables = [
        { contenedor: "[data-menu-perfil]", menu: "menu-perfil" },
        { contenedor: "[data-menu-avisos]", menu: "menu-avisos" }
    ];

    desplegables.forEach(({ contenedor, menu }) => {
        const raiz = document.querySelector(contenedor);

        if (!raiz) {
            return;
        }

        const boton = raiz.querySelector("button");
        const panel = document.getElementById(menu);

        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            evento.stopPropagation();

            const abierto = boton.getAttribute("aria-expanded") === "true";

            document.querySelectorAll("[data-menu-perfil] button, [data-menu-avisos] button")
                .forEach((otro) => otro.setAttribute("aria-expanded", "false"));
            document.querySelectorAll(".perfil__menu, .avisos__menu")
                .forEach((otro) => { otro.hidden = true; });

            boton.setAttribute("aria-expanded", abierto ? "false" : "true");
            panel.hidden = abierto;
        });
    });

    document.addEventListener("click", (evento) => {
        if (evento.target.closest("[data-menu-perfil], [data-menu-avisos]")) {
            return;
        }

        document.querySelectorAll("[data-menu-perfil] button, [data-menu-avisos] button")
            .forEach((boton) => boton.setAttribute("aria-expanded", "false"));
        document.querySelectorAll(".perfil__menu, .avisos__menu")
            .forEach((panel) => { panel.hidden = true; });
    });

});
