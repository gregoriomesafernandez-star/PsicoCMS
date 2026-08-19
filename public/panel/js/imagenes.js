/**
 * Previsualiza la imagen elegida antes de subirla, en el mismo hueco donde se
 * vera en la web.
 */
document.addEventListener("DOMContentLoaded", () => {

    /* ---------- Ver la imagen a tamano completo ---------- */

    const visor = document.getElementById("visor-imagen");
    const abrir = document.getElementById("visor-abrir");
    const titulo = document.getElementById("modal-imagen-titulo");

    const rotular = (texto) => {
        if (!titulo) {
            return;
        }

        const icono = titulo.querySelector("i");
        titulo.textContent = " " + texto;

        if (icono) {
            titulo.insertBefore(icono, titulo.firstChild);
        }
    };

    document.querySelectorAll("[data-ver-imagen]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();

            if (!visor) {
                return;
            }

            const url = boton.dataset.verImagen;

            visor.src = url;
            visor.alt = boton.dataset.titulo || "";

            if (abrir) {
                abrir.href = url;
            }

            rotular(boton.dataset.titulo || "Vista de la imagen");
            PsicoModal.abrir("modal-imagen");
        });
    });

    /* ---------- Previsualizacion antes de subir ---------- */

    document.querySelectorAll("[data-vista-previa]").forEach((campo) => {
        const hueco = campo.closest(".hueco");
        const vista = hueco ? hueco.querySelector(".hueco__imagen") : null;

        if (!vista) {
            return;
        }

        const original = vista.src;
        const lupa = hueco.querySelector("[data-ver-imagen]");

        campo.addEventListener("change", () => {
            const archivo = campo.files && campo.files[0];

            if (!archivo || !archivo.type.startsWith("image/")) {
                vista.src = original;

                if (lupa) {
                    lupa.dataset.verImagen = original;
                }

                return;
            }

            const anterior = vista.dataset.objeto;

            if (anterior) {
                URL.revokeObjectURL(anterior);
            }

            const url = URL.createObjectURL(archivo);

            vista.dataset.objeto = url;
            vista.src = url;

            if (lupa) {
                lupa.dataset.verImagen = url;
            }
        });
    });

});
