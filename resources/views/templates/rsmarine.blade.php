<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['head_title'] }}</title>
    <meta name="description" content="{{ $data['meta_description'] }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>

    <div class="top homepage">
        <img src="{{ asset('statics/header.jpg') }}" alt="">
        <div class="topInfo">
            <div class="inner">
                <div class="logoWrap">
                    <div>
                        <p>RS Marine Shipmanagement LTD</p>
                        <p>Connecting seafarers, vessel and Shipowner</p>
                    </div>
                    <div><img src="{{ asset('statics/rs-marine-logo.jpg') }}" alt="RS Marine - Logo"></div>
                </div>
            </div>
        </div>
    </div>

    <nav class="mainNav">
        <div class="inner">
            <input type="checkbox" id="burger-check">
            <label for="burger-check" class="burger-label">
                <span></span>
                <span></span>
                <span></span>
            </label>
            {!! $data['html_menu'] !!}
        </div>
    </nav>

    <div class="contentWrapper">
        @yield('content')
    </div>
    
    <footer>
        <div class="inner">
            <div>&copy; Copyright {{ date('Y') }} RS Marine Shipmanagement Ltd</div>
            <div>Connecting seafarers, vessel and Shipowner Connecting seafarers, vessel and Shipowner Connecting seafarers, vessel and Shipowner Connecting seafarers, vessel and Shipowner</div>
        </div>
    </footer>
    
</body>
</html>