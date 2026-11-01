<article class="product-card on-scroll">
    <a href="{{ route('product', $product) }}" class="thumb">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
        @if ($product->category)<span class="tag">{{ $product->category }}</span>@endif
        @if ($product->old_price > $product->price)<span class="tag sale">Sale</span>@endif
    </a>
    <div class="info">
        <a href="{{ route('product', $product) }}"><h3>{{ $product->name }}</h3></a>
        <span class="notes">{{ $product->notes }} · {{ $product->size_ml }} ml</span>
        <div class="row">
            <span class="price">
                {{ money($product->price) }}
                @if ($product->old_price > $product->price)<del>{{ number_format($product->old_price) }}</del>@endif
            </span>
            <form method="POST" action="{{ route('cart.add', $product) }}">
                @csrf
                <button class="add-btn" title="Add to bag" aria-label="Add to bag" @disabled($product->stock < 1)>
                    <svg class="icon" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </button>
            </form>
        </div>
    </div>
</article>
