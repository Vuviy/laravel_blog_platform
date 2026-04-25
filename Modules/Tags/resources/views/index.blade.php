@extends('layout')

@section('content')

    <style>
        .list-group-item {
            border: none;
            border-bottom: 1px solid #eee;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .list-group-item:hover {
            background-color: #fafafa;
        }

        h5 a:hover {
            color: #0d6efd;
        }
    </style>

    <div class="container py-4">

        <div class="list-group list-group-flush">

            @foreach($entities as $entity)
                <div class="list-group-item py-4">

                    <div class="d-flex justify-content-between align-items-start">

                        {{-- ліва частина --}}
                        <div class="me-3">

                            {{-- тип --}}
                            <div class="mb-2">
                            <span class="badge bg-primary">
                                {{ $entity->type }}
                            </span>
                            </div>

                            {{-- заголовок --}}
                            <h5 class="mb-1">
                                <a href="{{ $entity->url }}" class="text-decoration-none text-dark">
                                    {{ $entity->title }}
                                </a>
                            </h5>

                            {{-- текст --}}
                            <p class="mb-2 text-muted small">
                                {{ \Illuminate\Support\Str::limit($entity->text, 180) }}
                            </p>

                        </div>

                        {{-- дата --}}
                        <div class="text-end flex-shrink-0">
                            <time class="text-secondary small">
                                {{ $entity->createdAt->format('d.m.Y') }}
                            </time>
                        </div>

                    </div>

                </div>
            @endforeach

        </div>

        {{-- пагінація --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $entities->links() }}
        </div>

    </div>

@endsection
