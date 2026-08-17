document.addEventListener("DOMContentLoaded", () => {

    const nodoDatos = document.getElementById("datos-calendario");
    const contenedor = document.getElementById("calendario");

    if (!nodoDatos || !contenedor) {
        return;
    }

    const datos = JSON.parse(nodoDatos.textContent);

    const cuerpoDetalle = document.getElementById("detalle-cuerpo");
    const formEstado = document.getElementById("form-estado-detalle");
    const selectEstado = document.getElementById("detalle-estado");
    const formBorrar = document.getElementById("form-borrar-detalle");
    const enlaceFicha = document.getElementById("detalle-ficha");
    const botonEditar = document.getElementById("detalle-editar");
    const botonAgendaNueva = document.getElementById("agenda-nueva");
    const cabeceraAgenda = document.getElementById("agenda-fecha");
    const listaAgenda = document.getElementById("agenda");

    let diaSeleccionado = fechaLocal(new Date());
    let citaAbierta = null;

    function dosDigitos(numero) {
        return String(numero).padStart(2, "0");
    }

    function fechaLocal(momento) {
        return `${momento.getFullYear()}-${dosDigitos(momento.getMonth() + 1)}-${dosDigitos(momento.getDate())}`;
    }

    function horaLocal(momento) {
        return `${dosDigitos(momento.getHours())}:${dosDigitos(momento.getMinutes())}`;
    }

    const vaciar = (elemento) => {
        while (elemento.firstChild) {
            elemento.removeChild(elemento.firstChild);
        }
    };

    const iconoModalidad = (modalidad) => {
        const icono = document.createElement("i");
        icono.className = modalidad === "online" ? "fa-solid fa-video" : "fa-solid fa-location-dot";

        return icono;
    };

    const fechaLarga = (dia) => {
        const momento = new Date(`${dia}T00:00:00`);

        return momento.toLocaleDateString("es-ES", {
            weekday: "long",
            day: "numeric",
            month: "long"
        });
    };

    const esCita = (evento) => evento.display !== "background";

    const soloTexto = (html) => {
        if (!html) {
            return "";
        }

        const documento = new DOMParser().parseFromString(html, "text/html");

        return documento.body.textContent.replace(/\s+/g, " ").trim();
    };

    const citasDelDia = (dia) => calendario.getEvents()
        .filter((evento) => esCita(evento) && evento.extendedProps.fecha === dia)
        .sort((uno, otro) => uno.extendedProps.horaInicio.localeCompare(otro.extendedProps.horaInicio));

    const filaAgenda = (evento) => {
        const propiedades = evento.extendedProps;

        const boton = document.createElement("button");
        boton.type = "button";
        boton.className = `agenda__cita agenda__cita--${propiedades.modalidad} agenda__cita--${propiedades.estado}`;

        const horas = document.createElement("span");
        horas.className = "agenda__horas";

        const inicio = document.createElement("span");
        inicio.className = "agenda__hora";
        inicio.textContent = propiedades.horaInicio;

        const duracion = document.createElement("span");
        duracion.className = "agenda__duracion";
        duracion.textContent = `${propiedades.duracion} min`;

        horas.appendChild(inicio);
        horas.appendChild(duracion);

        const cuerpo = document.createElement("span");
        cuerpo.className = "agenda__datos";

        const paciente = document.createElement("span");
        paciente.className = "agenda__paciente";
        paciente.textContent = evento.title;

        const modalidad = document.createElement("span");
        modalidad.className = "agenda__modalidad";
        modalidad.appendChild(iconoModalidad(propiedades.modalidad));
        modalidad.appendChild(document.createTextNode(` ${propiedades.modalidadLegible} · ${propiedades.estadoLegible}`));

        cuerpo.appendChild(paciente);
        cuerpo.appendChild(modalidad);

        boton.appendChild(horas);
        boton.appendChild(cuerpo);

        boton.addEventListener("click", (accion) => {
            accion.preventDefault();
            abrirDetalle(evento);
        });

        return boton;
    };

    const agendaVacia = () => {
        const vacio = document.createElement("div");
        vacio.className = "vacio vacio--compacto";

        const icono = document.createElement("span");
        icono.className = "vacio__icono";

        const taza = document.createElement("i");
        taza.className = "fa-solid fa-mug-hot";
        icono.appendChild(taza);

        const titulo = document.createElement("p");
        titulo.className = "vacio__titulo";
        titulo.textContent = "Dia libre";

        const mensaje = document.createElement("p");
        mensaje.className = "vacio__mensaje";
        mensaje.textContent = "No tienes citas programadas para este dia.";

        vacio.appendChild(icono);
        vacio.appendChild(titulo);
        vacio.appendChild(mensaje);

        return vacio;
    };

    const pintarAgenda = () => {
        cabeceraAgenda.textContent = fechaLarga(diaSeleccionado);
        vaciar(listaAgenda);

        const citas = citasDelDia(diaSeleccionado);

        if (!citas.length) {
            listaAgenda.appendChild(agendaVacia());
            return;
        }

        citas.forEach((evento) => listaAgenda.appendChild(filaAgenda(evento)));
    };

    const marcarDiaElegido = () => {
        contenedor.querySelectorAll(".fc-daygrid-day[data-date]").forEach((celda) => {
            celda.classList.toggle("dia--elegido", celda.dataset.date === diaSeleccionado);
        });
    };

    const elegirDia = (dia) => {
        diaSeleccionado = dia;
        marcarDiaElegido();
        pintarAgenda();
    };

    const dato = (etiqueta, valor, icono) => {
        const fila = document.createElement("div");
        fila.className = "detalle__fila";

        const nombre = document.createElement("span");
        nombre.className = "detalle__etiqueta";
        nombre.textContent = etiqueta;

        const contenido = document.createElement("span");
        contenido.className = "detalle__valor";

        if (icono) {
            contenido.appendChild(icono);
            contenido.appendChild(document.createTextNode(" "));
        }

        contenido.appendChild(document.createTextNode(valor));

        fila.appendChild(nombre);
        fila.appendChild(contenido);

        return fila;
    };

    const abrirDetalle = (evento) => {
        const propiedades = evento.extendedProps;
        citaAbierta = evento;

        vaciar(cuerpoDetalle);

        const cabecera = document.createElement("div");
        cabecera.className = "detalle__paciente";

        const inicial = document.createElement("span");
        inicial.className = "paciente__inicial";
        inicial.textContent = propiedades.pacienteIniciales || "?";

        const nombres = document.createElement("div");

        const nombre = document.createElement("p");
        nombre.className = "celda__principal";
        nombre.textContent = evento.title;

        const telefono = document.createElement("p");
        telefono.className = "celda__secundario";
        telefono.textContent = propiedades.pacienteTelefono || "Sin telefono";

        nombres.appendChild(nombre);
        nombres.appendChild(telefono);

        cabecera.appendChild(inicial);
        cabecera.appendChild(nombres);
        cuerpoDetalle.appendChild(cabecera);

        cuerpoDetalle.appendChild(dato("Fecha", fechaLarga(propiedades.fecha)));
        cuerpoDetalle.appendChild(dato(
            "Horario",
            `${propiedades.horaInicio} - ${propiedades.horaFin} (${propiedades.duracion} min)`
        ));
        cuerpoDetalle.appendChild(dato("Modalidad", propiedades.modalidadLegible, iconoModalidad(propiedades.modalidad)));

        if (propiedades.pacienteEmail) {
            cuerpoDetalle.appendChild(dato("Email", propiedades.pacienteEmail));
        }

        const motivo = soloTexto(propiedades.motivo);
        const notas = soloTexto(propiedades.notas);

        if (motivo) {
            cuerpoDetalle.appendChild(dato("Motivo", motivo));
        }

        if (notas) {
            cuerpoDetalle.appendChild(dato("Notas", notas));
        }

        formEstado.action = `${datos.urlBase}/${evento.id}/estado`;
        selectEstado.value = propiedades.estado;
        selectEstado.className = `estado-rapido__select estado-rapido__select--${propiedades.tono}`;

        formBorrar.action = `${datos.urlBase}/${evento.id}`;

        const fichaActiva = datos.fichaDisponible && propiedades.pacienteId;
        enlaceFicha.href = fichaActiva ? `${datos.urlPacientes}/${propiedades.pacienteId}` : "#";
        enlaceFicha.classList.toggle("boton--inactivo", !fichaActiva);
        enlaceFicha.title = fichaActiva ? "Ver ficha del paciente" : "Ficha del paciente (disponible proximamente)";

        PsicoModal.abrir("modal-detalle");
    };

    const actualizarEvento = (evento, nuevo) => {
        Object.keys(nuevo.extendedProps).forEach((clave) => {
            evento.setExtendedProp(clave, nuevo.extendedProps[clave]);
        });
    };

    const mover = async (info) => {
        const evento = info.event;
        const propiedades = evento.extendedProps;
        const inicio = evento.start;
        const fin = evento.end || new Date(inicio.getTime() + propiedades.duracion * 60000);

        const cuerpo = new URLSearchParams({
            _method: "PATCH",
            _token: datos.token,
            fecha: fechaLocal(inicio),
            hora_inicio: horaLocal(inicio),
            duracion: String(Math.round((fin.getTime() - inicio.getTime()) / 60000))
        });

        try {
            const respuesta = await fetch(`${datos.urlBase}/${evento.id}/mover`, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: cuerpo.toString()
            });

            const resultado = await respuesta.json();

            if (!respuesta.ok) {
                info.revert();
                PsicoToast.mostrar(resultado.mensaje || "No se ha podido mover la cita.", "error");
                return;
            }

            actualizarEvento(evento, resultado.evento);
            pintarAgenda();
            PsicoToast.mostrar(resultado.mensaje, "exito");
        } catch (error) {
            info.revert();
            PsicoToast.mostrar("No se ha podido guardar el cambio. Intentalo de nuevo.", "error");
        }
    };

    const calendario = new FullCalendar.Calendar(contenedor, {
        locale: "es",
        initialView: "dayGridMonth",
        height: "auto",
        firstDay: 1,
        nowIndicator: true,
        editable: true,
        eventStartEditable: true,
        eventDurationEditable: true,
        dayMaxEvents: 3,
        slotDuration: "00:15:00",
        slotMinTime: datos.horaMinima,
        slotMaxTime: datos.horaMaxima,
        allDaySlot: false,
        businessHours: datos.horarioLaboral.length ? datos.horarioLaboral : false,
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
        },

        events: (peticion, exito, fallo) => {
            const parametros = new URLSearchParams({
                desde: peticion.startStr.slice(0, 10),
                hasta: peticion.endStr.slice(0, 10)
            });

            fetch(`${datos.urlEventos}?${parametros.toString()}`, {
                headers: { "Accept": "application/json" }
            })
                .then((respuesta) => respuesta.json())
                .then((lista) => exito(lista))
                .catch((error) => {
                    fallo(error);
                    PsicoToast.mostrar("No se han podido cargar las citas.", "error");
                });
        },

        eventContent: (argumentos) => {
            if (!esCita(argumentos.event)) {
                return;
            }

            const propiedades = argumentos.event.extendedProps;

            const caja = document.createElement("div");
            caja.className = "cita-evento__contenido";

            const hora = document.createElement("span");
            hora.className = "cita-evento__hora";
            hora.textContent = propiedades.horaInicio;

            const titulo = document.createElement("span");
            titulo.className = "cita-evento__titulo";
            titulo.textContent = argumentos.event.title;

            caja.appendChild(iconoModalidad(propiedades.modalidad));
            caja.appendChild(hora);
            caja.appendChild(titulo);

            return { domNodes: [caja] };
        },

        eventDidMount: (argumentos) => {
            if (!esCita(argumentos.event)) {
                return;
            }

            const propiedades = argumentos.event.extendedProps;

            argumentos.el.setAttribute(
                "title",
                `${propiedades.horaInicio} - ${propiedades.horaFin} · ${argumentos.event.title} · ${propiedades.modalidadLegible} · ${propiedades.estadoLegible}`
            );
        },

        eventClick: (argumentos) => {
            argumentos.jsEvent.preventDefault();

            if (!esCita(argumentos.event)) {
                return;
            }

            elegirDia(argumentos.event.extendedProps.fecha);
            abrirDetalle(argumentos.event);
        },

        dateClick: (argumentos) => {
            elegirDia(fechaLocal(argumentos.date));

            if (argumentos.allDay) {
                return;
            }

            PsicoCitaForm.abrirNueva({
                fecha: diaSeleccionado,
                hora: horaLocal(argumentos.date)
            });
        },

        datesSet: (argumentos) => {
            const inicio = fechaLocal(argumentos.start);
            const fin = fechaLocal(new Date(argumentos.end.getTime() - 1));

            if (diaSeleccionado < inicio || diaSeleccionado > fin) {
                const hoy = fechaLocal(new Date());
                diaSeleccionado = hoy >= inicio && hoy <= fin ? hoy : inicio;
            }

            marcarDiaElegido();
            pintarAgenda();
        },

        eventsSet: () => {
            marcarDiaElegido();
            pintarAgenda();
        },

        eventDrop: mover,
        eventResize: mover
    });

    calendario.render();

    botonEditar.addEventListener("click", (evento) => {
        evento.preventDefault();

        if (!citaAbierta) {
            return;
        }

        const propiedades = citaAbierta.extendedProps;

        PsicoModal.cerrar(document.getElementById("modal-detalle"));

        PsicoCitaForm.abrirEdicion({
            id: citaAbierta.id,
            paciente_nombre: propiedades.pacienteNombre,
            paciente_telefono: propiedades.pacienteTelefono,
            paciente_email: propiedades.pacienteEmail,
            modalidad: propiedades.modalidad,
            fecha: propiedades.fecha,
            hora_inicio: propiedades.horaInicio,
            estado: propiedades.estado,
            motivo: propiedades.motivo,
            notas: propiedades.notas
        });
    });

    selectEstado.addEventListener("change", () => formEstado.submit());

    botonAgendaNueva.addEventListener("click", (evento) => {
        evento.preventDefault();
        PsicoCitaForm.abrirNueva({ fecha: diaSeleccionado });
    });

    enlaceFicha.addEventListener("click", (evento) => {
        if (enlaceFicha.classList.contains("boton--inactivo")) {
            evento.preventDefault();
        }
    });

});
