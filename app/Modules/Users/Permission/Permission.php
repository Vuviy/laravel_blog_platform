<?php

namespace Modules\Users\Permission;

class Permission implements PermissionInterface
{
    public const READ   = 'user.read';
    public const CREATE = 'user.create';
    public const EDIT   = 'user.edit';
    public const DELETE = 'user.delete';

    public function __construct(private string $key)
    {
        if (!in_array($key, [self::READ, self::CREATE, self::EDIT, self::DELETE], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid permission: %s', $key));
        }
    }

    public function key(): string
    {
        return $this->key;
    }
}
