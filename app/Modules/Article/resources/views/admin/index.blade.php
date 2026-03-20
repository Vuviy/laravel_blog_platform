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
                {{-- Приклад. Потім заміниш на @foreach --}}

                @foreach($articles as $article)
                    <tr>
                        <td>{{$article->id}}</td>
                        <td>{{$article->title}}</td>
                        <td>{{$article->created_at->format('F j, Y, g:i a')}}</td>
                        <td>
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active">
{{--                                    <label class="form-check-label" for="is_active">Активна</label>--}}
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{@route('admin.articles.edit', $article->id)}}" class="btn btn-warning btn-sm">Edit</a>

{{--                            <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" style="display:inline-block;">--}}
{{--                                <input type="hidden" name="_method" value="DELETE">--}}
                                <button data-id="{{$article->id}}" class="btn btn-danger btn-sm btn-delete">Delete</button>
{{--                            </form>--}}
                        </td>
                    </tr>

                @endforeach
{{--                <tr>--}}
{{--                    <td>1</td>--}}
{{--                    <td>Test article</td>--}}
{{--                    <td>2026-03-19</td>--}}
{{--                    <td>--}}
{{--                        <a href="/admin/articles/1/edit" class="btn btn-warning btn-sm">Edit</a>--}}

{{--                        <form action="/admin/articles/1" method="POST" style="display:inline-block;">--}}
{{--                            <input type="hidden" name="_method" value="DELETE">--}}
{{--                            <button class="btn btn-danger btn-sm">Delete</button>--}}
{{--                        </form>--}}
{{--                    </td>--}}
{{--                </tr>--}}

                </tbody>
            </table>
        </div>
    </div>

    <script>

        let btnDel = $('.btn-delete');


    </script>
@endsection
