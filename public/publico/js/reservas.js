/*
 * Reserva de cita en la parte publica.
 *
 * Cuatro pasos: tipo de sesion -> dia -> hora -> datos. Los dias y las horas
 * los decide siempre el servidor (DisponibilidadService); aqui solo se pintan.
 *
 * Sin innerHTML: todo el DOM se construye con createElement y textContent.
 */
document.addEventListener("DOMContentLoaded", () => {

    const formulario = document.querySelector("[data-reserva]");

    /* ---------- Modal de confirmacion ---------- */

    const modal = document.querySelector("[data-modal-reserva]");

    if (modal) {
        const cerrar = () => modal.remove();

        modal.querySelector("[data-cerrar-modal]")?.addEventListener("click", (evento) => {
            evento.preventDefault();
            cerrar();
        });

        modal.addEventListener("click", (evento) => {
            if (evento.target === modal) {
                cerrar();
            }
        });

        document.addEventListener("keydown", (evento) => {
            if (evento.key === "Escape") {
                cerrar();
            }
        });
    }

    if (!formulario) {
        return;
    }

    /* ---------- Referencias ---------- */

    const urlDias = formulario.dataset.urlDias;
    const urlHuecos = formulario.dataset.urlHuecos;

    const pasos = Array.from(formulario.querySelectorAll("[data-paso]"));
    const guias = Array.from(formulario.querySelectorAll("[data-guia]"));
    const modalidades = Array.from(formulario.querySelectorAll("input[name='modalidad']"));

    const calendario = formulario.querySelector("[data-calendario]");
    const contenedorDias = formulario.querySelector("[data-dias]");
    const tituloMes = formulario.querySelector("[data-titulo]");
    const contenedorHoras = formulario.querySelector("[data-horas]");
    const leyendaHoras = formulario.querySelector("[data-leyenda-horas]");
    const resumen = formulario.querySelector("[data-resumen]");

    const campoFecha = formulario.querySelector("[data-campo-fecha]");
    const campoHora = formulario.querySelector("[data-campo-hora]");

    let mesActual = null;
    let navegacion = { anterior: null, siguiente: null };

    /* ---------- Pasos ---------- */

    const abrirHasta = (numero) => {
        pasos.forEach((paso) => {
            paso.hidden = Number(paso.dataset.paso) > numero;
        });

        guias.forEach((guia) => {
            const suyo = Number(guia.dataset.guia);

            guia.classList.toggle("es-hecho", suyo < numero);
            guia.classList.toggle("es-actual", suyo === numero);
        });
    };

    const irA = (numero) => {
        abrirHasta(numero);

        const destino = pasos.find((paso) => Number(paso.dataset.paso) === numero);

        if (destino) {
            destino.scrollIntoView({ behavior: "smooth", block: "nearest" });
        }
    };

    const modalidadElegida = () => modalidades.find((opcion) => opcion.checked)?.value ?? null;

    const vaciar = (contenedor) => {
        while (contenedor.firstChild) {
            contenedor.removeChild(contenedor.firstChild);
        }
    };

    const mensaje = (contenedor, texto) => {
        vaciar(contenedor);

        const parrafo = document.createElement("p");

        parrafo.className = "reserva__vacio";
        parrafo.textContent = texto;
        contenedor.appendChild(parrafo);
    };

    /* ---------- Calendario ---------- */

    const mesDeHoy = () => {
        const hoy = new Date();
        const mes = String(hoy.getMonth() + 1).padStart(2, "0");

        return `${hoy.getFullYear()}-${mes}`;
    };

    /* La rejilla empieza en lunes: getDay() da 0 al domingo. */
    const huecosPrevios = (fecha) => (fecha.getDay() + 6) % 7;

    const pintarDias = (datos) => {
        vaciar(contenedorDias);

        const fechas = Object.keys(datos.dias).sort();

        if (fechas.length === 0) {
            return;
        }

        const primero = new Date(`${fechas[0]}T00:00:00`);

        for (let i = 0; i < huecosPrevios(primero); i++) {
            const hueco = document.createElement("span");

            hueco.className = "calendario__hueco";
            contenedorDias.appendChild(hueco);
        }

        fechas.forEach((fecha) => {
            const libre = datos.dias[fecha];
            const boton = document.createElement("button");

            boton.type = "button";
            boton.className = "calendario__dia";
            boton.textContent = String(Number(fecha.slice(8, 10)));
            boton.dataset.fecha = fecha;

            if (libre) {
                boton.classList.add("es-libre");
                boton.addEventListener("click", (evento) => {
                    evento.preventDefault();
                    elegirDia(fecha, boton);
                });
            } else {
                boton.disabled = true;
                boton.setAttribute("aria-label", "Sin huecos libres");
            }

            if (fecha === campoFecha.value) {
                boton.classList.add("es-elegido");
                boton.setAttribute("aria-pressed", "true");
            }

            contenedorDias.appendChild(boton);
        });
    };

    const cargarMes = (mes) => {
        const modalidad = modalidadElegida();

        if (!modalidad) {
            return;
        }

        tituloMes.textContent = "Cargando…";
        vaciar(contenedorDias);

        fetch(`${urlDias}?modalidad=${encodeURIComponent(modalidad)}&mes=${encodeURIComponent(mes)}`, {
            headers: { Accept: "application/json" },
        })
            .then((respuesta) => (respuesta.ok ? respuesta.json() : Promise.reject(respuesta)))
            .then((datos) => {
                mesActual = datos.mes;
                navegacion = { anterior: datos.anterior, siguiente: datos.siguiente };

                tituloMes.textContent = datos.titulo;
                pintarDias(datos);
                actualizarMoverse();
            })
            .catch(() => {
                tituloMes.textContent = "No se ha podido cargar el calendario";
            });
    };

    const actualizarMoverse = () => {
        calendario.querySelectorAll("[data-mover]").forEach((boton) => {
            boton.disabled = navegacion[boton.dataset.mover] === null;
        });
    };

    calendario?.querySelectorAll("[data-mover]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();

            const destino = navegacion[boton.dataset.mover];

            if (destino) {
                cargarMes(destino);
            }
        });
    });

    /* ---------- Horas ---------- */

    const elegirDia = (fecha, boton) => {
        campoFecha.value = fecha;
        campoHora.value = "";

        contenedorDias.querySelectorAll(".calendario__dia").forEach((dia) => {
            dia.classList.toggle("es-elegido", dia === boton);
            dia.setAttribute("aria-pressed", dia === boton ? "true" : "false");
        });

        cargarHoras(fecha);
        irA(3);
    };

    const pintarHoras = (datos) => {
        vaciar(contenedorHoras);

        if (datos.huecos.length === 0) {
            mensaje(contenedorHoras, "Ese dia se ha quedado sin huecos. Prueba con otro.");

            return;
        }

        datos.huecos.forEach((hora) => {
            const boton = document.createElement("button");

            boton.type = "button";
            boton.className = "reserva__hora";
            boton.textContent = hora;

            boton.addEventListener("click", (evento) => {
                evento.preventDefault();
                elegirHora(hora, boton, datos);
            });

            contenedorHoras.appendChild(boton);
        });
    };

    const cargarHoras = (fecha) => {
        const modalidad = modalidadElegida();

        leyendaHoras.textContent = "Elige la hora";
        mensaje(contenedorHoras, "Cargando…");

        fetch(`${urlHuecos}?modalidad=${encodeURIComponent(modalidad)}&fecha=${encodeURIComponent(fecha)}`, {
            headers: { Accept: "application/json" },
        })
            .then((respuesta) => (respuesta.ok ? respuesta.json() : Promise.reject(respuesta)))
            .then((datos) => {
                leyendaHoras.textContent = `Horas libres el ${datos.titulo}`;
                pintarHoras(datos);
            })
            .catch(() => {
                mensaje(contenedorHoras, "No se han podido cargar las horas. Intentalo de nuevo.");
            });
    };

    const elegirHora = (hora, boton, datos) => {
        campoHora.value = hora;

        contenedorHoras.querySelectorAll(".reserva__hora").forEach((otra) => {
            otra.classList.toggle("es-elegida", otra === boton);
            otra.setAttribute("aria-pressed", otra === boton ? "true" : "false");
        });

        resumen.textContent = `${datos.titulo} a las ${hora} · ${datos.duracion} minutos`;
        irA(4);
    };

    /* ---------- Arranque ---------- */

    modalidades.forEach((opcion) => {
        opcion.addEventListener("change", () => {
            campoFecha.value = "";
            campoHora.value = "";
            vaciar(contenedorHoras);

            cargarMes(mesActual ?? mesDeHoy());
            irA(2);
        });
    });

    formulario.querySelector("[data-solo-numeros]")?.addEventListener("input", (evento) => {
        evento.target.value = evento.target.value.replace(/\D/g, "");
    });

    // Al volver de un error de validacion, se recupera el punto donde estaba.
    if (campoHora.value) {
        abrirHasta(4);
        cargarMes(campoFecha.value.slice(0, 7));
        cargarHoras(campoFecha.value);
    } else if (campoFecha.value) {
        abrirHasta(3);
        cargarMes(campoFecha.value.slice(0, 7));
        cargarHoras(campoFecha.value);
    } else if (modalidadElegida()) {
        abrirHasta(2);
        cargarMes(mesDeHoy());
    } else {
        abrirHasta(1);
    }
});
