<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\PerfilPublico;
use App\Models\Psicologa;

class DocumentoService
{
    public const HUECO = '________________';

    private function catalogo(): array
    {
        return [
            'Datos del paciente' => [
                'paciente_nombre' => 'Nombre y apellidos',
                'paciente_dni' => 'DNI o NIE',
                'paciente_telefono' => 'Telefono',
                'paciente_email' => 'Email',
                'paciente_direccion' => 'Direccion',
                'paciente_fecha_nacimiento' => 'Fecha de nacimiento',
            ],
            'Tus datos profesionales' => [
                'psicologa_nombre' => 'Tu nombre y apellidos',
                'num_colegiado' => 'Numero de colegiada',
                'consulta_direccion' => 'Direccion de la consulta',
                'consulta_lugar' => 'Lugar de consulta',
                'consulta_telefono' => 'Telefono de contacto',
                'consulta_email' => 'Email de contacto',
            ],
            'Otros' => [
                'fecha' => 'Fecha de hoy',
            ],
        ];
    }

    /**
     * El token ya montado viaja hasta la vista: Blade no sabe compilar
     * unas llaves dobles escritas dentro de una expresion suya.
     */
    public function placeholders(): array
    {
        $grupos = [];

        foreach ($this->catalogo() as $grupo => $campos) {
            foreach ($campos as $clave => $texto) {
                $grupos[$grupo][] = [
                    'clave' => $clave,
                    'token' => $this->token($clave),
                    'texto' => $texto,
                ];
            }
        }

        return $grupos;
    }

    public function token(string $clave): string
    {
        return '{{' . $clave . '}}';
    }

    public function claves(): array
    {
        return array_keys(array_merge(...array_values($this->catalogo())));
    }

    public function nombreProfesional(): string
    {
        $perfil = PerfilPublico::actual();

        return $perfil->nombre_publico ?: (Psicologa::first()?->nombre_completo ?? 'PsicoCMS');
    }

    public function colegiado(): ?string
    {
        return PerfilPublico::actual()->num_colegiado;
    }

    public function resolver(?string $html, ?Paciente $paciente = null): string
    {
        $valores = $paciente ? $this->deUnPaciente($paciente) : $this->enBlanco();

        return strtr((string) $html, $valores);
    }

    private function enBlanco(): array
    {
        $vacios = [];

        foreach ($this->claves() as $clave) {
            $vacios[$this->token($clave)] = self::HUECO;
        }

        return array_merge($vacios, $this->marcar($this->deLaPsicologa()));
    }

    private function deUnPaciente(Paciente $paciente): array
    {
        return $this->marcar(array_merge($this->deLaPsicologa(), [
            'paciente_nombre' => $paciente->nombre_completo,
            'paciente_dni' => $paciente->dni,
            'paciente_telefono' => $paciente->telefono,
            'paciente_email' => $paciente->email,
            'paciente_direccion' => $paciente->direccion,
            'paciente_fecha_nacimiento' => $paciente->fecha_nacimiento?->format('d/m/Y'),
        ]));
    }

    private function deLaPsicologa(): array
    {
        $perfil = PerfilPublico::actual();
        $psicologa = Psicologa::first();

        return [
            'psicologa_nombre' => $perfil->nombre_publico ?: $psicologa?->nombre_completo,
            'num_colegiado' => $perfil->num_colegiado,
            'consulta_direccion' => $perfil->direccion,
            'consulta_lugar' => $perfil->lugar_consulta,
            'consulta_telefono' => $perfil->telefono_citas,
            'consulta_email' => $perfil->email_citas,
            'fecha' => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
        ];
    }

    private function marcar(array $valores): array
    {
        $marcados = [];

        foreach ($valores as $clave => $valor) {
            // Escapado obligatorio: el valor viene de la base de datos y acaba dentro del HTML del PDF.
            $marcados[$this->token($clave)] = filled($valor) ? e($valor) : self::HUECO;
        }

        return $marcados;
    }
}
