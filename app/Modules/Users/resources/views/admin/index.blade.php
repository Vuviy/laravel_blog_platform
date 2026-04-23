@extends('admin.layout')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User</h3>

            <div class="card-tools">
                <a href="{{route('admin.users.create')}}" class="btn btn-primary btn-sm">
                    Додати user
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
                    <th>Username</th>
                    <th>Email</th>
                    <th>Дата</th>
                    <th style="width: 150px">Дії</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{$user->id}}</td>
                        <td>{{$user->username}}</td>
                        <td>{{$user->email}}</td>
                        <td>{{$user->createdAt->format('F j, Y, g:i a')}}</td>

                        <td>
                            <a href="{{route('admin.users.edit', $user->id)}}" class="btn btn-warning btn-sm">Edit</a>

                            <form action="{{route('admin.users.destroy', $user->id)}}" method="POST" style="display:inline-block;">
                                @method('DELETE')
                                @csrf
                                <button data-id="{{$user->id}}" class="btn btn-danger btn-sm btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>

                @endforeach

                </tbody>
            </table>
            <div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
