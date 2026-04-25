@extends('admin.layout')

@section('content')

    @foreach ($errors->all() as $error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
  <div>
      <pre style="background:#f4f4f4; padding:16px; border-radius:6px; overflow:auto; font-size:13px;">{{ $content }}</pre>
  </div>

    <div>
        <form action="{{route('admin.generateSitemap')}}" method="POST">
            @csrf
            <button type="submit">regenerate</button>
        </form>
    </div>

@endsection
