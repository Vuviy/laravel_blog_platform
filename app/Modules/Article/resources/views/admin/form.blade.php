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
        @if(isset($article))
            <form method="POST"   enctype="multipart/form-data" action="{{route('admin.articles.update', ['article' => $article->id])}}" id="article_form">
                @method('PUT')
                @else
                    <form method="POST"  enctype="multipart/form-data"  action="{{route('admin.articles.store')}}" id="article_form">
                        @endif
                        @csrf
                        <div class="card-body">


                            {{-- Теги --}}
                            <div class="form-group">
                                <label>Теги</label>
                                <select name="tags[]" id="tags-select" class="form-control" multiple>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}"
                                                @if(isset($article) && in_array($tag->id->getValue(), $selectedTagIds ?? []))
                                                    selected
                                            @endif
                                        >
                                            {{ $tag->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Можна обрати декілька тегів</small>
                            </div>

                            <div class="form-group">
                                <label>Slug</label>
                                <input type="text"
                                       name="slug"
                                       id="slug"
                                       class="form-control"
                                       value="{{ isset($article) ? $article->slug : '' }}">

                            </div>

                            <div>
                                <button id="generateSlug">generateSlug</button>
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

                                        <div class="form-group">
                                            <label>{{ __('common.title') }}</label>
                                            <input type="text"
                                                   name="translations[{{ $locale }}][title]"
                                                   class="form-control"
                                                   value="{{ isset($article) ? $article->translate($locale)?->title?->getValue() : '' }}">
                                        </div>

                                        <div class="form-group">
                                            <label>{{ __('common.text') }}</label>

                                            <div id="editor-{{ $locale }}" style="height: 300px;">
                                                {!! isset($article) ? $article->translate($locale)?->text?->getValue() : '' !!}
                                            </div>

                                            <textarea
                                                name="translations[{{ $locale }}][text]"
                                                id="content-{{ $locale }}"
                                                style="display:none">{{ isset($article) ? $article->translate($locale)?->text?->getValue() : '' }}</textarea>
                                        </div>

{{--                                        Seo--}}

                                        <div>
                                            <div class="form-group">
                                                <label>{{ __('common.seo_title') }}</label>
                                                <input type="text"
                                                       name="translations[{{ $locale }}][seo_title]"
                                                       class="form-control"
                                                       value="{{ isset($article) ? $article->translate($locale)?->seoTitle : '' }}">
                                            </div>

                                            <div class="form-group">
                                                <label>{{ __('common.seo_description') }}</label>
                                                <input type="text"
                                                       name="translations[{{ $locale }}][seo_description]"
                                                       class="form-control"
                                                       value="{{ isset($article) ? $article->translate($locale)?->seoDescription : '' }}">
                                            </div>

                                            <div class="form-group">
                                                <label>{{ __('common.seo_keywords') }}</label>
                                                <input type="text"
                                                       name="translations[{{ $locale }}][seo_keywords]"
                                                       class="form-control"
                                                       value="{{ isset($article) ? $article->translate($locale)?->seoKeywords : '' }}">
                                            </div>

                                            <div class="form-group">
                                                <label>{{ __('common.seo_og_image') }}</label>

                                                @if(isset($article) && $article->translate($locale)?->seoOgImage)
                                                    <div>
                                                        <img src="{{ asset('storage/' . $article->translate($locale)->seoOgImage) }}"
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

                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status"
                                           @if(isset($article) && $article->status) checked @endif>
                                    <label class="form-check-label" for="status">Активна</label>
                                </div>
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
            $('#generateSlug').on('click', function (e) {
                e.preventDefault();

                const titleInput = $('input[name="translations[en][title]"]');
                const title = titleInput.val().trim();

                if (!title) {
                    titleInput.addClass('is-invalid');

                    if (!titleInput.next('.invalid-feedback').length) {
                        titleInput.after('<div class="invalid-feedback">Заповніть title для EN локалі</div>');
                        alert('Заповніть title для EN локалі');
                    }
                    return;
                }

                titleInput.removeClass('is-invalid');
                titleInput.next('.invalid-feedback').remove();

                const randomNum = String(Math.floor(Math.random() * 10000)).padStart(4, '0');
                const slug = title
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-') + '-' + randomNum;

                $('#slug').val(slug);
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            $('#tags-select').select2({
                placeholder: 'Оберіть теги...',
                allowClear: true,
                width: '100%',
            });
        });

    </script>

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

                $('#article_form').on('submit', function () {
                    $('#content-{{ $loc }}').val(quill.root.innerHTML);
                });
            })();
            @endforeach
        });
    </script>
@endpush
