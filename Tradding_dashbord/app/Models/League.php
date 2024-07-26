<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_name',
        'number_of_teams',
    ];
    public function teams()
    {
        return $this->hasMany(Team::class, 'league_id', 'id');
    }
    public function getNumberOfTeamsAddedAttribute()
    {
        return $this->teams()->count();
    }
}
