@extends('admin.layout')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">{{__('common.create_update') }}</h3>
        </div>
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(isset($seoPage))
            <form method="POST"   enctype="multipart/form-data" action="{{route('admin.seo.update', ['seo' => $seoPage->id])}}" id="seo_form">
                @method('PUT')
                @else
                    <form method="POST"  enctype="multipart/form-data"  action="{{route('admin.seo.store')}}" id="seo_form">
                        @endif
                        @csrf
                        <div class="card-body">

                            <div class="form-group">
                                <label>Url</label>
                                <input type="text"
                                       name="url"
                                       id="url"
                                       class="form-control"
                                       value="{{ isset($seoPage) ? $seoPage->url : '' }}">

                            </div>

                            {{-- Таби для мов --}}
                            <ul class="nav nav-tabs" id="localeTabs">
                                @foreach(config('app.available_locales') as $locale)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                           data-bs-toggle="tab"
                                           href="#locale-{{ $locale }}">
                                            {{ strtoupper($locale) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content mt-3">
                                @foreach(config('app.available_locales') as $locale)
                                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                         id="locale-{{ $locale }}">

                                        <div>
                                            <div class="form-group">
                                                <label>{{ __('common.seo_title') }}</label>
                                                <input type="text"
                                                       name="translations[{{ $locale }}][seo_title]"
                                                       class="form-control"
                                                       value="{{ isset($seoPage) ? $seoPage->translate($locale)?->seoTitle : '' }}">
                                            </div>

                                            <div class="form-group">
                                                <label>{{ __('common.seo_description') }}</label>
                                                <input type="text"
                                                       name="translations[{{ $locale }}][seo_description]"
                                                       class="form-control"
                                                       value="{{ isset($seoPage) ? $seoPage->translate($locale)?->seoDescription : '' }}">
                                            </div>

                                            <div class="form-group">
                                                <label>{{ __('common.seo_keywords') }}</label>
                                                <input type="text"
                                                       name="translations[{{ $locale }}][seo_keywords]"
                                                       class="form-control"
                                                       value="{{ isset($seoPage) ? $seoPage->translate($locale)?->seoKeywords : '' }}">
                                            </div>

                                            <div class="form-group">
                                                <label>{{ __('common.seo_og_image') }}</label>

                                                @if(isset($seoPage) && $seoPage->translate($locale)?->seoOgImage)
                                                    <div>
                                                        <img src="{{ asset('storage/' . $seoPage->translate($locale)->seoOgImage) }}"
                                                             width="200">
                                                    </div>
                                                @endif

                                                <input type="file"
                                                       name="translations[{{ $locale }}][seo_og_image]"
                                                       class="form-control"
                                                       accept="image/jpg,image/jpeg,image/png,image/webp">
                                            </div>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Зберегти</button>
                        </div>
                    </form>
    </div>
@endsection

@push('scripts')

    <script>
        $(document).ready(function () {
            @foreach(config('app.available_locales') as $loc)
            (function () {
                var quill = new Quill('#editor-{{ $loc }}', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{'header': [1, 2, 3, false]}],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            ['link', 'image'],
                            ['clean'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ size: [ 'small', false, 'large', 'huge' ]}]
                        ]
                    }
                });

                $('#seo_form').on('submit', function () {
                    $('#content-{{ $loc }}').val(quill.root.innerHTML);
                });
            })();
            @endforeach
        });
    </script>
@endpush
