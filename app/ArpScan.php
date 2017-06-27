<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArpScan extends Model
{
    protected $fillable = [
        'time',
        'mac',
        'ip',
        'info',
    ];

    protected $casts = [
        'time' => 'datetime',
    ];
}
