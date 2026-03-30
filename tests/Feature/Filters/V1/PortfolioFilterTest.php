<?php

namespace Tests\Feature\Filters\V1;

use App\DTO\PortfolioDTO;
use App\Http\Filters\V1\PortfolioFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PortfolioFilterTest extends TestCase
{
    private function getMockCollection(): Collection
    {
        return collect([
            new PortfolioDTO('AAPL', 'stock', '10', '100', '150', '1000', '1500', '50.00', '500', '1.50', '15', 'foo'),
            new PortfolioDTO('MSFT', 'stock', '5', '200', '180', '1000', '900', '-10.00', '-100', '-1.00', '-10', 'bar'),
            new PortfolioDTO('BTC', 'crypto', '2', '20000', '30000', '40000', '60000', '50.00', '20000', '5.00', '1000', 'baz'),
        ]);
    }

    public function test_can_filter_by_ticker(): void
    {
        $request = rute_request(['ticker' => 'AAPL']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('AAPL', $result->first()->ticker);
    }

    public function test_can_filter_by_type(): void
    {
        $request = rute_request(['type' => 'crypto']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('BTC', $result->first()->ticker);
    }

    public function test_can_filter_by_quantity_gt(): void
    {
        $request = rute_request(['quantity_gt' => '5']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('AAPL', $result->first()->ticker);
    }

    public function test_can_filter_by_quantity_lt(): void
    {
        $request = rute_request(['quantity_lt' => '5']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('BTC', $result->first()->ticker);
    }

    public function test_can_filter_by_total_cost_gt(): void
    {
        $request = rute_request(['total_cost_gt' => '20000']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('BTC', $result->first()->ticker);
    }

    public function test_can_filter_by_total_cost_lt(): void
    {
        $request = rute_request(['total_cost_lt' => '1500']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(2, $result); // AAPL and MSFT
    }

    public function test_can_filter_by_total_value_gt(): void
    {
        $request = rute_request(['total_value_gt' => '1000']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(2, $result); // AAPL and BTC
    }

    public function test_can_filter_by_total_value_lt(): void
    {
        $request = rute_request(['total_value_lt' => '1000']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result); // MSFT
    }

    public function test_can_filter_with_profit_value(): void
    {
        $request = rute_request(['with_profit_value' => 'true']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(2, $result);
        $this->assertTrue(collect($result)->every(fn($i) => (float)$i->total_profit_loss_value > 0));
    }

    public function test_can_filter_with_loss_value(): void
    {
        $request = rute_request(['with_loss_value' => 'true']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('MSFT', $result->first()->ticker);
    }
    
    public function test_can_filter_with_profit_percent(): void
    {
        $request = rute_request(['with_profit_percent' => 'true']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(2, $result);
    }

    public function test_can_filter_with_loss_percent(): void
    {
        $request = rute_request(['with_loss_percent' => 'true']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('MSFT', $result->first()->ticker);
    }

    public function test_can_filter_with_daily_profit_percent(): void
    {
        $request = rute_request(['with_daily_profit_percent' => 'true']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(2, $result);
    }

    public function test_can_filter_with_daily_loss_percent(): void
    {
        $request = rute_request(['with_daily_loss_percent' => 'true']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertCount(1, $result);
        $this->assertEquals('MSFT', $result->first()->ticker);
    }

    public function test_can_order_by_asc(): void
    {
        $request = rute_request(['order_by' => 'total_value', 'order' => 'asc']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertEquals('MSFT', $result->first()->ticker);
        $this->assertEquals('BTC', $result->last()->ticker);
    }

    public function test_can_order_by_desc(): void
    {
        $request = rute_request(['order_by' => 'total_value', 'order' => 'desc']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertEquals('BTC', $result->first()->ticker);
        $this->assertEquals('MSFT', $result->last()->ticker);
    }

    public function test_can_order_by_desc_shorthand(): void
    {
        $request = rute_request(['order_by' => '-total_value']);
        $filter = new PortfolioFilter($request);
        $result = $filter->apply($this->getMockCollection());

        $this->assertEquals('BTC', $result->first()->ticker);
        $this->assertEquals('MSFT', $result->last()->ticker);
    }
}

// Helper to create requests
function rute_request(array $query): Request
{
    $request = new Request();
    $request->merge($query);
    return $request;
}
