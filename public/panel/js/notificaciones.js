document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll("[data-ver-password]").forEach((boton) => {
        const campo = document.getElementById(boton.dataset.verPassword);

        if (!campo) {
            return;
        }

        boton.addEventListener("click", (evento) => {
            evento.preventDefault();

            const oculta = campo.type === "password";
            const icono = boton.querySelector("i");

            campo.type = oculta ? "text" : "password";
            boton.setAttribute("aria-label", oculta ? "Ocultar la contrasena" : "Mostrar la contrasena");

            if (icono) {
                icono.classList.toggle("fa-eye", !oculta);
                icono.classList.toggle("fa-eye-slash", oculta);
            }
        });
    });

});
