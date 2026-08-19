document.addEventListener("DOMContentLoaded", () => {

    const configuracion = () => {
        const nodo = document.getElementById("datos-historia");

        if (!nodo) {
            return null;
        }

        try {
            return JSON.parse(nodo.textContent);
        } catch (error) {
            return null;
        }
    };

    const vaciar = (nodo) => {
        while (nodo.firstChild) {
            nodo.removeChild(nodo.firstChild);
        }
    };

    const pesoLegible = (bytes) => {
        const kilobytes = bytes / 1024;

        return kilobytes < 1024
            ? `${Math.round(kilobytes)} KB`
            : `${(kilobytes / 1024).toFixed(1)} MB`;
    };

    /* ---------- Citas del paciente elegido ---------- */

    const enlazarCitas = (datos) => {
        const paciente = document.getElementById("paciente_id");
        const cita = document.getElementById("cita_id");

        if (!paciente || !cita || !datos) {
            return;
        }

        const opcionVacia = (texto) => {
            const opcion = document.createElement("option");
            opcion.value = "";
            opcion.textContent = texto;
            return opcion;
        };

        paciente.addEventListener("change", async () => {
            vaciar(cita);

            if (!paciente.value) {
                cita.appendChild(opcionVacia("Elige antes un paciente"));
                cita.disabled = true;
                return;
            }

            cita.disabled = true;
            cita.appendChild(opcionVacia("Buscando sus citas..."));

            try {
                const respuesta = await fetch(`${datos.urlCitas}?paciente=${encodeURIComponent(paciente.value)}`, {
                    headers: { "Accept": "application/json" }
                });

                const resultado = await respuesta.json();

                vaciar(cita);
                cita.appendChild(opcionVacia("Sin vincular"));

                resultado.citas.forEach((suya) => {
                    const opcion = document.createElement("option");
                    opcion.value = suya.id;
                    opcion.textContent = suya.texto;
                    cita.appendChild(opcion);
                });

                cita.disabled = false;
            } catch (error) {
                vaciar(cita);
                cita.appendChild(opcionVacia("No se han podido cargar las citas"));
                PsicoToast.mostrar("No se han podido cargar las citas de ese paciente.", "error");
            }
        });
    };

    /* ---------- Archivos elegidos antes de subirlos ---------- */

    const listarArchivos = () => {
        const campo = document.getElementById("adjuntos");
        const lista = document.getElementById("adjuntos-elegidos");

        if (!campo || !lista) {
            return;
        }

        const MAXIMO_BYTES = 8 * 1024 * 1024;

        const fila = (archivo) => {
            const item = document.createElement("li");
            item.className = "adjunto-fila";

            const icono = document.createElement("i");
            const esPdf = archivo.type === "application/pdf";
            icono.className = `fa-solid ${esPdf ? "fa-file-pdf" : "fa-file-image"} adjunto-fila__icono`;

            if (esPdf) {
                icono.classList.add("adjunto-fila__icono--pdf");
            }

            const cuerpo = document.createElement("span");
            cuerpo.className = "adjunto-fila__cuerpo";

            const nombre = document.createElement("span");
            nombre.className = "adjunto-fila__nombre";
            nombre.textContent = archivo.name;

            const meta = document.createElement("span");
            meta.className = "adjunto-fila__meta";
            meta.textContent = archivo.size > MAXIMO_BYTES
                ? `${pesoLegible(archivo.size)} - demasiado grande, el maximo son 8 MB`
                : `Se subira al guardar - ${pesoLegible(archivo.size)}`;

            cuerpo.appendChild(nombre);
            cuerpo.appendChild(meta);
            item.appendChild(icono);
            item.appendChild(cuerpo);

            return item;
        };

        campo.addEventListener("change", () => {
            vaciar(lista);

            Array.from(campo.files).forEach((archivo) => lista.appendChild(fila(archivo)));

            const pesados = Array.from(campo.files).filter((archivo) => archivo.size > MAXIMO_BYTES);

            if (pesados.length) {
                PsicoToast.mostrar(
                    `${pesados.length} archivo(s) superan los 8 MB y no se podran subir.`,
                    "atencion"
                );
            }
        });
    };

    /* ---------- Visor de imagenes ---------- */

    const visorDeImagenes = () => {
        const visor = document.getElementById("visor-imagen");
        const titulo = document.getElementById("modal-imagen-titulo");

        if (!visor) {
            return;
        }

        document.querySelectorAll("[data-ver-imagen]").forEach((boton) => {
            boton.addEventListener("click", (evento) => {
                evento.preventDefault();

                visor.src = boton.dataset.verImagen;
                visor.alt = boton.dataset.nombre || "";

                if (titulo) {
                    const icono = titulo.querySelector("i");
                    titulo.textContent = " " + (boton.dataset.nombre || "Archivo adjunto");

                    if (icono) {
                        titulo.insertBefore(icono, titulo.firstChild);
                    }
                }

                PsicoModal.abrir("modal-imagen");
            });
        });
    };

    enlazarCitas(configuracion());
    listarArchivos();
    visorDeImagenes();

});
