<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
    public function remove(int $productId, CartService $cart): void
    {
        $cart->remove($productId);
        $this->dispatch('cart-updated');
    }

    public function render(CartService $cart)
    {
        return view('livewire.cart.cart-page', [
            'items' => $cart->items(),
            'total' => $cart->totalBaseAoa(),
        ]);
    }
}