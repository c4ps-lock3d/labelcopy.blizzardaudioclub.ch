<x-mail::message>
# Kikou,

Le membre de référence 
@foreach($release->release_members as $member)
@if ($member['is_reference'])
{{ $member['firstname'] }} {{ $member['lastname'] }} ({{ $member['email'] }})
@endif
@endforeach
a fait un nouvel enregistrement de son labelcopy <a href="{{ route('dashboard.editrelease', ['release' => $release->id]) }}">{{ $release->catalog }}</a>.
@php
    // Initialiser le suivi des modifications
    $hasChanges = false;
    $changes = [];


// Comparer les champs principaux
foreach ($release->getAttributes() as $key => $value) {
    if (!in_array($key, ['created_at', 'updated_at', 'id'])) {
        $oldValue = $release_before[$key] ?? null;
        $newValue = $value;

        // Traitement spécial pour les booléens
        if (in_array($key, ['isProduitsDerives', 'isBesoinSubvention', 'isBesoinPromo', 'isBesoinDigitalMarketing', 'isBesoinContacts', 'isActive'])) {
            $oldValue = (bool)$oldValue;
            $newValue = (bool)$newValue;
        }

        // Traitement spécial pour les champs avec traduction
        if ($key === 'artistName') {
            $key = "Nom de l'artiste";
        }elseif ($key === 'artistBiography') {
            $key = "Biographie de l'artiste";
        }elseif ($key === 'name') {
            $key = "Nom de la sortie";
        }elseif ($key === 'artistIBAN') {
            $key = "IBAN de l'artiste";
        }elseif ($key === 'description') {
            $key = "Description de la sortie";
        }elseif ($key === 'SourceFinancement') {
            $key = "Source de financement du projet";
        }elseif ($key === 'BesoinFinancement') {
            $key = "Besoin de financement";
        }elseif ($key === 'IsBesoinSubvention') {
            $key = "Besoin de subvention";
        }elseif ($key === 'IsBesoinDigitalMarketing') {
            $key = "Besoin de marketing digital";
        }elseif ($key === 'isProduitsDerives') {
            $key = "Produits dérivés";
        }elseif ($key === 'isBesoinPromo') {
            $key = "Besoin de promo";
        }elseif ($key === 'isBesoinContacts') {
            $key = "Besoin de contacts";
        }elseif ($key === 'besoinContacts') {
            $key = "Raison(s) du besoin de contacts";
        }
        

        // Traitement spécial pour release_type_id
        if ($key === 'release_type_id') {
            $oldType = \App\Models\ReleaseType::find($oldValue);
            $newType = \App\Models\ReleaseType::find($newValue);
            $key = 'type'; // Modifier le nom du champ pour l'affichage
            $oldValue = $oldType ? $oldType->name : 'N/A';
            $newValue = $newType ? $newType->name : 'N/A';
        }

        if ($oldValue !== $newValue) {
            $hasChanges = true;
            $changes['attributes'][] = [
                'field' => $key,
                'old' => $oldValue,
                'new' => $newValue
            ];
        }
    }

}   

