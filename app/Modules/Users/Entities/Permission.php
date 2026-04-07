<?php

namespace Modules\Users\Entities;

use App\ValueObjects\Id;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\PermissionKey;
use Modules\Users\ValueObjects\Username;

class Permission
{
    public function __construct(
        public PermissionKey $key,
        public ?Id $id = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    )
    {
    }

}
