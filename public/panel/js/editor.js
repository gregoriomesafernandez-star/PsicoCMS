const PsicoEditor = (() => {

    const instancias = new Map();

    const OPCIONES = {
        language: "es",
        height: 420,
        minHeight: 260,
        toolbarAdaptive: true,
        toolbarSticky: false,
        showCharsCounter: false,
        showWordsCounter: true,
        showXPathInStatusbar: false,
        askBeforePasteHTML: false,
        askBeforePasteFromWord: false,
        defaultActionOnPaste: "insert_clear_html",
        buttons: [
            "bold", "italic", "underline", "strikethrough", "|",
            "ul", "ol", "|",
            "paragraph", "align", "|",
            "link", "image", "table", "hr", "|",
            "brush", "eraser", "|",
            "undo", "redo", "|",
            "fullsize", "source"
        ]
    };

    const opcionesCompactas = {
        height: 220,
        minHeight: 160,
        showWordsCounter: false,
        buttons: [
            "bold", "italic", "underline", "|",
            "ul", "ol", "|",
            "link", "eraser", "|",
            "undo", "redo"
        ]
    };

    const crear = (campo) => {
        if (!campo || instancias.has(campo) || typeof Jodit === "undefined") {
            return null;
        }

        const compacto = campo.dataset.editor === "compacto";
        const opciones = compacto ? { ...OPCIONES, ...opcionesCompactas } : { ...OPCIONES };

        // Jodit trae su propio tema oscuro; sus colores se ajustan a los del
        // panel desde editor.css.
        if (document.documentElement.dataset.tema === "oscuro") {
            opciones.theme = "dark";
        }

        if (campo.placeholder) {
            opciones.placeholder = campo.placeholder;
        }

        const instancia = Jodit.make(campo, opciones);
        instancias.set(campo, instancia);

        return instancia;
    };

    const visible = (elemento) => elemento.offsetParent !== null || elemento.getClientRects().length > 0;

    const iniciar = (raiz = document) => {
        raiz.querySelectorAll("textarea.editor-rico").forEach((campo) => {
            if (visible(campo) || !campo.closest(".modal")) {
                crear(campo);
            }
        });
    };

    const vigilarModales = () => {
        document.querySelectorAll(".modal").forEach((modal) => {
            if (!modal.querySelector("textarea.editor-rico")) {
                return;
            }

            const observador = new MutationObserver(() => {
                if (!modal.hidden) {
                    iniciar(modal);
                }
            });

            observador.observe(modal, { attributes: true, attributeFilter: ["hidden"] });
        });
    };

    const instancia = (idONodo) => {
        const campo = typeof idONodo === "string" ? document.getElementById(idONodo) : idONodo;

        return campo ? instancias.get(campo) || null : null;
    };

    const poner = (idONodo, html) => {
        const campo = typeof idONodo === "string" ? document.getElementById(idONodo) : idONodo;

        if (!campo) {
            return;
        }

        campo.value = html || "";

        const editor = instancias.get(campo);

        if (editor) {
            editor.value = html || "";
        }
    };

    const sincronizar = (formulario) => {
        instancias.forEach((editor, campo) => {
            if (!formulario || campo.form === formulario) {
                campo.value = editor.value;
            }
        });
    };

    document.addEventListener("DOMContentLoaded", () => {
        iniciar();
        vigilarModales();

        document.addEventListener("submit", (evento) => {
            if (evento.target instanceof HTMLFormElement) {
                sincronizar(evento.target);
            }
        }, true);
    });

    return { iniciar, instancia, poner, sincronizar };

})();
