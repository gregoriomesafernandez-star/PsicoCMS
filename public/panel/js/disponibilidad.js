document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll("[data-pestana]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();

            const objetivo = boton.dataset.pestana;

            document.querySelectorAll("[data-pestana]").forEach((otro) => {
                const activo = otro === boton;
                otro.classList.toggle("pestanas__boton--activo", activo);
                otro.setAttribute("aria-selected", activo ? "true" : "false");
            });

            document.querySelectorAll("[data-panel]").forEach((panel) => {
                panel.hidden = panel.dataset.panel !== objetivo;
            });
        });
    });

    document.querySelectorAll("[data-descanso]").forEach((tarjeta) => {
        const modalidad = tarjeta.dataset.descanso;
        const toggle = tarjeta.querySelector(".toggle__entrada");
        const minutos = tarjeta.querySelector(".campo__control");
        const resultado = tarjeta.querySelector("[data-resultado]");
        const duracion = document.getElementById(`duracion_${modalidad}`);

        if (!toggle || !minutos) {
            return;
        }

        const sincronizar = () => {
            minutos.readOnly = !toggle.checked;
            tarjeta.classList.toggle("descanso__tarjeta--activa", toggle.checked);

            if (!resultado || !duracion) {
                return;
            }

            const sesion = parseInt(duracion.value, 10) || 0;
            const pausa = toggle.checked ? (parseInt(minutos.value, 10) || 0) : 0;
            const total = sesion + pausa;

            while (resultado.firstChild) {
                resultado.removeChild(resultado.firstChild);
            }

            if (total <= 0) {
                return;
            }

            const texto = document.createTextNode("Cada hueco ocupara ");
            const fuerte = document.createElement("strong");
            fuerte.textContent = `${total} minutos`;
            const cierre = document.createTextNode(
                pausa > 0 ? ` (${sesion} de sesion + ${pausa} de descanso).` : ` (sin descanso).`
            );

            resultado.appendChild(texto);
            resultado.appendChild(fuerte);
            resultado.appendChild(cierre);
        };

        sincronizar();

        toggle.addEventListener("change", sincronizar);
        minutos.addEventListener("input", sincronizar);

        if (duracion) {
            duracion.addEventListener("input", sincronizar);
        }
    });

    const formModo = document.getElementById("form-modo-vacaciones");
    const toggleModo = document.getElementById("modo-vacaciones");

    if (formModo && toggleModo) {
        toggleModo.addEventListener("change", () => formModo.submit());
    }

    const mensaje = document.getElementById("mensaje_vacaciones");
    const contador = document.querySelector("[data-contador-mensaje]");

    if (mensaje && contador) {
        const sincronizar = () => {
            contador.textContent = mensaje.value.length;
        };

        sincronizar();
        mensaje.addEventListener("input", sincronizar);
    }

    document.querySelectorAll(".form-franjas").forEach((formulario) => {

        const entradas = formulario.querySelectorAll(".hueco__entrada");
        const contador = formulario.querySelector("[data-contador] strong");
        const limpiar = formulario.querySelector("[data-limpiar]");

        const refrescarContador = () => {
            const marcados = formulario.querySelectorAll(".hueco__entrada:checked").length;
            contador.textContent = marcados;
        };

        const pintar = (entrada) => {
            entrada.closest(".hueco").classList.toggle("hueco--activo", entrada.checked);
        };

        let arrastrando = false;
        let valorArrastre = true;

        entradas.forEach((entrada) => {
            const celda = entrada.closest(".hueco");

            entrada.addEventListener("change", () => {
                pintar(entrada);
                refrescarContador();
            });

            celda.addEventListener("mousedown", (evento) => {
                evento.preventDefault();

                arrastrando = true;
                valorArrastre = !entrada.checked;
                entrada.checked = valorArrastre;
                pintar(entrada);
                refrescarContador();
            });

            celda.addEventListener("mouseenter", () => {
                if (!arrastrando) {
                    return;
                }

                entrada.checked = valorArrastre;
                pintar(entrada);
                refrescarContador();
            });
        });

        document.addEventListener("mouseup", () => {
            arrastrando = false;
        });

        formulario.querySelectorAll("[data-columna]").forEach((boton) => {
            if (boton.tagName !== "BUTTON") {
                return;
            }

            boton.addEventListener("click", (evento) => {
                evento.preventDefault();

                const columna = boton.dataset.columna;
                const celdas = formulario.querySelectorAll(`.hueco__entrada[data-columna="${columna}"]`);
                const todosMarcados = Array.from(celdas).every((celda) => celda.checked);

                celdas.forEach((celda) => {
                    celda.checked = !todosMarcados;
                    pintar(celda);
                });

                refrescarContador();
            });
        });

        if (limpiar) {
            limpiar.addEventListener("click", (evento) => {
                evento.preventDefault();

                entradas.forEach((entrada) => {
                    entrada.checked = false;
                    pintar(entrada);
                });

                refrescarContador();
            });
        }

        refrescarContador();
    });

});
