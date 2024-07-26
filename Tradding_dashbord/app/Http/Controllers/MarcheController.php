<?php

namespace App\Http\Controllers;

use App\Models\Marche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcheController extends Controller
{
    public function index()
    {
        $marches = Marche::all(); // Pagination par défaut de 10 éléments par page
        return view('management-tables.marches-management', compact('marches'));
    }

    public function create()
    {
        return view('marches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'icon' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Récupérer le fichier téléchargé
        $file = $request->file('icon');

        // Conserver le nom d'origine
        $filename = $file->getClientOriginalName();

        // Stocker le fichier avec son nom original
        $path = $file->storeAs('imagemarches', $filename, 'public');
        $url = Storage::url($path);

        // Créer le marché avec le chemin de l'image
        Marche::create([
            'titre' => $request->titre,
            'icon' => $url,
        ]);

        return redirect()->route('marches.index')->with('marche_success', 'Marché créé avec succès.');
    }

    public function show(Marche $marche)
    {
        return view('marches.show', compact('marche'));
    }

    public function edit(Marche $marche)
    {
        return view('marches.edit', compact('marche'));
    }

    public function update(Request $request, Marche $marche)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('icon')) {
            // Supprimer l'ancienne image si elle existe
            if ($marche->icon) {
                $oldPath = str_replace('/storage/', 'public/', $marche->icon);
                Storage::delete($oldPath);
            }

            // Récupérer le fichier téléchargé
            $file = $request->file('icon');

            // Conserver le nom d'origine
            $filename = $file->getClientOriginalName();

            // Stocker le fichier avec son nom original
            $path = $file->storeAs('icons', $filename, 'public');
            $url = Storage::url($path);
            $marche->icon = $url;
        }

        $marche->titre = $request->titre;
        $marche->save();

        return redirect()->route('marches.index')->with('marche_success', 'Marché mis à jour avec succès.');
    }

    public function destroy(Marche $marche)
    {
        // Supprimer l'image associée
        if ($marche->icon) {
            $path = str_replace('/storage/', 'public/', $marche->icon);
            Storage::delete($path);
        }

        $marche->delete();

        return redirect()->route('marches.index')->with('marche_success', 'Marché supprimé avec succès.');
    }
}
