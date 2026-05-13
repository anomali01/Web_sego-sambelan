@extends('layouts.admin')
@section('title', 'Tambah Menu')
@section('page-title', 'Tambah Menu Baru')

@section('content')
<div class="card glass-card form-card">
    <form method="POST" action="/admin/products" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="name">Nama Menu <span class="required">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required class="form-input @error('name') input-error @enderror" placeholder="Contoh: Sego Sambelan Komplit">
            @error('name')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" class="form-input form-textarea" placeholder="Deskripsi singkat menu...">{{ old('description') }}</textarea>
            @error('description')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category">Kategori <span class="required">*</span></label>
                <select id="category" name="category" required class="form-input">
                    <option value="food" {{ old('category') === 'food' ? 'selected' : '' }}>🍛 Makanan</option>
                    <option value="drink" {{ old('category') === 'drink' ? 'selected' : '' }}>🥤 Minuman</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Harga (Rp) <span class="required">*</span></label>
                <input type="number" id="price" name="price" value="{{ old('price') }}" required min="0" step="500" class="form-input @error('price') input-error @enderror" placeholder="25000">
                @error('price')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="stock">Stok <span class="required">*</span></label>
                <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" required min="0" class="form-input @error('stock') input-error @enderror">
                @error('stock')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="check-label">
                    <input type="checkbox" name="is_available" value="1" {{ old('is_available', true) ? 'checked' : '' }} class="check-input">
                    Menu Tersedia
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="image">Gambar Menu</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" class="form-input form-file" onchange="previewImage(this)">
            <small class="form-hint">Format: JPEG, PNG, WebP. Maks 2MB.</small>
            @error('image')<span class="form-error">{{ $message }}</span>@enderror
            <div id="image-preview" class="image-preview" style="display:none;">
                <img id="preview-img" src="" alt="Preview">
            </div>
        </div>

        <div class="form-actions">
            <a href="/admin/products" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Menu</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const img = document.getElementById('preview-img');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
