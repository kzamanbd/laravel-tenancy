{{--
    The public status page.

    Rendered once at publish time and written to object storage as a flat file.
    Everything it needs is inlined: no stylesheet request, no script, no font
    fetch, no call back to this application. It has to render correctly when
    the service it reports on -- and this deployment -- are unreachable, which
    rules out every external dependency.
--}}
@php
    $page = $snapshot['page'];
    $status = $snapshot['status'];
    $tone = [
        'operational' => ['#16a34a', '#dcfce7', '#14532d'],
        'under_maintenance' => ['#0284c7', '#e0f2fe', '#0c4a6e'],
        'degraded_performance' => ['#d97706', '#fef3c7', '#78350f'],
        'partial_outage' => ['#ea580c', '#ffedd5', '#7c2d12'],
        'major_outage' => ['#dc2626', '#fee2e2', '#7f1d1d'],
    ];
    $swatch = fn (string $value) => $tone[$value] ?? $tone['operational'];
    $banner = $swatch($status['value']);
    $formatted = fn (?string $iso) => $iso
        ? \Illuminate\Support\Carbon::parse($iso)->setTimezone($page['timezone'])->format('M j, Y H:i T')
        : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $page['name'] }} Status</title>
<meta name="description" content="{{ $status['description'] }} — {{ $page['name'] }}">
<meta name="robots" content="index, follow">
<style>
    :root {
        --brand: {{ $page['primaryColor'] }};
        --bg: #ffffff;
        --fg: #111827;
        --muted: #6b7280;
        --border: #e5e7eb;
        --card: #ffffff;
    }
    @media (prefers-color-scheme: dark) {
        :root {
            --bg: #0b0b0c;
            --fg: #f9fafb;
            --muted: #9ca3af;
            --border: #26262a;
            --card: #131316;
        }
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        background: var(--bg);
        color: var(--fg);
        font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .wrap { max-width: 46rem; margin: 0 auto; padding: 2.5rem 1.25rem 4rem; }
    header { display: flex; align-items: center; gap: .75rem; margin-bottom: 2rem; }
    header img { height: 2rem; }
    header h1 { font-size: 1.25rem; margin: 0; }
    .banner {
        border-radius: .75rem; padding: 1.25rem 1.5rem; margin-bottom: 2rem;
        background: {{ $banner[1] }}; color: {{ $banner[2] }};
        border: 1px solid {{ $banner[0] }}33;
    }
    .banner strong { display: block; font-size: 1.125rem; }
    .banner span { font-size: .875rem; opacity: .85; }
    h2 { font-size: .875rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); margin: 2rem 0 .75rem; }
    .card { border: 1px solid var(--border); border-radius: .75rem; background: var(--card); overflow: hidden; }
    .row { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .875rem 1.125rem; border-bottom: 1px solid var(--border); }
    .row:last-child { border-bottom: 0; }
    .row .name { font-weight: 500; }
    .row .desc { display: block; font-size: .8125rem; color: var(--muted); font-weight: 400; }
    .pill { font-size: .75rem; font-weight: 600; padding: .25rem .625rem; border-radius: 999px; white-space: nowrap; }
    article { border: 1px solid var(--border); border-radius: .75rem; background: var(--card); padding: 1.125rem; margin-bottom: 1rem; }
    article h3 { margin: 0 0 .25rem; font-size: 1rem; }
    article .meta { font-size: .8125rem; color: var(--muted); }
    .update { border-left: 2px solid var(--border); padding: .5rem 0 .5rem .875rem; margin-top: .875rem; }
    .update p { margin: .375rem 0 0; font-size: .9375rem; white-space: pre-line; }
    form.subscribe { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .75rem; }
    form.subscribe input {
        flex: 1 1 16rem; min-width: 0; padding: .625rem .75rem; font: inherit; font-size: .9375rem;
        color: var(--fg); background: var(--card); border: 1px solid var(--border); border-radius: .5rem;
    }
    form.subscribe button {
        padding: .625rem 1.125rem; font: inherit; font-size: .9375rem; font-weight: 600; cursor: pointer;
        color: #fff; background: var(--brand); border: 0; border-radius: .5rem;
    }
    .subscribe-note { font-size: .8125rem; color: var(--muted); margin: .5rem 0 0; }
    footer { margin-top: 3rem; text-align: center; font-size: .8125rem; color: var(--muted); }
    footer a { color: var(--brand); }
    .empty { color: var(--muted); font-size: .9375rem; padding: 1.125rem; }
