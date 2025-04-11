<x-mail::message>
@foreach($release->release_members as $member)
@if ($member['is_reference'])
# Salut {{ $member['firstname'] }} !
@endif
@endforeach

Un nouveau labelcopy a été créé pour toi.

Tu peux le consulter et commencer de le renseigner dès maintenant en cliquant sur le bouton ci-dessous :

<x-mail::button :url="route('dashboard.editrelease', ['release' => $release->id])">
Labelcopy {{ $release->catalog }}
</x-mail::button>
A tout bientôt,<br>
Blizzard Audio Club
</x-mail::message>