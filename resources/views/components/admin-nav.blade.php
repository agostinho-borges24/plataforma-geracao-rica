<nav class="admin-nav">
    <a href="{{ route('admin.dashboard') }}" wire:navigate>Dashboard</a>
    <a href="{{ route('admin.products.index') }}" wire:navigate>Produtos</a>
    <a href="{{ route('admin.payments.index') }}" wire:navigate>Pagamentos pendentes</a>
</nav>