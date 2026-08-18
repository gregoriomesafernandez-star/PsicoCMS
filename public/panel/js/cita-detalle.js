const PsicoCitaDetalle = (() => {

    let ajustes = { urlBase: "", urlPacientes: "", fichaDisponible: false };
    let citaActual = null;

    const configurar = (opciones) => {
        ajustes = Object.assign({}, ajustes, opciones);
    };

    const vaciar = (elemento) => {
        while (elemento.firstChild) {
            elemento.removeChild(elemento.firstChild);
        }
    };

    const soloTexto = (html) => {
        if (!html) {
            return "";
        }

        return new DOMParser()
            .parseFromString(html, "text/html")
            .body.textContent.replace(/\s+/g, " ")
            .trim();
    };

    const fechaLarga = (dia) => new Date(`${dia}T00:00:00`).toLocaleDateString("es-ES", {
        weekday: "long",
        day: "numeric",
        month: "long"
    });

    const iconoModalidad = (modalidad) => {
        const icono = document.createElement("i");
        icono.className = modalidad === "online" ? "fa-solid fa-video" : "fa-solid fa-location-dot";

        return icono;
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

    const abrir = (cita) => {
        const cuerpo = document.getElementById("detalle-cuerpo");
        const formEstado = document.getElementById("form-estado-detalle");
        const selectEstado = document.getElementById("detalle-estado");
        const formBorrar = document.getElementById("form-borrar-detalle");
        const enlaceFicha = document.getElementById("detalle-ficha");

        if (!cuerpo) {
            return;
        }

        citaActual = cita;
        vaciar(cuerpo);

        const cabecera = document.createElement("div");
        cabecera.className = "detalle__paciente";

        const inicial = document.createElement("span");
        inicial.className = "paciente__inicial";
        inicial.textContent = cita.paciente_iniciales || "?";

        const nombres = document.createElement("div");

        const nombre = document.createElement("p");
        nombre.className = "celda__principal";
        nombre.textContent = cita.paciente_completo || cita.paciente_nombre || "Sin paciente";

        const telefono = document.createElement("p");
        telefono.className = "celda__secundario";
        telefono.textContent = cita.paciente_telefono || "Sin telefono";

        nombres.appendChild(nombre);
        nombres.appendChild(telefono);
        cabecera.appendChild(inicial);
        cabecera.appendChild(nombres);
        cuerpo.appendChild(cabecera);

        cuerpo.appendChild(dato("Fecha", fechaLarga(cita.fecha)));
        cuerpo.appendChild(dato("Horario", `${cita.hora_inicio} - ${cita.hora_fin} (${cita.duracion} min)`));
        cuerpo.appendChild(dato("Modalidad", cita.modalidad_legible, iconoModalidad(cita.modalidad)));

        if (cita.paciente_email) {
            cuerpo.appendChild(dato("Email", cita.paciente_email));
        }

        const motivo = soloTexto(cita.motivo);
        const notas = soloTexto(cita.notas);

        if (motivo) {
            cuerpo.appendChild(dato("Motivo", motivo));
        }

        if (notas) {
            cuerpo.appendChild(dato("Notas", notas));
        }

        formEstado.action = `${ajustes.urlBase}/${cita.id}/estado`;
        selectEstado.value = cita.estado;
        selectEstado.className = `estado-rapido__select estado-rapido__select--${cita.tono}`;

        formBorrar.action = `${ajustes.urlBase}/${cita.id}`;

        const fichaActiva = ajustes.fichaDisponible && cita.paciente_id;
        enlaceFicha.href = fichaActiva ? `${ajustes.urlPacientes}/${cita.paciente_id}` : "#";
        enlaceFicha.classList.toggle("boton--inactivo", !fichaActiva);
        enlaceFicha.title = fichaActiva
            ? "Ver la ficha completa del paciente"
            : "Ficha del paciente (disponible proximamente)";

        PsicoModal.abrir("modal-detalle");
    };

    document.addEventListener("DOMContentLoaded", () => {
        const nodoAjustes = document.getElementById("datos-detalle");

        if (nodoAjustes) {
            configurar(JSON.parse(nodoAjustes.textContent));
        }

        const formEstado = document.getElementById("form-estado-detalle");
        const selectEstado = document.getElementById("detalle-estado");
        const botonEditar = document.getElementById("detalle-editar");
        const enlaceFicha = document.getElementById("detalle-ficha");

        if (selectEstado && formEstado) {
            selectEstado.addEventListener("change", () => formEstado.submit());
        }

        if (botonEditar) {
            botonEditar.addEventListener("click", (evento) => {
                evento.preventDefault();

                if (!citaActual || typeof PsicoCitaForm === "undefined") {
                    return;
                }

                PsicoModal.cerrar(document.getElementById("modal-detalle"));
                PsicoCitaForm.abrirEdicion(citaActual);
            });
        }

        if (enlaceFicha) {
            enlaceFicha.addEventListener("click", (evento) => {
                if (enlaceFicha.classList.contains("boton--inactivo")) {
                    evento.preventDefault();
                }
            });
        }

        document.querySelectorAll("[data-ver-cita]").forEach((boton) => {
            boton.addEventListener("click", (evento) => {
                evento.preventDefault();
                abrir(JSON.parse(boton.dataset.cita));
            });
        });
    });

    return { configurar, abrir };

})();
