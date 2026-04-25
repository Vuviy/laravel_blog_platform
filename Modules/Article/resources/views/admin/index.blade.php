@extends('admin.layout')

@section('content')

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.articles.index') }}">
                <div class="row g-3 align-items-end">

                    {{-- пошук --}}
                    <div class="col-md-3">
                        <label class="form-label">Пошук</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filter->search }}"
                            class="form-control"
                            placeholder="Назва статті">
                    </div>

                    {{-- статус --}}
                    <div class="col-md-2">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="">Всі</option>
                            <option value="1" {{ $filter->status === 1 ? 'selected' : '' }}>Активні</option>
                            <option value="0" {{ $filter->status === 0 ? 'selected' : '' }}>Неактивні</option>
                        </select>
                    </div>

                    {{-- дата від --}}
                    <div class="col-md-2">
                        <label class="form-label">Дата від</label>
                        <input
                            type="date"
                            name="date_from"
                            value="{{ $filter->dateFrom }}"
                            class="form-control">
                    </div>

                    {{-- дата до --}}
                    <div class="col-md-2">
                        <label class="form-label">Дата до</label>
                        <input
                            type="date"
                            name="date_to"
                            value="{{ $filter->dateTo }}"
                            class="form-control">
                    </div>

                    {{-- сортування --}}
                    <div class="col-md-2">
                        <label class="form-label">Сортувати по</label>
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ $filter->sortBy === 'created_at' ? 'selected' : '' }}>Дата створення</option>
                            <option value="updated_at" {{ $filter->sortBy === 'updated_at' ? 'selected' : '' }}>Дата оновлення</option>
                            <option value="status" {{ $filter->sortBy === 'status' ? 'selected' : '' }}>Статус</option>
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

                    {{-- кнопки --}}
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Застосувати
                        </button>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-secondary w-100">
                            Скинути
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Статті</h3>

            <div class="card-tools">
                <a href="{{@route('admin.articles.create')}}" class="btn btn-primary btn-sm">
                    Додати статтю
                </a>
            </div>
        </div>
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Назва</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th style="width: 150px">Дії</th>
                </tr>
                </thead>
                <tbody>
                @foreach($articles as $article)
                    <tr>
                        <td>{{$article->id}}</td>
                        <td>{{$article->translate(app()->currentLocale())->title}}</td>
                        <td>{{$article->created_at->format('F j, Y, g:i a')}}</td>
                        <td>
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" @if($article->status) checked @endif>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{@route('admin.articles.edit', $article->id)}}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" style="display:inline-block;">
                                @method('DELETE')
                                @csrf
                                <button data-id="{{$article->id}}" class="btn btn-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>
            <div>
                {{ $articles->links() }}
            </div>
        </div>
    </div>

@endsection
