@extends('admin.layout')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Створити / Редагувати тег</h3>
        </div>
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(isset($tag))
            <form method="POST" action="{{route('admin.tags.update', ['tag' => $tag->id])}}">
        @method('PUT')
        @else
            <form method="POST" action="{{route('admin.tags.store')}}">
        @endif
                @csrf
                <div class="card-body">

                    <div class="form-group">
                        <label>Назва</label>
                        <input type="text" name="title" class="form-control" placeholder="Введи назву"
                               value="{{isset($tag) ? $tag->title : ''}}">
                    </div>

                </div>


                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Зберегти</button>
                </div>
            </form>
    </div>
@endsection
