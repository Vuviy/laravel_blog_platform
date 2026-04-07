
@extends('layout')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Article -->
                <article class="py-4">

                    <!-- Title -->
                    <h1 class="mb-3 fw-bold">
                        {{ $article->translate(app()->currentLocale())->title }}
                    </h1>

                    <!-- Meta -->
                    <div class="mb-4 text-muted small">
                        {{ $article->created_at->format('F j, Y, g:i a') }}
                        <span class="mx-2">•</span>
                        Автор: Admin
                    </div>

                    <!-- Tags (hardcoded) -->
                    <div class="mb-4">
                        @if(0 < count($article->tags))
                            @foreach($article->tags as $tag)
                                <span class="badge bg-primary">{{$tag->title}}</span>
                            @endforeach
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="fs-5 lh-lg">
                        {!! nl2br(e($article->translate(app()->currentLocale())->text)) !!}
                    </div>

                    <!-- Divider -->
                    <hr class="my-5">

                    <!-- Footer of article -->
                    <div class="d-flex justify-content-between align-items-center">

                        <a href="{{ route('articles', ['locale' => app()->currentLocale()]) }}"
                           class="btn btn-outline-secondary btn-sm">
                            ← Назад до списку
                        </a>

                        <div class="text-muted small">
                            Оновлено: {{ $article->updated_at->format('F j, Y') }}
                        </div>

                    </div>

                </article>

            </div>
        </div>
    </div>


    <div>

        @if($user)
            @include('comments::form', ['entityId' => $article->id, 'entityType' => get_class($article)])
        @else
            <div>
                <h3>Зареєструйтесь щоб залишати коментарі</h3>
            </div>
        @endif
        <h2>Comments:</h2>
    @include('comments::index', ['comments' => $article->comments])

@endsection
