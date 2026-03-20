@extends('admin.layout')

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Створити / Редагувати статтю</h3>
        </div>

        <form method="POST" action="/admin/articles">
            <div class="card-body">

                <div class="form-group">
                    <label>Назва</label>
                    <input type="text" name="title" class="form-control" placeholder="Введи назву" value="{{isset($article) ? $article->title : ''}}">
                </div>

                <div class="form-group">
                    <label>Текст</label>
                    <textarea name="text" class="form-control" rows="5">
                        {{isset($article) ? $article->text : ''}}
                    </textarea>
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Зберегти</button>
            </div>
        </form>
    </div>
@endsection
