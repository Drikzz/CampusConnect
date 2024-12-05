<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    //
    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
