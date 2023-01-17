<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['head_title'] }}</title>
    <meta name="description" content="{{ $data['meta_description'] }}">
    {{-- <script src="{{ asset('js/glider.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/glider.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}"> --}}
</head>
<body>
    {!! $data['html_menu'] !!}
    @yield('content')
</body>
</html>