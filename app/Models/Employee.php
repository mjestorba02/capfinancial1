<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'department', 'approval_status'];

    protected $hidden = ['password'];
}