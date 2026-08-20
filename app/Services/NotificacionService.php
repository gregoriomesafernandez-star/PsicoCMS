<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envio de avisos por email con la cuenta de Gmail de la psicologa.
 *
 * La configuracion vive en `ajustes`, no en el `.env`: la introduce ella desde
 * el panel y tiene que poder cambiarla sin tocar ficheros.
 */
class NotificacionService
{
    public const HOST = 'smtp.gmail.com';

    public const PUERTO = 587;

    private const CLAVES = ['smtp_email', 'smtp_password', 'smtp_nombre', 'smtp_avisar_citas'];

    public function __construct(private AjustesService $ajustes)
    {
    }

    public function configuracion(): array
    {
        return [
            'email' => $this->ajustes->obtener('smtp_email'),
            'nombre' => $this->ajustes->obtener('smtp_nombre'),
            'avisar_citas' => (bool) $this->ajustes->obtener('smtp_avisar_citas', true),
            'tiene_password' => filled($this->ajustes->obtener('smtp_password')),
        ];
    }

    public function configurado(): bool
    {
        return filled($this->ajustes->obtener('smtp_email'))
            && filled($this->ajustes->obtener('smtp_password'));
    }

    /** La contrasena de aplicacion se guarda cifrada, nunca en claro. */
    public function guardar(string $email, ?string $password, ?string $nombre, bool $avisarCitas): void
    {
        $valores = [
            'smtp_email' => $email,
            'smtp_nombre' => $nombre ?: null,
            'smtp_avisar_citas' => $avisarCitas,
        ];

        if (filled($password)) {
            $valores['smtp_password'] = Crypt::encryptString($this->limpiarPassword($password));
        }

        $this->ajustes->guardarVarios($valores, 'notificaciones');
    }

    public function olvidar(): void
    {
        $this->ajustes->guardarVarios(array_fill_keys(self::CLAVES, null), 'notificaciones');
    }

    /** Aplica el SMTP guardado al mailer, solo para este envio. */
    public function aplicar(): bool
    {
        if (! $this->configurado()) {
            return false;
        }

        $password = $this->password();

        if ($password === null) {
            return false;
        }

        $email = (string) $this->ajustes->obtener('smtp_email');

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => self::HOST,
            'port' => self::PUERTO,
            'encryption' => 'tls',
            'username' => $email,
            'password' => $password,
            'timeout' => 10,
        ]);
        Config::set('mail.from', [
            'address' => $email,
            'name' => $this->ajustes->obtener('smtp_nombre') ?: 'PsicoCMS',
        ]);

        return true;
    }

    /**
     * Envia un aviso a la propia psicologa. Nunca lanza: un fallo de SMTP no
     * puede tumbar el flujo desde el que se llama (por ejemplo, una reserva).
     */
    public function avisar(string $asunto, string $vista, array $datos = []): bool
    {
        if (! $this->aplicar()) {
            return false;
        }

        try {
            Mail::send($vista, $datos, function ($mensaje) use ($asunto) {
                $mensaje->to((string) $this->ajustes->obtener('smtp_email'))->subject($asunto);
            });

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    /** Como avisar(), pero devolviendo el motivo del fallo para la pantalla. */
    public function probar(): array
    {
        if (! $this->aplicar()) {
            return [false, 'Faltan datos: revisa el email y la contrasena de aplicacion.'];
        }

        try {
            Mail::send('panel.notificaciones.correo-prueba', [
                'nombre' => $this->ajustes->obtener('smtp_nombre'),
            ], function ($mensaje) {
                $mensaje->to((string) $this->ajustes->obtener('smtp_email'))
                    ->subject('Prueba de PsicoCMS');
            });

            return [true, 'Email enviado. Revisa tu bandeja de entrada.'];
        } catch (Throwable $e) {
            report($e);

            return [false, $this->explicar($e)];
        }
    }

    private function password(): ?string
    {
        $guardada = $this->ajustes->obtener('smtp_password');

        if (blank($guardada)) {
            return null;
        }

        try {
            return Crypt::decryptString($guardada);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Gmail rechaza la contrasena si lleva los espacios con los que se copia. */
    private function limpiarPassword(string $password): string
    {
        return preg_replace('/\s+/', '', $password);
    }

    private function explicar(Throwable $e): string
    {
        $mensaje = $e->getMessage();

        if (str_contains($mensaje, '535') || stripos($mensaje, 'authenticate') !== false) {
            return 'Gmail ha rechazado el acceso. Comprueba que la contrasena es una '
                . 'contrasena de aplicacion de 16 letras y que la verificacion en dos pasos esta activada.';
        }

        if (stripos($mensaje, 'timed out') !== false || stripos($mensaje, 'connection') !== false) {
            return 'No se ha podido conectar con Gmail. Puede ser un corte de red o un firewall '
                . 'bloqueando el puerto ' . self::PUERTO . '.';
        }

        return 'No se ha podido enviar: ' . $mensaje;
    }
}
