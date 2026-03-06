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
    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }
}
