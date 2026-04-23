<?php

declare(strict_types=1);

namespace Modules\Article\Filter;

use Illuminate\Http\Request;

class ArticleFilter
{
    private array $allowedSorts = ['created_at', 'updated_at', 'status'];

    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int    $status = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
        public readonly ?string $sortBy = 'created_at',
        public readonly ?string $sortDir = 'desc',
        public readonly string|int $perPage = '10',
    ) {}

    public static function fromRequest(Request $request): static
    {
        $allowedSorts = ['created_at', 'updated_at', 'status'];

        return new static(
            search:   $request->input('search'),
            status:   $request->input('status') !== null ? (int) $request->input('status') : null,
            dateFrom: $request->input('date_from'),
            dateTo:   $request->input('date_to'),
            sortBy:   in_array($request->input('sort_by'), $allowedSorts)
                ? $request->input('sort_by')
                : 'created_at',
            sortDir:  in_array($request->input('sort_dir'), ['asc', 'desc'])
                ? $request->input('sort_dir')
                : 'desc',
            perPage: $request->input('per_page') ? (int) $request->input('per_page') : env('PAGINATE_ARTICLES'),
        );
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
