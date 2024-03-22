<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    public function getJWTCustomClaims()
    {
        return [];
    }
    

    public function transformData(Request $request)
    {
        if (isset($request["password"]))
            $request["password"] = Hash::make($request["password"]);
    }

    public function beforeSave()
    {
    }

    public function afterSave()
    {
    }

    public function getRules()
    {
        return [];
    }

    public function with_includes()
    {
        return array_merge($this->belongsTo, $this->hasOne, $this->hasMany);
    }

    public $belongsTo = [];
    public $hasOne = [];

    public function __toString()
    {
        return $this->name;
    }

    public $hasMany = ['enderecos'];
    public function enderecos(){
        return $this->hasMany(Endereco::class);
    }
}
