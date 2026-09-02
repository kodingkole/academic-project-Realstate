@extends('layouts.investor')
@section('title', 'Document Vault | Intern Estate')
@section('page-heading', 'Document Vault')
@section('content')
<section class="dashboard-panel"><div class="panel-header"><div><h3>My documents</h3><p>Contracts, deeds and payment receipts</p></div></div>
@forelse($documents as $document)<div class="investor-profile-row"><div><strong>{{ $document->title }}</strong><span>{{ $document->doc_type }} · {{ $document->issued_at?->format('d M Y') }}</span></div>@if($document->file_path)<a class="erp-secondary" href="{{ route('investor.documents.download', $document) }}">Download</a>@endif</div>
@empty<div class="investor-empty-state"><h4>No documents available</h4><p>Verified documents will appear here when issued.</p></div>@endforelse</section>
@endsection
