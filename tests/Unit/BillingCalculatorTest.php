<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BillingCalculatorTest extends TestCase
{

    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    private function calc(): BillingCalculator { return new BillingCalculator(); }

    public function test_full_cycle_no_overage(): void
    {
        $r = $this->calc()->segment(1_000_000, 100_000, 50, 30, 30, 90_000);
        $this->assertSame(1_000_000, $r['base_fee']);
        $this->assertSame(0, $r['overage_charge']);
    }

    public function test_usage_exactly_at_allowance_has_no_overage(): void
    {
        $r = $this->calc()->segment(1_000_000, 100_000, 50, 30, 30, 100_000);
        $this->assertSame(0, $r['overage_units']);
    }

    public function test_zero_usage_still_charges_base_fee(): void
    {
        $r = $this->calc()->segment(1_000_000, 100_000, 50, 30, 30, 0);
        $this->assertSame(1_000_000, $r['subtotal']);
    }

    public function test_mid_cycle_start_prorates_base_and_allowance(): void
    {
        // 20 of 30 days, 80,000 units used
        $r = $this->calc()->segment(1_000_000, 100_000, 50, 20, 30, 80_000);
        $this->assertSame(666_667, $r['base_fee']);        // ₹6,666.67
        $this->assertSame(66_666, $r['allowance']);
        $this->assertSame(13_334, $r['overage_units']);
        $this->assertSame(666_700, $r['overage_charge']);  // ₹6,667.00
    }

    public function test_start_on_last_day_of_cycle(): void
    {
        $r = $this->calc()->segment(3_000_000, 30_000, 10, 1, 30, 0);
        $this->assertSame(100_000, $r['base_fee']);
    }

    public function test_leap_year_february_uses_29_days(): void
    {
        $r = $this->calc()->segment(2_900_000, 29_000, 10, 29, 29, 0);
        $this->assertSame(2_900_000, $r['base_fee']);
    }

}
