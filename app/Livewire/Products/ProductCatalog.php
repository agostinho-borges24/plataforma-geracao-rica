<?php

namespace App\Livewire\Products;

use App\Enums\ProductType;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    #[Url]
    public string $type = ''; // '' | 'course' | 'ebook'

    #[Url]
    public string $search = '';

    public function render()
    {
        $products = Product::query()
            ->active()
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(12);

        return view('livewire.products.product-catalog', [
            'products' => $products,
            'types' => ProductType::cases(),
        ]);
    }
}