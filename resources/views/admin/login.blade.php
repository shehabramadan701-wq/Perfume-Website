<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('shop.name') }} Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=1">
</head>
<body>
<div class="login-page">
    <div class="login-art"><div class="quote">“A scent that leaves a mark.”</div></div>
    <div class="login-box">
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="brand" style="padding:0 0 8px">{{ config('shop.name') }}<span>.</span><small>DASHBOARD</small></div>
            <h2 style="font-size:24px;margin:30px 0 6px">Welcome back</h2>
            <p class="muted" style="margin-bottom:28px">Sign in to manage your store.</p>

            <div class="field">
                <label class="lbl">Email</label>
                <input class="input" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label class="lbl">Password</label>
                <input class="input" type="password" name="password" required>
            </div>
            <label class="switch" style="border:0;padding:0 0 20px"><input type="checkbox" name="remember"> Remember me</label>
            <button class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">Sign in</button>
        </form>
    </div>
</div>
</body>
</html>
