@extends('layouts.investor')
@section('title', 'Document Vault | Intern Estate')
@section('page-heading', 'Document Vault')
@section('content')
<section class="dashboard-panel" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 28px; color: #0f172a; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
    <div class="panel-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 4px; color: #0f172a;">My Document Vault & Deeds</h3>
            <p style="color: #64748b; font-size: 13px;">View PDF or picture documents directly in browser without downloading.</p>
        </div>
    </div>

    @forelse($documents as $document)
        <div class="investor-profile-row" style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div>
                <strong style="display: block; font-size: 15px; color: #0f172a; margin-bottom: 4px;">{{ $document->title }}</strong>
                <span style="color: #0f766e; background: #ccfbf1; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 6px;">{{ $document->doc_type }}</span>
                <span style="color: #64748b; font-size: 12px; margin-left: 8px;">· Issued: {{ $document->issued_at?->format('d M Y') ?? 'N/A' }}</span>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="previewInvestorDoc('{{ route('investor.documents.preview', $document) }}', '{{ $document->title }}')" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: #ffffff; border: none; padding: 9px 18px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(13,148,136,0.25);">
                    👁️ View PDF / Picture
                </button>
                @if($document->file_path)
                    <a class="erp-secondary" href="{{ route('investor.documents.download', $document) }}" style="background: #ffffff; color: #334155; border: 1px solid #cbd5e1; text-decoration: none; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center;">
                        Download
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="investor-empty-state" style="text-align: center; padding: 40px; color: #64748b;">
            <h4 style="color: #0f172a; font-weight: 700;">No documents available</h4>
            <p>Verified Deeds and Legal Contracts will appear here when issued.</p>
        </div>
    @endforelse
</section>

{{-- Inline Document Viewer Dialog Modal --}}
<dialog id="investorDocModal" style="border: 1px solid #cbd5e1; border-radius: 16px; padding: 0; width: 90vw; max-width: 950px; background: #ffffff; color: #0f172a; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
    <div style="padding: 16px 22px; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0;">
        <h4 id="investorDocTitle" style="margin: 0; font-size: 16px; font-weight: 800; color: #0f172a;">Document Preview</h4>
        <button type="button" onclick="document.getElementById('investorDocModal').close()" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; border-radius: 8px; padding: 6px 14px; cursor: pointer; font-weight: 700;">
            ✕ Close Viewer
        </button>
    </div>
    <div style="padding: 0; height: 78vh; background: #ffffff; overflow: hidden;">
        <iframe id="investorDocFrame" style="width: 100%; height: 100%; border: none; background: #ffffff;"></iframe>
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
