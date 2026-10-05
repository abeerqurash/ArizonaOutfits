<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'IdeoStream' }}</title>
    <meta name="description" content="{{ $meta_description ?? '' }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    <link rel="stylesheet" href="{{ asset('asset/css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
</head>

<body id="{{ str_replace('.', '-', Route::currentRouteName()) }}" 
    class="{{ str_replace('.', ' ', Route::currentRouteName()) }}">

    @include('partials.headerportfolio')
    <main>
        @yield('content')
    </main>
    @include('partials.footerportfolio')
@stack('page-scripts')

{{-- Or, conditional script loading based on route --}}
@if(Route::currentRouteName() === 'abeerKhan-page')
<script src="{{ asset('asset/js/main.js') }}"></script>
@endif

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"></script>
</body>

</html>