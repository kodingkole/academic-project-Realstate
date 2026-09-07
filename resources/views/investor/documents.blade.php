@extends('layouts.investor')
@section('title', 'Document Vault | Intern Estate')
@section('page-heading', 'Document Vault')
@section('content')
<section class="dashboard-panel investor-documents-panel">
    <div class="panel-header investor-documents-header">
        <div>
            <span class="investor-documents-kicker">YOUR SECURE RECORDS</span>
            <h3>My Document Vault &amp; Deeds</h3>
            <p>Open PDF or image documents directly in your browser, or download a copy when needed.</p>
        </div>
    </div>

    <div class="investor-document-list">
    @forelse($documents as $document)
        <article class="investor-document-card">
            <div class="investor-document-details">
                <h4>{{ $document->title }}</h4>
                <span style="color: #0f766e; background: #ccfbf1; font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 6px;">{{ $document->doc_type }}</span>
                <span style="color: #64748b; font-size: 12px; margin-left: 8px;">· Issued: {{ $document->issued_at?->format('d M Y') ?? 'N/A' }}</span>
            </div>
            
            <div class="investor-document-actions">
                <button type="button" class="investor-document-preview" onclick="previewInvestorDoc('{{ route('investor.documents.preview', $document) }}', '{{ $document->title }}')">
                    View document
                </button>
                @if($document->file_path)
                    <a class="investor-document-download" href="{{ route('investor.documents.download', $document) }}">
                        Download
                    </a>
                @endif
            </div>
        </article>
    @empty
        <div class="investor-empty-state" style="text-align: center; padding: 40px; color: #64748b;">
            <h4 style="color: #0f172a; font-weight: 700;">No documents available</h4>
            <p>Verified Deeds and Legal Contracts will appear here when issued.</p>
        </div>
    @endforelse
    </div>
</section>

{{-- Inline Fullpage Document Viewer Dialog Modal --}}
<dialog id="investorDocModal" style="border: none; border-radius: 0; padding: 0; width: 100vw; height: 100vh; max-width: 100vw; max-height: 100vh; margin: 0; background: #ffffff; color: #0f172a; position: fixed; inset: 0; z-index: 99999;">
    <div style="padding: 12px 24px; height: 56px; box-sizing: border-box; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <h4 id="investorDocTitle" style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">Document Preview</h4>
        </div>
        <button type="button" onclick="document.getElementById('investorDocModal').close()" style="background: #0f172a; border: none; color: #ffffff; border-radius: 8px; padding: 8px 18px; cursor: pointer; font-weight: 800; font-size: 13px; box-shadow: 0 2px 6px rgba(15,23,42,0.15);">
            Close Fullscreen Viewer
        </button>
    </div>
    <div style="padding: 0; height: calc(100vh - 56px); width: 100vw; background: #525659; overflow: hidden;">
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
