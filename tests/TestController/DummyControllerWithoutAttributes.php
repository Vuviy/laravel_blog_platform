<?php

declare(strict_types=1);

namespace Tests\TestController;

class DummyControllerWithoutAttributes
{
    public function index()
    {
        return response('ok');
    }
}
