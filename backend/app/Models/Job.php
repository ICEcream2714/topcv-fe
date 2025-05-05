<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'positionName',
        'companyName',
        'companyLocation',
        'description',
        'applicationButton',
        'deadline'
    ];
}
