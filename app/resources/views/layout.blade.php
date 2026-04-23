<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {!! \Modules\Seo\Facades\Seo::generate(); !!}

    <script
        src="https://code.jquery.com/jquery-4.0.0.js"
        integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U="
        crossorigin="anonymous"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @foreach(config('app.available_locales') as $locale)
        <link rel="alternate"
              hreflang="{{ $locale }}"
              href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters(), ['locale' => $locale])) }}">
    @endforeach
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Header -->
@include('includes.header')

<!-- Content -->
<main class="flex-grow-1 container py-4">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @yield('content')
</main>

<!-- Footer -->
@include('includes.footer')


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
