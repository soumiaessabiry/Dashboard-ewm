<?php


namespace App\Http\Controllers;

use App\Imports\DataLeagueImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\League;
use App\Models\DataLeague;

class DataLeagueController extends Controller
{


    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls', // Validez le type de fichier ici
        'league_id' => 'required|exists:leagues,id', // Validez l'id de la ligue ici
    ]);

    try {
        $league_id = $request->league_id;
        $file = $request->file('file');

        // Récupérer le nom de la ligue pour créer le dossier
        $league = League::findOrFail($league_id);
        $leagueFolder = 'leagues/' . $league->league_name;

        // Vérifier s'il existe déjà un fichier pour cette ligue
        $existingFiles = Storage::files($leagueFolder);

        // Déterminer le nom pour le nouveau fichier
        $newFileName = Carbon::now()->timestamp . '_' . $file->getClientOriginalName();

        foreach ($existingFiles as $existingFile) {
            // Renommer l'ancien fichier en ajoutant la date et l'heure
            $dateTime = Carbon::now()->format('Ymd_His');
            $oldFileName = basename($existingFile);
            $newOldFileName = $dateTime . '_' . $oldFileName;

            // Déplacer l'ancien fichier avec le nouveau nom dans le même dossier
            Storage::move($existingFile, $leagueFolder . '/' . $newOldFileName);
        }

        // Sauvegarder le nouveau fichier dans le dossier de la ligue
        $filePath = $file->storeAs($leagueFolder, $newFileName);

        // Importer les données depuis le fichier Excel
        Excel::import(new DataLeagueImport($league_id), storage_path('app/' . $filePath));

        return redirect()->back()->with('csv_success', 'Fichier CSV importé avec succès pour la ligue.');
    } catch (\Exception $e) {
        return redirect()->back()->with('csv_error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');
    }
}
public function getDataForLeague($leagueId)
    {
        $data = DataLeague::where('league_id', $leagueId)->get();

        // Convertir en JSON
        $jsonData = $data->toJson();

        // Retourner une réponse JSON
        return response()->json($jsonData);
    }
 }



