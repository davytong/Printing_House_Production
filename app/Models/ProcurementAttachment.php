<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementAttachment extends Model
{
    protected $fillable = [
        'procurement_request_id', 'file_name', 'file_path',
        'file_type', 'file_size', 'uploaded_by',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    public function isImage(): bool
    {
        return in_array($this->file_type, ['image', 'jpg', 'jpeg', 'png']);
    }

    public function sizeForHumans(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 0) . ' KB';
        return $bytes . ' B';
    }
}
