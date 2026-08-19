document.addEventListener("DOMContentLoaded", () => {

    /* ---------- Alta y edicion en el modal ---------- */

    const nodoDatos = document.getElementById("datos-faqs");
    const formulario = document.getElementById("form-faq");

    if (nodoDatos && formulario) {
        const datos = JSON.parse(nodoDatos.textContent);
        const metodo = document.getElementById("faq-metodo");
        const titulo = document.getElementById("modal-faq-titulo");
        // El id del componente toggle cae en la etiqueta, no en el input.
        const visible = formulario.querySelector('[name="activa"]');

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

        const ponerRespuesta = (html) => {
            if (typeof PsicoEditor !== "undefined") {
                PsicoEditor.poner("faq-respuesta", html || "");
                return;
            }

            const campo = document.getElementById("faq-respuesta");

            if (campo) {
                campo.value = html || "";
            }
        };

        const abrirNueva = () => {
            formulario.reset();
            formulario.action = datos.urlCrear;
            metodo.value = "POST";

            ponerRespuesta("");
            visible.checked = true;

            cambiarTitulo("Nueva pregunta");
            PsicoModal.abrir("modal-faq");
        };

        const abrirEdicion = (faq) => {
            formulario.action = `${datos.urlBase}/${faq.id}`;
            metodo.value = "PUT";

            document.getElementById("faq-pregunta").value = faq.pregunta ?? "";
            visible.checked = Boolean(faq.activa);

            cambiarTitulo("Editar pregunta");
            PsicoModal.abrir("modal-faq");

            // Jodit se crea al mostrarse el modal, asi que el contenido se pone despues.
            window.requestAnimationFrame(() => ponerRespuesta(faq.respuesta));
        };

        document.querySelectorAll("#boton-nueva-faq, [data-nueva-faq]").forEach((boton) => {
            boton.addEventListener("click", (evento) => {
                evento.preventDefault();
                abrirNueva();
            });
        });

        document.querySelectorAll("[data-editar-faq]").forEach((boton) => {
            boton.addEventListener("click", (evento) => {
                evento.preventDefault();
                abrirEdicion(JSON.parse(boton.dataset.faq));
            });
        });

        if (datos.errores) {
            PsicoModal.abrir("modal-faq");
        }
    }

    /* ---------- Acordeon de la vista previa ---------- */

    document.querySelectorAll(".acordeon__cabecera").forEach((cabecera) => {
        cabecera.addEventListener("click", (evento) => {
            evento.preventDefault();

            const respuesta = document.getElementById(cabecera.getAttribute("aria-controls"));

            if (!respuesta) {
                return;
            }

            const abierta = cabecera.getAttribute("aria-expanded") === "true";

            cabecera.setAttribute("aria-expanded", abierta ? "false" : "true");
            respuesta.hidden = abierta;
        });
    });

});
