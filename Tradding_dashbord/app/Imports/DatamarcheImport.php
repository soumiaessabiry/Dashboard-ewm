<?php


namespace App\Imports;

use App\Models\DataMarche;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatamarcheImport implements ToModel, WithHeadingRow
{
    private $marche_id,$mois_selected;

    public function __construct($marche_id, $mois_selected)
    {
        $this->marche_id = $marche_id;
        $this->mois_selected = $mois_selected;
    }


    public function model(array $row)
    {
        // dd($row); // Ajoutez cette ligne pour déboguer


        return new DataMarche([
            'marche_id' => $this->marche_id,
            'HEURES' => $row['heures'], // Utilisation de la clé 'heures' pour 'HEURES'
            'JOUR_1'    => $row['jour_1'],
            'JOUR_2'    => $row['jour_2'],
            'JOUR_3'    => $row['jour_3'],
            'JOUR_4'    => $row['jour_4'],
            'JOUR_5'    => $row['jour_5'],
            'JOUR_6'    => $row['jour_6'],
            'JOUR_7'    => $row['jour_7'],
            'JOUR_8'    => $row['jour_8'],
            'JOUR_9'    => $row['jour_9'],
            'JOUR_10'   => $row['jour_10'],
            'JOUR_11'   => $row['jour_11'],
            'JOUR_12'   => $row['jour_12'],
            'JOUR_13'   => $row['jour_13'],
            'JOUR_14'   => $row['jour_14'],
            'JOUR_15'   => $row['jour_15'],
            'JOUR_16'   => $row['jour_16'],
            'JOUR_17'   => $row['jour_17'],
            'JOUR_18'   => $row['jour_18'],
            'JOUR_19'   => $row['jour_19'],
            'JOUR_20'   => $row['jour_20'],
            'JOUR_21'   => $row['jour_21'],
            'JOUR_22'   => $row['jour_22'],
            'JOUR_23'   => $row['jour_23'],
            'JOUR_24'   => $row['jour_24'],
            'JOUR_25'   => $row['jour_25'],
            'mois_selected' => $this->mois_selected,
        ]);
    }
}
