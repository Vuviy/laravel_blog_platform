@extends('admin.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">permissions</h3>

            <div class="card-tools">
                <a href="{{route('admin.permissions.create')}}" class="btn btn-primary btn-sm">
                    Додати permission
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
                @foreach($permissions as $permission)
                    <tr>
                        <td>{{$permission->id}}</td>
                        <td>{{$permission->key}}</td>
                        <td>{{$permission->createdAt->format('F j, Y, g:i a')}}</td>

                        <td>
                            <a href="{{route('admin.permissions.edit', $permission->id)}}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{route('admin.permissions.destroy', $permission->id)}}" method="POST" style="display:inline-block;">
                                @method('DELETE')
                                @csrf
                                <button data-id="{{$permission->id}}" class="btn btn-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>
            <div>
                {{ $permissions->links() }}
            </div>
        </div>
    </div>
@endsection
