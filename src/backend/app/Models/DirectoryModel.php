<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectoryModel extends Model
{
    protected $table = 'directories';
    protected $fillable = ['name', 'path', 'parent_id'];
    protected $casts = [
        'parent_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DirectoryModel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DirectoryModel::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(FileModel::class, 'directory_id');
    }
}
