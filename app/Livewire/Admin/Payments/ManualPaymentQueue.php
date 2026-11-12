<?php

namespace App\Livewire\Admin\Payments;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ManualPaymentQueue extends Component
{
    use WithPagination;

    public function render()
    {
        $orders = Order::query()
            ->where('status', OrderStatus::AwaitingConfirmation)
            ->with('user', 'items.product')
            ->latest('updated_at')
            ->paginate(15);

        return view('livewire.admin.payments.manual-payment-queue', [
            'orders' => $orders,
        ]);
    }
}