<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Release;
use App\Models\ReleaseFormat;
use App\Models\ReleaseType;
use App\Models\ReleaseTrack;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ReleaseController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'releases' => Release::with('release_type')->get()
        ]);
    }

    public function add(): Response
    {
        return Inertia::render('AddRelease', []);
    }

    public function edit(Release $release): Response
    {
        return Inertia::render('EditRelease', [
            'release' => Release::with('release_type', 'release_formats', 'release_tracks')->findOrFail($release->id), // Récupérer la release à éditer
            'releaseFormats' => ReleaseFormat::all(), // Récupérer tous les formats de release
            'releaseTypes' => ReleaseType::all(), // Récupérer tous les formats de release
            'releaseTracks' => ReleaseTrack::all(), // Récupérer tous les formats de release
        ]);
    }

    public function store(Request $request, Release $release): RedirectResponse
    {
        $request->validate([
            'catalog' => 'required|string|max:6',
        ]);

        $release->create([
            'catalog' => $request->catalog,
        ]);

        return redirect(route('dashboard', absolute: false));
    }

    public function update(Request $request, Release $release): RedirectResponse
    {
        $validated = $request->validate([
            'catalog' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'artistName' => 'required|string|max:255',
            'artistIBAN' => 'required|string|max:255',
            'release_type_id' => 'required|exists:release_types,id',
            'release_format_ids' => 'required|array',
            'release_format_ids.*' => 'exists:release_formats,id',
            'tracks' => 'array',
            'tracks.*.id' => 'nullable',  // Permettre id null pour nouvelles pistes
            'tracks.*.title' => 'required|string|max:255',
            'tracks.*.number' => 'required|integer|max:255',
        ]);
    
        $release->update([
            'catalog' => $validated['catalog'],
            'name' => $validated['name'],
            'artistName' => $validated['artistName'],
            'artistIBAN' => $validated['artistIBAN'],
            'release_type_id' => $validated['release_type_id']
        ]);

        $release->release_formats()->sync($validated['release_format_ids']);

        // Sauvegarde des pistes
        foreach ($validated['tracks'] as $trackData) {
            if (isset($trackData['id'])) {
                $release->release_tracks()
                    ->where('id', $trackData['id'])
                    ->update([
                        'title' => $trackData['title'],
                        'number' => $trackData['number']
                    ]);
            } else {
                $release->release_tracks()->create([
                    'title' => $trackData['title'],
                    'number' => $trackData['number']
                ]);
            }
        }
    
        return redirect(route('dashboard', absolute: false));
    }
}
