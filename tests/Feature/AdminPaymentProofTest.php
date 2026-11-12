<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    private function pendingProof(): PaymentProof
    {
        $customer = User::factory()->create();
        $order = Order::create(['user_id' => $customer->id, 'total' => 5000, 'status' => 'review']);

        return PaymentProof::create([
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'doc' => 'comprovativos/teste.pdf',
        ]);
    }

    public function test_admin_must_write_a_reason_to_reject(): void
    {
        $proof = $this->pendingProof();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::admin.order.index')
            ->call('openReject', $proof->id)
            ->set('msg', '   ')
            ->call('reject')
            ->assertHasErrors(['msg' => 'required']);

        $this->assertSame('pending', $proof->fresh()->status);
        $this->assertSame('review', $proof->order->fresh()->status);
    }

    public function test_rejecting_saves_the_reason_and_rejects_the_order(): void
    {
        $proof = $this->pendingProof();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::admin.order.index')
            ->call('openReject', $proof->id)
            ->set('msg', 'O valor transferido não corresponde ao total.')
            ->call('reject')
            ->assertHasNoErrors()
            ->assertSet('rejectingId', null);

        $proof->refresh();

        $this->assertSame('rejected', $proof->status);
        $this->assertSame('O valor transferido não corresponde ao total.', $proof->msg);
        $this->assertSame('rejected', $proof->order->status);
    }

    public function test_accepting_marks_the_order_as_paid(): void
    {
        $proof = $this->pendingProof();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::admin.order.index')
            ->call('accept', $proof->id);

        $this->assertSame('accepted', $proof->fresh()->status);
        $this->assertSame('paid', $proof->order->fresh()->status);
    }
}
