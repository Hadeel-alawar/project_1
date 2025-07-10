<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'owner');
    }
}
