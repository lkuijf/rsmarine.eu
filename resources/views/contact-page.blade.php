@extends('templates.rsmarine')
@section('content')
    @include('snippets.contentSections')
    <div class="inner">@include('snippets.contact-form')</div>
    @include('snippets.route')
@endsection
@section('after_body_tag')
    @if(session('success'))
        <div class="alert alert-success">
            {{-- <div><p class="thumbsUpIcon"></p></div> --}}
            <div><p>{{ $data['website_options']['form_success'] }}</p></div>
            {{-- <div><p>Gelukt</p></div> --}}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            {{-- <div><p class="exclamationTriangleIcon"></i></div> --}}
            <div></p><p>{{ $data['website_options']['form_error'] }}</p></div>
            {{-- <div><p>MISlukt</p></div> --}}
        </div>
    @endif
@endsection
@section('before_closing_body_tag')
    @if($errors->any())
    <script>
        const errors = document.querySelectorAll('.error');
        errors.forEach((el) => {
            const err = document.createElement('span');
            err.classList.add('errMsg');
            err.innerHTML = el.dataset.errMsg;
            el.appendChild(err);
        });
    </script>
    @endif
@endsection
