<?php

namespace Modules\Article\Http\Controllers;

use App\ValueObjects\Id;
use Illuminate\Http\Request;
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

    // generic interface repo але це не обовязеово
    public function index(Request $request)
    {
        $articles = $this->service->getAll();
        $title = __('common.articles');
        return view('article::index', compact('articles', 'title'));


    }

    public function show(string $id)
    {

        $article = $this->service->getArticleById( new Id($id));

        $title = __('common.article');

        return view('article::show', compact('article', 'title'));

    }

}
