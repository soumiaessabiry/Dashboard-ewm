<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\MarcheController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataLeagueController;
use App\Http\Controllers\DataMarchesController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/', [HomeController::class, 'home']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [SessionsController::class, 'destroy'])->name('logout');
    // profil
    Route::get('/profil_utilisateur', [InfoUserController::class, 'edit'])->name('user-profile.edit');
    Route::put('/user-profile/update', [InfoUserController::class, 'update'])->name('user-profile.update');
    // ligues
    Route::get('/gestion-des-ligues', [LeagueController::class, 'index'])->name('leagues.index');
    Route::post('/leagues', [LeagueController::class, 'store'])->name('leagues.store');
    Route::get('leagues/{id}', [LeagueController::class, 'show'])->name('leagues.show');
    Route::put('leagues/{id}', [LeagueController::class, 'update'])->name('leagues.update');
    Route::delete('leagues/{id}', [LeagueController::class, 'destroy'])->name('leagues.destroy');
    Route::get('test-session', [LeagueController::class, 'testSession'])->name('test.session');

    // Route pour afficher le formulaire d'importation CSV
    Route::get('/leagues/{id}/import-csv', [LeagueController::class, 'showImportForm'])->name('leagues.importCsvForm');

    // Route pour traiter l'importation CSV
    Route::post('/leagues/{id}/import-csv', [LeagueController::class, 'storeCsv'])->name('leagues.storeCsv');

    // Routes pour les utilisateurs
    Route::get('/gestion-des-utilisateurs', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Routes pour les equipe
    Route::get('/gestion-des-equipes', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('/teams/{team}/members', [TeamController::class, 'storeMember'])->name('teams.storeMember');
    Route::get('/teams/{team}/members', [TeamController::class, 'showMembers'])->name('teams.showMembers');
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'destroyMember'])->name('teams.destroyMember');

    // Routes pour les marches
    Route::get('/gestion-des-marches', [MarcheController::class, 'index'])->name('marches.index');

    // Route pour afficher le formulaire de création de marché
    Route::get('/marches/create', [MarcheController::class, 'create'])->name('marches.create');

    // Route pour sauvegarder un nouveau marché
    Route::post('/marches', [MarcheController::class, 'store'])->name('marches.store');

    // Route pour afficher un marché spécifique
    Route::get('/marches/{marche}', [MarcheController::class, 'show'])->name('marches.show');

    // Route pour afficher le formulaire de mise à jour d'un marché
    Route::get('/marches/{marche}/edit', [MarcheController::class, 'edit'])->name('marches.edit');

    // Route pour mettre à jour un marché spécifique
    Route::put('/marches/{marche}', [MarcheController::class, 'update'])->name('marches.update');

    // Route pour supprimer un marché spécifique
    Route::delete('/marches/{marche}', [MarcheController::class, 'destroy'])->name('marches.destroy');

    // route csv marches
    Route::get('/marches_exportation_csv', [DataMarchesController::class, 'index'])->name('marches_exportation_csv');
    Route::post('/import_data', [DataMarchesController::class, 'storedatamarche'])->name('storedatamarche.process');


    // new route csv

    Route::get('/exportation_csv', [LeagueController::class, 'showleagues'])->name('exportation_csv');
    Route::post('/leagues/{id}/storeCsv', [LeagueController::class, 'storeCsv'])->name('leagues.storeCsv');

    // Routes pour les lagues
    Route::get('/leagues/import/{id}', [LeagueController::class, 'showImportForm'])->name('leagues.showImportForm');
    Route::post('/leagues/store-csv', [LeagueController::class, 'storeCsv'])->name('leagues.storeCsv');
    Route::get('/leagues/{league_id}/csv-data', [LeagueController::class, 'showCsvData'])->name('leagues.showCsvData');
    Route::get('/leagues/{league}/csv-data', [LeagueController::class, 'getCsvDataByLeague']);

    Route::post('/leagues/storeCsv', [LeagueController::class, 'storeCsv'])->name('leagues.storeCsv');
    // route correct
    Route::post('/import', [DataLeagueController::class, 'import'])->name('import.process');

    Route::get('/league/{league_id}/data', 'LeagueController@getLeagueData');


    Route::get('profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('rtl', function () {
        return view('rtl');
    })->name('rtl');


    Route::get('tables', function () {
        return view('tables');
    })->name('tables');

    Route::get('virtual-reality', function () {
        return view('virtual-reality');
    })->name('virtual-reality');
});


Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionsController::class, 'create']);
    Route::post('/session', [SessionsController::class, 'store']);
    Route::get('/login/forgot-password', [ResetController::class, 'create']);
    Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
    Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
    Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});

Route::get('/login', function () {
    return view('session/login-session');
})->name('login');
