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
- **{{ ucfirst($change['field']) }}** : 
@if (is_bool($change['old']))
{{ $change['old'] ? 'Oui' : 'Non' }} -> {{ $change['new'] ? 'Oui' : 'Non' }}
@else
{{ $change['old'] ?? 'N/A' }} -> {{ $change['new'] ?? 'N/A' }}
@endif
@endforeach
@endif

@if (isset($changes['formats']))
@foreach ($changes['formats'] as $format)
- **Format {{ $format['format_number'] }}** :
@foreach ($format['changes'] as $change)
    - {{ ucfirst($change['field']) }} : {{ $change['old'] }} -> {{ $change['new'] }}
@endforeach
@endforeach
@endif

@if (isset($changes['tracks']))
@foreach ($changes['tracks'] as $track)
- **Piste {{ $track['number'] }}** :
@foreach ($track['changes'] as $key => $change)
    @if ($key === 'percentages')
        @foreach ($change as $percentage_change)
        - Pourcentage {{ $percentage_change['member_name'] }} : {{ $percentage_change['old'] }} -> {{ $percentage_change['new'] }}
        @endforeach
    @else
        - {{ ucfirst($change['field']) }} : {{ $change['old'] }} -> {{ $change['new'] }}
    @endif
@endforeach
@endforeach
@endif

@if (isset($changes['members']))
@foreach ($changes['members'] as $member)
- **Membre {{ $member['member_number'] }}** :
@if (isset($member['changes']['name']))
    - Nom : {{ $member['changes']['name']['old'] }} -> {{ $member['changes']['name']['new'] }}
@endif
@if (isset($member['changes']['birthdate']))
    - Date de naissance : {{ $member['changes']['birthdate']['old'] }} -> {{ $member['changes']['birthdate']['new'] }}
@endif
@if (isset($member['changes']['ipi']))
    - Numéro IPI : {{ $member['changes']['ipi']['old'] }} -> {{ $member['changes']['ipi']['new'] }}
@endif
@endforeach
@endif

@else
**Aucune modification effectuée.**
@endif

<br>

Bisous
</x-mail::message>