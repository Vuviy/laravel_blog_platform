<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AllowedPermissions
{
    public function __construct(public readonly array $permissions)
    {
    }

}
