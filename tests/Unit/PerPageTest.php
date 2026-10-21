<?php

namespace Tests\Unit;

use App\Support\PerPage;
use Illuminate\Http\Request;
use Tests\TestCase;

class PerPageTest extends TestCase
{
    private function request(array $query): Request
    {
        return Request::create('/api/product', 'GET', $query);
    }

    public function test_a_request_without_a_number_gets_the_default(): void
    {
        $this->assertSame(PerPage::DEFAULT, PerPage::from($this->request([])));
    }

    public function test_a_request_with_a_number_gets_that_number(): void
    {
        $this->assertSame(25, PerPage::from($this->request(['per_page' => 25])));
    }

    public function test_a_number_below_one_gets_the_default(): void
    {
        $this->assertSame(PerPage::DEFAULT, PerPage::from($this->request(['per_page' => 0])));
        $this->assertSame(PerPage::DEFAULT, PerPage::from($this->request(['per_page' => -5])));
    }

    public function test_a_number_that_is_not_a_number_gets_the_default(): void
    {
        $this->assertSame(PerPage::DEFAULT, PerPage::from($this->request(['per_page' => 'many'])));
    }

    public function test_a_number_above_the_bound_gets_the_bound(): void
    {
        $this->assertSame(PerPage::MAX, PerPage::from($this->request(['per_page' => 100000])));
    }
}
