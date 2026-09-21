<?php

namespace Tests\Feature;

use App\Filament\Resources\Bills\Pages\ListBills;
use App\Filament\Resources\CashEntries\Pages\ListCashEntries;
use App\Filament\Resources\PaymentTypes\Pages\ListPaymentTypes;
use App\Models\Bill;
use App\Models\CashEntry;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminKeuanganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_finance_pages_render(): void
    {
        PaymentType::factory()->count(2)->create();
        Bill::factory()->count(2)->create();
        CashEntry::factory()->count(2)->create();

        Livewire::test(ListPaymentTypes::class)->assertSuccessful();
        Livewire::test(ListBills::class)->assertSuccessful();
        Livewire::test(ListCashEntries::class)->assertSuccessful();
    }

    /** Lunas diturunkan dari nominal, bukan kolom status yang bisa bertentangan. */
    public function test_paid_status_comes_from_the_amounts(): void
    {
        $belum = Bill::factory()->create(['amount' => 300_000, 'amount_paid' => 100_000]);
        // state() sebelum lunas(): closure-nya membaca amount yang sudah
        // ditetapkan, sedangkan argumen create() baru menimpa sesudahnya.
        $lunas = Bill::factory()->state(['amount' => 300_000])->lunas()->create();

        $this->assertFalse($belum->isPaid());
        $this->assertSame(200_000, $belum->outstanding());
        $this->assertTrue($lunas->isPaid());
        $this->assertSame(0, $lunas->outstanding());
    }

    public function test_receiving_a_payment_settles_the_bill_and_issues_a_receipt(): void
    {
        $bill = Bill::factory()->create(['amount' => 250_000, 'amount_paid' => 0]);

        Livewire::test(ListBills::class)
            ->callTableAction('lunasi', $bill)
            ->assertHasNoTableActionErrors();

        $bill->refresh();
        $this->assertTrue($bill->isPaid());
        $this->assertNotNull($bill->paid_at);
        $this->assertNotNull($bill->receipt_no);
    }

    /** Satu siswa tidak boleh punya dua tagihan untuk jenis dan periode sama. */
    public function test_a_bill_cannot_be_issued_twice_for_the_same_period(): void
    {
        $bill = Bill::factory()->create(['period' => '2026-09']);

        $this->expectException(QueryException::class);
        Bill::factory()->create([
            'student_id' => $bill->student_id,
            'payment_type_id' => $bill->payment_type_id,
            'period' => '2026-09',
        ]);
    }

    public function test_cash_entries_sign_outgoing_amounts_negative(): void
    {
        $masuk = CashEntry::factory()->create(['direction' => 'masuk', 'amount' => 500_000]);
        $keluar = CashEntry::factory()->create(['direction' => 'keluar', 'amount' => 200_000]);

        $this->assertSame(500_000, $masuk->signedAmount());
        $this->assertSame(-200_000, $keluar->signedAmount());
    }
}
