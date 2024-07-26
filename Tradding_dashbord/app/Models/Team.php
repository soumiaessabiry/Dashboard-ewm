<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id',
        'team_code',
        'team_name',
        'number_of_players',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'team_id', 'id');
    }

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'id');
    }
    public function getNumberOfPlayersAttribute()
    {
        return $this->users()->count();
    }

}
