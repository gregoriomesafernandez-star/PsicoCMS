<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;
use RuntimeException;

class InstaladorService
{
    public const BD_VACIA = 'vacia';

    public const BD_PSICOCMS = 'psicocms';

    public const BD_AJENA = 'ajena';

    public function estaInstalado(): bool
    {
        return file_exists($this->rutaMarcador());
    }

    public function marcarInstalado(): void
    {
        file_put_contents($this->rutaMarcador(), now()->toDateTimeString());
    }

    public function rutaMarcador(): string
    {
        return storage_path('installed');
    }

    public function probarConexion(array $datos): void
    {
        try {
            new PDO(
                sprintf('mysql:host=%s;port=%s', $datos['db_host'], $datos['db_port']),
                $datos['db_username'],
                $datos['db_password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
            );
        } catch (PDOException $e) {
            throw new RuntimeException($this->mensajeConexion($e));
        }
    }

    public function crearBaseDatos(array $datos): void
    {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%s', $datos['db_host'], $datos['db_port']),
                $datos['db_username'],
                $datos['db_password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
            );

            $nombre = str_replace('`', '', $datos['db_database']);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$nombre}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            throw new RuntimeException($this->mensajeConexion($e));
        }
    }

    public function aplicarConexionEnRuntime(array $datos): void
    {
        config([
            'database.connections.mysql.host' => $datos['db_host'],
            'database.connections.mysql.port' => $datos['db_port'],
            'database.connections.mysql.database' => $datos['db_database'],
            'database.connections.mysql.username' => $datos['db_username'],
            'database.connections.mysql.password' => $datos['db_password'] ?? '',
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    public function escribirEnv(array $datos): void
    {
        $ruta = base_path('.env');
        $contenido = file_get_contents($ruta);

        $reemplazos = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $datos['db_host'],
            'DB_PORT' => $datos['db_port'],
            'DB_DATABASE' => $datos['db_database'],
            'DB_USERNAME' => $datos['db_username'],
            'DB_PASSWORD' => $datos['db_password'] ?? '',
        ];

        foreach ($reemplazos as $clave => $valor) {
            $valor = $this->entrecomillarSiHaceFalta($valor);
            $patron = '/^' . preg_quote($clave, '/') . '=.*$/m';

            $contenido = preg_match($patron, $contenido)
                ? preg_replace($patron, $clave . '=' . $valor, $contenido)
                : $contenido . PHP_EOL . $clave . '=' . $valor;
        }

        $temporal = $ruta . '.tmp';
        file_put_contents($temporal, $contenido);
        rename($temporal, $ruta);
    }

    public function estadoBaseDatos(): string
    {
        $tablas = [];

        foreach (DB::connection('mysql')->select('SHOW TABLES') as $fila) {
            $valores = array_values((array) $fila);
            $tablas[] = (string) ($valores[0] ?? '');
        }

        if (empty($tablas)) {
            return self::BD_VACIA;
        }

        $propias = ['migrations', 'psicologas', 'ajustes', 'perfil_publico'];

        return array_intersect($propias, $tablas) ? self::BD_PSICOCMS : self::BD_AJENA;
    }

    public function migrar(): void
    {
        $estado = $this->estadoBaseDatos();

        if ($estado === self::BD_AJENA) {
            throw new RuntimeException(
                'Esa base de datos ya contiene tablas de otra aplicacion. '
                . 'Elige otro nombre para la base de datos de PsicoCMS.'
            );
        }

        $comando = $estado === self::BD_PSICOCMS ? 'migrate:fresh' : 'migrate';

        Artisan::call($comando, ['--force' => true]);
    }

    public function sembrar(): void
    {
        Artisan::call('db:seed', ['--force' => true]);
    }

    public function limpiarCaches(): void
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    private function entrecomillarSiHaceFalta(string $valor): string
    {
        return preg_match('/\s/', $valor) ? '"' . $valor . '"' : $valor;
    }

    private function mensajeConexion(PDOException $e): string
    {
        return match ((int) $e->getCode()) {
            1045 => 'El usuario o la contrasena de MySQL no son correctos.',
            2002 => 'No se puede conectar con el servidor MySQL. Comprueba que XAMPP este arrancado y que el host y el puerto sean correctos.',
            default => 'No se ha podido conectar con la base de datos: ' . $e->getMessage(),
        };
    }
}
