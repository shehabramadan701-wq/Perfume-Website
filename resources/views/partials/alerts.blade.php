@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-error">Please check the highlighted fields.</div>
@endif
