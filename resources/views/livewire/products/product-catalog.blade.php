<div class="product-catalog">
    <h1>Cursos e E-books</h1>

    <div class="product-catalog__filters">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Pesquisar…">

        <select wire:model.live="type">
            <option value="">Todos os tipos</option>
            @foreach ($types as $t)
                <option value="{{ $t->value }}">{{ $t->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="product-catalog__grid">
        @forelse ($products as $product)
            <a href="{{ route('products.show', $product) }}" class="product-card" wire:navigate wire:key="product-{{ $product->id }}">
                @if ($product->cover_image)
                    <img src="{{ $product->cover_image }}" alt="{{ $product->title }}" loading="lazy">
                @endif
                <span class="product-card__type">{{ $product->type->label() }}</span>
                <h2 class="product-card__title">{{ $product->title }}</h2>
                <span class="product-card__price">{{ number_format((float) $product->price, 2, ',', '.') }} Kz</span>
            </a>
        @empty
            <p>Nenhum produto encontrado.</p>
        @endforelse
    </div>

    {{ $products->links() }}
</div>