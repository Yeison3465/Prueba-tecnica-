<?php

namespace App\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Database
{
    private static bool $booted = false;

    public static function init(): void
    {
        if (self::$booted) {
            return;
        }

        $config = require __DIR__ . '/../../config/database.php';

        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => $config['driver'] ?? 'mysql',
            'host'      => $config['host'] ?? '127.0.0.1',
            'port'      => $config['port'] ?? '3306',
            'database'  => $config['database'] ?? 'licitaciones_db',
            'username'  => $config['username'] ?? 'root',
            'password'  => $config['password'] ?? '',
            'charset'   => $config['charset'] ?? 'utf8mb4',
            'collation' => $config['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => $config['prefix'] ?? '',
            'options'   => [
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ]);

        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$booted = true;
    }
}
