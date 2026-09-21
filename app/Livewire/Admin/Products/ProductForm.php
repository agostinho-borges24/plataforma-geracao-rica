<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductForm extends Component
{
    public ?Product $product = null;

    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public string $type = 'course';
    public string $price = '';
    public string $coverImage = '';
    public bool $active = true;

    /** @var array<int, array{label: string, url: string}> */
    public array $accessLinks = [];

    protected bool $slugManuallyEdited = false;

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            $this->product = $product;
            $this->title = $product->title;
            $this->slug = $product->slug;
            $this->description = $product->description ?? '';
            $this->type = $product->type->value;
            $this->price = (string) $product->price;
            $this->coverImage = $product->cover_image ?? '';
            $this->active = $product->active;
            $this->accessLinks = $product->accessLinks()->orderBy('position')->get()
                ->map(fn ($link) => ['label' => $link->label, 'url' => $link->url])
                ->all();
            $this->slugManuallyEdited = true;
        }

        if (empty($this->accessLinks)) {
            $this->accessLinks = [['label' => '', 'url' => '']];
        }
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->slugManuallyEdited) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = true;
    }

    public function addAccessLink(): void
    {
        $this->accessLinks[] = ['label' => '', 'url' => ''];
    }

    public function removeAccessLink(int $index): void
    {
        unset($this->accessLinks[$index]);
        $this->accessLinks = array_values($this->accessLinks);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'required', 'alpha_dash', 'max:220',
                'unique:products,slug,'.($this->product?->id ?? 'NULL').',id',
            ],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:course,ebook'],
            'price' => ['required', 'numeric', 'min:0'],
            'coverImage' => ['nullable', 'url', 'max:500'],
            'accessLinks' => ['array'],
            'accessLinks.*.label' => ['required_with:accessLinks.*.url', 'nullable', 'string', 'max:100'],
            'accessLinks.*.url' => ['required_with:accessLinks.*.label', 'nullable', 'url', 'max:500'],
        ], attributes: [
            'title' => 'título',
            'slug' => 'slug',
            'price' => 'preço',
            'coverImage' => 'imagem de capa',
        ]);

        $this->product = Product::updateOrCreate(
            ['id' => $this->product?->id],
            [
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'price' => $validated['price'],
                'currency' => 'AOA',
                'cover_image' => $validated['coverImage'] ?: null,
                'active' => $this->active,
            ],
        );

        // Substitui os links por completo — mais simples que fazer diff, e o
        // volume de links por produto é pequeno o suficiente pra não pesar.
        $this->product->accessLinks()->delete();

        collect($this->accessLinks)
            ->filter(fn ($link) => filled($link['label']) && filled($link['url']))
            ->values()
            ->each(fn ($link, $index) => $this->product->accessLinks()->create([
                'label' => $link['label'],
                'url' => $link['url'],
                'position' => $index,
            ]));

        session()->flash('success', 'Produto guardado com sucesso.');

        $this->redirect(route('admin.products.index'));
    }

    public function render()
    {
        return view('livewire.admin.products.product-form');
    }
}