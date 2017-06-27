<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    public function interfaces()
    {
        return $this->belongsToMany(NetworkInterface::class)->withTimestamps();
    }
}
