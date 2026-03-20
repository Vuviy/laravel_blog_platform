@extends('layout')

@section('content')

    <h1>home</h1>

    <div>
        <a href="{{@route('articles')}}">articles</a>
    </div>

    <div>
        <a href="{{@route('admin.dashboard')   }}">admin</a>
    </div>
@endsection
