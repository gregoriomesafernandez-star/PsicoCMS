/**
 * Contador de caracteres para cualquier campo con data-contador y maxlength.
 * Vive en el layout porque lo usan varias secciones del panel.
 */
document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll("[data-contador]").forEach((campo) => {
        const maximo = Number(campo.getAttribute("maxlength"));

        if (!maximo) {
            return;
        }

        const contador = document.createElement("p");
        contador.className = "campo__contador";

        const pintar = () => {
            contador.textContent = `${campo.value.length} / ${maximo} caracteres`;
            contador.classList.toggle("campo__contador--limite", campo.value.length >= maximo);
        };

        campo.insertAdjacentElement("afterend", contador);
        campo.addEventListener("input", pintar);
        pintar();
    });

});
