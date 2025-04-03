<x-mail::message>
# Kikou,

Le membre de référence 
@foreach($release->release_members as $member)
    @if ($member->is_reference)
        {{ $member->firstname }} {{ $member->lastname }} ({{ $member->email }})
    @endif
@endforeach
a fait un nouvel enregistrement de son labelcopy <a href="{{ route('dashboard.editrelease', ['release' => $release->id]) }}">{{ $release->catalog }}</a>.

Bisous
</x-mail::message>