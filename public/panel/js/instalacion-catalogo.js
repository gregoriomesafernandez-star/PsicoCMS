document.addEventListener("DOMContentLoaded", () => {

    const contenedores = {
        servicios: document.getElementById("lista-servicios"),
        especialidades: document.getElementById("lista-especialidades"),
        planes: document.getElementById("lista-planes")
    };

    if (!contenedores.servicios) {
        return;
    }

    const contadores = { servicios: 0, especialidades: 0, planes: 0 };

    const crearCampo = (etiqueta, nombre, valor, tipo) => {
        const campo = document.createElement("div");
        campo.className = "campo";
        campo.style.marginBottom = "0";

        const label = document.createElement("label");
        label.className = "campo__etiqueta";
        label.textContent = etiqueta;

        const control = tipo === "select"
            ? document.createElement("select")
            : document.createElement("input");

        control.className = "campo__control";
        control.name = nombre;

        if (tipo === "select") {
            [["online", "Online"], ["presencial", "Presencial"]].forEach(([valorOpcion, textoOpcion]) => {
                const opcion = document.createElement("option");
                opcion.value = valorOpcion;
                opcion.textContent = textoOpcion;
                opcion.selected = valorOpcion === valor;
                control.appendChild(opcion);
            });
        } else {
            control.type = tipo || "text";
            control.value = valor || "";

            if (tipo === "number") {
                control.min = "0";
                control.step = "0.01";
            }
        }

        const identificador = nombre.replace(/[^a-zA-Z0-9]/g, "-");
        control.id = identificador;
        label.setAttribute("for", identificador);

        campo.appendChild(label);
        campo.appendChild(control);

        return campo;
    };

    const crearFila = (tipo, valores) => {
        const indice = contadores[tipo]++;
        const datos = valores || {};

        const fila = document.createElement("div");
        fila.className = "repetible__fila";

        const campos = document.createElement("div");
        campos.className = "repetible__campos";

        if (tipo === "servicios") {
            campos.classList.add("repetible__campos--dos");
            campos.appendChild(crearCampo("Titulo del servicio", `servicios[${indice}][titulo]`, datos.titulo));
            campos.appendChild(crearCampo("Descripcion breve", `servicios[${indice}][descripcion]`, datos.descripcion));
        }

        if (tipo === "especialidades") {
            campos.appendChild(crearCampo("Nombre de la especialidad", `especialidades[${indice}][nombre]`, datos.nombre));
        }

        if (tipo === "planes") {
            campos.classList.add("repetible__campos--tres");
            campos.appendChild(crearCampo("Nombre del plan", `planes[${indice}][nombre]`, datos.nombre));
            campos.appendChild(crearCampo("Modalidad", `planes[${indice}][modalidad]`, datos.modalidad || "online", "select"));
            campos.appendChild(crearCampo("Precio (EUR)", `planes[${indice}][precio]`, datos.precio, "number"));
        }

        const quitar = document.createElement("button");
        quitar.type = "button";
        quitar.className = "boton boton--peligro boton--pequeno";
        quitar.setAttribute("aria-label", "Quitar este elemento");

        const icono = document.createElement("i");
        icono.className = "fa-solid fa-trash-can";
        quitar.appendChild(icono);

        quitar.addEventListener("click", (evento) => {
            evento.preventDefault();

            if (contenedores[tipo].children.length > 1) {
                fila.remove();
            }
        });

        fila.appendChild(campos);
        fila.appendChild(quitar);

        return fila;
    };

    document.querySelectorAll("[data-anadir]").forEach((boton) => {
        boton.addEventListener("click", (evento) => {
            evento.preventDefault();
            const tipo = boton.dataset.anadir;
            contenedores[tipo].appendChild(crearFila(tipo));
        });
    });

    const nodoDatos = document.getElementById("datos-catalogo");
    let guardado = null;

    if (nodoDatos) {
        try {
            guardado = JSON.parse(nodoDatos.textContent);
        } catch (error) {
            guardado = null;
        }
    }

    const sugerencias = {
        servicios: [
            { titulo: "Terapia individual", descripcion: "Acompanamiento personalizado para adultos." },
            { titulo: "Terapia de pareja", descripcion: "Comunicacion y vinculo en la relacion." }
        ],
        especialidades: [{ nombre: "Ansiedad" }, { nombre: "Autoestima" }],
        planes: [
            { nombre: "Sesion online", modalidad: "online", precio: "45" },
            { nombre: "Sesion presencial", modalidad: "presencial", precio: "55" }
        ]
    };

    Object.keys(contenedores).forEach((tipo) => {
        const valores = (guardado && Array.isArray(guardado[tipo]) && guardado[tipo].length)
            ? guardado[tipo]
            : sugerencias[tipo];

        valores.forEach((valor) => contenedores[tipo].appendChild(crearFila(tipo, valor)));
    });

});
