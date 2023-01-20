<article class="newsWrap">
    <div class="newsTitle"><a href="{{ $news_url }}" target="_blank"><h3>{{ $title }}</h3></a></div>
    @if ($displayType == 'fullwidth')<div class="newsInner">@endif
        <div class="newsImage"><img src="{{ $image[0]['img'] }}" alt="{{ $image[0]['alt'] }}"></div>
        <div class="newsText">{!! $text !!}</div>
    @if ($displayType == 'fullwidth')</div>@endif
    <div class="newsSite"><a href="{{ $news_url }}" target="_blank">{{ $site_title }}</a></div>
</article>
