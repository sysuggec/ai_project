<?php
declare(strict_types=1);

namespace App\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class Database
{
    private Capsule $capsule;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        $this->capsule = new Capsule();
        $this->capsule->addConnection($config);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $this->initializeTables();
    }

    private function initializeTables(): void
    {
        $schema = $this->capsule->schema();

        if (!$schema->hasTable('directories')) {
            $schema->create('directories', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('path', 500);
                $table->unsignedInteger('parent_id')->nullable();
                $table->timestamps();
                $table->unique('path');
            });
        }

        if (!$schema->hasTable('files')) {
            $schema->create('files', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255);
                $table->string('hash', 64);
                $table->unsignedBigInteger('size');
                $table->string('mime_type', 100)->nullable();
                $table->unsignedInteger('directory_id');
                $table->string('storage_path', 500);
                $table->timestamp('upload_time');
                $table->timestamps();
                $table->index('hash');
                $table->index('directory_id');
            });
        }

        if (!$schema->hasTable('upload_histories')) {
            $schema->create('upload_histories', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('file_id')->nullable();
                $table->string('original_name', 255);
                $table->string('target_directory', 500);
                $table->unsignedBigInteger('file_size');
                $table->string('hash', 64)->nullable();
                $table->boolean('is_instant_upload')->default(false);
                $table->string('status', 20);
                $table->text('error_message')->nullable();
                $table->timestamp('upload_time');
                $table->timestamps();
            });
        }

        // 回收站表 - v1.1.0
        if (!$schema->hasTable('deleted_files')) {
            $schema->create('deleted_files', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('file_id');
                $table->string('file_name', 255);
                $table->unsignedInteger('original_directory_id')->nullable();
                $table->string('original_directory_path', 500)->nullable();
                $table->unsignedBigInteger('file_size');
                $table->string('hash', 64)->nullable();
                $table->string('storage_path', 500);
                $table->string('mime_type', 100)->nullable();
                $table->timestamp('deleted_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
                $table->index('file_id');
                $table->index('expires_at');
                $table->index('deleted_at');
            });
        }

        // 文件分享表 - v1.1.0
        if (!$schema->hasTable('shares')) {
            $schema->create('shares', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('file_id');
                $table->string('token', 64)->unique();
                $table->string('password', 255)->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('download_count')->default(0);
                $table->timestamp('created_at')->useCurrent();
                $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
                $table->index('token');
                $table->index('file_id');
                $table->index('expires_at');
            });
        }
    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }
}
