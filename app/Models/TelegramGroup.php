<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramGroup extends Model
{
    use HasFactory;

    protected $table = 'telegram_groups';

    protected $fillable = [
        'chat_id',
        'name',
        'type',
    ];


    // VERY IMPORTANT
    public $timestamps = true;
}
