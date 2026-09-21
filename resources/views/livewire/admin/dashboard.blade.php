<div class="admin-dashboard">
    <x-admin-nav />

    <h1>Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-card__label">Pedidos pagos</span>
            <strong class="stat-card__value">{{ $stats['total_orders_paid'] }}</strong>
        </div>

        <div class="stat-card">
            <span class="stat-card__label">Receita total</span>
            <strong class="stat-card__value">{{ number_format((float) $stats['total_revenue_aoa'], 2, ',', '.') }} Kz</strong>
        </div>

        <div class="stat-card @if ($stats['pending_manual'] > 0) stat-card--alert @endif">
            <span class="stat-card__label">Comprovativos pendentes</span>
            <strong class="stat-card__value">{{ $stats['pending_manual'] }}</strong>
            @if ($stats['pending_manual'] > 0)
                <a href="{{ route('admin.payments.index') }}" wire:navigate>Rever agora →</a>
            @endif
        </div>

        <div class="stat-card">
            <span class="stat-card__label">Pedidos pendentes</span>
            <strong class="stat-card__value">{{ $stats['pending_orders'] }}</strong>
        </div>
    </div>

    <h2>Últimos pedidos</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Status</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recentOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>{{ number_format((float) $order->total_charged, 2, ',', '.') }} {{ $order->currency }}</td>
                    <td>{{ $order->status->label() }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Ainda sem pedidos.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>