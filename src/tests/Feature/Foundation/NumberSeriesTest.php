<?php

namespace Tests\Feature\Foundation;

use App\Models\Core\Company;
use App\Models\Core\NumberSeries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NumberSeriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_sequential_numbers_with_default_format(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $first = NumberSeries::next($company->id, 'purchase_order');
        $second = NumberSeries::next($company->id, 'purchase_order');

        $year = now()->year;
        $this->assertSame("PO-{$year}-000001", $first);
        $this->assertSame("PO-{$year}-000002", $second);
    }

    public function test_it_reuses_the_same_series_row_across_calls(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        NumberSeries::next($company->id, 'sales_order');
        NumberSeries::next($company->id, 'sales_order');

        $this->assertSame(1, NumberSeries::where('company_id', $company->id)
            ->where('document_type', 'sales_order')->count());

        $series = NumberSeries::where('company_id', $company->id)->where('document_type', 'sales_order')->first();
        $this->assertSame(3, $series->next_number);
    }

    public function test_it_resets_the_counter_on_a_new_year_when_reset_yearly_is_enabled(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $series = NumberSeries::create([
            'company_id' => $company->id, 'document_type' => 'invoice_test', 'prefix' => 'INV',
            'format' => '{PREFIX}-{YEAR}-{NUMBER}', 'next_number' => 42, 'padding' => 4,
            'reset_yearly' => true, 'last_reset_year' => now()->year - 1,
        ]);

        $number = $series->generateNumber();

        $this->assertSame('INV-'.now()->year.'-0001', $number);
        $this->assertSame(now()->year, $series->fresh()->last_reset_year);
    }

    public function test_it_does_not_reset_when_reset_yearly_is_disabled(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $series = NumberSeries::create([
            'company_id' => $company->id, 'document_type' => 'invoice_test', 'prefix' => 'INV',
            'format' => '{PREFIX}-{NUMBER}', 'next_number' => 10, 'padding' => 3,
            'reset_yearly' => false, 'last_reset_year' => now()->year - 1,
        ]);

        $number = $series->generateNumber();

        $this->assertSame('INV-010', $number);
    }
}
