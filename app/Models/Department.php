<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function users()
    {
        // Change from belongsTo to hasMany since a department has many users
        return $this->hasMany(User::class, 'wmsu_dept_id');
    }
}
