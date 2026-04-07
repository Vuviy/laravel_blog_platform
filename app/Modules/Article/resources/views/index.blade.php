
@extends('layout')

@section('content')

    <div class="container">

        @foreach($articles as $article)

            <div class="mb-4 pb-4 border-bottom">

                <!-- Title -->
                <h2 class="h4 mb-2">
                    {{ $article->translate(app()->currentLocale())->title }}
                </h2>

                <!-- Meta -->
                <div class="mb-2 text-muted small">
                    20 березня 2026 • Автор: Admin
                </div>

                <!-- Tags (hardcoded) -->
                <div class="mb-2">
                    @if(0 < count($article->tags))

                    @foreach($article->tags as $tag)

                    <span class="badge bg-primary">{{$tag->title}}</span>

                    @endforeach
                    @endif
                </div>

                <!-- Text preview -->
                <p class="mb-3">
                    {{ \Illuminate\Support\Str::limit($article->translate(app()->currentLocale())->text, 250) }}
                </p>

                <!-- Button -->
                <a href="{{route('articles.show', ['id' => $article->id, 'locale' => app()->currentLocale()])}}" class="btn btn-sm btn-outline-dark">
                    {{__('common.read_more')}} →
                </a>

            </div>
        @endforeach

        <div>
            {{ $articles->links() }}
        </div>
    </div>


@endsection
