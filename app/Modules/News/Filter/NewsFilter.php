<?php

declare(strict_types=1);

namespace Modules\News\Filter;

class NewsFilter
{
    protected static function perPageDefault(): int
    {
        return (int) env('PAGINATE_NEWS', 10);
    }
}
