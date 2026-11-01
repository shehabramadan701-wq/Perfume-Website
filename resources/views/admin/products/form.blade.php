@extends('layouts.admin')
@section('title', $product->exists ? 'Edit product' : 'New product')
@section('subtitle', $product->exists ? $product->name : 'Add a new perfume to your store')

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
      class="grid g-main">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <div class="card">
        <div class="card-head"><h3>Details</h3></div>
        <div class="field">
            <label class="lbl">Name *</label>
            <input class="input" name="name" value="{{ old('name', $product->name) }}" required>
            @error('name')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="grid g-2" style="gap:0 18px">
            <div class="field">
                <label class="lbl">Category</label>
                <input class="input" name="category" value="{{ old('category', $product->category) }}" placeholder="Woody, Floral, Oriental…" list="cats">
                <datalist id="cats"><option>Woody</option><option>Floral</option><option>Oriental</option><option>Fresh</option></datalist>
            </div>
            <div class="field">
                <label class="lbl">Notes</label>
                <input class="input" name="notes" value="{{ old('notes', $product->notes) }}" placeholder="Oud, Amber, Vanilla">
            </div>
        </div>
        <div class="field">
            <label class="lbl">Short description</label>
            <input class="input" name="short_description" value="{{ old('short_description', $product->short_description) }}" maxlength="255">
        </div>
        <div class="field">
            <label class="lbl">Full description</label>
            <textarea class="input" name="description" rows="6">{{ old('description', $product->description) }}</textarea>
        </div>
        <div class="grid g-4" style="gap:0 18px">
            <div class="field">
                <label class="lbl">Price ({{ config('shop.currency') }}) *</label>
                <input class="input" type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required>
                @error('price')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label class="lbl">Old price (for sale)</label>
                <input class="input" type="number" step="0.01" name="old_price" value="{{ old('old_price', $product->old_price) }}">
            </div>
            <div class="field">
                <label class="lbl">Stock *</label>
                <input class="input" type="number" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required>
            </div>
            <div class="field">
                <label class="lbl">Size (ml) *</label>
                <input class="input" type="number" name="size_ml" value="{{ old('size_ml', $product->size_ml) }}" required>
            </div>
        </div>
    </div>

    <div>
        <div class="card mb">
            <div class="card-head"><h3>Photo</h3></div>
            <label class="upload">
                <img id="preview" src="{{ $product->image_url }}" alt="" @if (! $product->image) style="opacity:.5" @endif>
                <span class="muted">Click to upload (JPG, PNG, WEBP — max 4MB)</span>
                <input type="file" name="image" accept="image/*" hidden
                       onchange="if(this.files[0]){const p=document.getElementById('preview');p.src=URL.createObjectURL(this.files[0]);p.style.opacity=1}">
            </label>
            @error('image')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="card">
            <div class="card-head"><h3>Visibility</h3></div>
            <label class="switch mb"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Visible on the website</label>
            <label class="switch mb"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))> Featured on the home page</label>
            <button class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">{{ $product->exists ? 'Save changes' : 'Create product' }}</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:10px">Cancel</a>
        </div>
    </div>
</form>
@endsection
