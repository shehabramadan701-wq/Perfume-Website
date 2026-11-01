@extends('layouts.admin')
@section('title', 'Messages')
@section('subtitle', 'Contact form submissions')

@section('content')
<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th></th><th>From</th><th>Subject</th><th>Message</th><th>Received</th></tr></thead>
            <tbody>
            @forelse ($messages as $m)
                <tr onclick="location='{{ route('admin.messages.show', $m) }}'" style="cursor:pointer;{{ $m->is_read ? '' : 'font-weight:600' }}">
                    <td style="width:30px">@unless ($m->is_read)<span class="pill info" title="Unread">New</span>@endunless</td>
                    <td>{{ $m->name }}<br><small class="muted" style="font-weight:400">{{ $m->email }}</small></td>
                    <td>{{ $m->subject ?: '—' }}</td>
                    <td class="muted" style="font-weight:400">{{ \Illuminate\Support\Str::limit($m->message, 70) }}</td>
                    <td class="muted" style="font-weight:400;white-space:nowrap">{{ $m->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">No messages yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $messages->links() }}
</div>
@endsection
