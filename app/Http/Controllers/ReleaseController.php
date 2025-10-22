<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Release;
use App\Models\ReleaseFormat;
use App\Models\ReleaseType;
use App\Models\ReleaseTrack;
use App\Models\ReleaseMember;
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
use App\Mail\labelcopyEdited;
use App\Mail\labelcopyCreated;
use Illuminate\Support\Facades\Storage;

class ReleaseController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
    
        // Vérifier si l'utilisateur est "lynxadmin"
        if ($user->name === "lynxadmin") {
            // Récupérer toutes les releases
            $releases = Release::with('release_type')->orderBy('catalog', 'desc')->get();
        } else {
            // Récupérer uniquement les releases associées à l'utilisateur via la table pivot
            $releases = $user->releases()->with('release_type')->orderBy('catalog', 'desc')->get();
        }

        $members = auth()->user()->name === 'lynxadmin' ? ReleaseMember::orderBy('lastname', 'asc')->get() : [];

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
        ])->findOrFail($release->id);

        return Inertia::render('EditRelease', [
            'release' => $releaseWithRelations, 
            'releaseFormats' => ReleaseFormat::all(),
            'releaseTypes' => ReleaseType::orderBy('name')->get(),
            'releaseTracks' => ReleaseTrack::all(),
            'releaseMembers' => ReleaseMember::all(),
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    public function addupload(Release $release): Response
    {
        $releaseWithRelations = Release::with([
            'release_type',
            'release_formats',
            'release_tracks.release_members' => function ($query) {
                $query->withPivot('percentage');
            },
            'release_members',
        ])->findOrFail($release->id);

        return Inertia::render('UploadRelease', [
            'release' => $releaseWithRelations, 
            'releaseFormats' => ReleaseFormat::all(),
            'releaseTypes' => ReleaseType::orderBy('name')->get(),
            'releaseTracks' => ReleaseTrack::all(),
            'releaseMembers' => ReleaseMember::all(),
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    public function store(Request $request, Release $release, User $user, ReleaseMember $releaseMember): RedirectResponse
    {
        $validated = $request->validate([
            'catalog' => 'required|string|max:6|unique:releases,catalog',
            'email' => 'required|string|email|max:255',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
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

            Mail::to($existingMember)->queue(new labelcopyCreated($release));

            return redirect()->route('dashboard')->with('success', [
                'message' => 'Labelcopy créé avec succès. E-mail envoyé au membre de référence.',
            ]);
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
    
                // Envoyer l'email d'initialisation du compte
                $expiresAt = now()->addDay(4);
                $user->sendWelcomeNotification($expiresAt);
            }

            return redirect()->route('dashboard')->with('success', [
                'message' => 'Labelcopy et membre de référence créés avec succès. E-mail envoyé au membre de référence.',
            ]);
        }
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
        // Capturer l'état précédent et le convertir en tableau
        $release_before = $release->fresh(['release_members', 'release_tracks','release_tracks.release_members', 'release_formats', 'release_type'])->toArray();

        $validated = $request->validate([
            'catalog' => 'required|string|max:6',
            'name' => 'required|string|max:255',
            'artistName' => 'required|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'bandcamp' => 'nullable|string|max:255',
            'applemusic' => 'nullable|string|max:255',
            'spotify' => 'nullable|string|max:255',
            'soundcloud' => 'nullable|string|max:255',
            'artistIBAN' => 'nullable|string|max:255',
            'artistBiography' => 'required|string|max:21845',
            'artistWebsite' => 'nullable|string|max:255',
            'credits' => 'required|string|max:21845',
            'remerciements' => 'nullable|string|max:21845',
            'style' => 'required|string|max:255',
            'release_date' => 'nullable|date',
            'description' => 'required|string|max:21845',
            'remarques' => 'nullable|string|max:21845',
            'envies' => 'nullable|string|max:21845',
            'budget' => 'nullable|integer|min:0|max:8388607',
            'sourceFinancement' => 'nullable|string|max:21845',
            'besoinFinancement' => 'nullable|string|max:21845',
            'isProduitsDerives' => 'required|boolean',
            'isBesoinSubvention' => 'required|boolean',
            'isBesoinPromo' => 'required|boolean',
            'isBesoinDigitalMarketing' => 'required|boolean',
            'isBesoinContacts' => 'required|boolean',
            'besoinContacts' => 'nullable|string|max:21845',
            'isActive' => 'nullable|boolean',
            'release_type_id' => 'required|exists:release_types,id',
            'release_format_ids' => 'required|array',
            'release_format_ids.*' => 'exists:release_formats,id',
            'CodeBarre' => 'array',
            'CodeBarre.*' => 'nullable|string|max:255',
            'price' => 'array',
            'price.*' => 'nullable|integer|min:0|max:32767',
            'tracks' => 'array',
            'tracks.*.id' => 'nullable',  // Permettre id null pour nouvelles pistes
            'tracks.*.title' => 'required|string|max:255',
            'tracks.*.number' => 'required|integer|min:0|max:127',
            'tracks.*.isSingle' => 'required|boolean',
            'tracks.*.hasClip' => 'required|boolean',
            'tracks.*.IRSC' => 'nullable|string|max:255',
            'tracks.*.release_date_single' => 'nullable|date',
            'tracks.*.release_description' => 'nullable|string|max:21845',
            'tracks.*.participations' => 'array',
            'tracks.*.participations.*.member_id' => 'nullable|exists:release_members,id',
            'tracks.*.participations.*.percentage' => 'required|numeric|min:0|max:100',
            'members' => 'array',
            'members.*.id' => 'nullable',  // Permettre id null pour nouveaux membres
            'members.*.firstname' => 'required|string|max:255',
            'members.*.lastname' => 'required|string|max:255',
            'members.*.IPI' => 'nullable|string|max:255',
            'members.*.shirtsize' => 'nullable|string|max:255',
            'members.*.city' => 'nullable|string|max:255',
            'members.*.country' => 'nullable|string|max:255',
            'members.*.email' => 'nullable|string|max:255',
            'members.*.street' => 'nullable|string|max:255',
            'members.*.zip_code' => 'nullable|string|max:255',
            'members.*.phone_number' => 'nullable|string|max:255',
            'members.*.birth_date' => 'required|date',
            'members.*.is_reference' => 'nullable|boolean',
        ]);
    
        $release->update([
            'catalog' => $validated['catalog'],
            'name' => $validated['name'],
            'artistName' => $validated['artistName'],
            'facebook' => $validated['facebook'],
            'instagram' => $validated['instagram'],
            'youtube' => $validated['youtube'],
            'bandcamp' => $validated['bandcamp'],
            'applemusic' => $validated['applemusic'],
            'spotify' => $validated['spotify'],
            'soundcloud' => $validated['soundcloud'],
            'artistIBAN' => $validated['artistIBAN'],
            'artistBiography' => $validated['artistBiography'],
            'artistWebsite' => $validated['artistWebsite'],
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
            'release_type_id' => $validated['release_type_id'],
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
                        'shirtsize' => $memberData['shirtsize'],
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
                        'shirtsize' => $memberData['shirtsize'],
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
                    'release_description' => $trackData['release_description'] ?? null,
                    'IRSC' => $trackData['IRSC'] ?? null,
                    'release_date_single' => $trackData['release_date_single'] ?? null,
                ]);
                $updatedTrackIds[] = $track->id;
            } else {
                $track = $release->release_tracks()->create([
                    'title' => $trackData['title'],
                    'number' => $trackData['number'],
                    'isSingle' => $trackData['isSingle'],
                    'hasClip' => $trackData['hasClip'],
                    'release_description' => $trackData['release_description'] ?? null,
                    'IRSC' => $trackData['IRSC'] ?? null,
                    'release_date_single' => $trackData['release_date_single'] ?? null,
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

        // Supprimer les pistes qui ne sont plus présentes dans la requête
        $tracksToDelete = array_diff($existingTrackIds, $updatedTrackIds);
        $release->release_tracks()->whereIn('id', $tracksToDelete)->delete();

        // Supprimer les membres qui ne sont plus présentes dans la requête
        $membersToDelete = array_diff($existingMemberIds, $updatedMemberIds);
        $release->release_members()->whereIn('id', $membersToDelete)->delete();

        Mail::queue(new labelcopyEdited($release->fresh(), $release_before));
       
        return redirect()->route('dashboard')->with('success', [
            'message' => 'Modifications enregistrées avec succès',
        ]);
    }

    /* public function upload(Request $request, Release $release): RedirectResponse
    {
        $validated = $request->validate([
            'catalog' => 'required|string|max:6',
            'file_release' => 'nullable|file|mimes:zip|max:2500000',
            'file_cover' => 'nullable|file|mimes:zip|max:25000',
            'file_release_name' => 'nullable|string|max:255',
            'file_cover_name' => 'nullable|string|max:255',
            'dzchunkindex' => 'nullable|integer',
            'dztotalchunkcount' => 'nullable|integer',
            'dzfilename' => 'nullable|string',
        ]);

        if ($request->hasFile('file_release')) {
            // Gestion des chunks pour file_release
            $file = $request->file('file_release');
            $chunkNumber = $request->input('dzchunkindex');
            $totalChunks = $request->input('dztotalchunkcount');
            $fileName = $request->input('dzfilename') ?? $file->getClientOriginalName();
            
            // Créer le répertoire des chunks s'il n'existe pas
            $chunksPath = storage_path('app/uploads/chunks');
            if (!file_exists($chunksPath)) {
                mkdir($chunksPath, 0777, true);
            }

            $chunkPath = $chunksPath . '/' . $fileName . '.part' . $chunkNumber;
            
            // Stocker le chunk actuel
            $file->move($chunksPath, basename($chunkPath));

            if ($chunkNumber == $totalChunks - 1) {
                // Créer le nom du fichier final sans l'extension .part
                $finalPath = storage_path('app/uploads/' . $fileName);
                $finalFile = fopen($finalPath, 'wb');
            
                try {
                    // Fusionner tous les chunks
                    for ($i = 0; $i < $totalChunks; $i++) {
                        $chunkFilePath = $chunksPath . '/' . $fileName . '.part' . $i;
                        if (file_exists($chunkFilePath)) {
                            $chunk = fopen($chunkFilePath, 'rb');
                            stream_copy_to_stream($chunk, $finalFile);
                            fclose($chunk);
                            unlink($chunkFilePath); // Supprimer le chunk après fusion
                        }
                    }
                    fclose($finalFile);
            
                    // Upload vers WebDAV avec le nom correct
                    $stream = fopen($finalPath, 'r');
                    Storage::disk('webdav')->put($release->catalog . '_' . time() . '_' . $fileName, $stream);
                    fclose($stream);
                    unlink($finalPath); // Nettoyer le fichier temporaire
            
                    // Mettre à jour la release avec le nom correct du fichier
                    $release->update([
                        'file_release_name' => $fileName // Nom original sans .part
                    ]);
            
                    \Log::info('Fichier uploadé avec succès vers WebDAV: ' . $fileName);
                } catch (\Exception $e) {
                    \Log::error('Erreur lors de la fusion/upload : ' . $e->getMessage());
                    throw $e;
                }
            }
        }

        if ($request->hasFile('file_cover')) {
            try {
                $file = $request->file('file_cover');
                $filename = $file->getClientOriginalName();
                
                // Lecture du contenu du fichier
                $fileContent = file_get_contents($file->getRealPath());
                
                // Envoi vers WebDAV
                Storage::disk('webdav')->put($filename, $fileContent);
                $release->update([
                    'file_cover_name' => $filename
                ]);
                
                \Log::info('Fichier uploadé avec succès vers WebDAV: ' . $filename);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'upload WebDAV: ' . $e->getMessage());
            }
        }
       
        return redirect()->route('dashboard')->with('success', [
            'message' => 'Modifications enregistrées avec succès',
        ]);
    } */
}
