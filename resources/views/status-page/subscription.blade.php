{{--
    Confirmation and unsubscribe landing pages.

    Rendered by the application rather than published, because they are reached
    from a one-off token. Kept self-contained for the same reason the status
    page is: they must render when nothing else does.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $heading }}</title>
<meta name="robots" content="noindex">
<style>
    :root { --bg: #ffffff; --fg: #111827; --muted: #6b7280; --border: #e5e7eb; }
    @media (prefers-color-scheme: dark) {
        :root { --bg: #0b0b0c; --fg: #f9fafb; --muted: #9ca3af; --border: #26262a; }
    }
    body {
        margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
        background: var(--bg); color: var(--fg); padding: 1.5rem;
        font: 16px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .card { max-width: 28rem; text-align: center; border: 1px solid var(--border); border-radius: .75rem; padding: 2rem; }
    h1 { font-size: 1.25rem; margin: 0 0 .5rem; }
    p { color: var(--muted); margin: 0; }
</style>
</head>
<body>
<div class="card">
    <h1>{{ $heading }}</h1>
    <p>{{ $message }}</p>
</div>
</body>
</html>
