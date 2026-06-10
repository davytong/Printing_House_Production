<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['title', 'category', 'grade', 'target_qty', 'total_printed'];

    public function dailyPrints()
    {
        return $this->hasMany(DailyPrint::class);
    }
}

