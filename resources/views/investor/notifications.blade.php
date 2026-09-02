@extends('layouts.investor')
@section('title', 'Notifications | Intern Estate')
@section('page-heading', 'Notifications')
@section('content')
<section class="dashboard-panel"><div class="panel-header"><div><h3>Updates</h3><p>Construction, payment and legal notices</p></div>@if($notifications->where('is_read', false)->isNotEmpty())<form method="POST" action="{{ route('investor.notifications.read-all') }}">@csrf @method('PATCH')<button class="erp-secondary" type="submit">Mark all as read</button></form>@endif</div>
@forelse($notifications as $notification)<div class="investor-profile-row"><div><strong>{{ $notification->title }} @if(!$notification->is_read)<small class="investor-status">New</small>@endif</strong><span>{{ $notification->message }}</span></div><div><small>{{ $notification->created_at->diffForHumans() }}</small>@if(!$notification->is_read)<form method="POST" action="{{ route('investor.notifications.read', $notification) }}">@csrf @method('PATCH')<button class="erp-secondary" type="submit">Mark as read</button></form>@endif</div></div>
@empty<div class="investor-empty-state"><h4>You are all caught up</h4><p>New project updates will appear here.</p></div>@endforelse</section>
@endsection
