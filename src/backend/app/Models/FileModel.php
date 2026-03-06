<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileModel extends Model
{
    protected $table = 'files';
    protected $fillable = [
        'name',
        'hash',
        'size',
        'mime_type',
        'directory_id',
        'storage_path',
        'upload_time',
    ];
    protected $casts = [
        'size' => 'integer',
        'directory_id' => 'integer',
    ];

    public function directory(): BelongsTo
    {
        return $this->belongsTo(DirectoryModel::class, 'directory_id');
    }
}
