<?php
declare(strict_types=1);

namespace Modules\Article\Tests\Unit;

use Illuminate\Http\Request;
use Modules\Article\Filter\ArticleFilter;
use Tests\TestCase;

class ArticleFilterTest extends TestCase
{
    public function testCreatesWithDefaults(): void
    {
        $filter = new ArticleFilter();

        $this->assertNull($filter->search);
        $this->assertNull($filter->status);
        $this->assertNull($filter->dateFrom);
        $this->assertNull($filter->dateTo);
        $this->assertEquals('created_at', $filter->sortBy);
        $this->assertEquals('desc', $filter->sortDir);
    }

    public function testFromRequestParsesSearch(): void
    {
        $request = Request::create('/', 'GET', ['search' => 'тест']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals('тест', $filter->search);
    }

    public function testFromRequestParsesStatus(): void
    {
        $request = Request::create('/', 'GET', ['status' => '1']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertSame(1, $filter->status);
    }

    public function testFromRequestReturnsNullStatusWhenNotProvided(): void
    {
        $request = Request::create('/');
        $filter = ArticleFilter::fromRequest($request);

        $this->assertNull($filter->status);
    }

    public function testFromRequestParsesDateFrom(): void
    {
        $request = Request::create('/', 'GET', ['date_from' => '2024-01-01']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals('2024-01-01', $filter->dateFrom);
    }

    public function testFromRequestParsesDateTo(): void
    {
        $request = Request::create('/', 'GET', ['date_to' => '2024-12-31']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals('2024-12-31', $filter->dateTo);
    }

    public function testFromRequestParsesValidSortBy(): void
    {
        $request = Request::create('/', 'GET', ['sort_by' => 'updated_at']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals('updated_at', $filter->sortBy);
    }

    public function testFromRequestIgnoresInvalidSortBy(): void
    {
        $request = Request::create('/', 'GET', ['sort_by' => 'password']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals('created_at', $filter->sortBy);
    }

    public function testFromRequestParsesValidSortDir(): void
    {
        $request = Request::create('/', 'GET', ['sort_dir' => 'asc']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals('asc', $filter->sortDir);
    }

    public function testFromRequestIgnoresInvalidSortDir(): void
    {
        $request = Request::create('/', 'GET', ['sort_dir' => 'random']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals('desc', $filter->sortDir);
    }

    public function testFromRequestParsesPerPage(): void
    {
        $request = Request::create('/', 'GET', ['per_page' => '25']);
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals(25, $filter->perPage);
    }

    public function testFromRequestUsesEnvPerPageWhenNotProvided(): void
    {
        $request = Request::create('/');
        $filter = ArticleFilter::fromRequest($request);

        $this->assertEquals(env('PAGINATE_ARTICLES'), $filter->perPage);
    }

    public function testToArrayExcludesNullValues(): void
    {
        $filter = new ArticleFilter(search: 'тест');
        $array = $filter->toArray();

        $this->assertArrayHasKey('search', $array);
        $this->assertArrayNotHasKey('status', $array);
        $this->assertArrayNotHasKey('date_from', $array);
        $this->assertArrayNotHasKey('date_to', $array);
    }

    public function testToArrayIncludesAllSetValues(): void
    {
        $filter = new ArticleFilter(
            search:   'тест',
            status:   1,
            dateFrom: '2024-01-01',
            dateTo:   '2024-12-31',
            sortBy:   'updated_at',
            sortDir:  'asc',
            perPage:  25,
        );

        $array = $filter->toArray();

        $this->assertEquals('тест', $array['search']);
        $this->assertEquals(1, $array['status']);
        $this->assertEquals('2024-01-01', $array['date_from']);
        $this->assertEquals('2024-12-31', $array['date_to']);
        $this->assertEquals('updated_at', $array['sort_by']);
        $this->assertEquals('asc', $array['sort_dir']);
        $this->assertEquals(25, $array['per_page']);
    }
}
