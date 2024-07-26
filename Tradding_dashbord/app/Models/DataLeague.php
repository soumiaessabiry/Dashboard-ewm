<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLeague extends Model
{
    use HasFactory;

    protected $table = 'league_csv_data';

   
    protected $fillable = [
        'league_id',
        'HEURES',
        'JOUR_1', 'JOUR_2', 'JOUR_3', 'JOUR_4', 'JOUR_5', 'JOUR_6', 'JOUR_7', 'JOUR_8', 'JOUR_9', 'JOUR_10',
        'JOUR_11', 'JOUR_12', 'JOUR_13', 'JOUR_14', 'JOUR_15', 'JOUR_16', 'JOUR_17', 'JOUR_18', 'JOUR_19', 'JOUR_20',
        'JOUR_21', 'JOUR_22', 'JOUR_23', 'JOUR_24', 'JOUR_25',
    ];
    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
