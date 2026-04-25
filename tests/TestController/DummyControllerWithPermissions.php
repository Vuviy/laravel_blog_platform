<?php

declare(strict_types=1);

namespace Tests\TestController;

use App\Attributes\AllowedPermissions;

class DummyControllerWithPermissions
{
    #[AllowedPermissions(['edit', 'delete'])]
    public function index()
    {
        return response('ok');
    }
}
