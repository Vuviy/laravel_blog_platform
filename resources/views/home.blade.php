@extends('layout')

@section('content')

    <h1>home</h1>

    <div>
        <a href="{{@route('articles', ['locale' => app()->currentLocale()])}}">{{__('common.articles')}}</a>
    </div>

    <div>
        <a href="{{@route('news', ['locale' => app()->currentLocale()])}}">{{__('common.news')}}</a>
    </div>

    <div>
        <a href="{{@route('admin.dashboard')}}">admin</a>
    </div>
@endsection
