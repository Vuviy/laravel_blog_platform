<?php

namespace App\Attributes;

interface PermissionCheckAttribute
{
    public function validate(array $permissions): ?string;
}
