@extends('layouts.portal')
@section('title', $config['label'].' | Intern Estate')
@section('page-heading', $config['label'])
@section('content')
<section class="dashboard-panel">
    <div class="panel-header">
        <div><h3>{{ $config['label'] }} Management</h3><p>Create, update and manage {{ strtolower($config['label']) }}.</p></div>
        <a class="module-primary-button" href="{{ route('admin.modules.create', $module) }}">+ Add {{ $config['singular'] }}</a>
    </div>
    <div class="table-responsive"><table class="dashboard-table module-table">
        <thead><tr><th>Name / Title</th><th>Status</th><th>Quantity</th><th>Amount</th><th>Due Date</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($records as $record)
            <tr>
                <td><strong>{{ $record->title }}</strong><small>{{ $record->details }}</small></td>
                <td><span class="table-status">{{ $record->status }}</span></td>
                <td>{{ $record->quantity ?? '—' }}</td>
                <td>{{ $record->amount !== null ? '৳'.number_format((float) $record->amount, 2) : '—' }}</td>
                <td>{{ $record->due_date?->format('d M Y') ?? '—' }}</td>
                <td><div class="module-actions">
                    <a href="{{ route('admin.modules.edit', [$module, $record]) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.modules.destroy', [$module, $record]) }}" onsubmit="return confirm('Delete this record?')">@csrf @method('DELETE')<button type="submit">Delete</button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="6" class="module-empty">No records yet. Add the first {{ strtolower($config['singular']) }}.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="module-pagination">{{ $records->links() }}</div>
</section>
@endsection
