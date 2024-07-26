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

    // public function storedatamarche(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|mimes:xlsx,xls',
    //         'marche_id' => 'required|exists:marches,id',
    //         'mois_selected' => 'required',
    //     ]);

    //     try {
    //         $marche_id = $request->marche_id;
    //         $file = $request->file('file');
    //         $mois_selected = $request->mois_selected;

    //         $marche = Marche::findOrFail($marche_id);
    //         $marchesFolder = 'marches/' . $marche->titre;

    //         $existingFiles = Storage::files($marchesFolder);

    //         $newFileName = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

    //         foreach ($existingFiles as $existingFile) {
    //             $dateTime = Carbon::now()->format('Ymd_His');
    //             $oldFileName = basename($existingFile);
    //             $newOldFileName = $dateTime . '_' . $oldFileName;

    //             Storage::move($existingFile, $marchesFolder . '/' . $newOldFileName);
    //         }

    //         $filePath = $file->storeAs($marchesFolder, $newFileName);

    //         $mois_selected_date = '01/' . $mois_selected . '/' . Carbon::now()->year;

    //         Excel::import(new DatamarcheImport($marche_id, $mois_selected_date), storage_path('app/' . $filePath));
    //         $importedData = Datamarche::where('marche_id', $marche_id)->where('mois_selected', $mois_selected_date)->get();
    //         foreach ($importedData as $data) {
    //             $this->distributeDataAcrossMonth($data, $mois_selected_date);
    //         }

    //         return redirect()->back()->with('csv_success', 'Fichier CSV importé avec succès pour le marché.');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('csv_error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');
    //     }
    // }

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


    // cette fonction reurner les valuer de huer pour chque jour
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

        // Récupérer les jours du mois sélectionné et exclure les week-ends
        $dateParam = $request->query('date');

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $dateParam)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $days = [];
        $dayIndex = 1;

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

    // cette fonction reurner les valuer de huer pour chque jour heur:velue
    //     public function getDataForMarches(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Initialiser un tableau pour stocker les valeurs par jour
    //     $valuesByDay = [];
    //     foreach ($data as $record) {
    //         $date = Carbon::createFromFormat('d/m/Y', $record->mois_selected); // Assuming mois_selected is in d/m/Y format
    //         $heures = $record->HEURES; // Valeur de la colonne HEURES
    //         for ($i = 1; $i <= 25; $i++) {
    //             $dayKey = "JOUR_$i";
    //             if (isset($record->$dayKey)) {
    //                 $valuesByDay[$date->format('Y-m-d')][$heures] = $record->$dayKey;
    //             }
    //         }
    //     }

    //     // Récupérer les jours du mois sélectionné et exclure les week-ends
    //     $dateParam = $request->query('date');

    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $dateParam)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format'], 400);
    //     }

    //     $days = [];

    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend()) {
    //             $formattedDate = $date->format('Y-m-d');
    //             $days[] = [
    //                 'date' => $formattedDate,
    //                 'day' => $date->translatedFormat('l'),
    //                 'values' => array_map(function($heure, $value) {
    //                     return [
    //                         'heures' => $heure,
    //                         'value' => $value
    //                     ];
    //                 }, array_keys($valuesByDay[$formattedDate] ?? []), $valuesByDay[$formattedDate] ?? [])
    //             ];
    //         }
    //     }

    //     return response()->json($days);
    // }


    // Fonction coorect
    // public function getDataForMarches(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Initialiser les tableaux pour stocker les valeurs par jour
    //     $valuesByDay = [];
    //     $heuresByDay = [];

    //     // Parcourir les données et ajouter les valeurs aux tableaux correspondants
    //     foreach ($data as $record) {
    //         $heuresValue = $record->HEURES; // Extraire la valeur de HEURES
    //         for ($i = 1; $i <= 25; $i++) {
    //             $dayKey = "JOUR_$i";
    //             if (isset($record->$dayKey)) {
    //                 $valuesByDay[$i][] = $record->$dayKey;
    //                 $heuresByDay[$i][] = $heuresValue; // Associer la valeur HEURES
    //             }
    //         }
    //     }

    //     // Récupérer les jours du mois sélectionné et exclure les week-ends
    //     $dateParam = $request->query('date');

    //     try {
    //         $startDate = Carbon::createFromFormat('d/m/Y', $dateParam)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format'], 400);
    //     }

    //     $days = [];
    //     $dayIndex = 1;

    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $dayKey = $dayIndex;
    //             $values = $valuesByDay[$dayKey] ?? [];
    //             $heures = $heuresByDay[$dayKey] ?? [];

    //             // Créer une liste des valeurs avec leur correspondance HEURES
    //             $dayValues = [];
    //             foreach ($values as $index => $value) {
    //                 $dayValues[] = [
    //                     'heures' => $heures[$index] ?? null,
    //                     'value' => $value
    //                 ];
    //             }

    //             $days[$dayKey] = [
    //                 'date' => $date->format('Y-m-d'),
    //                 'day' => $date->translatedFormat('l'),
    //                 'values' => $dayValues
    //             ];

    //             $dayIndex++;
    //         }
    //     }

    //     return response()->json($days);
    // }
    // public function getDataForMarches(Request $request, $marche_id)
    // {
    //     // Récupérer toutes les données pour le marché spécifié
    //     $data = DataMarche::where('marche_id', $marche_id)->get();

    //     // Vérifier si des données ont été récupérées
    //     if ($data->isEmpty()) {
    //         return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
    //     }

    //     // Initialiser les tableaux pour stocker les valeurs par jour
    //     $valuesByDay = [];
    //     $heuresByDay = [];

    //     // Extraire la date du mois sélectionné
    //     $moisSelectedDate = null;
    //     foreach ($data as $record) {
    //         // Assumer que chaque enregistrement a une colonne `mois_selected`
    //         $moisSelectedDate = $record->mois_selected;
    //         break; // Utiliser la première date trouvée pour tous les enregistrements
    //     }

    //     if (!$moisSelectedDate) {
    //         return response()->json(['message' => 'Date du mois sélectionné non trouvée'], 404);
    //     }

    //     try {
    //         // Convertir la date en un objet Carbon et obtenir le premier et dernier jour du mois
    //         $startDate = Carbon::createFromFormat('d/m/Y', $moisSelectedDate)->startOfMonth();
    //         $endDate = $startDate->copy()->endOfMonth();
    //     } catch (\Exception $e) {
    //         Log::error("Date format error: " . $e->getMessage());
    //         return response()->json(['error' => 'Invalid date format'], 400);
    //     }

    //     // Parcourir les données et ajouter les valeurs aux tableaux correspondants
    //     foreach ($data as $record) {
    //         $heuresValue = $record->HEURES; // Extraire la valeur de HEURES
    //         for ($i = 1; $i <= 25; $i++) {
    //             $dayKey = "JOUR_$i";
    //             if (isset($record->$dayKey)) {
    //                 $valuesByDay[$i][] = $record->$dayKey;
    //                 $heuresByDay[$i][] = $heuresValue; // Associer la valeur HEURES
    //             }
    //         }
    //     }

    //     $days = [];
    //     $dayIndex = 1;

    //     for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
    //         if (!$date->isWeekend() && $dayIndex <= 25) {
    //             $dayKey = $dayIndex;
    //             $values = $valuesByDay[$dayKey] ?? [];
    //             $heures = $heuresByDay[$dayKey] ?? [];

    //             // Créer une liste des valeurs avec leur correspondance HEURES
    //             $dayValues = [];
    //             foreach ($values as $index => $value) {
    //                 $dayValues[] = [
    //                     'heures' => $heures[$index] ?? null,
    //                     'value' => $value
    //                 ];
    //             }

    //             $days[$dayKey] = [
    //                 'date' => $date->format('Y-m-d'),
    //                 'day' => $date->translatedFormat('l'),
    //                 'values' => $dayValues
    //             ];

    //             $dayIndex++;
    //         }
    //     }

    //     return response()->json($days);
    // }

    public function getDaysFromMonth(Request $request)
    {
        $dateParam = $request->query('date');

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $dateParam)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            Log::error("Date format error: " . $e->getMessage());
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $dates = [];
        Carbon::setLocale('fr');

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->translatedFormat('l')  // Utiliser
            ];
        }

        return response()->json($dates);
    }
    public function getDataForToday(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Déterminer la date d'aujourd'hui
        $today = Carbon::now()->format('Y-m-d');

        // Initialiser les tableaux pour stocker les valeurs par jour
        $valuesByDay = [];
        $heuresByDay = [];

        // Parcourir les données et ajouter les valeurs aux tableaux correspondants
        foreach ($data as $record) {
            $heuresValue = $record->HEURES; // Extraire la valeur de HEURES
            for ($i = 1; $i <= 25; $i++) {
                $dayKey = "JOUR_$i";
                if (isset($record->$dayKey)) {
                    // Ajouter les valeurs et HEURES dans les tableaux
                    $valuesByDay[$i][] = $record->$dayKey;
                    $heuresByDay[$i][] = $heuresValue;
                }
            }
        }

        // Trouver l'index du jour actuel
        $todayIndex = null;
        $startDate = Carbon::now()->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->format('Y-m-d') === $today) {
                $todayIndex = $date->day;
                break;
            }
        }

        if ($todayIndex === null) {
            return response()->json(['message' => 'Aucune donnée trouvée pour aujourd\'hui'], 404);
        }

        // Préparer les valeurs pour aujourd'hui
        $values = $valuesByDay[$todayIndex] ?? [];
        $heures = $heuresByDay[$todayIndex] ?? [];

        // Créer une liste des valeurs avec leur correspondance HEURES
        $dayValues = [];
        foreach ($values as $index => $value) {
            $dayValues[] = [
                'heures' => $heures[$index] ?? null,
                'value' => $value
            ];
        }

        return response()->json([
            'date' => $today,
            'day' => Carbon::parse($today)->translatedFormat('l'),
            'values' => $dayValues
        ]);
    }



    public function getCurrentDateTime(Request $request)
    {
        // Déterminer la date et l'heure actuelles
        $now = Carbon::now();

        // Formater la date et l'heure actuelles
        $currentDate = $now->format('Y-m-d');  // Date au format 'YYYY-MM-DD'
        $currentTime = $now->format('H:i:s');  // Heure au format 'HH:MM:SS'

        return response()->json([
            'date' => $currentDate,
            'time' => $currentTime
        ]);
    }


    public function getDataForCurrentHour(Request $request, $marche_id)
    {
        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Déterminer la date et l'heure actuelles
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentHourFormatted = $now->format('H.i'); // Formatez l'heure au format 'H.i'

        // Initialiser les tableaux pour stocker les valeurs par jour et heure
        $valuesByDay = [];
        $heuresByDay = [];

        // Parcourir les données et ajouter les valeurs aux tableaux correspondants
        foreach ($data as $record) {
            $heuresValue = $record->HEURES; // Extraire la valeur de HEURES
            for ($i = 1; $i <= 25; $i++) {
                $dayKey = "JOUR_$i";
                if (isset($record->$dayKey)) {
                    $valuesByDay[$i][] = $record->$dayKey;
                    $heuresByDay[$i][] = $heuresValue;
                }
            }
        }

        // Trouver l'index du jour actuel
        $todayIndex = null;
        $startDate = Carbon::now()->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->format('Y-m-d') === $today) {
                $todayIndex = $date->day;
                break;
            }
        }

        if ($todayIndex === null) {
            return response()->json(['message' => 'Aucune donnée trouvée pour aujourd\'hui'], 404);
        }

        // Préparer les valeurs pour l'heure actuelle
        $values = $valuesByDay[$todayIndex] ?? [];
        $heures = $heuresByDay[$todayIndex] ?? [];

        // Filtrer les valeurs pour l'heure actuelle
        $currentHourValues = [];
        foreach ($heures as $index => $heure) {
            // Comparer en arrondissant l'heure stockée à la minute la plus proche
            $heureFormatted = number_format((float) $heure, 2, '.', '');
            if ($heureFormatted == $currentHourFormatted) {
                $currentHourValues[] = [
                    'heures' => $heure,
                    'value' => $values[$index]
                ];
            }
        }

        if (empty($currentHourValues)) {
            return response()->json(['message' => 'Aucune donnée trouvée pour l\'heure actuelle'], 404);
        }

        return response()->json([
            'date' => $today,
            'day' => Carbon::parse($today)->translatedFormat('l'),
            'time' => $now->format('H:i:s'),
            'values' => $currentHourValues
        ]);
    }
    public function getDataForCurrentDateAndHour(Request $request, $marche_id)
    {
        // Déterminer la date et l'heure actuelles
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentHour = $now->format('H.i'); // Formater l'heure actuelle au format H.i

        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Initialiser les tableaux pour stocker les valeurs par jour
        $valuesByDay = [];
        $heuresByDay = [];

        // Parcourir les données et ajouter les valeurs aux tableaux correspondants
        foreach ($data as $record) {
            $heuresValue = $record->HEURES; // Extraire la valeur de HEURES
            for ($i = 1; $i <= 25; $i++) {
                $dayKey = "JOUR_$i";
                if (isset($record->$dayKey)) {
                    $valuesByDay[$i][] = $record->$dayKey;
                    $heuresByDay[$i][] = $heuresValue; // Associer la valeur HEURES
                }
            }
        }

        // Filtrer les données pour la date actuelle
        $dayIndex = $now->day;
        $values = $valuesByDay[$dayIndex] ?? [];
        $heures = $heuresByDay[$dayIndex] ?? [];

        // Préparer les valeurs pour l'heure actuelle
        $currentHourValues = [];
        foreach ($heures as $index => $heure) {
            // Comparer en arrondissant l'heure stockée à la minute la plus proche
            $heureFormatted = number_format((float) $heure, 2, '.', '');
            if ($heureFormatted == $currentHour) {
                $currentHourValues[] = [
                    'heures' => $heure,
                    'value' => $values[$index]
                ];
            }
        }

        if (empty($currentHourValues)) {
            return response()->json(['message' => 'Aucune donnée trouvée pour l\'heure actuelle'], 404);
        }

        return response()->json([
            'date' => $today,
            'day' => $now->translatedFormat('l'),
            'time' => $now->format('H:i:s'),
            'values' => $currentHourValues
        ]);
    }


    public function getDataForCurrentDate(Request $request, $marche_id)
    {
        // Déterminer la date et l'heure d'aujourd'hui
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentHour = $now->format('H.i'); // Heure au format 'H.i'

        // Récupérer toutes les données pour le marché spécifié
        $data = DataMarche::where('marche_id', $marche_id)
            ->get();

        // Vérifier si des données ont été récupérées
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour ce marché'], 404);
        }

        // Initialiser les tableaux pour stocker les valeurs par jour
        $valuesByDay = [];
        $heuresByDay = [];

        // Parcourir les données et ajouter les valeurs aux tableaux correspondants
        foreach ($data as $record) {
            $heuresValue = $record->HEURES; // Extraire la valeur de HEURES
            for ($i = 1; $i <= 25; $i++) {
                $dayKey = "JOUR_$i";
                if (isset($record->$dayKey)) {
                    // Ajouter les valeurs et HEURES dans les tableaux
                    $valuesByDay[$i][] = $record->$dayKey;
                    $heuresByDay[$i][] = $heuresValue;
                }
            }
        }

        // Trouver l'index du jour actuel
        $todayIndex = null;
        $startDate = Carbon::now()->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->format('Y-m-d') === $today) {
                $todayIndex = $date->day;
                break;
            }
        }

        if ($todayIndex === null) {
            return response()->json(['message' => 'Aucune donnée trouvée pour aujourd\'hui'], 404);
        }

        // Préparer les valeurs pour aujourd'hui
        $values = $valuesByDay[$todayIndex] ?? [];
        $heures = $heuresByDay[$todayIndex] ?? [];

        // Filtrer les valeurs pour l'heure actuelle
        $dayValues = [];
        foreach ($values as $index => $value) {
            $heure = $heures[$index] ?? null;
            $heureFormatted = number_format((float) $heure, 2, '.', '');
            if ($heureFormatted == $currentHour) {
                $dayValues[] = [
                    'heures' => $heure,
                    'value' => $value
                ];
            }
        }

        return response()->json([
            'date' => $today,
            'day' => Carbon::parse($today)->translatedFormat('l'),
            'time' => $now->format('H:i:s'),
            'values' => $dayValues
        ]);
    }


}
