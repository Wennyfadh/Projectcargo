<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CurrencyMonitoringTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_currency_page_requires_authentication(): void
    {
        $response = $this->get(route('currency.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_currency_monitoring(): void
    {
        $response = $this->actingAs($this->user)->get(route('currency.index'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring Kurs & Deteksi Volatilitas Valas', false);
        $response->assertSee('Cek Nilai Tukar & Konversi', false);
        $response->assertSee('1-to-1');
        $response->assertSee('1-to-Many');
        $response->assertSee('open.er-api.com');
    }

    public function test_currency_page_renders_multi_mode_checker_controls(): void
    {
        $response = $this->actingAs($this->user)->get(route('currency.index'));

        $response->assertStatus(200);
        $response->assertSee('tabMode1to1', false);
        $response->assertSee('tabMode1toMany', false);
        $response->assertSee('viewMode1to1', false);
        $response->assertSee('viewMode1toMany', false);
        $response->assertSee('swapCheckerCurrencies', false);
        $response->assertSee('multiCurrencyGrid', false);
        $response->assertSee('simulationActiveNotice', false);
        $response->assertSee('detectionAlertBanner', false);
    }

    public function test_currency_page_does_not_contain_hardcoded_static_fake_rates(): void
    {
        $response = $this->actingAs($this->user)->get(route('currency.index'));

        $response->assertStatus(200);
        // Ensure static numbers are not hardcoded in the view
        $response->assertDontSee('1 USD = Rp 15.845', false);
        $response->assertDontSee('1 IDR = 0,00006311 USD', false);
    }

    public function test_currency_convert_api_handles_one_to_one(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('currency.convert', [
            'amount' => 1,
            'from' => 'USD',
            'to' => 'IDR',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'from',
            'amount',
            'is_live',
            'provider',
            'single' => [
                'from',
                'to',
                'amount',
                'rate',
                'converted',
                'inverse_rate',
                'formatted_converted',
                'formatted_ratio',
                'formatted_inverse',
            ],
            'multi',
            'synced_at',
        ]);
        $this->assertEquals('USD', $response->json('single.from'));
        $this->assertEquals('IDR', $response->json('single.to'));
        $this->assertGreaterThan(1000, $response->json('single.converted'));
        $this->assertTrue($response->json('is_live'));
    }

    public function test_currency_convert_api_handles_one_to_many(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('currency.convert', [
            'amount' => 100,
            'from' => 'USD',
            'targets' => 'IDR,MYR,SGD,EUR,JPY',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'from',
            'amount',
            'multi' => [
                '*' => [
                    'code',
                    'name',
                    'symbol',
                    'flag',
                    'region',
                    'rate',
                    'converted',
                    'formatted_converted',
                    'formatted_ratio',
                ],
            ],
        ]);

        $multiCodes = collect($response->json('multi'))->pluck('code')->all();
        $this->assertContains('IDR', $multiCodes);
        $this->assertContains('MYR', $multiCodes);
        $this->assertContains('SGD', $multiCodes);
        $this->assertContains('EUR', $multiCodes);
        $this->assertContains('JPY', $multiCodes);
        $this->assertEquals(100, $response->json('amount'));
    }

    public function test_currency_convert_supports_arbitrary_base_currencies(): void
    {
        // Test EUR as base
        $eurResponse = $this->actingAs($this->user)->getJson(route('currency.convert', [
            'amount' => 50,
            'from' => 'EUR',
            'to' => 'IDR',
        ]));
        $eurResponse->assertStatus(200);
        $this->assertEquals('EUR', $eurResponse->json('single.from'));
        $this->assertGreaterThan(10000, $eurResponse->json('single.rate'));

        // Test SGD as base
        $sgdResponse = $this->actingAs($this->user)->getJson(route('currency.convert', [
            'amount' => 10,
            'from' => 'SGD',
            'to' => 'MYR',
        ]));
        $sgdResponse->assertStatus(200);
        $this->assertEquals('SGD', $sgdResponse->json('single.from'));
        $this->assertEquals('MYR', $sgdResponse->json('single.to'));
    }

    public function test_currency_rates_api_returns_json_structure_with_live_status(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('currency.rates', ['base' => 'USD']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'result',
            'is_live',
            'provider',
            'base_code',
            'rates',
            'time_last_update_utc',
            'last_fetched_at',
        ]);
        $this->assertEquals('USD', $response->json('base_code'));
        $this->assertTrue($response->json('is_live'));
        $this->assertArrayHasKey('IDR', $response->json('rates'));
    }

    public function test_currency_history_api_returns_historical_datapoints(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('currency.history', [
            'base' => 'USD',
            'target' => 'IDR',
            'days' => 7,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'base',
            'target',
            'labels',
            'values',
        ]);
        $this->assertCount(7, $response->json('labels'));
        $this->assertCount(7, $response->json('values'));
    }
}
