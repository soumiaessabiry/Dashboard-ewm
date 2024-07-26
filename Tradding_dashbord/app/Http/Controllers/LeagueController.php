<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\League;
use App\Models\LeagueCsvData;
use App\Models\DataLeague;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LeagueCsvImport;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeagueController extends Controller
{
    public function index()
    {
        $leagues = League::paginate(7);
        // $leagues = League::all();
        return view('management-tables.league-management', compact('leagues'));
    }
    public function showleagues()
    {
        $leagues = League::all(); // Paginer les ligues selon vos besoins
        return view('exportation_csv', compact('leagues'));
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'league_name' => 'required|string|max:255|unique:leagues,league_name',
                'number_of_teams' => 'required|integer',
            ], [
                'league_name.unique' => 'Le nom de la ligue existe déjà.',
            ]);

            $leagueData = $request->only(['league_name', 'number_of_teams']);
            $league = League::create($leagueData);

            Session::flash('ligue_success', 'La ligue a été ajoutée avec succès.');

            return redirect()->route('leagues.index');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['league_name'])) {
                Session::flash('ligue_error', $errors['league_name'][0]);
            }
            return redirect()->route('leagues.index');
        }
    }

    public function show($id)
    {
        $league = League::findOrFail($id);
        return response()->json($league);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'league_name' => 'sometimes|required|string|max:255|unique:leagues,league_name,' . $id,
                'number_of_teams' => 'sometimes|required|integer',
            ], [
                'league_name.unique' => 'Le nom de la ligue existe déjà.',
            ]);

            $league = League::findOrFail($id);
            $league->update($request->only(['league_name', 'number_of_teams']));

            Session::flash('ligue_success', 'La ligue a été mise à jour avec succès.');

            return redirect()->route('leagues.index');
        } catch (ValidationException $e) {
            $errors = $e->errors();
            if (isset($errors['league_name'])) {
                Session::flash('ligue_error', $errors['league_name'][0]);
            }
            return redirect()->route('leagues.index');
        }
    }

    public function destroy($id)
    {
        try {
            $league = League::findOrFail($id);
            $league->delete();

            Session::flash('ligue_success', 'La ligue a été supprimée avec succès.');

            return redirect()->route('leagues.index');
        } catch (\Exception $e) {
            Session::flash('ligue_error', 'Une erreur est survenue lors de la suppression de la ligue.');
            return redirect()->route('leagues.index');
        }
    }


    public function showImportForm($id)
{
    $league = League::findOrFail($id);
    return view('import-csv-form', compact('league'));
}

// public function showCsvData($league_id)
// {
//     $csvData = LeagueCsvData::where('league_id', $league_id)->first();

//     if ($csvData) {
//         $data = json_decode($csvData->csv_data, true);
//         return view('exportation_csv', compact('data'));
//     } else {
//         return redirect()->back()->with('csv_error', 'Aucune donnée trouvée pour cette ligue.');
//     }
// }
// public function getCsvDataByLeague($leagueId)
// {
//     $csvData = LeagueCsvData::where('league_id', $leagueId)->get();

//     if ($csvData->isEmpty()) {
//         return response()->json(['message' => 'Aucune donnée trouvée pour cette ligue.'], 404);
//     }

//     return response()->json($csvData, 200);
// }


// public function storeCsv(Request $request)
// {
//     try {
//         $request->validate([
//             'file' => 'required|mimes:csv,txt,xlsx|max:10240', // Validation du fichier
//             'league_id' => 'required|exists:leagues,id',
//         ]);

//         // Importer le fichier CSV en utilisant Maatwebsite Excel
//         $file = $request->file('file');
//         Excel::import(new LeagueCsvImport($request->league_id), $file);

//         // Message de succès
//         return redirect()->back()->with('csv_success', 'Fichier CSV importé avec succès pour la ligue.');
//     } catch (\Exception $e) {
//         Log::error('Erreur lors de l\'importation du fichier CSV: ' . $e->getMessage());
//         return redirect()->back()->with('csv_error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');
//     }
// }



