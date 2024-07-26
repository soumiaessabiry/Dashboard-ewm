<?php

namespace App\Imports;

use App\Models\DataLeague;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeagueCsvImport implements ToModel, WithHeadingRow
{
    protected $leagueId;

    public function __construct($leagueId)
    {
        $this->leagueId = $leagueId;
    }

    public function model(array $row)
    {
        // return new DataLeague([
        //     'league_id' => $this->leagueId,
        //     'HEURE' => $row['HEURE'],
        //     'JOUR_1' => $row['JOUR_1'],
        //     'JOUR_2' => $row['JOUR_2'],
        //     'JOUR_3' => $row['JOUR_3'],
        //     'JOUR_4' => $row['JOUR_4'],
        //     'JOUR_5' => $row['JOUR_5'],
        //     'JOUR_6' => $row['JOUR_6'],
        //     'JOUR_7' => $row['JOUR_7'],
        //     'JOUR_8' => $row['JOUR_8'],
        //     'JOUR_9' => $row['JOUR_9'],
        //     'JOUR_10' => $row['JOUR_10'],
        //     'JOUR_11' => $row['JOUR_11'],
        //     'JOUR_12' => $row['JOUR_12'],
        //     'JOUR_13' => $row['JOUR_13'],
        //     'JOUR_14' => $row['JOUR_14'],
        //     'JOUR_15' => $row['JOUR_15'],
        //     'JOUR_16' => $row['JOUR_16'],
        //     'JOUR_17' => $row['JOUR_17'],
        //     'JOUR_18' => $row['JOUR_18'],
        //     'JOUR_19' => $row['JOUR_19'],
        //     'JOUR_20' => $row['JOUR_20'],
        //     'JOUR_21' => $row['JOUR_21'],
        //     'JOUR_22' => $row['JOUR_22'],
        //     'JOUR_23' => $row['JOUR_23'],
        //     'JOUR_24' => $row['JOUR_24'],
        //     'JOUR_25' => $row['JOUR_25'],
        // ]);
    }
}

