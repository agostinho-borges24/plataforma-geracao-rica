<?php

namespace App\Livewire\Cart;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class AddToCartButton extends Component
{
    public Product $product;

    public function add(CartService $cart): void
    {
        $cart->add($this->product);

        // Outros componentes na página (ex: ícone do carrinho no header)
        // escutam este evento para atualizar a contagem sem recarregar a página.
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.cart.add-to-cart-button', [
            'inCart' => app(CartService::class)->has($this->product->id),
        ]);
    }
}