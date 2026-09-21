<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function toggleActive(Product $product): void
    {
        $product->update(['active' => ! $product->active]);
    }

    public function delete(Product $product): void
    {
        $product->delete(); // soft delete — pedidos antigos continuam intactos
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.products.product-index', [
            'products' => $products,
        ]);
    }
}