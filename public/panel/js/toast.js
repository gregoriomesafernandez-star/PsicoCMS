const PsicoToast = (() => {

    const ICONOS = {
        exito: "fa-circle-check",
        error: "fa-circle-exclamation",
        info: "fa-circle-info",
        atencion: "fa-triangle-exclamation"
    };

    const mostrar = (texto, tono = "info", duracion = 5000) => {
        const contenedor = document.getElementById("toasts");

        if (!contenedor) {
            return;
        }

        const toast = document.createElement("div");
        toast.className = `toast toast--${tono}`;
        toast.setAttribute("role", "status");

        const icono = document.createElement("i");
        icono.className = `toast__icono fa-solid ${ICONOS[tono] || ICONOS.info}`;

        const parrafo = document.createElement("p");
        parrafo.className = "toast__texto";
        parrafo.textContent = texto;

        const cerrar = document.createElement("button");
        cerrar.className = "toast__cerrar";
        cerrar.type = "button";
        cerrar.setAttribute("aria-label", "Cerrar aviso");

        const aspa = document.createElement("i");
        aspa.className = "fa-solid fa-xmark";
        cerrar.appendChild(aspa);

        const retirar = () => {
            toast.classList.add("toast--saliendo");
            setTimeout(() => toast.remove(), 250);
        };

        cerrar.addEventListener("click", (evento) => {
            evento.preventDefault();
            retirar();
        });

        toast.appendChild(icono);
        toast.appendChild(parrafo);
        toast.appendChild(cerrar);
        contenedor.appendChild(toast);

        if (duracion > 0) {
            setTimeout(retirar, duracion);
        }
    };

    document.addEventListener("DOMContentLoaded", () => {
        const flash = document.getElementById("mensaje-flash");

        if (!flash) {
            return;
        }

        try {
            const datos = JSON.parse(flash.textContent);
            mostrar(datos.texto, datos.tono);
        } catch (error) {
            return;
        }
    });

    return { mostrar };

})();
