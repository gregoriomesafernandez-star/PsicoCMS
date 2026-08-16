document.addEventListener("DOMContentLoaded", () => {

    const boton = document.getElementById("ver-password");
    const password = document.getElementById("password");
    const telefono = document.getElementById("telefono");

    if (boton && password) {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();

            const oculta = password.type === "password";
            password.type = oculta ? "text" : "password";
            boton.setAttribute("aria-label", oculta ? "Ocultar la contrasena" : "Mostrar la contrasena");

            const icono = boton.querySelector("i");
            icono.className = oculta ? "fa-solid fa-eye-slash" : "fa-solid fa-eye";

            password.focus();
        });
    }

    if (telefono) {
        telefono.addEventListener("input", () => {
            const limpio = telefono.value.replace(/\D/g, "");

            if (telefono.value !== limpio) {
                telefono.value = limpio;
            }
        });
    }

});
