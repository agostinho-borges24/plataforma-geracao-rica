<div>
    @if ($inCart)
        <a href="{{ route('cart.index') }}" class="btn btn--secondary" wire:navigate>
            Já está no carrinho — ver carrinho
        </a>
    @else
        <button type="button" wire:click="add" wire:loading.attr="disabled" class="btn btn--primary">
            <span wire:loading.remove wire:target="add">Adicionar ao carrinho</span>
            <span wire:loading wire:target="add">A adicionar…</span>
        </button>
    @endif
</div>