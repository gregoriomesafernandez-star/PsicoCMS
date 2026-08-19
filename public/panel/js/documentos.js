document.addEventListener("DOMContentLoaded", () => {

    const campo = document.getElementById("contenido");

    if (!campo) {
        return;
    }

    const insertarEnTextarea = (texto) => {
        const inicio = campo.selectionStart ?? campo.value.length;
        const fin = campo.selectionEnd ?? campo.value.length;

        campo.value = campo.value.slice(0, inicio) + texto + campo.value.slice(fin);
        campo.focus();
        campo.setSelectionRange(inicio + texto.length, inicio + texto.length);
    };

    const insertar = (texto) => {
        const editor = typeof PsicoEditor !== "undefined" ? PsicoEditor.instancia(campo) : null;

        if (editor) {
            editor.selection.focus();
            editor.selection.insertHTML(texto);
        } else {
            insertarEnTextarea(texto);
        }

        PsicoToast.mostrar(`${texto} anadido a la plantilla.`, "exito", 2500);
    };

    document.querySelectorAll("[data-insertar]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            insertar(boton.dataset.insertar);
        });
    });

});
