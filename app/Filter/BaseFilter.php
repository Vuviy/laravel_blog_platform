<?php

declare(strict_types=1);

namespace App\Filter;

use App\Contracts\FilterInterface;
use Illuminate\Http\Request;

abstract class BaseFilter implements FilterInterface
{
    protected const DEFAULT_SORT = 'created_at';
    protected const DEFAULT_SORT_DIR = 'desc';
    protected const ALLOWED_SORTS = ['created_at', 'updated_at', 'status'];

    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int    $status = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $sortBy = self::DEFAULT_SORT,
        public readonly ?string $sortDir = self::DEFAULT_SORT_DIR,
        public readonly string|int $perPage = 10,
    ) {}

    abstract protected static function perPageDefault(): int;

    public static function fromRequest(Request $request): static
    {
        return new static(
            search:   $request->input('search'),
            status:   $request->input('status') !== null ? (int) $request->input('status') : null,
            dateFrom: $request->input('date_from'),
            dateTo:   $request->input('date_to'),
            sortBy:   static::resolveSortBy($request->input('sort_by')),
            sortDir:  static::resolveSortDir($request->input('sort_dir')),
            perPage:  static::resolvePerPage($request),
        );
    }

    protected static function resolveSortBy(?string $value): string
    {
        return in_array($value, self::ALLOWED_SORTS)
            ? $value
            : self::DEFAULT_SORT;
    }

    protected static function resolveSortDir(?string $value): string
    {
        return in_array($value, ['asc', 'desc'])
            ? $value
            : self::DEFAULT_SORT_DIR;
    }

    protected static function resolvePerPage(Request $request): int
    {
        return $request->input('per_page')
            ? (int) $request->input('per_page')
            : static::perPageDefault();
    }

    public function toArray(): array
    {
        return array_filter([
            'search'   => $this->search,
            'status'   => $this->status,
            'date_from'=> $this->dateFrom,
            'date_to'  => $this->dateTo,
            'sort_by'  => $this->sortBy,
            'sort_dir' => $this->sortDir,
            'per_page' => $this->perPage,
        ], fn($value) => $value !== null);
    }
}
