document.addEventListener("DOMContentLoaded", () => {

    const formulario = document.getElementById("form-articulo");

    if (!formulario) {
        return;
    }

    const campoTitulo = document.getElementById("titulo");
    const campoSlug = document.getElementById("slug");
    const campoImagen = document.getElementById("imagen");
    const vistaImagen = document.getElementById("vista-imagen");
    const vistaVacia = document.getElementById("vista-vacia");
    const casillaQuitar = document.getElementById("eliminar_imagen");

    const DIACRITICOS = /[̀-ͯ]/g;

    const aSlug = (texto) => texto
        .toLowerCase()
        .normalize("NFD")
        .replace(DIACRITICOS, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");

    if (campoTitulo && campoSlug) {
        let slugManual = campoSlug.value.trim() !== "";

        campoSlug.addEventListener("input", () => {
            slugManual = campoSlug.value.trim() !== "";
        });

        campoTitulo.addEventListener("input", () => {
            if (!slugManual) {
                campoSlug.value = aSlug(campoTitulo.value);
            }
        });
    }

    if (campoImagen && vistaImagen && vistaVacia) {
        campoImagen.addEventListener("change", () => {
            const archivo = campoImagen.files[0];

            if (!archivo) {
                return;
            }

            vistaImagen.src = URL.createObjectURL(archivo);
            vistaImagen.classList.remove("oculto");
            vistaVacia.classList.add("oculto");

            if (casillaQuitar) {
                casillaQuitar.checked = false;
            }
        });
    }

    if (casillaQuitar && vistaImagen && vistaVacia) {
        casillaQuitar.addEventListener("change", () => {
            vistaImagen.classList.toggle("oculto", casillaQuitar.checked);
            vistaVacia.classList.toggle("oculto", !casillaQuitar.checked);
        });
    }

});
