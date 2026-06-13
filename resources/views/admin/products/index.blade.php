@extends('layouts.admin')
@section('title', 'Menu Management')
@section('page-title', 'Kelola Menu')

@section('content')
<div class="toolbar">
    <form class="toolbar-search" method="GET" action="/admin/products">
        <input type="text" name="search" placeholder="Cari menu..." value="{{ request('search') }}" class="form-input">
        <select name="category" class="form-input" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <option value="food" {{ request('category') === 'food' ? 'selected' : '' }}>Makanan</option>
            <option value="drink" {{ request('category') === 'drink' ? 'selected' : '' }}>Minuman</option>
        </select>
    </form>
    <a href="/admin/products/create" class="btn btn-primary">+ Tambah Menu</a>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>
                    @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="table-thumb">
                    @else
                    <div class="table-thumb-placeholder">{{ $product->category === 'food' ? '🍛' : '🥤' }}</div>
                    @endif
                </td>
                <td><strong>{{ $product->name }}</strong></td>
                <td><span class="badge badge-{{ $product->category }}">{{ $product->category === 'food' ? 'Makanan' : 'Minuman' }}</span></td>
                <td>{{ $product->formatted_price }}</td>
                <td>{{ $product->stock }}</td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" {{ $product->is_available ? 'checked' : '' }}
                               onchange="toggleAvailability({{ $product->id }}, this)">
                        <span class="toggle-slider"></span>
                    </label>
                    <small class="toggle-label">{{ $product->is_available ? 'Tersedia' : 'Habis' }}</small>
                </td>
                <td class="actions-cell">
                    <a href="/admin/products/{{ $product->id }}/edit" class="btn btn-sm btn-outline">Edit</a>
                    <form action="/admin/products/{{ $product->id }}" method="POST" class="inline" onsubmit="return confirm('Hapus menu ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">Belum ada menu.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $products->links() }}

@push('scripts')
<script>
function toggleAvailability(productId, checkbox) {
    fetch(`/admin/products/${productId}/toggle`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(r => r.json()).then(data => {
        const label = checkbox.closest('td').querySelector('.toggle-label');
        if (label) label.textContent = data.is_available ? 'Tersedia' : 'Habis';
    });
}
</script>
@endpush
@endsection
