<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category'];

    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('kind')
            ->wherePivot('kind', 'mentor');
    }
}
