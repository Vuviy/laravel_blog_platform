<?php

declare(strict_types=1);

namespace Tests\TestController;

use App\ValueObjects\Id;
use Modules\Users\Entities\User;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\Username;

class FakeUserWithPermissions extends User
{
    public function __construct(private bool $permission)
    {
        parent::__construct(
            id: new Id('1'),
            username: new Username('fake'),
            password: Password::fromPlain('fake'),
            email: new Email('fake@fake.com')
        );
    }

    public function hasPermission(string $permissionKey): bool
    {
        return $this->permission;
    }
}
