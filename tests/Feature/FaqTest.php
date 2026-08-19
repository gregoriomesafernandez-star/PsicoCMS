<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Faq;
use App\Models\Psicologa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        $this->actingAs(Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]));
    }

    private function crearFaq(array $extra = []): Faq
    {
        return Faq::create(array_merge([
            'pregunta' => '¿Cuanto dura una sesion?',
            'respuesta' => '<p>Unos 50 minutos.</p>',
            'orden' => Faq::siguienteOrden(),
            'activa' => true,
        ], $extra));
    }

    public function test_las_faqs_requieren_autenticacion(): void
    {
        $faq = $this->crearFaq();

        Auth::logout();

        $this->get('/panel-psicologa/faqs')->assertRedirect('/acceso-psicologa');
        $this->post(route('panel.faqs.store'), ['pregunta' => 'x'])->assertRedirect('/acceso-psicologa');
        $this->patch(route('panel.faqs.activa', $faq))->assertRedirect('/acceso-psicologa');
        $this->delete(route('panel.faqs.destroy', $faq))->assertRedirect('/acceso-psicologa');
    }

    public function test_la_pantalla_se_muestra_con_su_empty_state(): void
    {
        $this->get('/panel-psicologa/faqs')
            ->assertOk()
            ->assertSee('Todavia no hay preguntas')
            ->assertSee('Anadir preguntas de ejemplo');
    }

    public function test_se_crea_una_pregunta_con_la_respuesta_saneada(): void
    {
        $this->post(route('panel.faqs.store'), [
            'pregunta' => '¿La terapia online funciona?',
            'respuesta' => '<p onclick="robar()">Si, <strong>funciona</strong></p><script>alert(1)</script>',
            'activa' => '1',
        ])->assertSessionHasNoErrors();

        $faq = Faq::first();

        $this->assertSame('¿La terapia online funciona?', $faq->pregunta);
        $this->assertStringContainsString('<strong>funciona</strong>', $faq->respuesta);
        $this->assertStringNotContainsString('onclick', $faq->respuesta);
        $this->assertStringNotContainsString('<script', $faq->respuesta);
        $this->assertTrue($faq->activa);
    }

    public function test_la_pregunta_es_obligatoria(): void
    {
        $this->post(route('panel.faqs.store'), ['pregunta' => ''])
            ->assertSessionHasErrors('pregunta');

        $this->assertSame(0, Faq::count());
    }

    public function test_cada_pregunta_nueva_se_coloca_la_ultima(): void
    {
        $this->post(route('panel.faqs.store'), ['pregunta' => 'Primera', 'activa' => '1']);
        $this->post(route('panel.faqs.store'), ['pregunta' => 'Segunda', 'activa' => '1']);
        $this->post(route('panel.faqs.store'), ['pregunta' => 'Tercera', 'activa' => '1']);

        $this->assertSame(
            ['Primera', 'Segunda', 'Tercera'],
            Faq::ordenadas()->pluck('pregunta')->all()
        );
    }

    public function test_se_edita_sin_perder_su_posicion(): void
    {
        $primera = $this->crearFaq(['pregunta' => 'Primera']);
        $segunda = $this->crearFaq(['pregunta' => 'Segunda']);

        $this->put(route('panel.faqs.update', $segunda), [
            'pregunta' => 'Segunda corregida',
            'respuesta' => '<p>Nueva respuesta.</p>',
            'activa' => '1',
        ])->assertSessionHasNoErrors();

        $segunda->refresh();

        $this->assertSame('Segunda corregida', $segunda->pregunta);
        $this->assertSame(2, $segunda->orden);
        $this->assertSame(1, $primera->refresh()->orden);
    }

    public function test_las_flechas_cambian_el_orden(): void
    {
        $this->crearFaq(['pregunta' => 'Primera']);
        $segunda = $this->crearFaq(['pregunta' => 'Segunda']);
        $this->crearFaq(['pregunta' => 'Tercera']);

        $this->patch(route('panel.faqs.mover', [$segunda, 'arriba']));

        $this->assertSame(
            ['Segunda', 'Primera', 'Tercera'],
            Faq::ordenadas()->pluck('pregunta')->all()
        );

        $this->patch(route('panel.faqs.mover', [$segunda, 'abajo']));

        $this->assertSame(
            ['Primera', 'Segunda', 'Tercera'],
            Faq::ordenadas()->pluck('pregunta')->all()
        );
    }

    public function test_mover_la_primera_hacia_arriba_no_rompe_nada(): void
    {
        $primera = $this->crearFaq(['pregunta' => 'Primera']);
        $this->crearFaq(['pregunta' => 'Segunda']);

        $this->patch(route('panel.faqs.mover', [$primera, 'arriba']))->assertRedirect();

        $this->assertSame(
            ['Primera', 'Segunda'],
            Faq::ordenadas()->pluck('pregunta')->all()
        );
    }

    public function test_el_orden_se_arregla_aunque_haya_numeros_repetidos(): void
    {
        $a = $this->crearFaq(['pregunta' => 'A', 'orden' => 5]);
        $b = $this->crearFaq(['pregunta' => 'B', 'orden' => 5]);

        $this->patch(route('panel.faqs.mover', [$b, 'arriba']));

        $this->assertSame(['B', 'A'], Faq::ordenadas()->pluck('pregunta')->all());
        $this->assertSame([1, 2], Faq::ordenadas()->pluck('orden')->all());

        $this->assertNotNull($a->refresh());
    }

    public function test_la_visibilidad_se_cambia_con_un_boton(): void
    {
        $faq = $this->crearFaq();

        $this->patch(route('panel.faqs.activa', $faq));
        $this->assertFalse($faq->refresh()->activa);

        $this->patch(route('panel.faqs.activa', $faq));
        $this->assertTrue($faq->refresh()->activa);
    }

    public function test_solo_las_visibles_salen_en_la_vista_previa(): void
    {
        $this->crearFaq(['pregunta' => 'Se ve en la web']);
        $this->crearFaq(['pregunta' => 'Esta escondida', 'activa' => false]);

        $respuesta = $this->get('/panel-psicologa/faqs')->assertOk();

        // Las dos salen en el listado de gestion.
        $respuesta->assertSee('Se ve en la web')->assertSee('Esta escondida');

        $this->assertSame(
            ['Se ve en la web'],
            $respuesta->viewData('previsualizacion')->pluck('pregunta')->all()
        );
    }

    public function test_se_elimina_y_el_resto_se_renumera(): void
    {
        $this->crearFaq(['pregunta' => 'Primera']);
        $segunda = $this->crearFaq(['pregunta' => 'Segunda']);
        $this->crearFaq(['pregunta' => 'Tercera']);

        $this->delete(route('panel.faqs.destroy', $segunda))->assertRedirect();

        $this->assertDatabaseMissing('faqs', ['id' => $segunda->id]);
        $this->assertSame([1, 2], Faq::ordenadas()->pluck('orden')->all());
    }

    public function test_el_buscador_filtra_por_pregunta_y_respuesta(): void
    {
        $this->crearFaq(['pregunta' => 'Sobre el precio', 'respuesta' => '<p>Cuesta X.</p>']);
        $this->crearFaq(['pregunta' => 'Sobre el horario', 'respuesta' => '<p>De manana.</p>']);

        // Se comprueba sobre el listado, no sobre el HTML: la vista previa de abajo
        // enseña siempre todas las visibles, porque imita la seccion real de la web.
        $this->assertSame(
            ['Sobre el precio'],
            $this->get(route('panel.faqs.index', ['q' => 'precio']))
                ->assertOk()->viewData('faqs')->pluck('pregunta')->all()
        );

        $this->assertSame(
            ['Sobre el horario'],
            $this->get(route('panel.faqs.index', ['q' => 'manana']))
                ->assertOk()->viewData('faqs')->pluck('pregunta')->all()
        );
    }

    public function test_las_preguntas_de_ejemplo_se_cargan_solo_si_no_hay_ninguna(): void
    {
        $this->post(route('panel.faqs.ejemplos'))->assertRedirect();

        $creadas = Faq::count();

        $this->assertGreaterThan(0, $creadas);
        $this->assertSame(range(1, $creadas), Faq::ordenadas()->pluck('orden')->all());

        // Un segundo intento no duplica nada.
        $this->post(route('panel.faqs.ejemplos'))->assertSessionHas('error');
        $this->assertSame($creadas, Faq::count());
    }
}
