document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modal-confirmar");
    const mensaje = document.getElementById("modal-confirmar-mensaje");
    const aceptar = document.getElementById("modal-confirmar-aceptar");
    const titulo = document.getElementById("modal-confirmar-titulo");

    if (!modal || !aceptar) {
        return;
    }

    let formularioPendiente = null;

    document.querySelectorAll("[data-confirmar]").forEach((formulario) => {
        formulario.addEventListener("submit", (evento) => {
            evento.preventDefault();

            formularioPendiente = formulario;
            mensaje.textContent = formulario.dataset.confirmarMensaje || "Esta accion no se puede deshacer.";

            if (titulo) {
                const icono = titulo.querySelector("i");
                titulo.textContent = " " + (formulario.dataset.confirmarTitulo || "Confirmar");

                if (icono) {
                    titulo.insertBefore(icono, titulo.firstChild);
                }
            }

            PsicoModal.abrir("modal-confirmar");
        });
    });

    aceptar.addEventListener("click", (evento) => {
        evento.preventDefault();

        if (!formularioPendiente) {
            return;
        }

        aceptar.disabled = true;
        formularioPendiente.submit();
    });

});
