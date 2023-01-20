@foreach ($data['content_sections'] as $section)

        @if ($section['type'] == '1column')
            @foreach ($section['1column'] as $secData)

                @if ($secData->_type == 'tekst')
                    @include('sections.fullwidth_text', [
                    'text' => $secData->text,
                    ])
                @endif
                @if ($secData->_type == 'afbeelding')
                    @include('sections.fullwidth_afbeelding', [
                    'imgUrl' => $secData->img,
                    'imgAlt' => $secData->alt,
                    ])
                @endif
                @if ($secData->_type == 'bestand')
                    @include('sections.fullwidth_bestand', [
                    'file' => $secData->file,
                    'title' => $secData->title,
                    ])
                @endif
                @if ($secData->_type == 'nieuws-items')
                    @foreach ($secData->news_associations as $newsItem)
                        @include('sections.fullwidth_news', [
                        'title' => $newsItem->title,
                        'site_title' => $newsItem->site_title,
                        'news_url' => $newsItem->news_url,
                        'text' => $newsItem->text,
                        'image' => $newsItem->image,
                        ])
                    @endforeach
                @endif

            @endforeach
        @endif

        @if ($section['type'] == '2column')
            <div class="inner">
            <div class="columns">
                <div>
                    @foreach ($section['2column']['left'] as $secData)
                        @include('snippets.contentSection_2columns')
                    @endforeach
                </div>
                <div>
                    @foreach ($section['2column']['right'] as $secData)
                        @include('snippets.contentSection_2columns')
                    @endforeach
                </div>
            </div>
            </div>
        @endif






    {{-- @if ($section['type'] == 'banner')
        @include('sections.banner', [
            'image' => $section['img'], 
            'extraPadding' => $section['checked'],
            // 'wl' => $section['wl_header'],
            // 'bl' => $section['bl_header'],
            't_align' => $section['text_align'],
            't_color' => $section['text_color'],
            'i_opacity' => $section['image_opacity'],
            'text' => $section['text'],
            'buttons' => $section['links'],
            ])
    @endif
    @if ($section['type'] == 'text')
        @include('sections.text', [
            // 'image' => $section['img'], 
            // 'wl' => $section['wl_header'],
            // 'bl' => $section['bl_header'],
            // 'vAlign' => $section['valign_center'],
            'text' => $section['text'],
            // 'bg_color' => $section['background_color'],
            // 'orientation' => $section['orientation'],
            // 'margin' => $section['margin'],
            ])
    @endif
    @if ($section['type'] == 'text_flex')
        @include('sections.text_flex', [
            // 'image' => $section['img'], 
            // 'wl' => $section['wl_header'],
            // 'bl' => $section['bl_header'],
            // 'vAlign' => $section['valign_center'],
            'header' => $section['hdr'],
            'text_left' => $section['text_l'],
            'text_right' => $section['text_r'],
            'bg_color' => $section['background_color'],
            'column_stretch' => $section['stretch'],
            'buttons_l' => $section['links_l'],
            'buttons_r' => $section['links_r'],
            // 'orientation' => $section['orientation'],
            // 'margin' => $section['margin'],
            ])
    @endif
    @if ($section['type'] == 'text_grid')
        @include('sections.text_grid', [
            'gridItems' => $section['grid_items'], 
            ])
    @endif
    @if ($section['type'] == 'info_icons')
        @include('sections.info_icons', [
            'infoItems' => $section['info_icons'], 
            ])
    @endif
    @if ($section['type'] == 'testimonials')
        @include('sections.testimonials', [
            'testimonials' => $section['testimonials'], 
            ])
    @endif
    @if ($section['type'] == 'colleagues')
        @include('sections.colleagues', [
            'colleagues' => $section['people'], 
            ])
    @endif
    @if ($section['type'] == 'joboffers')
        <div id="jobOffersHomepage">
            <div class="inner">
                @include('sections.jobOffers', [
                    'jobOffers' => $section['jobOffers'], 
                    ])
                <div role="tablist" class="jobOfferDots"></div>
            </div>
        </div>
        @section('before_closing_body_tag')
        <script>
            makeResultsClickable();
        </script>
        @endsection
    @endif
    @if ($section['type'] == 'information_blocks_holder')
        @include('sections.information_blocks', ['info_blocks' => $section['blocks']])
    @endif
    @if ($section['type'] == 'person_wraps')
        @include('sections.people_blocks', ['person_blocks' => $section['people']])
    @endif --}}
@endforeach
