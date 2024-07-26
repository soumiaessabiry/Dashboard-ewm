<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marche;
use App\Imports\DatamarcheImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\DataLeague;
use App\Models\DataMarche;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Ajoutez cette ligne

class DataMarchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $marches = Marche::all(); // Paginer les ligues selon vos besoins
        return view('marches_exportation_csv', compact('marches'));
    }


    public function storeDataMarche(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'marche_id' => 'required|exists:marches,id',
            'mois_selected' => 'required',
        ]);

        try {
            $marche_id = $request->marche_id;
            $file = $request->file('file');
            $mois_selected = $request->mois_selected;

            $marche = Marche::findOrFail($marche_id);
            $mois_francais = Carbon::createFromFormat('m', $mois_selected)->locale('fr_FR')->format('F_Y');
            $marchesFolder = 'marches/' . $marche->titre . '/' . $mois_francais;

            $existingFiles = Storage::files($marchesFolder);

            $newFileName = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

            foreach ($existingFiles as $existingFile) {
                $oldFileName = basename($existingFile);
                $newOldFileName = Carbon::now()->format('Ymd_His') . '_' . $oldFileName;
                Storage::move($existingFile, $marchesFolder . '/' . $newOldFileName);
            }

            $filePath = $file->storeAs($marchesFolder, $newFileName);

            $mois_selected_date = '01/' . $mois_selected . '/' . Carbon::now()->year;

            Excel::import(new DatamarcheImport($marche_id, $mois_selected_date), storage_path('app/' . $filePath));


            return redirect()->back()->with('csv_success', 'Fichier CSV importé avec succès pour le marché.');
        } catch (\Exception $e) {
            return redirect()->back()->with('csv_error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');
        }
    }

    // recupeer data par marcher pour tous les jours
    public function getDataForMarches(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Initialiser un tableau pour stocker les valeurs par jour
        $valuesByDay = [
            'JOUR_1' => [],
            'JOUR_2' => [],
            'JOUR_3' => [],
            'JOUR_4' => [],
            'JOUR_5' => [],
            'JOUR_6' => [],
            'JOUR_7' => [],
            'JOUR_8' => [],
            'JOUR_9' => [],
            'JOUR_10' => [],
            'JOUR_11' => [],
            'JOUR_12' => [],
            'JOUR_13' => [],
            'JOUR_14' => [],
            'JOUR_15' => [],
            'JOUR_16' => [],
            'JOUR_17' => [],
            'JOUR_18' => [],
            'JOUR_19' => [],
            'JOUR_20' => [],
            'JOUR_21' => [],
            'JOUR_22' => [],
            'JOUR_23' => [],
            'JOUR_24' => [],
            'JOUR_25' => [],
        ];

        // Parcourir les données et ajouter les valeurs aux tableaux correspondants
        foreach ($data as $record) {
            for ($i = 1; $i <= 25; $i++) {
                $dayKey = "JOUR_$i";
                if (isset($record->$dayKey)) {
                    $valuesByDay[$dayKey][] = $record->$dayKey;
                }
            }
        }

        // Récupérer le mois sélectionné
        $dateParam = $request->query('date');

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $dateParam)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        // Initialiser un tableau pour stocker les jours valides
        $days = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $dayKey = "JOUR_$dayIndex";
                $days[$dayKey] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->translatedFormat('l'),
                    'values' => $valuesByDay[$dayKey] ?? []
                ];
                $dayIndex++;
            }
        }

        return response()->json($days);
    }

    // recupeer data par jour  spesifer
    public function getDataForToday(Request $request, $marche_id)
    {

        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date spécifique
        $dateParam = $request->query('date'); // Format : d/m/Y

        try {
            $specificDate = Carbon::createFromFormat('d/m/Y', $dateParam);
        } catch (\Exception $e) {
            Log::error("Date format error: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        // Vérifier si la date spécifique appartient au mois sélectionné
        if ($specificDate->month != $startDate->month || $specificDate->year != $startDate->year) {
            return response()->json(['error' => 'The specified date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date spécifique est valide
        $specificDateFormatted = $specificDate->format('Y-m-d');
        if (!isset($validDays[$specificDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date spécifique
        $dayKey = $validDays[$specificDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $specificDateFormatted,
            'day' => $specificDate->translatedFormat('l'),
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                $result['values'][] = [
                    'heures' => $record->HEURES,
                    'value' => $record->$dayKey
                ];
            }
        }

        return response()->json($result);
    }
    // recupeer data par jour  actuel

    public function getDataForCurrenday(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date actuelle
        $specificDate = Carbon::now();

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($specificDate->month != $startDate->month || $specificDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $specificDateFormatted = $specificDate->format('Y-m-d');
        if (!isset($validDays[$specificDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$specificDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $specificDateFormatted,
            'day' => $specificDate->translatedFormat('l'),
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                $result['values'][] = [
                    'heures' => $record->HEURES,
                    'value' => $record->$dayKey
                ];
            }
        }

        return response()->json($result);
    }
    // recupere data par  de joure actuel par heur
    public function getDataForCurrentHour(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date et l'heure actuelles
        $specificDate = Carbon::now();
        $currentHour = $specificDate->hour;

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($specificDate->month != $startDate->month || $specificDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $specificDateFormatted = $specificDate->format('Y-m-d');
        if (!isset($validDays[$specificDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$specificDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $specificDateFormatted,
            'day' => $specificDate->translatedFormat('l'),
            'hour' => $currentHour,
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                // Récupérer les heures et les valeurs pour chaque enregistrement
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKey);
                foreach ($heureValeurs as $index => $heure) {
                    if (intval($heure) == $currentHour) {
                        $result['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }
    public function getDataForCurrentHourAndMinute(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date et l'heure actuelles
        $currentDate = Carbon::now();
        $currentHour = $currentDate->format('H');
        $currentMinute = $currentDate->format('i');

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $currentDateFormatted = $currentDate->format('Y-m-d');
        if (!isset($validDays[$currentDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$currentDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $currentDateFormatted,
            'day' => $currentDate->translatedFormat('l'),
            'hour' => $currentHour,
            'minute' => $currentMinute,
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                // Récupérer les heures et les valeurs pour chaque enregistrement
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKey);

                foreach ($heureValeurs as $index => $heure) {
                    if (strpos($heure, $currentHour) === 0 && strpos($heure, '.' . $currentMinute) !== false) {
                        $result['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }
    // par 5min

    // public function getDataForLast5Minutes(Request $request, $marche_id)
    // {

    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Récupérer le mois sélectionné depuis la table
    //     $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error in mois_selected: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    //     }

    //     // Récupérer la date et l'heure actuelles
    //     $currentDate = Carbon::now();
    //     $currentHour = $currentDate->format('H');
    //     $currentMinute = $currentDate->format('i');

    //     // Calculer l'heure et les minutes de début pour les 5 minutes précédentes
    //     $startMinute = max(0, $currentMinute - 5);

    //     // Vérifier si la date actuelle appartient au mois sélectionné
    //     if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
    //         return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
    //     }

    //     // Initialiser le tableau des jours valides
    //     $validDays = [];
    //     $dayIndex = 1;

    //     // Parcourir les jours du mois en excluant les week-ends
    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
    //             $dayIndex++;
    //         }
    //     }

    //     // Vérifier si la date actuelle est valide
    //     $currentDateFormatted = $currentDate->format('Y-m-d');
    //     if (!isset($validDays[$currentDateFormatted])) {
    //         return response()->json(['error' => 'Invalid day'], 400);
    //     }

    //     // Obtenir le champ JOUR_X correspondant à la date actuelle
    //     $dayKey = $validDays[$currentDateFormatted];

    //     // Initialiser le résultat
    //     $result = [
    //         'date' => $currentDateFormatted,
    //         'day' => $currentDate->translatedFormat('l'),
    //         'hour' => $currentDate->format('H:i'),
    //         'values' => []
    //     ];

    //     // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
    //     foreach ($data as $record) {
    //         if (isset($record->$dayKey) && isset($record->HEURES)) {
    //             // Récupérer les heures et les valeurs pour chaque enregistrement
    //             $heureValeurs = explode(',', $record->HEURES);
    //             $jourValeurs = explode(',', $record->$dayKey);

    //             foreach ($heureValeurs as $index => $heure) {
    //                 $heureMinute = (int) substr($heure, -2);
    //                 $heureHeure = (int) substr($heure, 0, 2);

    //                 // Vérifier si l'heure est dans la plage des 5 minutes précédentes
    //                 if ($heureHeure == $currentHour && $heureMinute >= $startMinute && $heureMinute < $currentMinute) {
    //                     $result['values'][] = [
    //                         'heures' => $heure,
    //                         'value' => $jourValeurs[$index] ?? null
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json($result);
    // }
    public function getDataForLast5Hours(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date et l'heure actuelles
        $currentDate = Carbon::now();
        $currentHour = $currentDate->format('H');
        $currentMinute = $currentDate->format('i');

        // Calculer l'heure et les minutes de début pour les 5 heures précédentes
        $startHour = $currentHour - 5;
        $startMinute = $currentMinute;

        if ($startHour < 0) {
            $startHour += 24;
            $startDate->subDay(); // Ajuster le début de la journée si nécessaire
        }

        // Ajuster le début de la plage si l'heure de début est avant 8:00
        if ($startHour < 8 || ($startHour == 8 && $startMinute < 0)) {
            $startHour = 8;
            $startMinute = 0;
        }

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $currentDateFormatted = $currentDate->format('Y-m-d');
        if (!isset($validDays[$currentDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$currentDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $currentDateFormatted,
            'day' => $currentDate->translatedFormat('l'),
            'hour' => $currentDate->format('H:i'),
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                // Récupérer les heures et les valeurs pour chaque enregistrement
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKey);

                foreach ($heureValeurs as $index => $heure) {
                    $heureMinute = (int) substr($heure, -2);
                    $heureHeure = (int) substr($heure, 0, 2);

                    // Vérifier si l'heure est dans la plage des 5 heures précédentes
                    if (
                        ($heureHeure > $startHour && $heureHeure < $currentHour) ||
                        ($heureHeure == $startHour && $heureMinute >= $startMinute) ||
                        ($heureHeure == $currentHour && $heureMinute <= $currentMinute) ||
                        ($startHour == 8 && $heureHeure == 8 && $heureMinute >= 0 && $heureMinute <= $currentMinute)
                    ) {
                        $result['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }



    // public function getDataForLast5Minutes(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Récupérer le mois sélectionné depuis la table
    //     $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error in mois_selected: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    //     }

    //     // Récupérer la date et l'heure actuelles
    //     $currentDate = Carbon::now();
    //     $currentHour = $currentDate->format('H');
    //     $currentMinute = $currentDate->format('i');

    //     // Calculer l'heure et les minutes de début pour les 5 minutes précédentes
    //     $startMinute = $currentMinute - 5;
    //     $startHour = $currentHour;

    //     if ($startMinute < 0) {
    //         $startMinute += 60;
    //         $startHour -= 1;
    //         if ($startHour < 0) {
    //             $startHour += 24;
    //         }
    //     }

    //     // Vérifier si la date actuelle appartient au mois sélectionné
    //     if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
    //         return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
    //     }

    //     // Initialiser le tableau des jours valides
    //     $validDays = [];
    //     $dayIndex = 1;

    //     // Parcourir les jours du mois en excluant les week-ends
    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
    //             $dayIndex++;
    //         }
    //     }

    //     // Vérifier si la date actuelle est valide
    //     $currentDateFormatted = $currentDate->format('Y-m-d');
    //     if (!isset($validDays[$currentDateFormatted])) {
    //         return response()->json(['error' => 'Invalid day'], 400);
    //     }

    //     // Obtenir le champ JOUR_X correspondant à la date actuelle
    //     $dayKey = $validDays[$currentDateFormatted];

    //     // Initialiser le résultat
    //     $result = [
    //         'date' => $currentDateFormatted,
    //         'day' => $currentDate->translatedFormat('l'),
    //         'hour' => $currentDate->format('H:i'),
    //         'values' => []
    //     ];

    //     // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
    //     foreach ($data as $record) {
    //         if (isset($record->$dayKey) && isset($record->HEURES)) {
    //             // Récupérer les heures et les valeurs pour chaque enregistrement
    //             $heureValeurs = explode(',', $record->HEURES);
    //             $jourValeurs = explode(',', $record->$dayKey);

    //             foreach ($heureValeurs as $index => $heure) {
    //                 $heureMinute = (int) substr($heure, -2);
    //                 $heureHeure = (int) substr($heure, 0, 2);

    //                 // Vérifier si l'heure est dans la plage des 5 minutes précédentes
    //                 if (
    //                     ($heureHeure == $currentHour && $heureMinute >= $startMinute && $heureMinute < $currentMinute) ||
    //                     ($heureHeure == $startHour && $heureMinute >= $startMinute && $startMinute > $currentMinute)
    //                 ) {
    //                     $result['values'][] = [
    //                         'heures' => $heure,
    //                         'value' => $jourValeurs[$index] ?? null
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json($result);
    // }
    public function getDataForLast5Minutes(Request $request, $marche_id)
{
    // Récupérer toutes les données pour le marché spécifié
    $data = DataMarche::where('marche_id', $marche_id)->get();

    // Vérifier si des données ont été récupérées
    if ($data->isEmpty()) {
        return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    }

    // Récupérer le mois sélectionné depuis la table
    $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    try {
        $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
    } catch (\Exception $e) {
        Log::error("Date format error in mois_selected: " . $e->getMessage());
        return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    }

    // Récupérer la date et l'heure actuelles
    $currentDate = Carbon::now();

    // Calculer l'heure et les minutes de début pour les 5 minutes précédentes
    $startDateTime = $currentDate->copy()->subMinutes(5)->startOfMinute();
    $endDateTime = $currentDate->copy()->startOfMinute(); // L'heure actuelle n'est pas incluse

    // Vérifier si la date actuelle appartient au mois sélectionné
    if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
        return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
    }

    // Initialiser le tableau des jours valides
    $validDays = [];
    $dayIndex = 1;

    // Parcourir les jours du mois en excluant les week-ends
    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
        if (!$date->isWeekend() && $dayIndex <= 25) {
            $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
            $dayIndex++;
        }
    }

    // Vérifier si la date actuelle est valide
    $currentDateFormatted = $currentDate->format('Y-m-d');
    if (!isset($validDays[$currentDateFormatted])) {
        return response()->json(['error' => 'Invalid day'], 400);
    }

    // Obtenir le champ JOUR_X correspondant à la date actuelle
    $dayKey = $validDays[$currentDateFormatted];

    // Initialiser le résultat
    $result = [
        'date' => $currentDateFormatted,
        'day' => $currentDate->translatedFormat('l'),
        'hour' => $currentDate->format('H:i'),
        'values' => []
    ];

    // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
    foreach ($data as $record) {
        if (isset($record->$dayKey) && isset($record->HEURES)) {
            // Récupérer les heures et les valeurs pour chaque enregistrement
            $heureValeurs = explode(',', $record->HEURES);
            $jourValeurs = explode(',', $record->$dayKey);

            foreach ($heureValeurs as $index => $heure) {
                $heureMinute = (int) substr($heure, -2);
                $heureHeure = (int) substr($heure, 0, 2);

                $heureDateTime = Carbon::createFromTime($heureHeure, $heureMinute);

                // Vérifier si l'heure est dans la plage des 5 minutes précédentes
                if ($heureDateTime->between($startDateTime, $endDateTime)) {
                    $result['values'][] = [
                        'heures' => $heure,
                        'value' => $jourValeurs[$index] ?? null
                    ];
                }
            }
        }
    }

    return response()->json($result);
}


    // public function getDataForLast15Minutes(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Récupérer le mois sélectionné depuis la table
    //     $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error in mois_selected: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    //     }

    //     // Récupérer la date et l'heure actuelles
    //     $currentDate = Carbon::now();
    //     $currentHour = $currentDate->format('H');
    //     $currentMinute = $currentDate->format('i');

    //     // Calculer l'heure et les minutes de début pour les 15 minutes précédentes
    //     $startMinute = $currentMinute - 14; // Inclure la minute actuelle et les 14 minutes précédentes
    //     $startHour = $currentHour;

    //     if ($startMinute < 0) {
    //         $startMinute += 60;
    //         $startHour -= 1;
    //         if ($startHour < 0) {
    //             $startHour += 24;
    //         }
    //     }

    //     // Vérifier si la date actuelle appartient au mois sélectionné
    //     if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
    //         return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
    //     }

    //     // Initialiser le tableau des jours valides
    //     $validDays = [];
    //     $dayIndex = 1;

    //     // Parcourir les jours du mois en excluant les week-ends
    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
    //             $dayIndex++;
    //         }
    //     }

    //     // Vérifier si la date actuelle est valide
    //     $currentDateFormatted = $currentDate->format('Y-m-d');
    //     if (!isset($validDays[$currentDateFormatted])) {
    //         return response()->json(['error' => 'Invalid day'], 400);
    //     }

    //     // Obtenir le champ JOUR_X correspondant à la date actuelle
    //     $dayKey = $validDays[$currentDateFormatted];

    //     // Initialiser le résultat
    //     $result = [
    //         'date' => $currentDateFormatted,
    //         'day' => $currentDate->translatedFormat('l'),
    //         'hour' => $currentDate->format('H:i'),
    //         'values' => []
    //     ];

    //     // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
    //     foreach ($data as $record) {
    //         if (isset($record->$dayKey) && isset($record->HEURES)) {
    //             // Récupérer les heures et les valeurs pour chaque enregistrement
    //             $heureValeurs = explode(',', $record->HEURES);
    //             $jourValeurs = explode(',', $record->$dayKey);

    //             foreach ($heureValeurs as $index => $heure) {
    //                 $heureMinute = (int) substr($heure, -2);
    //                 $heureHeure = (int) substr($heure, 0, 2);

    //                 // Vérifier si l'heure est dans la plage des 15 minutes précédentes
    //                 if (
    //                     ($heureHeure == $currentHour && $heureMinute >= $startMinute && $heureMinute <= $currentMinute) ||
    //                     ($heureHeure == $startHour && $heureMinute >= $startMinute && $startMinute > $currentMinute)
    //                 ) {
    //                     $result['values'][] = [
    //                         'heures' => $heure,
    //                         'value' => $jourValeurs[$index] ?? null
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json($result);
    // }
    public function getDataForLast15Minutes(Request $request, $marche_id)
{
    // Récupérer toutes les données pour le marché spécifié
    $data = DataMarche::where('marche_id', $marche_id)->get();

    // Vérifier si des données ont été récupérées
    if ($data->isEmpty()) {
        return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    }

    // Récupérer le mois sélectionné depuis la table
    $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    try {
        $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
    } catch (\Exception $e) {
        Log::error("Date format error in mois_selected: " . $e->getMessage());
        return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    }

    // Récupérer la date et l'heure actuelles
    $currentDate = Carbon::now();

    // Calculer l'heure et les minutes de début pour les 15 minutes précédentes
    $startDateTime = $currentDate->copy()->subMinutes(15)->startOfMinute();
    $endDateTime = $currentDate->copy()->startOfMinute(); // Exclut l'heure actuelle

    // Vérifier si la date actuelle appartient au mois sélectionné
    if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
        return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
    }

    // Initialiser le tableau des jours valides
    $validDays = [];
    $dayIndex = 1;

    // Parcourir les jours du mois en excluant les week-ends
    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
        if (!$date->isWeekend() && $dayIndex <= 25) {
            $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
            $dayIndex++;
        }
    }

    // Vérifier si la date actuelle est valide
    $currentDateFormatted = $currentDate->format('Y-m-d');
    if (!isset($validDays[$currentDateFormatted])) {
        return response()->json(['error' => 'Invalid day'], 400);
    }

    // Obtenir le champ JOUR_X correspondant à la date actuelle
    $dayKey = $validDays[$currentDateFormatted];

    // Initialiser le résultat
    $result = [
        'date' => $currentDateFormatted,
        'day' => $currentDate->translatedFormat('l'),
        'hour' => $currentDate->format('H:i'),
        'values' => []
    ];

    // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
    foreach ($data as $record) {
        if (isset($record->$dayKey) && isset($record->HEURES)) {
            // Récupérer les heures et les valeurs pour chaque enregistrement
            $heureValeurs = explode(',', $record->HEURES);
            $jourValeurs = explode(',', $record->$dayKey);

            foreach ($heureValeurs as $index => $heure) {
                $heureMinute = (int) substr($heure, -2);
                $heureHeure = (int) substr($heure, 0, 2);

                $heureDateTime = Carbon::createFromTime($heureHeure, $heureMinute);

                // Vérifier si l'heure est dans la plage des 15 minutes précédentes
                // et exclure les valeurs pour l'heure actuelle
                if ($heureDateTime->between($startDateTime, $endDateTime) &&
                    !($heureHeure == $currentDate->hour && $heureMinute == $currentDate->minute)
                ) {
                    $result['values'][] = [
                        'heures' => $heure,
                        'value' => $jourValeurs[$index] ?? null
                    ];
                }
            }
        }
    }

    return response()->json($result);
}


    public function getDataForLastHour(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date et l'heure actuelles
        $currentDate = Carbon::now();
        $currentHour = $currentDate->format('H');
        $currentMinute = $currentDate->format('i');

        // Calculer l'heure et les minutes de début pour l'heure précédente
        $startHour = $currentHour - 1;
        $startMinute = $currentMinute;

        // Si l'heure de début est avant 8:00, ajuster à 8:00
        if ($startHour < 8) {
            $startHour = 8;
            $startMinute = 0;
        }

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $currentDateFormatted = $currentDate->format('Y-m-d');
        if (!isset($validDays[$currentDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$currentDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $currentDateFormatted,
            'day' => $currentDate->translatedFormat('l'),
            'hour' => $currentDate->format('H:i'),
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                // Récupérer les heures et les valeurs pour chaque enregistrement
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKey);

                foreach ($heureValeurs as $index => $heure) {
                    $heureMinute = (int) substr($heure, -2);
                    $heureHeure = (int) substr($heure, 0, 2);

                    // Vérifier si l'heure est dans la plage de la dernière heure jusqu'à maintenant
                    if (
                        ($heureHeure == $currentHour && $heureMinute <= $currentMinute) ||
                        ($heureHeure == $startHour && $heureMinute >= $startMinute)
                    ) {
                        $result['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }


    public function getDataForLast30Minutes(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date et l'heure actuelles
        $currentDate = Carbon::now();
        $currentHour = $currentDate->format('H');
        $currentMinute = $currentDate->format('i');

        // Calculer l'heure et les minutes de début pour les 30 minutes précédentes
        $startMinute = $currentMinute - 29; // Inclure la minute actuelle et les 29 minutes précédentes
        $startHour = $currentHour;

        if ($startMinute < 0) {
            $startMinute += 60;
            $startHour -= 1;
            if ($startHour < 0) {
                $startHour += 24;
            }
        }

        // Ajuster le début de la plage si l'heure de début est avant 8:00
        if ($startHour < 8 || ($startHour == 8 && $startMinute < 0)) {
            $startHour = 8;
            $startMinute = 0;
        }

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $currentDateFormatted = $currentDate->format('Y-m-d');
        if (!isset($validDays[$currentDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$currentDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $currentDateFormatted,
            'day' => $currentDate->translatedFormat('l'),
            'hour' => $currentDate->format('H:i'),
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                // Récupérer les heures et les valeurs pour chaque enregistrement
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKey);

                foreach ($heureValeurs as $index => $heure) {
                    $heureMinute = (int) substr($heure, -2);
                    $heureHeure = (int) substr($heure, 0, 2);

                    // Vérifier si l'heure est dans la plage des 30 minutes précédentes
                    if (
                        ($heureHeure == $currentHour && $heureMinute >= $startMinute && $heureMinute <= $currentMinute) ||
                        ($heureHeure == $startHour && $heureMinute >= $startMinute && $startMinute > $currentMinute) ||
                        ($startHour == 8 && $heureHeure == 8 && $heureMinute >= 0 && $heureMinute <= $currentMinute)
                    ) {
                        $result['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }

    public function getDataForLast4Hours(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date et l'heure actuelles
        $currentDate = Carbon::now();
        $currentHour = $currentDate->format('H');
        $currentMinute = $currentDate->format('i');

        // Calculer l'heure et les minutes de début pour les 5 heures précédentes
        $startHour = $currentHour - 4;
        $startMinute = $currentMinute;

        if ($startHour < 0) {
            $startHour += 24;
            $startDate->subDay(); // Ajuster le début de la journée si nécessaire
        }

        // Ajuster le début de la plage si l'heure de début est avant 8:00
        if ($startHour < 8 || ($startHour == 8 && $startMinute < 0)) {
            $startHour = 8;
            $startMinute = 0;
        }

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $currentDateFormatted = $currentDate->format('Y-m-d');
        if (!isset($validDays[$currentDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$currentDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $currentDateFormatted,
            'day' => $currentDate->translatedFormat('l'),
            'hour' => $currentDate->format('H:i'),
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                // Récupérer les heures et les valeurs pour chaque enregistrement
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKey);

                foreach ($heureValeurs as $index => $heure) {
                    $heureMinute = (int) substr($heure, -2);
                    $heureHeure = (int) substr($heure, 0, 2);

                    // Vérifier si l'heure est dans la plage des 5 heures précédentes
                    if (
                        ($heureHeure > $startHour && $heureHeure < $currentHour) ||
                        ($heureHeure == $startHour && $heureMinute >= $startMinute) ||
                        ($heureHeure == $currentHour && $heureMinute <= $currentMinute) ||
                        ($startHour == 8 && $heureHeure == 8 && $heureMinute >= 0 && $heureMinute <= $currentMinute)
                    ) {
                        $result['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }

    public function getDataForLast5Days(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer la date actuelle
        $currentDate = Carbon::now();

        // Initialiser la date de début pour les 5 derniers jours ouvrables
        $validDays = [];
        $count = 0;
        $dateCursor = $currentDate->copy();

        // Rechercher les 5 derniers jours ouvrables
        while ($count < 5) {
            if (!$dateCursor->isWeekend()) {
                $validDays[] = $dateCursor->format('Y-m-d');
                $count++;
            }
            $dateCursor->subDay(); // Recule d'un jour
        }

        // Réverser l'ordre des jours pour obtenir du plus ancien au plus récent
        $validDays = array_reverse($validDays);

        // Initialiser le résultat
        $result = [];

        // Récupérer les valeurs pour les jours valides
        foreach ($data as $record) {
            foreach ($validDays as $date) {
                $dayKey = "JOUR_" . ($currentDate->copy()->startOfMonth()->diffInDays(Carbon::parse($date)) + 1);

                if (isset($record->$dayKey) && isset($record->HEURES)) {
                    // Récupérer les heures et les valeurs pour chaque enregistrement
                    $heureValeurs = explode(',', $record->HEURES);
                    $jourValeurs = explode(',', $record->$dayKey);

                    foreach ($heureValeurs as $index => $heure) {
                        // Ajouter les valeurs à la date correspondante
                        if (!isset($result[$date])) {
                            $result[$date] = [
                                'date' => $date,
                                'values' => []
                            ];
                        }

                        $result[$date]['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        // Retourner les résultats sous forme de tableau JSON
        return response()->json(array_values($result));
    }




    // public function getDataForCurrentDay(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Récupérer le mois sélectionné depuis la table
    //     $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error in mois_selected: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    //     }

    //     // Récupérer la date et l'heure actuelles
    //     $currentDate = Carbon::now();
    //     $currentHour = $currentDate->format('H');
    //     $currentMinute = $currentDate->format('i');

    //     // Début de la plage horaire (08:00) et fin de la plage horaire (heure et minute actuelles)
    //     $startOfPeriod = $currentDate->copy()->setTime(8, 0);
    //     $endOfPeriod = $currentDate->copy()->setTime($currentHour, $currentMinute);

    //     // Vérifier si la date actuelle appartient au mois sélectionné
    //     if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
    //         return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
    //     }

    //     // Initialiser le tableau des jours valides
    //     $validDays = [];
    //     $dayIndex = 1;

    //     // Parcourir les jours du mois en excluant les week-ends
    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
    //             $dayIndex++;
    //         }
    //     }

    //     // Vérifier si la date actuelle est valide
    //     $currentDateFormatted = $currentDate->format('Y-m-d');
    //     if (!isset($validDays[$currentDateFormatted])) {
    //         return response()->json(['error' => 'Invalid day'], 400);
    //     }

    //     // Obtenir le champ JOUR_X correspondant à la date actuelle
    //     $dayKey = $validDays[$currentDateFormatted];

    //     // Initialiser le résultat
    //     $result = [
    //         'date' => $currentDateFormatted,
    //         'day' => $currentDate->translatedFormat('l'),
    //         'hour' => $currentDate->format('H:i'),
    //         'values' => []
    //     ];

    //     // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
    //     foreach ($data as $record) {
    //         if (isset($record->$dayKey) && isset($record->HEURES)) {
    //             // Récupérer les heures et les valeurs pour chaque enregistrement
    //             $heureValeurs = explode(',', $record->HEURES);
    //             $jourValeurs = explode(',', $record->$dayKey);

    //             foreach ($heureValeurs as $index => $heure) {
    //                 $heureMinute = (int) substr($heure, -2);
    //                 $heureHeure = (int) substr($heure, 0, 2);

    //                 $heureDateTime = Carbon::createFromTime($heureHeure, $heureMinute);

    //                 // Vérifier si l'heure est dans la plage horaire de 08:00 à maintenant
    //                 if ($heureDateTime->between($startOfPeriod, $endOfPeriod)) {
    //                     $result['values'][] = [
    //                         'heures' => $heure,
    //                         'value' => $jourValeurs[$index] ?? null
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json($result);
    // }
    // public function getDataForLastDay(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Récupérer le mois sélectionné depuis la table
    //     $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error in mois_selected: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    //     }

    //     // Récupérer la date et l'heure actuelles
    //     $currentDate = Carbon::now();
    //     $currentDateFormatted = $currentDate->format('Y-m-d');
    //     $currentHour = $currentDate->format('H');
    //     $currentMinute = $currentDate->format('i');

    //     // Calculer la date du jour précédent
    //     $previousDate = $currentDate->copy()->subDay();
    //     $previousDateFormatted = $previousDate->format('Y-m-d');

    //     // Initialiser le tableau des jours valides
    //     $validDays = [];
    //     $dayIndex = 1;

    //     // Parcourir les jours du mois en excluant les week-ends
    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
    //             $dayIndex++;
    //         }
    //     }

    //     // Vérifier si les dates sont valides
    //     if (!isset($validDays[$previousDateFormatted]) || !isset($validDays[$currentDateFormatted])) {
    //         return response()->json(['error' => 'Invalid day'], 400);
    //     }

    //     // Obtenir les champs JOUR_X pour les jours précédents et actuels
    //     $previousDayKey = $validDays[$previousDateFormatted];
    //     $currentDayKey = $validDays[$currentDateFormatted];

    //     // Initialiser le résultat
    //     $result = [
    //         'previous_day' => [
    //             'date' => $previousDateFormatted,
    //             'day' => $previousDate->translatedFormat('l'),
    //             'values' => []
    //         ],
    //         'current_day' => [
    //             'date' => $currentDateFormatted,
    //             'day' => $currentDate->translatedFormat('l'),
    //             'hour' => $currentDate->format('H:i'),
    //             'values' => []
    //         ]
    //     ];

    //     // Récupérer les valeurs pour le jour précédent
    //     foreach ($data as $record) {
    //         if (isset($record->$previousDayKey) && isset($record->HEURES)) {
    //             // Récupérer les heures et les valeurs pour chaque enregistrement
    //             $heureValeurs = explode(',', $record->HEURES);
    //             $jourValeurs = explode(',', $record->$previousDayKey);

    //             foreach ($heureValeurs as $index => $heure) {
    //                 $result['previous_day']['values'][] = [
    //                     'heures' => $heure,
    //                     'value' => $jourValeurs[$index] ?? null
    //                 ];
    //             }
    //         }

    //         // Récupérer les valeurs pour le jour actuel
    //         if (isset($record->$currentDayKey) && isset($record->HEURES)) {
    //             // Récupérer les heures et les valeurs pour chaque enregistrement
    //             $heureValeurs = explode(',', $record->HEURES);
    //             $jourValeurs = explode(',', $record->$currentDayKey);

    //             foreach ($heureValeurs as $index => $heure) {
    //                 $heureMinute = (int) substr($heure, -2);
    //                 $heureHeure = (int) substr($heure, 0, 2);

    //                 // Inclure les données depuis 00:00 jusqu'à l'heure actuelle
    //                 if (
    //                     ($heureHeure < $currentHour) ||
    //                     ($heureHeure == $currentHour && $heureMinute <= $currentMinute)
    //                 ) {
    //                     $result['current_day']['values'][] = [
    //                         'heures' => $heure,
    //                         'value' => $jourValeurs[$index] ?? null
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json($result);
    // }



    // public function getDataForCurrentDay(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Récupérer le mois sélectionné depuis la table
    //     $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error in mois_selected: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
    //     }

    //     // Récupérer la date et l'heure actuelles
    //     $currentDate = Carbon::now();
    //     $currentHour = $currentDate->format('H');
    //     $currentMinute = $currentDate->format('i');

    //     // Début de la plage horaire (08:00) et fin de la plage horaire (heure et minute actuelles)
    //     $startOfPeriod = $currentDate->copy()->setTime(8, 0);
    //     $endOfPeriod = $currentDate->copy()->setTime($currentHour, $currentMinute);

    //     // Vérifier si la date actuelle appartient au mois sélectionné
    //     if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
    //         return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
    //     }

    //     // Initialiser le tableau des jours valides
    //     $validDays = [];
    //     $dayIndex = 1;

    //     // Parcourir les jours du mois en excluant les week-ends
    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
    //             $dayIndex++;
    //         }
    //     }

    //     // Vérifier si la date actuelle est valide
    //     $currentDateFormatted = $currentDate->format('Y-m-d');
    //     if (!isset($validDays[$currentDateFormatted])) {
    //         return response()->json(['error' => 'Invalid day'], 400);
    //     }

    //     // Obtenir le champ JOUR_X correspondant à la date actuelle
    //     $dayKey = $validDays[$currentDateFormatted];

    //     // Initialiser le résultat
    //     $result = [
    //         'date' => $currentDateFormatted,
    //         'day' => $currentDate->translatedFormat('l'),
    //         'hour' => $currentDate->format('H:i'),
    //         'values' => []
    //     ];

    //     // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
    //     foreach ($data as $record) {
    //         if (isset($record->$dayKey) && isset($record->HEURES)) {
    //             // Récupérer les heures et les valeurs pour chaque enregistrement
    //             $heureValeurs = explode(',', $record->HEURES);
    //             $jourValeurs = explode(',', $record->$dayKey);

    //             foreach ($heureValeurs as $index => $heure) {
    //                 $heureMinute = (int) substr($heure, -2);
    //                 $heureHeure = (int) substr($heure, 0, 2);

    //                 $heureDateTime = Carbon::createFromTime($heureHeure, $heureMinute);

    //                 // Vérifier si l'heure est dans la plage horaire de 08:00 à maintenant
    //                 if ($heureDateTime->between($startOfPeriod, $endOfPeriod)) {
    //                     $result['values'][] = [
    //                         'heures' => $heure,
    //                         'value' => $jourValeurs[$index] ?? null
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     return response()->json($result);
    // }

    public function getDataForCurrentDay(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date et l'heure actuelles
        $currentDate = Carbon::now();
        $currentHour = $currentDate->format('H');
        $currentMinute = $currentDate->format('i');

        // Début de la plage horaire (08:00) et fin de la plage horaire (heure et minute actuelles)
        $startOfPeriod = $currentDate->copy()->setTime(8, 0);
        $endOfPeriod = $currentDate->copy()->setTime($currentHour, $currentMinute);

        // Vérifier si la date actuelle appartient au mois sélectionné
        if ($currentDate->month != $startDate->month || $currentDate->year != $startDate->year) {
            return response()->json(['error' => 'The current date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si la date actuelle est valide
        $currentDateFormatted = $currentDate->format('Y-m-d');
        if (!isset($validDays[$currentDateFormatted])) {
            return response()->json(['error' => 'Invalid day'], 400);
        }

        // Obtenir le champ JOUR_X correspondant à la date actuelle
        $dayKey = $validDays[$currentDateFormatted];

        // Initialiser le résultat
        $result = [
            'date' => $currentDateFormatted,
            'day' => $currentDate->translatedFormat('l'),
            'hour' => $currentDate->format('H:i'),
            'values' => []
        ];

        // Récupérer les valeurs pour le champ JOUR_X et HEURES correspondants
        foreach ($data as $record) {
            if (isset($record->$dayKey) && isset($record->HEURES)) {
                // Récupérer les heures et les valeurs pour chaque enregistrement
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKey);

                foreach ($heureValeurs as $index => $heure) {
                    // Convertir l'heure en format 24h
                    $heureMinute = (int) substr($heure, -2);
                    $heureHeure = (int) substr($heure, 0, 2);

                    $heureDateTime = Carbon::createFromTime($heureHeure, $heureMinute);

                    // Vérifier si l'heure est dans la plage horaire de 08:00 à maintenant
                    if ($heureDateTime->between($startOfPeriod, $endOfPeriod)) {
                        $result['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        // Assurer que les valeurs jusqu'à l'heure et minute actuelles sont incluses
        // Ajouter la dernière valeur si elle existe et correspond
        if ($endOfPeriod->greaterThan($startOfPeriod)) {
            foreach ($data as $record) {
                if (isset($record->$dayKey) && isset($record->HEURES)) {
                    $heureValeurs = explode(',', $record->HEURES);
                    $jourValeurs = explode(',', $record->$dayKey);

                    foreach ($heureValeurs as $index => $heure) {
                        $heureMinute = (int) substr($heure, -2);
                        $heureHeure = (int) substr($heure, 0, 2);

                        $heureDateTime = Carbon::createFromTime($heureHeure, $heureMinute);

                        // Vérifier si l'heure est dans la plage horaire de 08:00 à maintenant
                        if ($heureDateTime->isSameMinute($endOfPeriod)) {
                            $result['values'][] = [
                                'heures' => $heure,
                                'value' => $jourValeurs[$index] ?? null
                            ];
                        }
                    }
                }
            }
        }

        return response()->json($result);
    }

    public function getDataForLastDay(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Récupérer le mois sélectionné depuis la table
        $moisSelected = $data->first()->mois_selected; // Supposant que tous les enregistrements ont le même mois sélectionné
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $moisSelected)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error in mois_selected: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format in mois_selected'], 400);
        }

        // Récupérer la date actuelle, le début de la journée précédente et la date actuelle jusqu'à maintenant
        $currentDate = Carbon::now();
        $previousDayStart = $currentDate->copy()->subDay()->startOfDay();
        $todayStart = $currentDate->copy()->startOfDay();

        // Vérifier si les dates sont dans le mois sélectionné
        if ($previousDayStart->month != $startDate->month || $previousDayStart->year != $startDate->year) {
            return response()->json(['error' => 'The previous date does not belong to the selected month'], 400);
        }

        // Initialiser le tableau des jours valides
        $validDays = [];
        $dayIndex = 1;

        // Parcourir les jours du mois en excluant les week-ends
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend() && $dayIndex <= 25) {
                $validDays[$date->format('Y-m-d')] = "JOUR_$dayIndex";
                $dayIndex++;
            }
        }

        // Vérifier si les jours précédents et actuels sont valides
        $previousDateFormatted = $previousDayStart->format('Y-m-d');
        $currentDateFormatted = $currentDate->format('Y-m-d');

        if (!isset($validDays[$previousDateFormatted]) || !isset($validDays[$currentDateFormatted])) {
            return response()->json(['error' => 'Invalid days'], 400);
        }

        // Obtenir les champs JOUR_X correspondants
        $dayKeyPrevious = $validDays[$previousDateFormatted];
        $dayKeyCurrent = $validDays[$currentDateFormatted];

        // Initialiser le résultat
        $result = [
            'previous_day' => [
                'date' => $previousDateFormatted,
                'day' => $previousDayStart->translatedFormat('l'),
                'current_date_time' => $currentDate->format('Y-m-d H:i'),
                'values' => []
            ],
            'current_day' => [
                'date' => $currentDateFormatted,
                'day' => $currentDate->translatedFormat('l'),
                'current_date_time' => $currentDate->format('Y-m-d H:i'),
                'values' => []
            ]
        ];

        // Récupérer les valeurs pour le jour précédent
        foreach ($data as $record) {
            // Traitement pour le jour précédent
            if (isset($record->$dayKeyPrevious) && isset($record->HEURES)) {
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKeyPrevious);

                foreach ($heureValeurs as $index => $heure) {
                    $heureMinute = (int) substr($heure, -2);
                    $heureHeure = (int) substr($heure, 0, 2);

                    // Vérifier si l'heure est dans la plage du début de la journée précédente jusqu'à maintenant
                    $heureDateTime = $previousDayStart->copy()->setTime($heureHeure, $heureMinute);
                    if ($heureDateTime->lte($currentDate) && $heureDateTime->gte($previousDayStart)) {
                        $result['previous_day']['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }

            // Traitement pour le jour actuel
            if (isset($record->$dayKeyCurrent) && isset($record->HEURES)) {
                $heureValeurs = explode(',', $record->HEURES);
                $jourValeurs = explode(',', $record->$dayKeyCurrent);

                foreach ($heureValeurs as $index => $heure) {
                    $heureMinute = (int) substr($heure, -2);
                    $heureHeure = (int) substr($heure, 0, 2);

                    // Vérifier si l'heure est dans la plage du début de la journée actuelle jusqu'à l'heure et la minute actuelles
                    $heureDateTime = $todayStart->copy()->setTime($heureHeure, $heureMinute);
                    if ($heureDateTime->lte($currentDate) && $heureDateTime->gte($todayStart)) {
                        $result['current_day']['values'][] = [
                            'heures' => $heure,
                            'value' => $jourValeurs[$index] ?? null
                        ];
                    }
                }
            }
        }

        return response()->json($result);
    }





}
