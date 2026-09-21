<div class="admin-products">
    <x-admin-nav />

    <div class="admin-header">
        <h1>Produtos</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn--primary" wire:navigate>Novo produto</a>
    </div>

    @if (session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Pesquisar por título…">

    <table class="admin-table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Tipo</th>
                <th>Preço</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr wire:key="product-{{ $product->id }}">
                    <td>{{ $product->title }}</td>
                    <td>{{ $product->type->label() }}</td>
                    <td>{{ number_format((float) $product->price, 2, ',', '.') }} Kz</td>
                    <td>
                        <button type="button" wire:click="toggleActive({{ $product->id }})">
                            {{ $product->active ? 'Ativo' : 'Inativo' }}
                        </button>
                    </td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product) }}" wire:navigate>Editar</a>
                        <button type="button"
                                wire:click="delete({{ $product->id }})"
                                wire:confirm="Tens a certeza que queres remover este produto?">
                            Remover
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Nenhum produto encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $products->links() }}
</div>