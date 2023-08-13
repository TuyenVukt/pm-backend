<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmailWithToken;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
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
        'workspace_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendEmailVerificationNotificationWithToken($token, $password)
    {
        $this->notify(new VerifyEmailWithToken($token, $password));
    }

    public function sendPasswordResetNotification($token)
    {
        //link-token-reset
        $url = 'http://localhost:3000/reset-password?token=' . $token;
        // $url = 'https://tuyen-sp.vercel.app/reset-password?token=' . $token;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'user_project', 'user_id', 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
