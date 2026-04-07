@extends('admin.layout')

@section('content')
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
{{--                                    <label class="form-check-label" for="is_active">Активна</label>--}}
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

    <script>

        let btnDel = $('.btn-delete');


    </script>
@endsection
