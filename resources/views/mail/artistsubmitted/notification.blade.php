<x-mail::message>
# Kikou,

Le membre de référence 
@foreach($release->release_members as $member)
@if ($member['is_reference'])
{{ $member['firstname'] }} {{ $member['lastname'] }} ({{ $member['email'] }})
@endif
@endforeach
a fait un nouvel enregistrement de son labelcopy <a href="{{ route('dashboard.editrelease', ['release' => $release->id]) }}">{{ $release->catalog }}</a>.

**Modifications effectuées :**
@if ($release['name'] !== $release_before['name'])
- **Nom de la sortie** : {{ $release_before['name'] }} -> {{ $release['name'] }}
@else
<br><i>Aucune modification effectuée.</i>
@endif
<br>

Bisous
</x-mail::message>