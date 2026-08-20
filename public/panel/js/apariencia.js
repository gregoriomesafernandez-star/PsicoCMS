/**
 * Tema claro/oscuro y color principal del panel.
 *
 * El servidor ya pinta data-tema y data-color en <html>, asi que la pagina
 * carga con el aspecto correcto y no hay parpadeo. Aqui solo se previsualiza
 * en vivo mientras se elige, y se revierte si se cierra sin guardar.
 */
document.addEventListener("DOMContentLoaded", () => {

    const formulario = document.getElementById("form-apariencia");

    if (!formulario) {
        return;
    }

    const raiz = document.documentElement;

    const guardado = {
        tema: raiz.dataset.tema,
        color: raiz.dataset.color,
    };

    const aplicar = (tema, color) => {
        raiz.dataset.tema = tema;
        raiz.dataset.color = color;

        // Jodit decide su tema al crearse, asi que los editores ya montados
        // se cambian a mano para que la previsualizacion sea completa.
        document.querySelectorAll(".jodit-container").forEach((editor) => {
            editor.classList.toggle("jodit_theme_dark", tema === "oscuro");
        });
    };

    const elegido = (nombre, porDefecto) => {
        const marcado = formulario.querySelector(`[name="${nombre}"]:checked`);

        return marcado ? marcado.value : porDefecto;
    };

    const previsualizar = () => {
        aplicar(elegido("tema", guardado.tema), elegido("color", guardado.color));
    };

    const restaurar = () => {
        aplicar(guardado.tema, guardado.color);

        formulario.querySelectorAll('[name="tema"], [name="color"]').forEach((entrada) => {
            entrada.checked = entrada.value === guardado[entrada.name];
        });
    };

    formulario.querySelectorAll('[name="tema"], [name="color"]').forEach((entrada) => {
        entrada.addEventListener("change", previsualizar);
    });

    document.querySelectorAll("[data-abrir-apariencia]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            PsicoModal.abrir("modal-apariencia");
        });
    });

    // Cerrar por donde sea (Cancelar, la X, Escape o pulsando fuera) descarta
    // la eleccion: todas esas vias acaban poniendo el modal en hidden.
    const modal = document.getElementById("modal-apariencia");

    if (modal) {
        new MutationObserver(() => {
            if (modal.hidden) {
                restaurar();
            }
        }).observe(modal, { attributes: true, attributeFilter: ["hidden"] });
    }

    // Al enviar, lo elegido pasa a ser lo guardado: ya no hay que revertir nada.
    formulario.addEventListener("submit", () => {
        guardado.tema = elegido("tema", guardado.tema);
        guardado.color = elegido("color", guardado.color);
    });

});
