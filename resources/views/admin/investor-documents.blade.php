@extends('layouts.portal')
@section('title', 'Investor Documents | Intern Estate')
@section('page-heading', 'Investor Document Vault')
@section('content')

{{-- Upload Form Panel --}}
<section class="dashboard-panel" style="margin-bottom: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(15,23,42,0.03); padding: 28px;">
    <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 22px;">
        <div>
            <h3 style="color: #0f172a; font-size: 18px; font-weight: 800; margin-bottom: 4px;">Upload a Secure Document</h3>
            <p style="color: #64748b; font-size: 13px;">Only the selected investor and administrators can view and download this file.</p>
        </div>
    </div>

    <form class="erp-form" method="POST" action="{{ route('admin.investor-documents.store') }}" enctype="multipart/form-data" style="display: grid; gap: 18px; grid-template-columns: 1fr 1fr; padding: 0 !important;">
        @csrf
        
        <div class="input-group">
            <label style="color: #334155; font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Assign to Investor *</label>
            <select name="user_id" required style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9px; color: #0f172a; font-size: 13px; padding: 11px 14px; width: 100%;">
                <option value="">Select investor</option>
                @foreach($investors as $investor)
                    <option value="{{ $investor->id }}">{{ $investor->name }} ({{ $investor->email }})</option>
                @endforeach
            </select>
        </div>

        <div class="input-group">
            <label style="color: #334155; font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Linked Project (Optional)</label>
            <select name="project_id" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9px; color: #0f172a; font-size: 13px; padding: 11px 14px; width: 100%;">
                <option value="">Not project-specific</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="input-group">
            <label style="color: #334155; font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Document Title *</label>
            <input name="title" placeholder="e.g. Unit A-4 Share Deed & Allocation Agreement" required style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9px; color: #0f172a; font-size: 13px; padding: 11px 14px; width: 100%;">
        </div>

        <div class="input-group">
            <label style="color: #334155; font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Document Type *</label>
            <input name="doc_type" placeholder="e.g. Title Deed, Legal Agreement, Money Receipt" required style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9px; color: #0f172a; font-size: 13px; padding: 11px 14px; width: 100%;">
        </div>

        <div class="input-group">
            <label style="color: #334155; font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Issue / Notarization Date</label>
            <input type="date" name="issued_at" value="{{ now()->toDateString() }}" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9px; color: #0f172a; font-size: 13px; padding: 11px 14px; width: 100%;">
        </div>

        {{-- Custom Styled File Upload Box --}}
        <div class="input-group">
            <label style="color: #334155; font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Attach File (PDF, JPG, PNG; max 10MB) *</label>
            <div style="position: relative; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; transition: border-color 0.2s;">
                <div>
                    <span id="selectedFileName" style="color: #64748b; font-size: 12px; font-weight: 600;">Choose PDF or Image file...</span>
                </div>
                <label for="docFileInput" style="background: #0f172a; color: #ffffff; padding: 7px 14px; border-radius: 8px; font-size: 11px; font-weight: 700; cursor: pointer; margin: 0; display: inline-flex; align-items: center;">
                    Browse File
                </label>
                <input type="file" id="docFileInput" name="document" accept=".pdf,.jpg,.jpeg,.png" required style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" onchange="updateFileName(this)">
            </div>
        </div>

        {{-- Submit Button --}}
        <div style="grid-column: 1 / -1; margin-top: 10px;">
            <button type="submit" class="erp-button" style="background: #0f172a; color: #ffffff; border: none; border-radius: 10px; font-size: 13px; font-weight: 800; padding: 13px 26px; cursor: pointer; display: inline-flex; align-items: center; box-shadow: 0 4px 14px rgba(15,23,42,0.12); transition: all 0.2s;">
                Upload and Assign to Investor
            </button>
        </div>
    </form>
</section>

{{-- Uploaded Documents Table --}}
<section class="dashboard-panel" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(15,23,42,0.03); padding: 28px;">
    <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px;">
        <div>
            <h3 style="color: #0f172a; font-size: 18px; font-weight: 800; margin-bottom: 4px;">Uploaded Investor Documents</h3>
            <p style="color: #64748b; font-size: 13px;">Repository of legal deeds, allocation agreements, and investment receipts.</p>
        </div>
    </div>

    <div class="erp-table-wrap">
        <table class="erp-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Investor</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Document Title & Type</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Project</th>
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Issued Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 14px 12px;">
                            <strong style="color: #0f172a; font-size: 13px;">{{ $document->investor->name }}</strong>
                            <small style="display: block; color: #64748b; font-size: 11px;">{{ $document->investor->email }}</small>
                        </td>
                        <td style="padding: 14px 12px;">
                            <strong style="color: #0f172a; font-size: 13px;">{{ $document->title }}</strong>
                            <span class="erp-pill" style="display: inline-block; font-size: 10px; margin-left: 6px; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 999px;">{{ $document->doc_type }}</span>
                        </td>
                        <td style="padding: 14px 12px; color: #334155; font-size: 13px;">
                            {{ $document->project?->title ?? '—' }}
                        </td>
                        <td style="padding: 14px 12px; color: #64748b; font-size: 12px;">
                            {{ $document->issued_at?->format('d M Y') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td class="erp-empty" colspan="4" style="text-align: center; padding: 30px; color: #94a3b8;">No investor documents uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $documents->links() }}
</section>

@push('scripts')
<script>
function updateFileName(input) {
    let nameElem = document.getElementById('selectedFileName');
    if (input.files && input.files[0]) {
        nameElem.textContent = input.files[0].name;
        nameElem.style.color = '#059669';
    } else {
        nameElem.textContent = 'Choose PDF or Image file...';
        nameElem.style.color = '#64748b';
    }
}
</script>
@endpush
@endsection
