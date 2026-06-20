<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementLog extends Model
{
    protected $fillable = [
        'procurement_request_id', 'action', 'performed_by',
        'details', 'old_value', 'new_value',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }
}
