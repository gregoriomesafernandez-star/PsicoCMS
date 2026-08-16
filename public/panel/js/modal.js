const PsicoModal = (() => {

    let ultimoFoco = null;

    const abrir = (id) => {
        const modal = document.getElementById(id);

        if (!modal) {
            return null;
        }

        ultimoFoco = document.activeElement;
        modal.hidden = false;
        document.body.style.overflow = "hidden";

        const enfocable = modal.querySelector("button, [href], input, select, textarea");

        if (enfocable) {
            enfocable.focus();
        }

        return modal;
    };

    const cerrar = (modal) => {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        document.body.style.overflow = "";

        if (ultimoFoco) {
            ultimoFoco.focus();
            ultimoFoco = null;
        }
    };

    const cerrarTodos = () => {
        document.querySelectorAll(".modal:not([hidden])").forEach(cerrar);
    };

    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("[data-cerrar-modal]").forEach((elemento) => {
            elemento.addEventListener("click", (evento) => {
                evento.preventDefault();
                cerrar(elemento.closest(".modal"));
            });
        });

        document.addEventListener("keydown", (evento) => {
            if (evento.key === "Escape") {
                cerrarTodos();
            }
        });
    });

    return { abrir, cerrar, cerrarTodos };

})();
