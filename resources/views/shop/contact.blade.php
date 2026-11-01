@extends('layouts.shop')
@section('title', 'Contact — '.config('shop.name'))

@section('content')
<section class="page-hero">
    <div class="container">
        <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a> / Contact</div>
        <h1 class="section-title">Let’s talk scent</h1>
        <p>Questions about an order, a gift, or finding your signature perfume? We’d love to hear from you.</p>
    </div>
</section>

<section class="container two-col">
    <div class="panel">
        @include('partials.alerts')
        <form method="POST" action="{{ route('contact.store') }}" class="form-grid">
            @csrf
            <div class="field"><label>Name</label><input class="input" name="name" value="{{ old('name') }}" required>@error('name')<span class="error">{{ $message }}</span>@enderror</div>
            <div class="field"><label>Email</label><input class="input" type="email" name="email" value="{{ old('email') }}" required>@error('email')<span class="error">{{ $message }}</span>@enderror</div>
            <div class="field"><label>Phone</label><input class="input" name="phone" value="{{ old('phone') }}"></div>
            <div class="field"><label>Subject</label><input class="input" name="subject" value="{{ old('subject') }}"></div>
            <div class="field full"><label>Message</label><textarea class="input" name="message" rows="6" required>{{ old('message') }}</textarea>@error('message')<span class="error">{{ $message }}</span>@enderror</div>
            <div class="full"><button class="btn btn-primary">Send message</button></div>
        </form>
    </div>
    <div class="contact-info">
        <div class="item"><div class="feature" style="padding:0;border:0;background:none"><div class="ico" style="margin:0">✆</div></div><div><small>Phone</small>{{ config('shop.phone') }}</div></div>
        <div class="item"><div class="feature" style="padding:0;border:0;background:none"><div class="ico" style="margin:0">✉</div></div><div><small>Email</small>{{ config('shop.email') }}</div></div>
        <div class="item"><div class="feature" style="padding:0;border:0;background:none"><div class="ico" style="margin:0">⌖</div></div><div><small>Boutique</small>{{ config('shop.address') }}</div></div>
        <div class="item">@include('partials.socials')</div>
    </div>
</section>
@endsection
