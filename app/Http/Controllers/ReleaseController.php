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
use App\Models\ReleaseMemberReleaseTrack;
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

        $members = auth()->user()->name === 'lynxadmin' ? ReleaseMember::all() : [];


        return Inertia::render('Dashboard', [
            'releases' => $releases,
            'members' => $members,
        ]);
    }

    public function add(): Response
    {
        return Inertia::render('AddRelease', []);
    }

    public function edit(Release $release): Response
    {
        $releaseWithRelations = Release::with([
            'release_type',
            'release_formats',
            'release_tracks.release_members' => function ($query) {
                $query->withPivot('percentage');
            },
            'release_members',
            'release_socials'
        ])->findOrFail($release->id);

        return Inertia::render('EditRelease', [
            'release' => $releaseWithRelations, 
            'releaseFormats' => ReleaseFormat::all(),
            'releaseTypes' => ReleaseType::all(),
            'releaseTracks' => ReleaseTrack::all(),
            'releaseMembers' => ReleaseMember::all(),
            'releaseSocials' => ReleaseSocial::all(),
            'releaseSocials' => ReleaseSocial::all(),
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    public function store(Request $request, Release $release, User $user, ReleaseMember $releaseMember): RedirectResponse
    {
        $validated = $request->validate([
            'catalog' => 'required|string|max:6|unique:releases,catalog',
            'email' => 'required|string|email',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'is_reference' => 'required|boolean',
            'isActive' => 'required|boolean',
        ]);

        // Vérifier si l'email existe déjà
        $existingUser = User::where('email', $validated['email'])->first();
        $existingMember = ReleaseMember::where('email', $validated['email'])->first();

        $release = Release::create([
            'catalog' => $validated['catalog'],
            'isActive' => $validated['isActive'] ?? true, // Définit isActive à true par défaut
        ]);

        if ($existingUser && $existingMember) {
            // Si l'utilisateur existe, associez-le simplement à la release
            $release->users()->attach($existingUser->id);
            $release->release_members()->attach($existingMember->id);
    
        } else {
            // Créer un nouveau membre si nécessaire
            if (!$existingMember) {
                $member = ReleaseMember::create([
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'email' => $validated['email'],
                    'is_reference' => $validated['is_reference'],
                ]);
                $release->release_members()->attach($member->id);
            }
    
            // Créer un nouvel utilisateur si nécessaire
            if (!$existingUser) {
                $user = User::create([
                    'name' => $validated['email'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['email']),
                ]);
                $release->users()->attach($user->id);
    
                // Envoyer l'email de bienvenue
                $expiresAt = now()->addDay();
                $user->sendWelcomeNotification($expiresAt);
            }
        }

        return redirect(route('dashboard'))->with('success', 'Release et utilisateur créés avec succès.');
    }

    public function checkEmail(Request $request)
    {
        $member = ReleaseMember::where('email', $request->email)->first();
        if ($member) {
            return response()->json([
                'exists' => true,
                'firstname' => $member->firstname,
                'lastname' => $member->lastname,
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function updateStatus(Request $request, Release $release)
    {
        $validated = $request->validate([
            'isActive' => 'required|boolean',
        ]);

        $release->update([
            'isActive' => $validated['isActive'],
        ]);

        return response()->json(['success' => true]);
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
            'cleRepartition' => 'nullable|string',
            'credits' => 'required|string',
            'remerciements' => 'nullable|string',
            'style' => 'required|string',
            'release_date' => 'nullable|date',
            'description' => 'required|string',
            'remarques' => 'nullable|string',
            'envies' => 'nullable|string',
            'budget' => 'nullable|integer',
            'sourceFinancement' => 'nullable|string',
            'besoinFinancement' => 'nullable|string',
            'isProduitsDerives' => 'required|boolean',
            'isBesoinSubvention' => 'required|boolean',
            'isBesoinPromo' => 'required|boolean',
            'isBesoinDigitalMarketing' => 'required|boolean',
            'isBesoinContacts' => 'required|boolean',
            'besoinContacts' => 'nullable|string',
            'isActive' => 'nullable|boolean',
            'release_type_id' => 'required|exists:release_types,id',
            'release_format_ids' => 'required|array',
            'release_format_ids.*' => 'exists:release_formats,id',
            'CodeBarre' => 'array',
            'CodeBarre.*' => 'nullable|string',
            'price' => 'array',
            'price.*' => 'nullable|integer',
            'tracks' => 'array',
            'tracks.*.id' => 'nullable',  // Permettre id null pour nouvelles pistes
            'tracks.*.title' => 'required|string',
            'tracks.*.number' => 'required|integer',
            'tracks.*.isSingle' => 'required|boolean',
            'tracks.*.hasClip' => 'required|boolean',
            'tracks.*.IRSC' => 'nullable|string',
            'tracks.*.participations' => 'array',
            'tracks.*.participations.*.member_id' => 'nullable|exists:release_members,id',
            'tracks.*.participations.*.percentage' => 'required|numeric|min:0|max:100',
            'members' => 'array',
            'members.*.id' => 'nullable',  // Permettre id null pour nouveaux membres
            'members.*.firstname' => 'required|string',
            'members.*.lastname' => 'required|string',
            'members.*.IPI' => 'nullable|string',
            'members.*.city' => 'nullable|string',
            'members.*.country' => 'nullable|string',
            'members.*.email' => 'nullable|string',
            'members.*.street' => 'nullable|string',
            'members.*.zip_code' => 'nullable|string',
            'members.*.phone_number' => 'nullable|string',
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
            'isBesoinPromo' => $validated['isBesoinPromo'],
            'isBesoinDigitalMarketing' => $validated['isBesoinDigitalMarketing'],
            'isBesoinContacts' => $validated['isBesoinContacts'],
            'besoinContacts' => $validated['besoinContacts'],
            'isActive' => $validated['isActive'],
            'style' => $validated['style'],
            'release_date' => $validated['release_date'],
            'description' => $validated['description'],
            'release_type_id' => $validated['release_type_id']
        ]);

        // Synchroniser les formats et leurs codes-barres
        $release->release_formats()->sync(
            collect($validated['release_format_ids'])->mapWithKeys(function ($formatId) use ($validated) {
                return [$formatId => [
                    'CodeBarre' => $validated['CodeBarre'][$formatId] ?? null,
                    'price' => $validated['price'][$formatId] ?? null
                    ]];
            })->toArray()
        );
        
        // Récupérer les IDs des pistes existantes
        $existingTrackIds = $release->release_tracks()->pluck('id')->toArray();

        // Récupérer les IDs des membres existants
        $existingMemberIds = $release->release_members()->pluck('id')->toArray();

        // Récupérer les IDs des réseaux sociaux existants
        $existingSocialIds = $release->release_socials()->pluck('id')->toArray();

         // Sauvegarde des pistes
        $updatedTrackIds = [];


        // Sauvegarde des membres
        $updatedMemberIds = [];
        $newMemberId = null; // Pour stocker l'ID du nouveau membre

        foreach ($validated['members'] as $memberData) {
            if (isset($memberData['id'])) {
                // Mise à jour du membre existant
                $release->release_members()
                    ->where('id', $memberData['id'])
                    ->update([
                        'firstname' => $memberData['firstname'],
                        'lastname' => $memberData['lastname'],
                        'birth_date' => $memberData['birth_date'],
                        'IPI' => $memberData['IPI'],
                        'is_reference' => $memberData['is_reference'],
                        'city' => $memberData['city'],
                        'country' => $memberData['country'],
                        'email' => $memberData['email'],
                        'street' => $memberData['street'],
                        'zip_code' => $memberData['zip_code'],
                        'phone_number' => $memberData['phone_number'],
                    ]);
                $updatedMemberIds[] = $memberData['id'];
            } else {
                // Vérifier si le membre existe déjà dans la table release_members
                $existingMember = ReleaseMember::where('firstname', $memberData['firstname'])
                    ->where('lastname', $memberData['lastname'])
                    ->where('birth_date', $memberData['birth_date'])
                    ->where('IPI', $memberData['IPI'])
                    ->first();
        
                if ($existingMember) {
                    // Si le membre existe, on l'ajoute uniquement dans la table pivot
                    $newMemberId = $existingMember->id;
                    $updatedMemberIds[] = $existingMember->id;
                    
                    // Ajouter dans release_release_member
                    $release->release_members()->syncWithoutDetaching([$existingMember->id]);
        
                    // Les participations seront gérées plus tard dans la boucle des pistes
                } else {
                    // Si le membre n'existe pas, on le crée
                    $newMember = ReleaseMember::create([
                        'firstname' => $memberData['firstname'],
                        'lastname' => $memberData['lastname'],
                        'birth_date' => $memberData['birth_date'],
                        'IPI' => $memberData['IPI'],
                        'is_reference' => $memberData['is_reference'],
                        'city' => $memberData['city'],
                        'country' => $memberData['country'],
                        'email' => $memberData['email'],
                        'street' => $memberData['street'],
                        'zip_code' => $memberData['zip_code'],
                        'phone_number' => $memberData['phone_number'],
                    ]);
                    
                    $newMemberId = $newMember->id;
                    $updatedMemberIds[] = $newMember->id;
                    
                    // Ajouter dans release_release_member
                    $release->release_members()->attach($newMember->id);
                }
            }
        }

        // Sauvegarde des pistes avec leurs participations
        foreach ($validated['tracks'] as $trackData) {
            if (isset($trackData['id'])) {
                $track = $release->release_tracks()
                    ->where('id', $trackData['id'])
                    ->first();

                $track->update([
                    'title' => $trackData['title'],
                    'number' => $trackData['number'],
                    'isSingle' => $trackData['isSingle'],
                    'hasClip' => $trackData['hasClip'],
                    'IRSC' => $trackData['IRSC'] ?? null,
                ]);
                $updatedTrackIds[] = $track->id;
            } else {
                $track = $release->release_tracks()->create([
                    'title' => $trackData['title'],
                    'number' => $trackData['number'],
                    'isSingle' => $trackData['isSingle'],
                    'hasClip' => $trackData['hasClip'],
                    'IRSC' => $trackData['IRSC'] ?? null,
                ]);
                $updatedTrackIds[] = $track->id;
            }

            if (isset($trackData['participations'])) {
                $participationsData = [];
                $totalPercentage = 0;
            
                foreach ($trackData['participations'] as $index => $participation) {
                    // Récupérer le membre correspondant à cet index dans le tableau des membres
                    $member = $validated['members'][$index] ?? null;
                    
                    if ($member) {
                        // Si le membre a un ID, l'utiliser directement
                        if (isset($member['id'])) {
                            $memberId = $member['id'];
                        } else {
                            // Sinon, rechercher le membre existant ou utiliser le nouveau
                            $existingMember = ReleaseMember::where('firstname', $member['firstname'])
                                ->where('lastname', $member['lastname'])
                                ->where('birth_date', $member['birth_date'])
                                ->where('IPI', $member['IPI'])
                                ->first();
                            
                            $memberId = $existingMember ? $existingMember->id : $newMemberId;
                        }
            
                        if (!$memberId) {
                            throw ValidationException::withMessages([
                                'participations' => "L'ID du membre est manquant pour une participation"
                            ]);
                        }
            
                        $totalPercentage += $participation['percentage'];
                        if ($totalPercentage > 100) {
                            throw ValidationException::withMessages([
                                'participations' => "Le total des pourcentages ne peut pas dépasser 100%"
                            ]);
                        }
            
                        $participationsData[$memberId] = [
                            'percentage' => $participation['percentage']
                        ];
                    }
                }
            
                // Synchroniser les participations avec syncWithoutDetaching pour préserver les relations existantes
                $track->release_members()->syncWithoutDetaching($participationsData);
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
