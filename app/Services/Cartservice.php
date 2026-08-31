<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    protected const SESSION_KEY = 'cart';

    public function add(Product $product): void
    {
        $ids = $this->productIds();

        if (! in_array($product->id, $ids, true)) {
            $ids[] = $product->id;
            session()->put(self::SESSION_KEY, $ids);
        }
    }

    public function remove(int $productId): void
    {
        $ids = array_values(array_filter(
            $this->productIds(),
            fn (int $id) => $id !== $productId,
        ));

        session()->put(self::SESSION_KEY, $ids);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->productIds(), true);
    }

    public function count(): int
    {
        return count($this->productIds());
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return array<int, int>
     */
    public function productIds(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    /**
     * Produtos atuais no carrinho. Busca fresco do banco (não confia em preço
     * antigo guardado na sessão) — produtos inativos/removidos somem daqui
     * silenciosamente, o que também reduz o total automaticamente.
     *
     * @return Collection<int, Product>
     */
    public function items(): Collection
    {
        $ids = $this->productIds();

        if (empty($ids)) {
            return collect();
        }

        return Product::query()
            ->active()
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))
            ->values();
    }

    public function totalBaseAoa(): float
    {
        return (float) $this->items()->sum(fn (Product $product) => (float) $product->price);
    }
}