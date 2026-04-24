<?php

declare(strict_types=1);

namespace Modules\Article\Filter;

use App\Filter\BaseFilter;

class ArticleFilter extends BaseFilter
{
    protected static function perPageDefault(): int
    {
        return (int) env('PAGINATE_ARTICLES', 10);
    }
}
