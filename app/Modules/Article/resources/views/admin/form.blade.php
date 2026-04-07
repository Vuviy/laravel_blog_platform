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
            <form method="POST" action="{{route('admin.articles.update', ['article' => $article->id])}}">
            @method('PUT')
        @else
            <form method="POST" action="{{route('admin.articles.store')}}">
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
                                <textarea name="translations[{{ $locale }}][text]"
                                          class="form-control"
                                          rows="5">{{ isset($article) ? $article->translate($locale)?->text?->getValue() : '' }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>


{{--                <div class="form-group">--}}
{{--                    <label>Назва</label>--}}
{{--                    <input type="text" name="title" class="form-control" placeholder="Введи назву"--}}
{{--                           value="{{isset($article) ? $article->title : ''}}">--}}
{{--                </div>--}}

{{--                <div class="form-group">--}}
{{--                    <label>Текст</label>--}}
{{--                    <textarea name="text" class="form-control" rows="5">{{isset($article) ? $article->text : ''}}</textarea>--}}
{{--                </div>--}}

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
            $('#tags-select').select2({
                placeholder: 'Оберіть теги...',
                allowClear: true,
                width: '100%',
            });
        });
    </script>
@endpush
