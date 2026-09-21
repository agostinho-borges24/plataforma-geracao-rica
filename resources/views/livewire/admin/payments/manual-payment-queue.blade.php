<div class="admin-payments-queue">
    <x-admin-nav />

    <h1>Comprovativos pendentes</h1>

    @if (session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    <table class="admin-table">
        <thead>
            <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Produtos</th>
                <th>Total</th>
                <th>Enviado em</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr wire:key="order-{{ $order->id }}">
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->user->name }}<br><small>{{ $order->whatsapp }}</small></td>
                    <td>{{ $order->items->pluck('product.title')->join(', ') }}</td>
                    <td>{{ number_format((float) $order->total_charged, 2, ',', '.') }} {{ $order->currency }}</td>
                    <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.payments.review', $order) }}" wire:navigate>Rever</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Nenhum comprovativo pendente. 🎉</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $orders->links() }}
</div>