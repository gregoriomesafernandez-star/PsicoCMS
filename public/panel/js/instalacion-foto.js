document.addEventListener("DOMContentLoaded", () => {

    const entrada = document.getElementById("foto");
    const vista = document.getElementById("foto-vista");

    if (!entrada || !vista) {
        return;
    }

    entrada.addEventListener("change", () => {
        const archivo = entrada.files[0];

        while (vista.firstChild) {
            vista.removeChild(vista.firstChild);
        }

        if (!archivo) {
            const icono = document.createElement("i");
            icono.className = "fa-solid fa-user";
            vista.appendChild(icono);
            return;
        }

        const imagen = document.createElement("img");
        imagen.src = URL.createObjectURL(archivo);
        imagen.alt = "Vista previa de tu foto";
        imagen.addEventListener("load", () => URL.revokeObjectURL(imagen.src));
        vista.appendChild(imagen);
    });

});
