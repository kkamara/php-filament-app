<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
    ];

    public function employees(): HasMany {
        return $this->hasMany(Employee::class);
    }
    
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }
}
