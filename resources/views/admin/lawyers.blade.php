@extends('layouts.portal')
@section('page-heading','Legal Desk')
@section('content')
<div class="erp-hero compact">
    <div>
        <span class="erp-kicker">LAND DUE DILIGENCE</span>
        <h2>Lawyers & JV Submission Review</h2>
        <p>Assign counsel, verify ownership, define the JV split and activate development.</p>
    </div>
</div>

{{-- Pending Action Queue --}}
<section class="erp-panel">
    <div class="erp-panel-head">
        <div>
            <h3>Pending Land Submissions (Action Needed)</h3>
            <p>Submissions awaiting lawyer assignment or JV approval.</p>
        </div>
    </div>
    <div class="erp-table-wrap">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Tracking / Property</th>
                    <th>Stage</th>
                    <th>Assign Lawyer</th>
                    <th>JV Decision & Approval</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingSubmissions as $s)
                <tr>
                    <td>
                        <b>{{ $s->code ?? $s->title }}</b>
                        <small>{{ $s->landowner_name }} · {{ $s->location }}</small>
                    </td>
                    <td>
                        <span class="erp-pill">{{ $s->stage ?? $s->status }}</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.submissions.assign',$s) }}" class="erp-row-form">
                            @csrf
                            <select name="lawyer_id" required style="padding: 8px 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 12px; background: #fff; color: #0f172a;">
                                <option value="">Select Lawyer…</option>
                                @foreach($lawyers as $l)
                                    <option value="{{ $l->id }}" @selected($s->assigned_lawyer_id===$l->id)>
                                        {{ $l->name }} ({{ $l->active_cases_count }} cases)
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-admin-action assign">Assign</button>
                        </form>
                    </td>
                    <td>
                        <div class="jv-admin-actions">
                            <form method="POST" action="{{ route('admin.submissions.approve',$s) }}" class="erp-row-form" style="gap: 6px;">
                                @csrf
                                <input type="number" name="landowner_share_pct" value="40" min="10" max="90" title="Landowner share %" style="width: 65px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
                                <input name="allocated_flats" placeholder="Flats (e.g. A4, B6)" title="Comma-separated flat codes" style="width: 140px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
                                <button type="submit" class="btn-admin-action approve">Draft JV & Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.submissions.reject',$s) }}" class="erp-row-form" style="gap: 6px;">
                                @csrf
                                <input name="rejection_reason" placeholder="Rejection reason" style="width: 160px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px;">
                                <button type="submit" class="btn-admin-action reject">Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="erp-empty">No pending submissions awaiting review.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Approved & Active JV Projects --}}
<section class="erp-panel" style="margin-top: 24px;">
    <div class="erp-panel-head">
        <div>
            <h3>Approved & Active JV Projects ({{ $approvedSubmissions->count() }})</h3>
            <p>Completed land approvals with active JV agreements and live projects.</p>
        </div>
    </div>
    <div class="erp-table-wrap">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Tracking / Property</th>
                    <th>Status</th>
                    <th>Assigned Lawyer</th>
                    <th>Approved JV Agreement & Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedSubmissions as $s)
                <tr>
                    <td>
                        <b>{{ $s->code ?? $s->title }}</b>
                        <small>{{ $s->landowner_name }} · {{ $s->location }}</small>
                    </td>
                    <td>
                        <span class="erp-pill risk low" style="background: #e6f4f1; color: #0f766e; border: 1px solid #b9e6dc; font-weight: 800;">✓ Approved & Active</span>
                    </td>
                    <td>
                        <div style="font-size: 12px; color: #0f172a; font-weight: 700;">
                            {{ $s->lawyer?->name ?? 'Assigned Counsel' }}
                        </div>
                        <small style="color: #64748b;">{{ $s->lawyer?->specialization ?? 'Land Law' }}</small>
                    </td>
                    <td>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                            <div>
                                <b style="color: #0f766e; font-size: 13px;">{{ $s->agreement?->landowner_share_pct ?? 40 }}% Landowner</b>
                                <span style="color: #64748b; font-size: 12px;"> / {{ $s->agreement?->developer_share_pct ?? 60 }}% Developer</span>
                                @if($s->agreement?->allocated_flats_json)
                                <div style="margin-top: 4px; font-size: 11px; color: #475569;">
                                    Flats Allocated: <strong>{{ is_array($s->agreement->allocated_flats_json) ? implode(', ', array_column($s->agreement->allocated_flats_json, 'code') ?: $s->agreement->allocated_flats_json) : $s->agreement->allocated_flats_json }}</strong>
                                </div>
                                @endif
                            </div>
                            <a href="{{ route('admin.projects') }}" class="btn-admin-action approve" style="padding: 6px 12px; font-size: 11px;">View Live Project →</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="erp-empty">No approved JV projects yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{-- Add New Lawyer Form --}}
<section class="erp-panel" style="margin-top: 24px;">
    <div class="erp-panel-head">
        <div>
            <h3>+ Add New Lawyer</h3>
            <p>Register a new legal counsel to assign land due diligence cases.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.lawyers.store') }}" style="display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 12px; align-items: end; padding: 10px 0;">
        @csrf
        <div>
            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Lawyer Full Name</label>
            <input type="text" name="name" placeholder="Adv. Kazi Rahman" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
        </div>
        <div>
            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Email Address</label>
            <input type="email" name="email" placeholder="kazi@internestate.test" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
        </div>
        <div>
            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Phone Number</label>
            <input type="text" name="phone" placeholder="+8801700000000" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
        </div>
        <div>
            <label style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 5px;">Specialization</label>
            <input type="text" name="specialization" placeholder="Land & Property Law" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; background: #fff; color: #0f172a;">
        </div>
        <div>
            <button type="submit" class="btn-admin-action approve" style="height: 42px; padding: 0 20px;">+ Add Lawyer</button>
        </div>
    </form>
</section>

{{-- Lawyer Directory --}}
<section class="erp-panel" style="margin-top: 24px;">
    <div class="erp-panel-head">
        <h3>Lawyer Directory ({{ $lawyers->count() }})</h3>
    </div>
    <div class="erp-module-grid">
        @forelse($lawyers as $l)
        <a href="#" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; text-decoration: none; display: block;">
            <span style="color: #0f766e; font-size: 10px; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase;">PROPERTY COUNSEL</span>
            <h3 style="font-size: 16px; color: #0f172a; margin: 8px 0 4px;">{{ $l->name }}</h3>
            <p style="color: #64748b; font-size: 12px; margin-bottom: 12px;">{{ $l->specialization }} · {{ $l->phone }}</p>
            <b style="color: #0f766e; font-size: 12px; display: inline-block; background: #e6f4f1; padding: 4px 10px; border-radius: 999px;">{{ $l->active_cases_count }} active cases</b>
        </a>
        @empty
        <p class="erp-empty">No lawyers added in directory yet.</p>
        @endforelse
    </div>
</section>
@endsection
