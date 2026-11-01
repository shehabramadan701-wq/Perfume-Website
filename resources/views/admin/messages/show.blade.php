@extends('layouts.admin')
@section('title', $message->subject ?: 'Message')
@section('subtitle', 'From '.$message->name.' · '.$message->created_at->format('d M Y, H:i'))
@section('actions')
    <a href="{{ route('admin.messages.index') }}" class="btn btn-ghost">← Back</a>
@endsection

@section('content')
<div class="grid g-main">
    <div class="card">
        <p style="white-space:pre-line;font-size:15px;line-height:1.8">{{ $message->message }}</p>
    </div>
    <div class="card">
        <div class="card-head"><h3>Sender</h3></div>
        <dl class="kv" style="grid-template-columns:70px 1fr">
            <dt>Name</dt><dd>{{ $message->name }}</dd>
            <dt>Email</dt><dd>{{ $message->email }}</dd>
            <dt>Phone</dt><dd>{{ $message->phone ?: '—' }}</dd>
        </dl>
        <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject ?? '') }}" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:20px">Reply by email</a>
        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?')" style="margin-top:10px">
            @csrf @method('DELETE')
            <button class="btn btn-danger" style="width:100%;justify-content:center">Delete</button>
        </form>
    </div>
</div>
@endsection
