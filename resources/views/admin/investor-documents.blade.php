@extends('layouts.portal')
@section('title', 'Investor Documents Vault | Intern Estate')
@section('page-heading', 'Investor Document Vault')
@section('content')

{{-- Upload Form Panel (Batch 3 Documents Upload) --}}
<section class="dashboard-panel" style="margin-bottom: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(15,23,42,0.03); padding: 28px;">
    <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h3 style="color: #0f172a; font-size: 18px; font-weight: 800; margin-bottom: 4px;">Upload Secure Investor Documents (Upload up to 3 Files)</h3>
            <p style="color: #64748b; font-size: 13px;">Upload Title Deeds, Allocation Agreements, or Money Receipts simultaneously for an investor.</p>
        </div>
        <span style="background: #e0f2fe; color: #0284c7; font-weight: 800; font-size: 11px; padding: 5px 12px; border-radius: 999px;">Batch Upload Supported (3 Files)</span>
    </div>

    <form class="erp-form" method="POST" action="{{ route('admin.investor-documents.store') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px; padding: 0 !important;">
        @csrf
        
        <div style="display: grid; gap: 18px; grid-template-columns: 1fr 1fr 1fr;">
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
                <label style="color: #334155; font-size: 12px; font-weight: 700; display: block; margin-bottom: 6px;">Issue / Notarization Date</label>
                <input type="date" name="issued_at" value="{{ now()->toDateString() }}" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9px; color: #0f172a; font-size: 13px; padding: 11px 14px; width: 100%;">
            </div>
        </div>

        {{-- 3 File Upload Cards Grid --}}
        <div style="display: grid; gap: 18px; grid-template-columns: repeat(3, 1fr);">
            
            {{-- Document 1 --}}
            <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <strong style="color: #0f172a; font-size: 13px;">Document 1 (Primary) *</strong>
                    <span style="background: #0f172a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px;">Slot 1</span>
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Document Title *</label>
                    <input name="docs[0][title]" placeholder="e.g. Unit A-4 Share Deed" value="Property Share & Title Deed" required style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px; font-size: 12px; width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Document Type *</label>
                    <input name="docs[0][doc_type]" placeholder="e.g. Title Deed" value="Title Deed" required style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px; font-size: 12px; width: 100%;">
                </div>
                <div class="input-group">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Attach File 1 (PDF / Image) *</label>
                    <input type="file" name="docs[0][file]" accept=".pdf,.jpg,.jpeg,.png" required style="font-size: 11px; width: 100%;">
                </div>
            </div>

            {{-- Document 2 --}}
            <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <strong style="color: #0f172a; font-size: 13px;">Document 2 (Optional)</strong>
                    <span style="background: #64748b; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px;">Slot 2</span>
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Document Title</label>
                    <input name="docs[1][title]" placeholder="e.g. Flat Allocation Agreement" value="Flat Allocation & Unit Allotment" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px; font-size: 12px; width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Document Type</label>
                    <input name="docs[1][doc_type]" placeholder="e.g. Legal Agreement" value="Legal Agreement" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px; font-size: 12px; width: 100%;">
                </div>
                <div class="input-group">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Attach File 2 (PDF / Image)</label>
                    <input type="file" name="docs[1][file]" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 11px; width: 100%;">
                </div>
            </div>

            {{-- Document 3 --}}
            <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <strong style="color: #0f172a; font-size: 13px;">Document 3 (Optional)</strong>
                    <span style="background: #64748b; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px;">Slot 3</span>
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Document Title</label>
                    <input name="docs[2][title]" placeholder="e.g. Money Receipt / Clearance" value="Official Payment Clearance Receipt" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px; font-size: 12px; width: 100%;">
                </div>
                <div class="input-group" style="margin-bottom: 10px;">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Document Type</label>
                    <input name="docs[2][doc_type]" placeholder="e.g. Money Receipt" value="Money Receipt" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 9px; font-size: 12px; width: 100%;">
                </div>
                <div class="input-group">
                    <label style="color: #475569; font-size: 11px; font-weight: 700;">Attach File 3 (PDF / Image)</label>
                    <input type="file" name="docs[2][file]" accept=".pdf,.jpg,.jpeg,.png" style="font-size: 11px; width: 100%;">
                </div>
            </div>

        </div>

        {{-- Submit Button --}}
        <div>
            <button type="submit" class="erp-button" style="background: #0f172a; color: #ffffff; border: none; border-radius: 10px; font-size: 14px; font-weight: 800; padding: 13px 28px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(15,23,42,0.12); transition: all 0.2s;">
                <span>📁 Upload Documents (3 Slots)</span>
            </button>
        </div>
    </form>
</section>

{{-- Uploaded Documents Table --}}
<section class="dashboard-panel" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 20px rgba(15,23,42,0.03); padding: 28px;">
    <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px;">
        <div>
            <h3 style="color: #0f172a; font-size: 18px; font-weight: 800; margin-bottom: 4px;">Uploaded Investor Documents Repository</h3>
            <p style="color: #64748b; font-size: 13px;">View PDF or Image documents directly in browser without mandatory file downloads.</p>
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
                    <th style="padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 800;">Inline Preview & Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 14px 12px;">
                            <strong style="color: #0f172a; font-size: 13px;">{{ $document->investor?->name }}</strong>
                            <small style="display: block; color: #64748b; font-size: 11px;">{{ $document->investor?->email }}</small>
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
                        <td style="padding: 14px 12px;">
                            <div style="display: flex; gap: 8px;">
                                <button type="button" onclick="previewDocModal('{{ route('investor.documents.preview', $document) }}', '{{ $document->title }}')" style="background: #0284c7; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer;">
                                    👁️ View PDF / Picture
                                </button>
                                <a href="{{ route('investor.documents.download', $document) }}" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; text-decoration: none; padding: 6px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                    Download
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="erp-empty" colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">No investor documents uploaded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $documents->links() }}
</section>

{{-- Inline Document Preview Modal --}}
<dialog id="docViewerModal" style="border: 1px solid #cbd5e1; border-radius: 16px; padding: 0; width: 90vw; max-width: 900px; background: #ffffff; color: #0f172a; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
    <div style="padding: 16px 20px; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0;">
        <h4 id="docModalTitle" style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">Document Preview</h4>
        <button type="button" onclick="document.getElementById('docViewerModal').close()" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; border-radius: 6px; padding: 6px 12px; cursor: pointer; font-weight: 700;">
            ✕ Close
        </button>
    </div>
    <div style="padding: 0; height: 75vh; background: #ffffff; overflow: hidden;">
        <iframe id="docFrame" style="width: 100%; height: 100%; border: none; background: #ffffff;"></iframe>
    </div>
</dialog>

@push('scripts')
<script>
function previewDocModal(url, title) {
    let modal = document.getElementById('docViewerModal');
    let frame = document.getElementById('docFrame');
    let titleElem = document.getElementById('docModalTitle');

    if (titleElem) titleElem.textContent = title || 'Document Preview';
    if (frame) frame.src = url;
    if (modal) modal.showModal();
}
</script>
@endpush
@endsection

