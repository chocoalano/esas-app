<?php
namespace App\Models\CoreApp;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = "notifications";
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'json',
    ];
}
