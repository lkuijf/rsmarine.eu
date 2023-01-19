
    <article class="newsWrap">
        <div class="newsTitle"><a href="{{ $news_url }}" target="_blank"><h3>{{ $title }}</h3></a></div>
        {{-- <div class="newsInner"> --}}
            <div class="newsImage"><img src="{{ $image[0]['img'] }}" alt="{{ $image[0]['alt'] }}"></div>
            <div class="newsText">{!! $text !!}</div>
        {{-- </div> --}}
        <div class="newsSite"><a href="{{ $news_url }}" target="_blank">{{ $site_title }}</a></div>
    </article>