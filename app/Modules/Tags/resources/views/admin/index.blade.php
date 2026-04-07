@extends('admin.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Теги</h3>

            <div class="card-tools">
                <a href="{{@route('admin.tags.create')}}" class="btn btn-primary btn-sm">
                    Додати тег
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
                    <th style="width: 150px">Дії</th>
                </tr>
                </thead>
                <tbody>
                @foreach($tags as $tag)
                    <tr>
                        <td>{{$tag->id}}</td>
                        <td>{{$tag->title}}</td>
                        <td>{{$tag->created_at->format('F j, Y, g:i a')}}</td>

                        <td>
                            <a href="{{@route('admin.tags.edit', $tag->id)}}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" style="display:inline-block;">
                                @method('DELETE')
                                @csrf
                                <button data-id="{{$tag->id}}" class="btn btn-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>
            <div>
                {{ $tags->links() }}
            </div>
        </div>
    </div>

    <script>

        let btnDel = $('.btn-delete');


    </script>
@endsection