</style>
@if ($page['customCss'])
<style>{!! $page['customCss'] !!}</style>
@endif
</head>
<body>
<div class="wrap">
    <header>
        @if ($page['logoPath'])
            <img src="{{ $page['logoPath'] }}" alt="{{ $page['name'] }}">
        @endif
        <h1>{{ $page['name'] }}</h1>
    </header>

    <div class="banner">
        <strong>{{ $status['description'] }}</strong>
        <span>Updated {{ $formatted($snapshot['generatedAt']) }}</span>
    </div>

    @if ($page['headline'])
        <p>{{ $page['headline'] }}</p>
    @endif

    <h2>Components</h2>
    <div class="card">
        @forelse ($snapshot['components'] as $component)
            @php $c = $swatch($component['status']); @endphp
            <div class="row">
                <span class="name">
                    {{ $component['name'] }}
                    @if ($component['description'])
                        <span class="desc">{{ $component['description'] }}</span>
                    @endif
                </span>
                <span class="pill" style="background: {{ $c[1] }}; color: {{ $c[2] }};">
                    {{ $component['statusLabel'] }}
                </span>
            </div>
        @empty
            <p class="empty">No components are being reported yet.</p>
        @endforelse
    </div>

    @if ($snapshot['maintenances'])
        <h2>Scheduled maintenance</h2>
        @foreach ($snapshot['maintenances'] as $window)
            <article>
                <h3>{{ $window['title'] }}</h3>
                <p class="meta">
                    {{ $formatted($window['scheduledStartAt']) }} — {{ $formatted($window['scheduledEndAt']) }}
                </p>
                @if ($window['description'])
                    <p>{{ $window['description'] }}</p>
                @endif
            </article>
        @endforeach
    @endif

    <h2>Incident history</h2>
    @forelse ($snapshot['incidents'] as $incident)
        @php $i = $swatch($incident['status'] === 'resolved' ? 'operational' : 'major_outage'); @endphp
        <article id="incident-{{ $incident['slug'] }}">
            <h3>{{ $incident['title'] }}</h3>
            <p class="meta">
                <span class="pill" style="background: {{ $i[1] }}; color: {{ $i[2] }};">
                    {{ $incident['statusLabel'] }}
                </span>
                {{ $incident['impactLabel'] }} impact · Started {{ $formatted($incident['startedAt']) }}
                @if ($incident['components'])
                    · {{ implode(', ', $incident['components']) }}
                @endif
            </p>
            @foreach ($incident['updates'] as $update)
                <div class="update">
                    <strong>{{ $update['statusLabel'] }}</strong>
                    <span class="meta">{{ $formatted($update['publishedAt']) }}</span>
                    <p>{{ $update['body'] }}</p>
                </div>
            @endforeach
        </article>
    @empty
        <div class="card"><p class="empty">No incidents reported.</p></div>
    @endforelse

    <h2>Subscribe to updates</h2>
    <div class="card" style="padding: 1.125rem;">
        {{-- Posts back to the application: the page itself is a static file on
             a CDN, but a subscription is a write. --}}
        <form class="subscribe" method="POST" action="{{ $page['subscribeUrl'] }}">
            <input type="email" name="email" required placeholder="you@example.com" aria-label="Email address">
            <button type="submit">Subscribe</button>
        </form>
        <p class="subscribe-note">
            We will send a confirmation link first. Nothing is sent to an address that has not confirmed.
        </p>
    </div>

    <footer>
        @if ($page['supportUrl'])
            <p><a href="{{ $page['supportUrl'] }}">Contact support</a></p>
        @endif
        @if ($page['showPoweredBy'])
            <p>Powered by {{ config('app.name') }}</p>
        @endif
    </footer>
</div>
</body>
</html>
