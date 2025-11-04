<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8" />
        <title> @yield('title') | {{config('app.name')}}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
          @php
        if (Cache::has('CMScolor')) {

            $color = Cache::get('CMScolor', '#FFC0CB');
        } else {
            $color = App\Models\ContentManagement::where('name', 'CMScolor')->value('value') ?? '#FFC0CB';
            Cache::put('CMScolor', $color);
        }

    @endphp
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.png')}}">
        @include('layouts.head-css')
  </head>

    @yield('body')
    
    @yield('content')

    @include('layouts.vendor-scripts')
    </body>
</html>