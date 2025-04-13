<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
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
        'address',
        'is_completed',
        'is_active',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 'remember_token','token',
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
            $id = Str::uuid();

            $model->id = $id;

            Wallet::create([
                'object_id' => $id,
                'objecct_type'=> config('constant.object.type.user')
            ]);

        });

        static::deleting(function ($model) {
           Lodging::where('user_id', $model->id)->delete();
        });

    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'object_id', 'id');
    }
}
