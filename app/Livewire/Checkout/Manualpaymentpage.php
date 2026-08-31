<?php

namespace App\Livewire\Checkout;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Jobs\NotifyAdminNewManualPaymentJob;
use App\Models\Order;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManualPaymentPage extends Component
{
    use WithFileUploads;

    public Order $order;

    #[Validate('required|file|mimes:jpg,jpeg,png,pdf|max:5120')]
    public $proof = null;

    public bool $submitted = false;

    public function mount(Order $order): void
    {
        // Só o dono do pedido (se autenticado) ou um admin pode ver esta página.
        // Como o checkout é "convidado", o acesso normalmente vem do link
        // enviado por e-mail logo após a compra — a rota usa a chave do pedido,
        // não um id sequencial adivinhável (ver order_number/route binding).
        abort_unless($order->isManualPayment(), 404);

        $this->order = $order;
        $this->submitted = $order->status === OrderStatus::AwaitingConfirmation;
    }

    public function submitProof(): void
    {
        $this->validate();

        $path = $this->proof->store('payment-proofs/'.$this->order->order_number, 'local');

        $this->order->payments()->latest()->first()?->update([
            'proof_file_path' => $path,
            'status' => PaymentStatus::Pending,
        ]);

        $this->order->update(['status' => OrderStatus::AwaitingConfirmation]);

        NotifyAdminNewManualPaymentJob::dispatch($this->order);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.checkout.manual-payment-page', [
            'bank' => config('payment.manual'),
        ]);
    }
}