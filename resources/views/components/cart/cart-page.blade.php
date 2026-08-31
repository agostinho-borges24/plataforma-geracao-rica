<div class="cart">
    <h1>O seu carrinho</h1>

    @if ($items->isEmpty())
        <p>O seu carrinho está vazio.</p>
        <a href="{{ route('products.index') }}" class="btn btn--primary" wire:navigate>Ver cursos e e-books</a>
    @else
        <ul class="cart__items">
            @foreach ($items as $product)
                <li class="cart__item" wire:key="cart-item-{{ $product->id }}">
                    <div class="cart__item-info">
                        <span class="cart__item-type">{{ $product->type->label() }}</span>
                        <h2 class="cart__item-title">{{ $product->title }}</h2>
                    </div>

                    <div class="cart__item-price">
                        {{ number_format((float) $product->price, 2, ',', '.') }} Kz
                    </div>

                    <button type="button" wire:click="remove({{ $product->id }})" class="cart__item-remove">
                        Remover
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="cart__summary">
            <strong>Total: {{ number_format($total, 2, ',', '.') }} Kz</strong>
            <a href="{{ route('checkout.index') }}" class="btn btn--primary" wire:navigate>
                Finalizar compra
            </a>
        </div>
    @endif
</div>