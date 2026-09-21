<div class="admin-product-form">
    <x-admin-nav />

    <h1>{{ $product?->exists ? 'Editar produto' : 'Novo produto' }}</h1>

    <form wire:submit="save">
        <label for="title">Título</label>
        <input type="text" id="title" wire:model.live="title">
        @error('title') <span class="field-error">{{ $message }}</span> @enderror

        <label for="slug">Slug (usado na URL do produto)</label>
        <input type="text" id="slug" wire:model.live="slug">
        @error('slug') <span class="field-error">{{ $message }}</span> @enderror

        <label for="type">Tipo</label>
        <select id="type" wire:model="type">
            <option value="course">Curso</option>
            <option value="ebook">E-book</option>
        </select>

        <label for="description">Descrição</label>
        <textarea id="description" wire:model="description" rows="6"></textarea>

        <label for="price">Preço (Kz)</label>
        <input type="number" step="0.01" min="0" id="price" wire:model="price">
        @error('price') <span class="field-error">{{ $message }}</span> @enderror

        <label for="coverImage">Imagem de capa (URL)</label>
        <input type="text" id="coverImage" wire:model="coverImage" placeholder="https://...">
        @error('coverImage') <span class="field-error">{{ $message }}</span> @enderror

        <label class="checkbox-label">
            <input type="checkbox" wire:model="active">
            Produto ativo (visível na loja)
        </label>

        <h2>Links de acesso</h2>
        <p><small>Liberados ao cliente automaticamente depois do pagamento ser confirmado.</small></p>

        @foreach ($accessLinks as $index => $link)
            <div class="access-link-row" wire:key="access-link-{{ $index }}">
                <input type="text" placeholder="Rótulo (ex: Google Drive, Módulo 1)"
                       wire:model="accessLinks.{{ $index }}.label">
                <input type="text" placeholder="URL"
                       wire:model="accessLinks.{{ $index }}.url">
                <button type="button" wire:click="removeAccessLink({{ $index }})">Remover</button>
            </div>
            @error("accessLinks.{$index}.label") <span class="field-error">{{ $message }}</span> @enderror
            @error("accessLinks.{$index}.url") <span class="field-error">{{ $message }}</span> @enderror
        @endforeach

        <button type="button" wire:click="addAccessLink" class="btn btn--secondary">+ Adicionar link</button>

        <div class="form-actions">
            <a href="{{ route('admin.products.index') }}" wire:navigate>Cancelar</a>
            <button type="submit" class="btn btn--primary">Guardar produto</button>
        </div>
    </form>
</div>