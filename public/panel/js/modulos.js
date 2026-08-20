/**
 * La etiqueta del interruptor y el aspecto de la fila responden al momento,
 * antes de guardar, para que se vea que el cambio esta hecho.
 */
document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".modulo").forEach((fila) => {
        const entrada = fila.querySelector(".toggle__entrada");
        const texto = fila.querySelector(".toggle__texto");

        if (!entrada) {
            return;
        }

        entrada.addEventListener("change", () => {
            fila.classList.toggle("modulo--apagado", !entrada.checked);

            if (texto) {
                texto.textContent = entrada.checked ? "Encendido" : "Apagado";
            }
        });
    });

});