// Comparer les formats
foreach ($release->release_formats as $index => $format) {
    $before_format = $release_before['release_formats'][$index] ?? [];
    $format_changes = [];
    
    // Vérifier le nom
    if ((string)($before_format['name'] ?? '') !== (string)($format['name'] ?? '')) {
        $format_changes['name'] = [
            'field' => 'format',
            'old' => $before_format['name'] ?? 'N/A',
            'new' => $format['name']
        ];
    }

    // Si des changements sont détectés pour ce format
    if (!empty($format_changes)) {
        $hasChanges = true;
        $changes['formats'][] = [
            'format_number' => $index + 1,
            'changes' => $format_changes
        ];
    }
}

    // Comparer les pistes
    foreach ($release->release_tracks as $index => $track) {
        $before_track = $release_before['release_tracks'][$index] ?? [];
        $track_changes = [];
        
        // Vérifier le titre
        if ((string)($before_track['title'] ?? '') !== (string)($track['title'] ?? '')) {
            $track_changes['title'] = [
                'field' => 'titre',
                'old' => $before_track['title'] ?? 'N/A',
                'new' => $track['title']
            ];
        }

        // Vérifier les pourcentages des membres pour cette piste
        foreach ($track->release_members as $member_index => $member) {
            $before_percentage = $before_track['release_members'][$member_index]['pivot']['percentage'] ?? 0;
            $current_percentage = $member->pivot->percentage ?? 0;

            if ((float)$before_percentage !== (float)$current_percentage) {
                $track_changes['percentages'][] = [
                    'field' => 'pourcentage',
                    'member_name' => $member->firstname . ' ' . $member->lastname,
                    'old' => $before_percentage . '%',
                    'new' => $current_percentage . '%'
                ];
            }
        }

        // Vérifier isSingle
        if ((bool)($before_track['isSingle'] ?? false) !== (bool)($track['isSingle'] ?? false)) {
            $track_changes['isSingle'] = [
                'field' => 'single',
                'old' => ($before_track['isSingle'] ?? false) ? 'Oui' : 'Non',
                'new' => ($track['isSingle'] ?? false) ? 'Oui' : 'Non'
            ];
        }

        // Vérifier hasClip
        if ((bool)($before_track['hasClip'] ?? false) !== (bool)($track['hasClip'] ?? false)) {
            $track_changes['hasClip'] = [
                'field' => 'clip',
                'old' => ($before_track['hasClip'] ?? false) ? 'Oui' : 'Non',
                'new' => ($track['hasClip'] ?? false) ? 'Oui' : 'Non'
            ];
        }

        // Vérifier release_date_single avec normalisation
        $old_release_date = trim((string)($before_track['release_date_single'] ?? ''));
        $new_release_date = trim((string)($track['release_date_single'] ?? ''));
        if ($old_release_date !== $new_release_date) {
            $track_changes['release_date_single'] = [
                'field' => 'date de sortie single',
                'old' => $old_release_date ?: 'N/A',
                'new' => $new_release_date ?: 'N/A'
            ];
        }

        // Si des changements sont détectés pour cette piste
        if (!empty($track_changes)) {
            $hasChanges = true;
            $changes['tracks'][] = [
                'number' => $track['number'],
                'changes' => $track_changes
            ];
        }
    }

    // Comparer les membres
    foreach ($release->release_members as $index => $member) {
        $before_member = $release_before['release_members'][$index] ?? [];
        $member_changes = [];
        
        // Vérifier le nom
        if (
            (string)($before_member['firstname'] ?? '') !== (string)($member['firstname'] ?? '') ||
            (string)($before_member['lastname'] ?? '') !== (string)($member['lastname'] ?? '')
        ) {
            $member_changes['name'] = [
                'old' => trim(($before_member['firstname'] ?? '') . ' ' . ($before_member['lastname'] ?? '')),
                'new' => trim($member['firstname'] . ' ' . $member['lastname'])
            ];
        }

        // Ajouter la vérification des coordonnées
        if ($member['is_reference']) {
            // Vérifier l'email
            if ((string)($before_member['email'] ?? '') !== (string)($member['email'] ?? '')) {
                $member_changes['email'] = [
                    'old' => $before_member['email'] ?? 'N/A',
                    'new' => $member['email'] ?? 'N/A'
                ];
            }

            // Vérifier l'adresse
            if (
                (string)($before_member['street'] ?? '') !== (string)($member['street'] ?? '') ||
                (string)($before_member['zip_code'] ?? '') !== (string)($member['zip_code'] ?? '') ||
                (string)($before_member['city'] ?? '') !== (string)($member['city'] ?? '')
            ) {
                $member_changes['address'] = [
                    'old' => trim(implode(' ', [
                        $before_member['street'] ?? '',
                        $before_member['zip_code'] ?? '',
                        $before_member['city'] ?? ''
                    ])) ?: 'N/A',
                    'new' => trim(implode(' ', [
                        $member['street'] ?? '',
                        $member['zip_code'] ?? '',
                        $member['city'] ?? ''
                    ])) ?: 'N/A'
                ];
            }

            // Vérifier le pays
            if ((string)($before_member['country'] ?? '') !== (string)($member['country'] ?? '')) {
                $member_changes['country'] = [
                    'old' => $before_member['country'] ?? 'N/A',
                    'new' => $member['country'] ?? 'N/A'
                ];
            }

            // Vérifier le téléphone
            if ((string)($before_member['phone_number'] ?? '') !== (string)($member['phone_number'] ?? '')) {
                $member_changes['phone'] = [
                    'old' => $before_member['phone_number'] ?? 'N/A',
                    'new' => $member['phone_number'] ?? 'N/A'
                ];
            }
        }

        // Vérifier la date de naissance avec normalisation
        $old_birthdate = trim((string)($before_member['birth_date'] ?? ''));
        $new_birthdate = trim((string)($member['birth_date'] ?? ''));
        if ($old_birthdate !== $new_birthdate) {
            $member_changes['birthdate'] = [
                'old' => $old_birthdate ?: 'N/A',
                'new' => $new_birthdate ?: 'N/A'
            ];
        }

        // Vérifier le numéro IPI avec normalisation
        $old_ipi = trim((string)($before_member['IPI'] ?? ''));
        $new_ipi = trim((string)($member['IPI'] ?? ''));
        if ($old_ipi !== $new_ipi) {
            $member_changes['ipi'] = [
                'old' => $old_ipi ?: 'N/A',
                'new' => $new_ipi ?: 'N/A'
            ];
        }

        // Si des changements sont détectés pour ce membre
        if (!empty($member_changes)) {
            $hasChanges = true;
            $changes['members'][] = [
                'member_number' => $index + 1,
                'changes' => $member_changes
            ];
        }
    }
