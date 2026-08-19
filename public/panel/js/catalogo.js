/**
 * Alta y edicion en modal de las listas ordenables del contenido web
 * (servicios y especialidades). Las dos pantallas comparten markup:
 * cambian los nombres de los campos, que se leen del propio JSON de la fila.
 */
document.addEventListener("DOMContentLoaded", () => {

    const nodoDatos = document.getElementById("datos-catalogo");

    if (!nodoDatos) {
        return;
    }

    const datos = JSON.parse(nodoDatos.textContent);
    const formulario = document.getElementById(datos.formulario);
    const metodo = document.getElementById(datos.metodo);

    if (!formulario) {
        return;
    }

    const titulo = document.getElementById(datos.modal + "-titulo");
    const imagenActual = formulario.querySelector("[data-imagen-actual]");
    const imagenVista = formulario.querySelector("[data-imagen-vista]");

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

    const ponerDescripcion = (html) => {
        if (typeof PsicoEditor !== "undefined") {
            PsicoEditor.poner(datos.editor, html || "");
            return;
        }

        const campo = document.getElementById(datos.editor);

        if (campo) {
            campo.value = html || "";
        }
    };

    const marcarIcono = (valor) => {
        formulario.querySelectorAll('[name="icono"]').forEach((entrada) => {
            entrada.checked = entrada.value === valor;
        });
    };

    const mostrarImagen = (url) => {
        if (!imagenActual || !imagenVista) {
            return;
        }

        imagenActual.hidden = !url;
        imagenVista.src = url || "";
    };

    const abrirNuevo = () => {
        formulario.reset();
        formulario.action = datos.urlCrear;
        metodo.value = "POST";

        ponerDescripcion("");
        marcarIcono(null);
        mostrarImagen(null);

        const visible = formulario.querySelector('[name="activo"]');

        if (visible) {
            visible.checked = true;
        }

        cambiarTitulo(datos.tituloNuevo);
        PsicoModal.abrir(datos.modal);
    };

    const abrirEdicion = (item) => {
        formulario.reset();
        formulario.action = `${datos.urlBase}/${item.id}`;
        metodo.value = "PUT";

        Object.entries(item).forEach(([clave, valor]) => {
            const campo = formulario.querySelector(`[name="${clave}"]`);

            if (!campo || clave === "icono" || clave === "descripcion") {
                return;
            }

            if (campo.type === "checkbox") {
                campo.checked = Boolean(valor);
                return;
            }

            campo.value = valor ?? "";
        });

        marcarIcono(item.icono);
        mostrarImagen(item.urlImagen);

        cambiarTitulo(datos.tituloEditar);
        PsicoModal.abrir(datos.modal);

        // El editor se crea al mostrarse el modal, asi que su contenido va despues.
        window.requestAnimationFrame(() => ponerDescripcion(item.descripcion));
    };

    document.querySelectorAll("[data-nuevo]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirNuevo();
        });
    });

    document.querySelectorAll("[data-editar]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirEdicion(JSON.parse(boton.dataset.item));
        });
    });

    if (datos.errores) {
        PsicoModal.abrir(datos.modal);
    }

});
