<?php

namespace Modules\Article\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Article\Services\ArticleService;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $service
    ) {}


    // модулі треба
    // три модульні штуки домейн модулі (ше один компосер пакети і ддд)
    // каст атребут
    // сіквенси в постгре, ждсон, вставляти неправильні дані, транзакції, партіал індекс
    //  в канві canva
    // rest graphql  rpc



    // тепер в мене помилка тут: Modules/Article/Http/Controllers/ArticleController.php
    //return view('index', compact('articles', 'title'));
    //View [index] not found.
    //Як зробити щоб шаблони тобто layouts був тут app/resources/views/layout.blade.php
    public function index()
    {

//       $id = new UuidV7();
//
//       echo $id;
//       dd(67);
//       $ar = new ArticleId($id);
//       dd($ar);

//        $articleId = new ArticleId('019cfcdc-6090-704b-bf39-476011590f29');
//        $articleId = new ArticleId('019d05d0-4623-7e4f-b60c-c1d466bb3f6b');

//         $this->service->update($articleId, ['title' => 'новий тітле']);
//         $this->service->create(['title' => 'craka kakae', 'text' => 'textocia']);




//        dd(897);

        $articles = $this->service->getAll();
        $title = 'Articles';

        return view('article::index', compact('articles', 'title'));

    }

}
