@extends('admin.layout')

@section('content')

    <form action="{{route('admin.saveRobot')}}" method="POST">
        @csrf
        <textarea name="content" rows="6" cols="100">
            {{$content}}
        </textarea>
        <button type="submit">save</button>
    </form>

@endsection
