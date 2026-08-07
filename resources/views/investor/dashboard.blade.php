@extends('layouts.investor')
@section('title', 'Investor Dashboard | Intern Estate')
@section('page-heading', 'Investor Dashboard')
@section('content')
<section class="investor-welcome-card"><div><span class="investor-eyebrow">INVESTMENT OVERVIEW</span><h2>Welcome back, {{ auth()->user()->name }}.</h2><p>Track your real estate portfolio from one secure, transparent workspace.</p></div><div class="investor-welcome-mark"><span>IE</span><small>INVESTOR</small></div></section>
<section class="stat-card-grid investor-stat-grid">
    <article class="stat-card investor-stat-card"><div class="stat-card-icon">৳</div><div><p>Total Investment</p><h3>৳0</h3><span>Portfolio value</span></div></article>
    <article class="stat-card investor-stat-card"><div class="stat-card-icon">P</div><div><p>Active Projects</p><h3>0</h3><span>Currently invested</span></div></article>
    <article class="stat-card investor-stat-card"><div class="stat-card-icon">R</div><div><p>Total Returns</p><h3>৳0</h3><span>Return received</span></div></article>
    <article class="stat-card investor-stat-card"><div class="stat-card-icon">D</div><div><p>Documents</p><h3>0</h3><span>Available files</span></div></article>
</section>
<section class="investor-dashboard-grid">
    <article class="dashboard-panel"><div class="panel-header"><div><h3>My Portfolio</h3><p>Your current property investments</p></div><span class="investor-live-label">LIVE OVERVIEW</span></div><div class="investor-empty-state"><div class="investor-empty-icon">⌂</div><h4>No investments yet</h4><p>Your property investments and construction progress will appear here.</p></div></article>
    <aside class="dashboard-panel"><div class="panel-header"><div><h3>Account</h3><p>Investor profile</p></div></div><div class="investor-profile-row"><span>Name</span><strong>{{ auth()->user()->name }}</strong></div><div class="investor-profile-row"><span>Email</span><strong>{{ auth()->user()->email }}</strong></div><div class="investor-profile-row"><span>Status</span><strong class="investor-status">Verified</strong></div></aside>
</section>
@endsection
