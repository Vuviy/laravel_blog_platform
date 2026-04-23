@extends('admin.layout')
@use('Modules\Comments\Enums\CommentStatus')
@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Створити / Редагувати comment</h3>
        </div>
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(isset($comment))
            <form method="POST" action="{{route('admin.comments.update', ['comment' => $comment->id])}}">
                @method('PUT')
                @else
                    <form method="POST" action="{{route('admin.comments.store')}}">
                        @endif
                        @csrf
                        <div class="card-body">

                            <div class="form-group">
                                <label>content</label>
                                <input type="text" name="content" class="form-control"
                                       value="{{isset($comment) ? $comment->content->getValue() : ''}}">
                            </div>

                            <div class="form-group">
                                <label>Status</label>

                                <select name="status">
                                    @foreach(CommentStatus::cases() as $status)
                                        <option value="{{ $status }}"
                                        @if(isset($comment))
                                            @selected($comment->status->value === $status->value)
                                        @endif
                                            >
                                            {{ ucfirst($status->value) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>


                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Зберегти</button>
                        </div>
                    </form>
    </div>
@endsection
