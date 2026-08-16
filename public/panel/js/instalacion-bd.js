document.addEventListener("DOMContentLoaded", () => {

    const boton = document.getElementById("probar-conexion");
    const formulario = document.getElementById("formulario-bd");
    const aviso = document.getElementById("resultado-conexion");
    const texto = document.getElementById("resultado-conexion-texto");

    if (!boton || !formulario) {
        return;
    }

    const mostrarResultado = (correcto, mensaje) => {
        aviso.classList.toggle("aviso--exito", correcto);
        aviso.classList.toggle("aviso--error", !correcto);
        texto.textContent = mensaje;
        aviso.hidden = false;
    };

    boton.addEventListener("click", async (evento) => {
        evento.preventDefault();

        boton.disabled = true;
        const textoOriginal = boton.textContent;
        boton.textContent = "Comprobando...";

        try {
            const respuesta = await fetch(formulario.dataset.probar || "/instalacion/probar-conexion", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": formulario.querySelector('input[name="_token"]').value,
                    "Accept": "application/json"
                },
                body: new FormData(formulario)
            });

            const datos = await respuesta.json();
            mostrarResultado(respuesta.ok, datos.mensaje || "No se ha podido comprobar la conexion.");
        } catch (error) {
            mostrarResultado(false, "No se ha podido comprobar la conexion. Revisa que el servidor este arrancado.");
        }

        boton.disabled = false;
        boton.textContent = textoOriginal;
    });

});
