<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 回收站模型
 * 
 * 存储已删除的文件记录，支持文件恢复和自动清理
 */
class DeletedFileModel extends Model
{
    protected $table = 'deleted_files';
    
    protected $fillable = [
        'file_id',
        'file_name',
        'original_directory_id',
        'original_directory_path',
        'file_size',
        'hash',
        'storage_path',
        'mime_type',
        'deleted_at',
        'expires_at',
    ];
    
    protected $casts = [
        'file_id' => 'integer',
        'original_directory_id' => 'integer',
        'file_size' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * 关联到原文件
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'file_id');
    }

    /**
     * 关联到原目录
     */
    public function directory(): BelongsTo
    {
        return $this->belongsTo(DirectoryModel::class, 'original_directory_id');
    }
}
