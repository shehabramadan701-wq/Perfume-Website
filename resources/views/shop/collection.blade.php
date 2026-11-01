@extends('layouts.shop')
@section('title', 'Collection — '.config('shop.name'))

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Collection</div>
        <h1 class="section-title">The Collection</h1>
        <p>Woods, resins, flowers and spices — each perfume tells its own story.</p>
    </div>
</section>

<section class="container">
    <div class="filters">
        <div class="chips">
            <a href="{{ route('collection', request()->except('category', 'page')) }}" class="chip {{ request('category') ? '' : 'active' }}">All</a>
            @foreach ($categories as $cat)
                <a href="{{ route('collection', ['category' => $cat] + request()->except('page')) }}" class="chip {{ request('category') === $cat ? 'active' : '' }}">{{ $cat }}</a>
            @endforeach
        </div>
        <form>
            @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            <select name="sort" class="input" onchange="this.form.submit()" style="width:auto;padding:10px 18px">
                <option value="">Newest</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: low to high</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: high to low</option>
            </select>
        </form>
    </div>

    @if (request('q'))
        <p style="color:var(--muted);margin-bottom:24px">Results for “{{ request('q') }}”</p>
    @endif

    <div class="grid">
        @forelse ($products as $product)
            @include('partials.product-card')
        @empty
            <p class="empty" style="grid-column:1/-1">No perfumes found.</p>
        @endforelse
    </div>
    {{ $products->links() }}
</section>
@endsection
