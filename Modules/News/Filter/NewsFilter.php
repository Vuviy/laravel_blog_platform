<?php

declare(strict_types=1);

namespace Modules\News\Filter;

use App\Filter\BaseFilter;

class NewsFilter  extends BaseFilter
{
    protected static function perPageDefault(): int
    {
        return (int) env('PAGINATE_NEWS', 10);
    }
}
