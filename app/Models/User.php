<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'job_title'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path
            ? URL::temporarySignedRoute('admin.avatar', now()->addHours(12), ['user' => $this->id, 'v' => substr(md5($this->avatar_path), 0, 8)])
            : null;
    }

    /** أول حرف من الاسم (لما ما في صورة) */
    public function initial(): string
    {
        return mb_substr(trim($this->name) ?: 'إ', 0, 1);
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0] ?: $this->name;
    }
}
