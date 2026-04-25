<?php

namespace Modules\Users\Entities;

use App\ValueObjects\Id;
use Modules\Users\ValueObjects\Email;
use Modules\Users\ValueObjects\Password;
use Modules\Users\ValueObjects\Username;

class User
{
    public function __construct(
        public Username $username,
        public Email $email,
        public Password $password,
        public ?Id $id = null,
        public array $roles = [],
        public array $permissions = [],
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    )
    {
        $this->optimizePermissions();
    }

    public function hasRole(string $roleName): bool
    {
        foreach ($this->roles as $role) {
            if ($roleName === $role->name->getValue()) {
                return true;
            }
        }
        return false;
    }

    public function hasPermission(string $permissionKey): bool
    {
        if (array_key_exists($permissionKey, $this->permissions)) {
            return true;
        }
        return false;
    }

    public function optimizePermissions(): void
    {
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                $this->permissions[$permission->value] = $permission;
            }
        }
    }

}
