<div class="product-show">
    <div class="product-show__header">
        @if ($product->cover_image)
            <img src="{{ $product->cover_image }}" alt="{{ $product->title }}">
        @endif

        <div class="product-show__info">
            <span class="product-show__type">{{ $product->type->label() }}</span>
            <h1>{{ $product->title }}</h1>
            <p class="product-show__price">{{ number_format((float) $product->price, 2, ',', '.') }} Kz</p>

            @livewire('cart.add-to-cart-button', ['product' => $product], key('add-to-cart-'.$product->id))
        </div>
    </div>

    <div class="product-show__description">
        {!! nl2br(e($product->description)) !!}
    </div>
</div>