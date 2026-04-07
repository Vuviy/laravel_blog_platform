@extends('admin.layout')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Створити / Редагувати permissions</h3>
        </div>
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(isset($permission))
            <form method="POST" action="{{route('admin.permissions.update', ['permission' => $permission->id])}}">
        @method('PUT')
        @else
            <form method="POST" action="{{route('admin.permissions.store')}}">
        @endif
                @csrf
                <div class="card-body">

                    <div class="form-group">
                        <label>Назва</label>
                        <input type="text" name="key" class="form-control" placeholder="Введи назву"
                               value="{{isset($permission) ? $permission->key : ''}}">
                    </div>

                </div>


                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </form>
    </div>
@endsection
