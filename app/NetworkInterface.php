<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NetworkInterface extends Model
{
    protected $table = 'înterfaces';

    public function devices()
    {
        return $this->belongsToMany(Device::class)->withTimestamps();
    }
}