// public function storeCsv(Request $request)
// {
//     try {
//         // Validation du fichier et de l'identifiant de la ligue
//         $request->validate([
//             'file' => 'required|mimes:csv,txt,xlsx|max:10240',
//             'league_id' => 'required|exists:leagues,id',
//         ]);

//         // Récupérer le fichier et l'identifiant de la ligue
//         $file = $request->file('file');
//         $leagueId = $request->input('league_id');

//         // Récupérer le nom de la ligue à partir de l'identifiant de la ligue
//         $league = League::find($leagueId);
//         $leagueName = $league->league_name;

//         // Supprimer les anciennes données de la table associée à la ligue
//         LeagueCsvData::where('league_id', $leagueId)->delete();

//         // Importer les nouvelles données du fichier CSV dans la base de données
//         Excel::import(new LeagueCsvImport($leagueId), $file);

//         // Définir le chemin de stockage du fichier dans le dossier correspondant au nom de la ligue
//         $path = 'leagues/' . $leagueName;

//         // Enregistrer le nouveau fichier dans le dossier correspondant
//         $fileName = now()->timestamp . '_' . $file->getClientOriginalName(); // Ajout d'un timestamp pour éviter les conflits de noms
//         $file->storeAs($path, $fileName);

//         // Message de succès
//         return redirect()->back()->with('csv_success', 'Fichier CSV importé et stocké avec succès pour la ligue.');
//     } catch (\Exception $e) {
//         // Loguer l'erreur
//         Log::error('Erreur lors de l\'importation du fichier CSV: ' . $e->getMessage());

//         // Message d'erreur
//         return redirect()->back()->with('csv_error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');
//     }
// }




// public function storeCsv(Request $request)
// {
//     try {
//         // Validation du fichier et récupération de l'identifiant de la ligue
//         $request->validate([
//             'file' => 'required|mimes:csv,txt,xlsx|max:10240',
//             'league_id' => 'required|exists:leagues,id',
//         ]);

//         // Récupérer le fichier et l'identifiant de la ligue
//         $file = $request->file('file');
//         $leagueId = $request->input('league_id');

//         // Supprimer les anciennes données de la table associée à la ligue
//         DataLeague::where('league_id', $leagueId)->delete();

//         // Importer les nouvelles données du fichier CSV dans la base de données
//         Excel::import(new LeagueCsvImport($leagueId), $file);

//         // Message de succès
//         return redirect()->back()->with('csv_success', 'Fichier CSV importé avec succès pour la ligue.');
//     } catch (\Exception $e) {
//         // Loguer l'erreur
//         Log::error('Erreur lors de l\'importation du fichier CSV: ' . $e->getMessage());

//         // Message d'erreur
//         return redirect()->back()->with('csv_error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');
//     }
// }
public function storeCsv(Request $request)
{
    try {
        // Validation du fichier et récupération de l'identifiant de la ligue
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:10240',
            'league_id' => 'required|exists:leagues,id',
        ]);

        // Récupérer le fichier et l'identifiant de la ligue
        $file = $request->file('file');
        $leagueId = $request->input('league_id');

        // Récupérer le nom de la ligue à partir de l'identifiant de la ligue (optionnel)
        $league = League::find($leagueId);
        $leagueName = $league->league_name ?? 'Unknown League';

        // Supprimer les anciennes données de la table associée à la ligue
        DataLeague::where('league_id', $leagueId)->delete();

        // Importer les nouvelles données du fichier CSV dans la base de données
        Excel::import(new LeagueCsvImport($leagueId), $file);

        // Définir le chemin de stockage du fichier dans le dossier correspondant au nom de la ligue (optionnel)
        $path = 'leagues/' . $leagueName;

        // Enregistrer le nouveau fichier dans le dossier correspondant (optionnel)
        $fileName = now()->timestamp . '_' . $file->getClientOriginalName();
        $file->storeAs($path, $fileName);

        // Message de succès
      return redirect()->back()->with('csv_success', 'Fichier CSV importé avec succès pour la ligue.');
} catch (\Exception $e) {
        // Journalisation de l'erreur
        Log::error('Erreur lors de l\'importation du fichier CSV: ' . $e->getMessage());

        // Message d'erreur
        return redirect()->back()->with('csv_error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');    }
}








}
