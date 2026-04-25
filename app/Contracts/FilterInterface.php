<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface FilterInterface
{
    public static function fromRequest(Request $request): static;

    public function toArray(): array;

}
