

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <title>{{ config('app.name', 'IdeoStream') }}</title>
    <meta name="description" content="@yield('meta_description', $meta_description ?? '')">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">

    <link rel="stylesheet" href="{{ asset('asset/css/style.css') }}">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
<!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('page-styles')
</head>

<body id="{{ Route::currentRouteName() ? str_replace('.', '-', Route::currentRouteName()) : 'page' }}"
      class="{{ Route::currentRouteName() ? str_replace('.', ' ', Route::currentRouteName()) : '' }}">

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
           

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"></script>

  {{-- Page Scripts --}}
    <script src="{{ asset('asset/js/main.js') }}"></script>


</body>
</html>
