
@extends('layout')

@section('content')


    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('news', ['locale' => app()->currentLocale()]) }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="form-label">Пошук</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filter->search }}"
                            class="form-control"
                            placeholder="Назва">
                    </div>


                    {{-- сортування --}}
                    <div class="col-md-2">
                        <label class="form-label">Сортувати по</label>
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ $filter->sortBy === 'created_at' ? 'selected' : '' }}>Дата створення</option>
                            <option value="updated_at" {{ $filter->sortBy === 'updated_at' ? 'selected' : '' }}>Дата оновлення</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Напрям</label>
                        <select name="sort_dir" class="form-select">
                            <option value="desc" {{ $filter->sortDir === 'desc' ? 'selected' : '' }}>↓</option>
                            <option value="asc" {{ $filter->sortDir === 'asc' ? 'selected' : '' }}>↑</option>
                        </select>
                    </div>

                    {{-- per page --}}
                    <div class="col-md-1">
                        <label class="form-label">К-сть</label>
                        <select name="per_page" class="form-select">
                            <option value="5" {{ $filter->perPage === 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ $filter->perPage === 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $filter->perPage === 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $filter->perPage === 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $filter->perPage === 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Застосувати
                        </button>
                        <a href="{{ route('news', ['locale' => app()->currentLocale()]) }}" class="btn btn-outline-secondary w-100">
                            Скинути
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <div class="container">

        @foreach($news as $new)

            <div class="mb-4 pb-4 border-bottom">

                <!-- Title -->
                <h2 class="h4 mb-2">
                    {{ $new->translate(app()->currentLocale())->title }}
                </h2>

                <!-- Meta -->
                <div class="mb-2 text-muted small">
                    20 березня 2026 • Автор: Admin
                </div>

                <!-- Tags (hardcoded) -->
                <div class="mb-2">
                    @if(0 < count($new->tags))

                    @foreach($new->tags as $tag)
                            <a
                                href="{{ route('tags.index', ['locale' => app()->currentLocale(), 'tagName' => $tag->title]) }}"
                                class="badge bg-primary text-white text-decoration-none">
                                {{ $tag->title }}
                            </a>

                    @endforeach
                    @endif
                </div>

                <!-- Text preview -->
                <p class="mb-3">
                    {{ \Illuminate\Support\Str::limit($new->translate(app()->currentLocale())->text, 250) }}
                </p>

                <!-- Button -->
                <a href="{{route('news.show', ['slug' => $new->slug, 'locale' => app()->currentLocale()])}}" class="btn btn-sm btn-outline-dark">
                    {{__('common.read_more')}} →
                </a>

            </div>
        @endforeach

        <div>
            {{ $news->links() }}
        </div>
    </div>


@endsection
