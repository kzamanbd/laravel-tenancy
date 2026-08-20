<x-mail::message>
# {{ $heading }}

@if ($impact)
**Impact:** {{ $impact }}
@endif

{{ $body }}

@if ($components)
**Affected:** {{ implode(', ', $components) }}
@endif

<x-mail::button :url="$pageUrl">
View status page
</x-mail::button>

<small>You are receiving this because you subscribed to {{ $pageName }}.
[Unsubscribe]({{ $unsubscribeUrl }}).</small>
</x-mail::message>
