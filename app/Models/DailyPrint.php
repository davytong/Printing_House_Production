<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyPrint extends Model
{
    protected $fillable = ['book_id','printed_today','date'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
