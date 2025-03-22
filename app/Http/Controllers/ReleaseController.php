<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Release;
use App\Models\ReleaseFormat;
use App\Models\ReleaseType;
use App\Models\ReleaseTrack;
use App\Models\ReleaseMember;
use App\Models\ReleaseSocial;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\welcomeMail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ReleaseController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
    
        // Vérifier si l'utilisateur est "lynxadmin"
        if ($user->name === "lynxadmin") {
            // Récupérer toutes les releases
            $releases = Release::with('release_type')->get();
        } else {
            // Récupérer uniquement les releases associées à l'utilisateur via la table pivot
            $releases = $user->releases()->with('release_type')->get();
        }
    
        return Inertia::render('Dashboard', [
            'releases' => $releases,
        ]);
    }

    public function add(): Response
    {
        return Inertia::render('AddRelease', []);
    }

    public function edit(Release $release): Response
    {
        return Inertia::render('EditRelease', [
            'release' => Release::with('release_type', 'release_formats', 'release_tracks', 'release_members', 'release_socials')->findOrFail($release->id), // Récupérer la release à éditer
            'releaseFormats' => ReleaseFormat::all(),
            'releaseTypes' => ReleaseType::all(),
            'releaseTracks' => ReleaseTrack::all(),
            'releaseMembers' => ReleaseMember::all(),
            'releaseSocials' => ReleaseSocial::all(),
        ]);
    }

    public function store(Request $request, Release $release, User $user, ReleaseMember $releaseMember): RedirectResponse
    {
        $validated = $request->validate([
            'catalog' => 'required|string|max:6',
            'email' => 'required|string|email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'is_reference' => 'is_reference|boolean',
        ]);

        // Vérifier si l'email existe déjà
        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser) {
            // Si l'utilisateur existe, associez-le simplement à la release
            $release = Release::create([
                'catalog' => $validated['catalog'],
            ]);
    
            $release->users()->attach($existingUser->id);
    
            return redirect(route('dashboard'))->with('success', 'Release créée et utilisateur existant associé.');
        }

        $release = Release::create([
            'catalog' => $validated['catalog'],
        ]);

        $releaseMember = ReleaseMember::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'is_reference' => $validated['is_reference'],
        ]);

        // Associer le membre à la release dans la table pivot
        $release->release_members()->attach($releaseMember->id);

        $user = User::create([
            'name' => $validated['email'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['email']),
        ]);

        // Associer l'utilisateur à la release dans la table pivot
        $release->users()->attach($user->id);

        $expiresAt = now()->addDay();
        $user->sendWelcomeNotification($expiresAt);

        return redirect(route('dashboard'))->with('success', 'Release et utilisateur créés avec succès.');
    }

    public function update(Request $request, Release $release): RedirectResponse
    {
        $validated = $request->validate([
            'catalog' => 'required|string',
            'name' => 'required|string',
            'artistName' => 'required|string',
            'artistIBAN' => 'nullable|string',
            'artistBiography' => 'required|string',
            'artistWebsite' => 'nullable|string',
            'CodeBarre' => 'nullable|string',
            'cleRepartition' => 'nullable|string',
            'credits' => 'required|string',
            'remerciements' => 'nullable|string',
            'style' => 'required|string',
            'price' => 'required|integer',
            'description' => 'required|string',
            'remarques' => 'nullable|string',
            'envies' => 'nullable|string',
            'budget' => 'nullable|integer',
            'sourceFinancement' => 'nullable|string',
            'besoinFinancement' => 'nullable|string',
            'isProduitsDerives' => 'nullable|boolean',
            'isBesoinSubvention' => 'nullable|boolean',
            'isBesoinBooking' => 'nullable|boolean',
            'isBesoinPromo' => 'nullable|boolean',
            'isBesoinDigitalMarketing' => 'nullable|boolean',
            'isBesoinContacts' => 'nullable|boolean',
            'isActive' => 'nullable|boolean',
            'release_type_id' => 'required|exists:release_types,id',
            'release_format_ids' => 'required|array',
            'release_format_ids.*' => 'exists:release_formats,id',
            'tracks' => 'array',
            'tracks.*.id' => 'nullable',  // Permettre id null pour nouvelles pistes
            'tracks.*.title' => 'required|string',
            'tracks.*.number' => 'required|integer',
            'tracks.*.isSingle' => 'required|boolean',
            'tracks.*.hasClip' => 'required|boolean',
            'tracks.*.IRSC' => 'nullable|string',
            'members' => 'array',
            'members.*.id' => 'nullable',  // Permettre id null pour nouveaux membres
            'members.*.firstname' => 'required|string',
            'members.*.lastname' => 'required|string',
            'members.*.IPI' => 'nullable|string',
            'members.*.city' => 'required|string',
            'members.*.street' => 'required|string',
            'members.*.zip_code' => 'required|string',
            'members.*.phone_number' => 'required|string',
            'members.*.birth_date' => 'required|date',
            'members.*.is_reference' => 'nullable',
            'socials' => 'array',
            'socials.*.id' => 'nullable',  // Permettre id null pour nouveaux réseaux sociaux
            'socials.*.link' => 'required|string',
        ]);
    
        $release->update([
            'catalog' => $validated['catalog'],
            'name' => $validated['name'],
            'artistName' => $validated['artistName'],
            'artistIBAN' => $validated['artistIBAN'],
            'artistBiography' => $validated['artistBiography'],
            'artistWebsite' => $validated['artistWebsite'],
            'CodeBarre' => $validated['CodeBarre'],
            'cleRepartition' => $validated['cleRepartition'],
            'credits' => $validated['credits'],
            'remerciements' => $validated['remerciements'],
            'remarques' => $validated['remarques'],
            'envies' => $validated['envies'],
            'budget' => $validated['budget'],
            'sourceFinancement' => $validated['sourceFinancement'],
            'besoinFinancement' => $validated['besoinFinancement'],
            'isProduitsDerives' => $validated['isProduitsDerives'],
            'isBesoinSubvention' => $validated['isBesoinSubvention'],
            'isBesoinBooking' => $validated['isBesoinBooking'],
            'isBesoinPromo' => $validated['isBesoinPromo'],
            'isBesoinDigitalMarketing' => $validated['isBesoinDigitalMarketing'],
            'isBesoinContacts' => $validated['isBesoinContacts'],
            'isActive' => $validated['isActive'],
            'style' => $validated['style'],
            'price' => $validated['price'],
            'description' => $validated['description'],
            'release_type_id' => $validated['release_type_id']
        ]);

        $release->release_formats()->sync($validated['release_format_ids']);
        
        // Récupérer les IDs des pistes existantes
        $existingTrackIds = $release->release_tracks()->pluck('id')->toArray();

        // Récupérer les IDs des membres existants
        $existingMemberIds = $release->release_members()->pluck('id')->toArray();

        // Récupérer les IDs des réseaux sociaux existants
        $existingSocialIds = $release->release_socials()->pluck('id')->toArray();

         // Sauvegarde des pistes
        $updatedTrackIds = [];
        foreach ($validated['tracks'] as $trackData) {
            if (isset($trackData['id'])) {
                $release->release_tracks()
                    ->where('id', $trackData['id'])
                    ->update([
                        'title' => $trackData['title'],
                        'number' => $trackData['number'],
                        'isSingle' => $trackData['isSingle'],
                        'hasClip' => $trackData['hasClip'],
                        'IRSC' => $trackData['IRSC'],
                    ]);
                $updatedTrackIds[] = $trackData['id'];
            } else {
                $newTrack = $release->release_tracks()->create([
                    'title' => $trackData['title'],
                    'number' => $trackData['number'],
                    'isSingle' => $trackData['isSingle'],
                    'hasClip' => $trackData['hasClip'],
                    'IRSC' => $trackData['IRSC'],
                ]);
                $updatedTrackIds[] = $newTrack->id;
            }
        }

        // Sauvegarde des membres
        $updatedMemberIds = [];
        foreach ($validated['members'] as $memberData) {
            if (isset($memberData['id'])) {
                $release->release_members()
                    ->where('id', $memberData['id'])
                    ->update([
                        'firstname' => $memberData['firstname'],
                        'lastname' => $memberData['lastname'],
                        'birth_date' => $memberData['birth_date'],
                        'IPI' => $memberData['IPI'],
                        'is_reference' => $memberData['is_reference'],
                        'city' => $memberData['city'],
                        'street' => $memberData['street'],
                        'zip_code' => $memberData['zip_code'],
                        'phone_number' => $memberData['phone_number'],
                    ]);
                $updatedMemberIds[] = $memberData['id'];
            } else {
                $newMember = $release->release_members()->create([
                    'firstname' => $memberData['firstname'],
                    'lastname' => $memberData['lastname'],
                    'birth_date' => $memberData['birth_date'],
                    'IPI' => $memberData['IPI'],
                    'is_reference' => $memberData['is_reference'],
                    'city' => $memberData['city'],
                    'street' => $memberData['street'],
                    'zip_code' => $memberData['zip_code'],
                    'phone_number' => $memberData['phone_number'],
                ]);
                $updatedMemberIds[] = $newMember->id;
            }
        }

        // Sauvegarde des réseaux sociaux
        $updatedSocialIds = [];
        foreach ($validated['socials'] as $socialData) {
            if (isset($socialData['id'])) {
                $release->release_socials()
                    ->where('id', $socialData['id'])
                    ->update([
                        'link' => $socialData['link'],
                    ]);
                $updatedSocialIds[] = $socialData['id'];
            } else {
                $newSocial = $release->release_socials()->create([
                    'link' => $socialData['link'],
                ]);
                $updatedSocialIds[] = $newSocial->id;
            }
        }

        // Supprimer les pistes qui ne sont plus présentes dans la requête
        $tracksToDelete = array_diff($existingTrackIds, $updatedTrackIds);
        $release->release_tracks()->whereIn('id', $tracksToDelete)->delete();

        // Supprimer les membres qui ne sont plus présentes dans la requête
        $membersToDelete = array_diff($existingMemberIds, $updatedMemberIds);
        $release->release_members()->whereIn('id', $membersToDelete)->delete();

        // Supprimer les réseaux sociaux qui ne sont plus présentes dans la requête
        $socialsToDelete = array_diff($existingSocialIds, $updatedSocialIds);
        $release->release_socials()->whereIn('id', $socialsToDelete)->delete();
    
        return redirect(route('dashboard', absolute: false));
    }
}
