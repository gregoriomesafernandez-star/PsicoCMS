document.addEventListener("DOMContentLoaded", () => {

    const nodoDatos = document.getElementById("datos-categorias");

    if (!nodoDatos) {
        return;
    }

    const datos = JSON.parse(nodoDatos.textContent);

    const formulario = document.getElementById("form-categoria");
    const metodo = document.getElementById("categoria-metodo");
    const titulo = document.getElementById("modal-categoria-titulo");

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

    const ponerValor = (nombre, valor) => {
        const campo = formulario.querySelector(`[name="${nombre}"]`);

        if (campo) {
            campo.value = valor ?? "";
        }
    };

    const abrirNueva = () => {
        formulario.reset();
        formulario.action = datos.urlCrear;
        metodo.value = "POST";
        cambiarTitulo("Nueva categoria");
        PsicoModal.abrir("modal-categoria");
    };

    const abrirEdicion = (categoria) => {
        formulario.action = `${datos.urlBase}/${categoria.id}`;
        metodo.value = "PUT";

        ponerValor("nombre", categoria.nombre);
        ponerValor("descripcion", categoria.descripcion);
        ponerValor("orden", categoria.orden);

        cambiarTitulo("Editar categoria");
        PsicoModal.abrir("modal-categoria");
    };

    document.querySelectorAll("#boton-nueva-categoria, [data-nueva-categoria]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirNueva();
        });
    });

    document.querySelectorAll("[data-editar-categoria]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirEdicion(JSON.parse(boton.dataset.categoria));
        });
    });

    if (datos.errores) {
        PsicoModal.abrir("modal-categoria");
    }

});
