<?php

namespace Modules\Tags\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TaggableRepositoryInterface
{
    public function getEntityType(): string;

    public function getByIds(array $ids): array;
}
