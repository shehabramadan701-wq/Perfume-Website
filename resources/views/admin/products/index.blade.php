@extends('layouts.admin')
@section('title', 'Products')
@section('subtitle', $products->total().' perfumes in your store')
@section('actions')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Add product</a>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <form class="toolbar">
            <input class="input" name="q" value="{{ request('q') }}" placeholder="Search products…">
            <button class="btn btn-ghost">Search</button>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>
                        <div class="cell-main">
                            <img class="thumb" src="{{ $product->image_url }}" alt="">
                            <div><strong>{{ $product->name }}</strong><small>{{ $product->size_ml }} ml · {{ $product->notes }}</small></div>
                        </div>
                    </td>
                    <td>{{ $product->category ?: '—' }}</td>
                    <td>
                        <strong>{{ money($product->price) }}</strong>
                        @if ($product->old_price)<br><small class="muted"><del>{{ money($product->old_price) }}</del></small>@endif
                    </td>
                    <td><span class="pill {{ $product->stock == 0 ? 'bad' : ($product->stock <= 5 ? 'warn' : 'good') }}">{{ $product->stock }}</span></td>
                    <td>
                        <span class="pill {{ $product->is_active ? 'good' : 'neutral' }}">{{ $product->is_active ? 'Visible' : 'Hidden' }}</span>
                        @if ($product->is_featured)<span class="pill info">Featured</span>@endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('product', $product) }}" target="_blank" class="btn btn-ghost btn-sm">View</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">No products yet. <a href="{{ route('admin.products.create') }}" style="color:var(--accent)">Add your first perfume</a></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
