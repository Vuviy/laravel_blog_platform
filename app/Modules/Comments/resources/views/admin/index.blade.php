@extends('admin.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Статті</h3>

            <div class="card-tools">
                <a href="{{@route('admin.comments.create')}}" class="btn btn-primary btn-sm">
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
                    <th>Entity</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>text</th>
                    <th style="width: 150px">Дії</th>
                </tr>
                </thead>
                <tbody>
                @foreach($comments as $comment)
                    <tr>
                        <td>{{$comment->id}}</td>
                        <td>{{$comment->entityType}}</td>
                        <td>{{$comment->created_at->format('F j, Y, g:i a')}}</td>
                        <td>
                            {{$comment->status}}
                        </td>
                        <td>{{$comment->content}}</td>
                        <td>
                            <a href="{{route('admin.comments.edit', $comment->id)}}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{route('admin.comments.destroy', $comment->id)}}" method="POST" style="display:inline-block;">
                                @method('DELETE')
                                @csrf
                                <button data-id="{{$comment->id}}" class="btn btn-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>
            <div>
{{--                {{ $comments->links() }}--}}
            </div>
        </div>
    </div>

@endsection
