<?php

namespace Modules\Comments\Enums;

enum EntityType: string
{
    case ARTICLE  = 'Modules\Article\Entities\Article';
    case NEW = 'Modules\News\Entities\News';

}
