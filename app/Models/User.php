<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'users';
    protected $fillable = [
        'full_name',
        'email',
        'email_verified_at',
        'gender',
        'phone',
        'identity_card',
        'is_public',
        'password',
        'date_of_birth',
        'relatives',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'remember_token','token', 'created_at', 'updated_at',
        'email_verified_at'
    ];


    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $casts = [
        'date_of_birth' => 'date',
        'relatives' => 'array',
    ];



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected static function boot(){
        parent::boot();
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
