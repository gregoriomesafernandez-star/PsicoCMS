document.addEventListener("DOMContentLoaded", () => {

    /* ---------- Pestanas ---------- */

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

    const existe = (nombre) => Array.from(botones).some((boton) => boton.dataset.pestana === nombre);

    botones.forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();

            const objetivo = boton.dataset.pestana;

            mostrar(objetivo);
            window.history.replaceState(null, "", "#" + objetivo);
        });
    });

    // El servidor no ve el ancla, asi que la pestana de vuelta tras un error de
    // validacion se restaura aqui.
    const ancla = window.location.hash.replace("#", "");

    if (ancla && existe(ancla)) {
        mostrar(ancla);
    }

    /* ---------- Vista previa del resultado en Google ---------- */

    const espejos = [
        ["meta_title", "titulo"],
        ["meta_description", "descripcion"],
    ];

    espejos.forEach(([nombre, destino]) => {
        const campo = document.getElementById(nombre);
        const vista = document.querySelector(`[data-vista="${destino}"]`);

        if (!campo || !vista) {
            return;
        }

        const original = vista.textContent;

        campo.addEventListener("input", () => {
            vista.textContent = campo.value.trim() || original;
        });
    });

    /* ---------- Alta y edicion de planes ---------- */

    const nodoDatos = document.getElementById("datos-planes");
    const formulario = document.getElementById("form-plan");

    if (!nodoDatos || !formulario) {
        return;
    }

    const datos = JSON.parse(nodoDatos.textContent);
    const metodo = document.getElementById("plan-metodo");
    const titulo = document.getElementById("modal-plan-titulo");

    const cambiarTitulo = (texto) => {
        if (!titulo) {
            return;
        }

        const icono = titulo.querySelector("i");
        titulo.textContent = " " + texto;

        if (icono) {
            titulo.insertBefore(icono, titulo.firstChild);
        }
    };

    const abrirNuevo = () => {
        formulario.reset();
        formulario.action = datos.urlCrear;
        metodo.value = "POST";

        cambiarTitulo("Nuevo plan");
        PsicoModal.abrir("modal-plan");
    };

    const abrirEdicion = (plan) => {
        formulario.reset();
        formulario.action = `${datos.urlBase}/${plan.id}`;
        metodo.value = "PUT";

        document.getElementById("plan-nombre").value = plan.nombre ?? "";
        document.getElementById("plan-modalidad").value = plan.modalidad ?? "presencial";
        document.getElementById("plan-precio").value = plan.precio ?? "";
        document.getElementById("plan-descripcion").value = plan.descripcion ?? "";
        document.getElementById("plan-caracteristicas").value = plan.caracteristicas ?? "";
        formulario.querySelector('[name="destacado"]').checked = Boolean(plan.destacado);

        cambiarTitulo("Editar plan");
        PsicoModal.abrir("modal-plan");
    };

    document.querySelectorAll("#boton-nuevo-plan, [data-nuevo-plan]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirNuevo();
        });
    });

    document.querySelectorAll("[data-editar-plan]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirEdicion(JSON.parse(boton.dataset.plan));
        });
    });

    if (datos.errores && ancla === "planes") {
        PsicoModal.abrir("modal-plan");
    }

});
