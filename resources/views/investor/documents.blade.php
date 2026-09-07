@extends('layouts.investor')
@section('title', 'Document Vault | Intern Estate')
@section('page-heading', 'Document Vault')
@section('content')
<section class="dashboard-panel" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(148, 163, 184, 0.15); border-radius: 20px; padding: 28px; color: #fff;">
    <div class="panel-header" style="border-bottom: 1px solid rgba(148, 163, 184, 0.15); padding-bottom: 16px; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 4px;">My Document Vault & Deeds</h3>
            <p style="color: #94a3b8; font-size: 13px;">View PDF or picture documents directly in browser without downloading.</p>
        </div>
    </div>

    @forelse($documents as $document)
        <div class="investor-profile-row" style="display: flex; align-items: center; justify-content: space-between; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 12px; padding: 16px 20px; margin-bottom: 14px;">
            <div>
                <strong style="display: block; font-size: 15px; color: #fff; margin-bottom: 4px;">{{ $document->title }}</strong>
                <span style="color: #38bdf8; font-size: 12px; font-weight: 700;">{{ $document->doc_type }}</span>
                <span style="color: #64748b; font-size: 12px; margin-left: 8px;">· Issued: {{ $document->issued_at?->format('d M Y') ?? 'N/A' }}</span>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="previewInvestorDoc('{{ route('investor.documents.preview', $document) }}', '{{ $document->title }}')" style="background: linear-gradient(135deg, #0077b6 0%, #00b4d8 100%); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    👁️ View PDF / Picture
                </button>
                @if($document->file_path)
                    <a class="erp-secondary" href="{{ route('investor.documents.download', $document) }}" style="background: rgba(255,255,255,0.1); color: #cbd5e1; text-decoration: none; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center;">
                        Download
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="investor-empty-state" style="text-align: center; padding: 40px; color: #94a3b8;">
            <h4>No documents available</h4>
            <p>Verified Deeds and Legal Contracts will appear here when issued.</p>
        </div>
    @endforelse
</section>

{{-- Inline Document Viewer Dialog Modal --}}
<dialog id="investorDocModal" style="border: none; border-radius: 16px; padding: 0; width: 90vw; max-width: 950px; background: #0f172a; color: #fff; box-shadow: 0 25px 50px rgba(0,0,0,0.6);">
    <div style="padding: 16px 22px; background: #1e293b; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155;">
        <h4 id="investorDocTitle" style="margin: 0; font-size: 16px; font-weight: 800; color: #38bdf8;">Document Preview</h4>
        <button type="button" onclick="document.getElementById('investorDocModal').close()" style="background: rgba(255,255,255,0.1); border: none; color: #fff; border-radius: 8px; padding: 6px 14px; cursor: pointer; font-weight: 700;">
            ✕ Close Viewer
        </button>
    </div>
    <div style="padding: 0; height: 78vh; background: #0f172a; overflow: hidden;">
        <iframe id="investorDocFrame" style="width: 100%; height: 100%; border: none;"></iframe>
    </div>
</dialog>

@push('scripts')
<script>
function previewInvestorDoc(url, title) {
    let modal = document.getElementById('investorDocModal');
    let frame = document.getElementById('investorDocFrame');
    let titleElem = document.getElementById('investorDocTitle');

    if (titleElem) titleElem.textContent = title || 'Document Preview';
    if (frame) frame.src = url;
    if (modal) modal.showModal();
}
</script>
@endpush
@endsection
