document.addEventListener("DOMContentLoaded", () => {

    const password = document.getElementById("password");
    const relleno = document.getElementById("fuerza-relleno");
    const texto = document.getElementById("fuerza-texto");

    if (!password || !relleno) {
        return;
    }

    const niveles = [
        { ancho: "25%", color: "#b6483f", texto: "Debil" },
        { ancho: "50%", color: "#c98a2e", texto: "Mejorable" },
        { ancho: "75%", color: "#4a8f63", texto: "Buena" },
        { ancho: "100%", color: "#2f7a4d", texto: "Muy buena" }
    ];

    const calcularFuerza = (valor) => {
        let puntos = 0;

        if (valor.length >= 8) puntos += 1;
        if (valor.length >= 12) puntos += 1;
        if (/[A-Z]/.test(valor) && /[a-z]/.test(valor)) puntos += 1;
        if (/[0-9]/.test(valor) && /[^A-Za-z0-9]/.test(valor)) puntos += 1;

        return puntos;
    };

    password.addEventListener("input", () => {
        const valor = password.value;

        if (valor.length === 0) {
            relleno.style.width = "0";
            texto.textContent = "Minimo 8 caracteres";
            return;
        }

        const nivel = niveles[Math.max(0, calcularFuerza(valor) - 1)];
        relleno.style.width = nivel.ancho;
        relleno.style.backgroundColor = nivel.color;
        texto.textContent = nivel.texto;
    });

});
