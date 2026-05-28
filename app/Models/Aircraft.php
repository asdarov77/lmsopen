<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Aircraft extends Model
{
    use HasFactory;

    protected $table = 'aircrafts';

    protected $fillable = ['title', 'path'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
