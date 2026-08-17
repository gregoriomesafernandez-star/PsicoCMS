document.addEventListener("DOMContentLoaded", () => {

    const nodoDatos = document.getElementById("datos-citas");

    if (!nodoDatos) {
        return;
    }

    const datos = JSON.parse(nodoDatos.textContent);

    const formulario = document.getElementById("form-cita");
    const metodo = document.getElementById("cita-metodo");
    const contenedorHuecos = document.getElementById("huecos-disponibles");
    const campoHora = document.getElementById("cita-hora");
    const campoFecha = document.getElementById("cita-fecha");
    const campoModalidad = document.getElementById("cita-modalidad");
    const titulo = document.getElementById("modal-cita-titulo");

    let citaEnEdicion = null;

    const vaciar = (elemento) => {
        while (elemento.firstChild) {
            elemento.removeChild(elemento.firstChild);
        }
    };

    const mensajeHuecos = (texto, clase, extra) => {
        vaciar(contenedorHuecos);

        const parrafo = document.createElement("p");
        parrafo.className = clase;
        parrafo.textContent = texto;
        contenedorHuecos.appendChild(parrafo);

        if (!extra) {
            return;
        }

        const pie = document.createElement("p");
        pie.className = "huecos__pie";
        pie.appendChild(document.createTextNode(extra.texto));

        if (extra.url) {
            const enlace = document.createElement("a");
            enlace.href = extra.url;
            enlace.textContent = extra.enlace;
            pie.appendChild(document.createTextNode(" "));
            pie.appendChild(enlace);
        }

        contenedorHuecos.appendChild(pie);
    };

    const explicarSinHuecos = (resultado) => {
        if (resultado.es_pasado) {
            mensajeHuecos("Esa fecha ya ha pasado. Elige un dia a partir de hoy.", "huecos__vacio");
            return;
        }

        if (resultado.sin_franjas) {
            const dias = resultado.dias_disponibles;
            const texto = `No atiendes ${resultado.modalidad.toLowerCase()} los ${resultado.dia_semana}.`;

            const extra = dias.length
                ? { texto: `Tus dias ${resultado.modalidad.toLowerCase()}: ${dias.join(", ")}.` }
                : {
                    texto: `Todavia no has marcado ningun horario ${resultado.modalidad.toLowerCase()}.`,
                    url: resultado.url_disponibilidad,
                    enlace: "Configurar disponibilidad"
                };

            mensajeHuecos(texto, "huecos__vacio", extra);
            return;
        }

        mensajeHuecos(
            `Ese ${resultado.dia_semana} ya tienes todas las horas ocupadas o pasadas.`,
            "huecos__vacio"
        );
    };

    const elegirHueco = (boton) => {
        contenedorHuecos.querySelectorAll(".hueco-boton").forEach((otro) => {
            otro.classList.remove("hueco-boton--elegido");
        });

        boton.classList.add("hueco-boton--elegido");
        campoHora.value = boton.dataset.hora;
    };

    const cargarHuecos = async () => {
        const fecha = campoFecha.value;
        const modalidad = campoModalidad.value;

        if (!fecha || !modalidad) {
            mensajeHuecos("Elige modalidad y fecha para ver tus huecos libres.", "huecos__cargando");
            return;
        }

        mensajeHuecos("Buscando huecos libres...", "huecos__cargando");

        const parametros = new URLSearchParams({ fecha, modalidad });

        if (citaEnEdicion) {
            parametros.append("cita", citaEnEdicion);
        }

        try {
            const respuesta = await fetch(`${datos.urlHuecos}?${parametros.toString()}`, {
                headers: { "Accept": "application/json" }
            });

            const resultado = await respuesta.json();

            if (resultado.modo_vacaciones) {
                mensajeHuecos("Tienes el modo vacaciones activado.", "huecos__vacio", {
                    texto: "Desactivalo para poder reservar.",
                    url: resultado.url_disponibilidad,
                    enlace: "Ir a disponibilidad"
                });
                return;
            }

            if (resultado.bloqueado) {
                mensajeHuecos("Ese dia esta dentro de un periodo de vacaciones.", "huecos__vacio");
                return;
            }

            if (!resultado.huecos.length) {
                explicarSinHuecos(resultado);
                return;
            }

            vaciar(contenedorHuecos);

            resultado.huecos.forEach((hora) => {
                const boton = document.createElement("button");
                boton.type = "button";
                boton.className = "hueco-boton";
                boton.dataset.hora = hora;
                boton.textContent = hora;

                if (campoHora.value === hora) {
                    boton.classList.add("hueco-boton--elegido");
                }

                boton.addEventListener("click", (evento) => {
                    evento.preventDefault();
                    elegirHueco(boton);
                });

                contenedorHuecos.appendChild(boton);
            });
        } catch (error) {
            mensajeHuecos("No se han podido cargar los huecos. Intentalo de nuevo.", "huecos__vacio");
        }
    };

    const ponerValor = (nombre, valor) => {
        const campo = formulario.querySelector(`[name="${nombre}"]`);

        if (campo) {
            campo.value = valor ?? "";
        }
    };

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

    const hoy = () => {
        const ahora = new Date();
        const desfase = ahora.getTimezoneOffset() * 60000;

        return new Date(ahora.getTime() - desfase).toISOString().slice(0, 10);
    };

    const abrirNueva = (preseleccion = {}) => {
        citaEnEdicion = null;
        formulario.reset();
        formulario.action = datos.urlCrear;
        metodo.value = "POST";
        campoFecha.value = preseleccion.fecha || hoy();
        campoHora.value = preseleccion.hora || "";

        if (preseleccion.modalidad) {
            campoModalidad.value = preseleccion.modalidad;
        }

        cambiarTitulo("Nueva cita");
        PsicoModal.abrir("modal-cita");
        cargarHuecos();
    };

    const abrirEdicion = (cita) => {
        citaEnEdicion = cita.id;
        formulario.action = `${datos.urlBase}/${cita.id}`;
        metodo.value = "PUT";

        ponerValor("paciente_nombre", cita.paciente_nombre);
        ponerValor("paciente_telefono", cita.paciente_telefono);
        ponerValor("paciente_email", cita.paciente_email);
        ponerValor("modalidad", cita.modalidad);
        ponerValor("fecha", cita.fecha);
        ponerValor("estado", cita.estado);
        ponerValor("motivo", cita.motivo);
        ponerValor("notas", cita.notas);
        campoHora.value = cita.hora_inicio;

        cambiarTitulo("Editar cita");
        PsicoModal.abrir("modal-cita");
        cargarHuecos();
    };

    window.PsicoCitaForm = { abrirNueva, abrirEdicion };

    document.querySelectorAll("#boton-nueva-cita, [data-nueva-cita]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirNueva();
        });
    });

    document.querySelectorAll("[data-editar-cita]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            abrirEdicion(JSON.parse(boton.dataset.cita));
        });
    });

    [campoFecha, campoModalidad].forEach((campo) => {
        if (campo) {
            campo.addEventListener("change", () => {
                campoHora.value = "";
                cargarHuecos();
            });
        }
    });

    formulario.addEventListener("submit", (evento) => {
        if (campoHora.value) {
            return;
        }

        evento.preventDefault();
        PsicoToast.mostrar("Elige una hora antes de guardar la cita.", "error");
    });

    document.querySelectorAll(".estado-rapido__select").forEach((select) => {
        select.addEventListener("change", () => select.closest("form").submit());
    });

    document.querySelectorAll("a.boton--inactivo").forEach((enlace) => {
        enlace.addEventListener("click", (evento) => evento.preventDefault());
    });

    if (datos.errores) {
        PsicoModal.abrir("modal-cita");
        cargarHuecos();
    }

});
