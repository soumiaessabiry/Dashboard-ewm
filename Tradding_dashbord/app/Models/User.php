<?php

// namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

// class User extends Authenticatable
// {
//     use HasApiTokens, HasFactory, Notifiable;

//     /**
//      * The attributes that are mass assignable.
//      *
//      * @var string[]
//      */
//     protected $fillable = [
//         // 'name',
//         // 'email',
//         // 'password',
//         // 'phone',
//         // 'location',
//         // 'about_me',
//         'username',
//         'email',
//         'password',
//         'country',
//         'team_id',
//         'remember_token',
//         'role', // Ajout du champ de rôle

//     ];

//     /**
//      * The attributes that should be hidden for serialization.
//      *
//      * @var array
//      */
//     protected $hidden = [
//         'password',
//         'remember_token',
//     ];

//     /**
//      * The attributes that should be cast.
//      *
//      * @var array
//      */

//     protected $casts = [
//         'email_verified_at' => 'datetime',
//     ];

//     // Méthode pour vérifier le rôle de l'utilisateur
//     // public function isAdmin()
//     // {
//     //     return $this->role === 'admin';
//     // }
//     public function team()
//     {
//         return $this->belongsTo(Team::class, 'team_id', 'id');
//     }
//     public function users()
//     {
//         return $this->hasMany(User::class, 'team_id', 'id');
//     }
//     public function getNumberOfPlayersAttribute()
//     {
//         return $this->users()->count();
//     }
//     public function league()
//     {
//         return $this->belongsTo(League::class, 'league_id', 'id');
//     }
// }

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'country',
        'team_id',
        'role', // Ajout du champ de rôle
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Méthode pour vérifier le rôle de l'utilisateur
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Relation avec l'équipe
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Mutateur pour gérer la valeur de team_id en fonction du rôle
    public function setTeamIdAttribute($value)
    {
        $this->attributes['team_id'] = $this->isAdmin() ? null : $value;
    }
}
