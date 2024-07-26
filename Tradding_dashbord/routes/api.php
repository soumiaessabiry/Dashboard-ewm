<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\DataLeagueController;
use App\Http\Controllers\DataMarchesController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/leagues/{league}/csv-data', [LeagueController::class, 'getCsvDataByLeague']);
Route::get('league/{league_id}/data', [DataLeagueController::class, 'getDataForLeague']);


// Route::resource('leagues', LeagueController::class);

// Route::post('leagues', [LeagueController::class, 'store'])->name('leagues.store');
// Route::get('/leagues', [LeagueController::class, 'index']);

// Route::get('leagues/{id}', [LeagueController::class, 'show']); // Récupérer une ligue spécifique
// Route::put('leagues/{id}', [LeagueController::class, 'update']); // Mettre à jour une ligue spécifique
// Route::delete('leagues/{id}', [LeagueController::class, 'destroy']); // Supprimer une ligue spécifique
// Route::get('/test-session', [LeagueController::class, 'testSession']);
// Route::get('gestion-des-ligues', [LeagueController::class, 'index'])->name('gestion-des-ligues');
// Route::post('leagues', [LeagueController::class, 'store'])->name('leagues.store');
// Route::get('/gestion-des-ligues', [LeagueController::class, 'index'])->name('gestion-des-ligues');
Route::get('/leagues', [LeagueController::class, 'index']);
Route::get('/marches/{marche_id}', [DataMarchesController::class, 'getDataForMarche']);
Route::get('/marches/{marche_id}/values-by-hour', [DataMarchesController::class, 'getValuesByHourForMarches']);

Route::get('/marches/{marche_id}/datamarches', [DataMarchesController::class, 'getDataForMarche']);
Route::get('/marches/{marche_id}', [DataMarchesController::class, 'getDataForMarches']);
Route::get('/jours-du-mois', [DataMarchesController::class, 'getDaysFromMonth']);
Route::get('/getDataForCurrentDay/{marche_id}', [DataMarchesController::class, 'getDataForCurrentDay']);

Route::get('/current-datetime', [DataMarchesController::class, 'getCurrentDateTime']);

Route::get('/current-hour-data/{marche_id}', [DataMarchesController::class, 'getDataForCurrentHour']);

Route::get('/getDataForCurrentHour/{marche_id}', [DataMarchesController::class, 'getDataForCurrentHour']);

Route::get('/getDataForCurrentminuts/{marche_id}', [DataMarchesController::class, 'getDataForCurrentHourAndMinute']);



Route::get('/getDataForCurrenday/{marche_id}', [DataMarchesController::class, 'getDataForCurrenday']);

Route::get('/getDataForLast5Minutes/{marche_id}', [DataMarchesController::class, 'getDataForLast5Minutes']);

Route::get('/getDataForLast15Minutes/{marche_id}', [DataMarchesController::class, 'getDataForLast15Minutes']);


Route::get('/getDataForLastHour/{marche_id}', [DataMarchesController::class, 'getDataForLastHour']);


Route::get('/getDataForLast4Hours/{marche_id}', [DataMarchesController::class, 'getDataForLast4Hours']);

Route::get('/getDataForLast5Days/{marche_id}', [DataMarchesController::class, 'getDataForLast5Days']);

Route::get('/getDataForLastDay/{marche_id}', [DataMarchesController::class, 'getDataForLastDay']);




Route::post('/import_data', [DataMarchesController::class, 'storedatamarche']);

Route::get('/getDataForLast30Minutes/{marche_id}', [DataMarchesController::class, 'getDataForLast30Minutes']);

// Route::get('/getDataForPrevious4Hours/{marche_id}', [DataMarchesController::class, 'getDataForPrevious4Hours']);
// Route::get('/getDataForLast5Hours/{marche_id}', [DataMarchesController::class, 'getDataForLast5Hours']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::resource('leagues', LeagueController::class);
// });