@endphp

@if ($hasChanges)
**Modifications effectuées :**

@if (isset($changes['attributes']))
@foreach ($changes['attributes'] as $change)
**{{ ucfirst($change['field']) }}** :
<br>
@if (is_bool($change['old']))
{{ $change['old'] ? 'Oui' : 'Non' }} → {{ $change['new'] ? 'Oui' : 'Non' }}
<br>
@else
{{ $change['old'] ?? 'N/A' }} → {{ $change['new'] ?? 'N/A' }}
<br>
@endif
@endforeach
@endif

@if (isset($changes['formats']))
@foreach ($changes['formats'] as $format)
**Format {{ $format['format_number'] }}** :
@foreach ($format['changes'] as $change)
    - {{ ucfirst($change['field']) }} : {{ $change['old'] }} → {{ $change['new'] }}
@endforeach
@endforeach
@endif

@if (isset($changes['tracks']))
@foreach ($changes['tracks'] as $track)
**Piste {{ $track['number'] }}** :
<br>
@foreach ($track['changes'] as $key => $change)
    @if ($key === 'percentages')
        @foreach ($change as $percentage_change)
        - Pourcentage {{ $percentage_change['member_name'] }} : {{ $percentage_change['old'] }} → {{ $percentage_change['new'] }}
        @endforeach
        <br>
    @else
        - {{ ucfirst($change['field']) }} : {{ $change['old'] }} → {{ $change['new'] }}
        <br>
    @endif
@endforeach
@endforeach
@endif

@if (isset($changes['members']))
@foreach ($changes['members'] as $member)
**Membre {{ $member['member_number'] }}** :
<br>
@if (isset($member['changes']['name']))
    - Nom : {{ $member['changes']['name']['old'] }} → {{ $member['changes']['name']['new'] }}
    <br>
@endif
@if (isset($member['changes']['email']))
    - Email : {{ $member['changes']['email']['old'] }} → {{ $member['changes']['email']['new'] }}
    <br>
@endif
@if (isset($member['changes']['address']))
    - Adresse : {{ $member['changes']['address']['old'] }} → {{ $member['changes']['address']['new'] }}
    <br>
@endif
@if (isset($member['changes']['country']))
    - Pays : {{ $member['changes']['country']['old'] }} → {{ $member['changes']['country']['new'] }}
    <br>
@endif
@if (isset($member['changes']['phone']))
    - Téléphone : {{ $member['changes']['phone']['old'] }} → {{ $member['changes']['phone']['new'] }}
    <br>
@endif

@endforeach
@endif

@else
**Enregistrement effectué sans modifications.**
@endif

<br>

Bisous
</x-mail::message>