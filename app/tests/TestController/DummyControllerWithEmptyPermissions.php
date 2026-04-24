<?php

declare(strict_types=1);

namespace Tests\TestController;

use App\Attributes\AllowedPermissions;

class DummyControllerWithEmptyPermissions
{
    #[AllowedPermissions([])]
    public function index()
    {
        return response('ok');
    }
}
