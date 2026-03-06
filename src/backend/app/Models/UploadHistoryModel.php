<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadHistoryModel extends Model
{
    protected $table = 'upload_histories';
    protected $fillable = [
        'file_id',
        'original_name',
        'target_directory',
        'file_size',
        'hash',
        'is_instant_upload',
        'status',
        'error_message',
        'upload_time',
    ];
    protected $casts = [
        'file_id' => 'integer',
        'file_size' => 'integer',
        'is_instant_upload' => 'boolean',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'file_id');
    }
}
