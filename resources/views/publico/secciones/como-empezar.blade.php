<section class="layout__how-start" id="como-empezar" aria-labelledby="titulo-como-empezar">
    <div class="how-start__container">

        <header class="how-start__header">
            <h2 class="how-start__title" id="titulo-como-empezar">
                {{ frase('como_empezar_titulo', 'Como empezamos') }}
            </h2>
        </header>

        <div class="how-start__steps">
            @foreach ([
                ['fa-regular fa-envelope', frase('como_empezar_paso_1', 'Pide tu cita'), 'Elige el dia y la hora que mejor te vengan, o escribeme y lo vemos juntas.'],
                ['fa-solid fa-comments', frase('como_empezar_paso_2', 'Primera sesion de valoracion'), 'Nos conocemos, me cuentas que te trae a consulta y resolvemos tus dudas.'],
                ['fa-solid fa-seedling', frase('como_empezar_paso_3', 'Empezamos a trabajar juntas'), 'Definimos objetivos y avanzamos a tu ritmo, sesion a sesion.'],
            ] as $indice => [$icono, $titulo, $descripcion])
                <article class="how-start__step">
                    <div class="step__container-ico">
                        <i class="{{ $icono }} step__ico" aria-hidden="true"></i>
                    </div>

                    <h3 class="step__title">{{ $titulo }}</h3>
                    <p class="step__description">{{ $descripcion }}</p>
                </article>
            @endforeach
        </div>

    </div>
</section>
