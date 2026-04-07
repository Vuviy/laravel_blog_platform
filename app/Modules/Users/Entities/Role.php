<?php

namespace Modules\Users\Entities;

use App\ValueObjects\Id;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\RoleName;
use Modules\Users\ValueObjects\Username;

class Role
{
    public function __construct(
        public RoleName $name,
        public ?Id $id = null,
        public array $permissions = [],
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    )
    {
    }

}
