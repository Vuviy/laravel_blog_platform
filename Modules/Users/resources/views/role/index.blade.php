@extends('admin.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Role</h3>

            <div class="card-tools">
                <a href="{{route('admin.roles.create')}}" class="btn btn-primary btn-sm">
                    Додати role
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
                @foreach($roles as $role)
                    <tr>
                        <td>{{$role->id}}</td>
                        <td>{{$role->name}}</td>
                        <td>{{$role->createdAt->format('F j, Y, g:i a')}}</td>

                        <td>
                            <a href="{{route('admin.roles.edit', $role->id)}}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{route('admin.roles.destroy', $role->id)}}" method="POST" style="display:inline-block;">
                                @method('DELETE')
                                @csrf
                                <button data-id="{{$role->id}}" class="btn btn-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>
            <div>
                {{ $roles->links() }}
            </div>
        </div>
    </div>
@endsection
