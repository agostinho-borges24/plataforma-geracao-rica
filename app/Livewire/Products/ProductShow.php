<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class ProductShow extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        abort_unless($product->active, 404);

        $this->product = $product;
    }

    public function render()
    {
        return view('livewire.products.product-show');
    }
}