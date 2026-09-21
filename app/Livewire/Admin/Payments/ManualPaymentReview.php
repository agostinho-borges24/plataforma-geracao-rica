<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Livewire\Component;

class ManualPaymentReview extends Component
{
    public Order $order;

    public string $rejectReason = '';
    public bool $showRejectForm = false;

    public function mount(Order $order): void
    {
        $this->order = $order->load('items.product', 'payments', 'user', 'country');
    }

    public function getProofUrlProperty(): ?string
    {
        $payment = $this->order->payments->last();

        if (! $payment?->proof_file_path) {
            return null;
        }

        return route('admin.payments.proof-download', $this->order);
    }

    public function approve(OrderFulfillmentService $fulfillment): void
    {
        $fulfillment->approveManualPayment($this->order, auth()->user());

        session()->flash('success', "Pedido {$this->order->order_number} aprovado. Acesso liberado e e-mail enviado.");

        $this->redirect(route('admin.payments.index'));
    }

    public function reject(OrderFulfillmentService $fulfillment): void
    {
        $this->validate([
            'rejectReason' => ['nullable', 'string', 'max:500'],
        ], attributes: ['rejectReason' => 'motivo']);

        $fulfillment->rejectManualPayment($this->order, auth()->user(), $this->rejectReason ?: null);

        session()->flash('success', "Pedido {$this->order->order_number} rejeitado. Cliente notificado por e-mail.");

        $this->redirect(route('admin.payments.index'));
    }

    public function render()
    {
        return view('livewire.admin.payments.manual-payment-review');
    }
}