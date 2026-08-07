@extends('layouts.portal')
@php($editing = $record->exists)
@section('title', ($editing ? 'Edit ' : 'Add ').$config['singular'].' | Intern Estate')
@section('page-heading', ($editing ? 'Edit ' : 'Add ').$config['singular'])
@section('content')
<section class="dashboard-panel module-form-panel">
    <div class="panel-header">
        <div><h3>{{ $editing ? 'Update' : 'Create' }} {{ $config['singular'] }}</h3><p>Fields marked with * are required.</p></div>
        <a href="{{ route('admin.modules.index', $module) }}">← Back</a>
    </div>
    <form class="module-form" method="POST" action="{{ $editing ? route('admin.modules.update', [$module, $record]) : route('admin.modules.store', $module) }}">
        @csrf @if($editing) @method('PUT') @endif
        <label class="full-width">Name / Title *<input name="title" value="{{ old('title', $record->title) }}" required maxlength="255">@error('title')<small>{{ $message }}</small>@enderror</label>
        <label>Status *<select name="status" required>@foreach(['Active', 'Pending', 'In Progress', 'Completed', 'Inactive'] as $status)<option @selected(old('status', $record->status) === $status)>{{ $status }}</option>@endforeach</select></label>
        <label>Due Date<input type="date" name="due_date" value="{{ old('due_date', $record->due_date?->format('Y-m-d')) }}"></label>
        <label>Quantity<input type="number" min="0" name="quantity" value="{{ old('quantity', $record->quantity) }}"></label>
        <label>Amount (BDT)<input type="number" min="0" step="0.01" name="amount" value="{{ old('amount', $record->amount) }}"></label>
        <label class="full-width">Details<textarea name="details" rows="5" maxlength="2000">{{ old('details', $record->details) }}</textarea></label>
        <div class="full-width module-form-actions"><a href="{{ route('admin.modules.index', $module) }}">Cancel</a><button type="submit">{{ $editing ? 'Save Changes' : 'Create Record' }}</button></div>
    </form>
</section>
@endsection
