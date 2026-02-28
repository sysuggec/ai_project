<?php

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    /**
     * 初始化数据库连接
     */
    public static function init(): void
    {
        $capsule = new Capsule();

        // 获取数据库配置
        $connection = self::getEnv('DB_CONNECTION', 'sqlite');
        
        if ($connection === 'sqlite') {
            $capsule->addConnection([
                'driver' => 'sqlite',
                'database' => self::getEnv('DB_DATABASE', __DIR__ . '/../database/riskctl.db'),
                'prefix' => '',
            ]);
        } else {
            $capsule->addConnection([
                'driver' => 'mysql',
                'host' => self::getEnv('DB_HOST', '127.0.0.1'),
                'port' => self::getEnv('DB_PORT', '3306'),
                'database' => self::getEnv('DB_DATABASE', 'riskctl'),
                'username' => self::getEnv('DB_USERNAME', 'root'),
                'password' => self::getEnv('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]);
        }

        // 设置全局可用
        $capsule->setAsGlobal();

        // 启动 Eloquent
        $capsule->bootEloquent();
    }

    /**
     * 获取环境变量
     */
    private static function getEnv(string $key, string $default = ''): string
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * 获取 Capsule 实例（用于直接查询）
     */
    public static function getCapsule(): Capsule
    {
        return app('db');
    }
}
