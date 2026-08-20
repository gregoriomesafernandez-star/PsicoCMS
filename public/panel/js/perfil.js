document.addEventListener("DOMContentLoaded", () => {

    const botones = document.querySelectorAll("[data-pestana]");

    const mostrar = (objetivo) => {
        botones.forEach((boton) => {
            const activo = boton.dataset.pestana === objetivo;
            boton.classList.toggle("pestanas__boton--activo", activo);
            boton.setAttribute("aria-selected", activo ? "true" : "false");
        });

        document.querySelectorAll("[data-panel]").forEach((panel) => {
            panel.hidden = panel.dataset.panel !== objetivo;
        });
    };

    botones.forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            mostrar(boton.dataset.pestana);
            window.history.replaceState(null, "", "#" + boton.dataset.pestana);
        });
    });

    const ancla = window.location.hash.replace("#", "");

    if (ancla && Array.from(botones).some((boton) => boton.dataset.pestana === ancla)) {
        mostrar(ancla);
    }

});
