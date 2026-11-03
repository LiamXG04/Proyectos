<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'career',
        'academic_year',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)->withPivot('kind')->withTimestamps();
    }

    public function mentorSubjects(): BelongsToMany
    {
        return $this->subjects()->wherePivot('kind', 'mentor');
    }

    public function menteeSubjects(): BelongsToMany
    {
        return $this->subjects()->wherePivot('kind', 'mentee');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(MentorAvailability::class)->orderBy('day_of_week')->orderBy('starts_at');
    }

    public function sessionsAsMentor(): HasMany
    {
        return $this->hasMany(MentoringSession::class, 'mentor_id');
    }

    public function sessionsAsMentee(): HasMany
    {
        return $this->hasMany(MentoringSession::class, 'mentee_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'mentor_id');
    }
}
