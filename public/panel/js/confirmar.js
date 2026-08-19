document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modal-confirmar");
    const mensaje = document.getElementById("modal-confirmar-mensaje");
    const aceptar = document.getElementById("modal-confirmar-aceptar");
    const titulo = document.getElementById("modal-confirmar-titulo");

    if (!modal || !aceptar) {
        return;
    }

    const BOTON_POR_DEFECTO = "Si, eliminar";
    const ICONO_POR_DEFECTO = "fa-trash-can";

    let formularioPendiente = null;

    const rotular = (elemento, texto, icono) => {
        if (!elemento) {
            return;
        }

        const marca = elemento.querySelector("i");
        elemento.textContent = " " + texto;

        if (marca) {
            if (icono) {
                marca.className = "fa-solid " + icono;
            }

            elemento.insertBefore(marca, elemento.firstChild);
        }
    };

    document.querySelectorAll("[data-confirmar]").forEach((formulario) => {
        formulario.addEventListener("submit", (evento) => {
            evento.preventDefault();

            formularioPendiente = formulario;
            mensaje.textContent = formulario.dataset.confirmarMensaje || "Esta accion no se puede deshacer.";

            rotular(titulo, formulario.dataset.confirmarTitulo || "Confirmar");

            rotular(
                aceptar,
                formulario.dataset.confirmarBoton || BOTON_POR_DEFECTO,
                formulario.dataset.confirmarIcono || ICONO_POR_DEFECTO
            );

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
