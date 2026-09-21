<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $paidOrders = Order::where('status', OrderStatus::Paid);

        $stats = [
            'total_orders_paid' => (clone $paidOrders)->count(),
            'total_revenue_aoa' => (clone $paidOrders)->sum('total_base_aoa'),
            'pending_manual' => Order::where('status', OrderStatus::AwaitingConfirmation)->count(),
            'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
        ];

        $recentOrders = Order::with('user')->latest()->limit(10)->get();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }
}