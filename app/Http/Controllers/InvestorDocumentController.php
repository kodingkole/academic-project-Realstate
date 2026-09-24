<?php

namespace App\Http\Controllers;

use App\Models\InvestorDocument;
use App\Models\InvestorBooking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvestorDocumentController extends Controller
{
    public function manage(): View
    {
        return view('admin.investor-documents', [
            'investors' => User::where('role', 'investor')->orderBy('name')->get(),
            'investorProjects' => InvestorBooking::with('project:id,title')
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->get()
                ->groupBy('user_id')
                ->map(fn ($bookings) => $bookings->pluck('project')->filter()->unique('id')->values()),
            'documents' => InvestorDocument::with(['investor', 'project'])->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'issued_at' => ['nullable', 'date'],
            // Support 3 documents at once
            'docs' => ['nullable', 'array'],
            'docs.*.title' => ['nullable', 'string', 'max:255'],
            'docs.*.doc_type' => ['nullable', 'string', 'max:100'],
            'docs.*.file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            // Fallback single document
            'title' => ['nullable', 'string', 'max:255'],
            'doc_type' => ['nullable', 'string', 'max:100'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        abort_unless(User::whereKey($validated['user_id'])->where('role', 'investor')->exists(), 422);

        if (!empty($validated['project_id'])) {
            abort_unless(
                InvestorBooking::where('user_id', $validated['user_id'])
                    ->where('project_id', $validated['project_id'])
                    ->whereNotIn('status', ['rejected', 'cancelled'])
                    ->exists(),
                422,
                'The selected investor does not have an active investment in this project.'
            );
        }

        $uploadedCount = 0;

        // Process batch docs array (up to 3 documents)
        if (!empty($validated['docs']) && is_array($validated['docs'])) {
            foreach ($validated['docs'] as $docItem) {
                if (isset($docItem['file']) && $docItem['file']->isValid()) {
                    $title = !empty($docItem['title']) ? $docItem['title'] : 'Legal Document';
                    $type = !empty($docItem['doc_type']) ? $docItem['doc_type'] : 'Legal Deed';
                    $filePath = $docItem['file']->store('investor-documents/'.$validated['user_id'], 'local');

                    InvestorDocument::create([
                        'user_id' => $validated['user_id'],
                        'project_id' => $validated['project_id'] ?? null,
                        'title' => $title,
                        'doc_type' => $type,
                        'issued_at' => $validated['issued_at'] ?? now()->toDateString(),
                        'file_path' => $filePath,
                    ]);
                    $uploadedCount++;
                }
            }
        }

        // Process single file fallback if provided
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('investor-documents/'.$validated['user_id'], 'local');
            InvestorDocument::create([
                'user_id' => $validated['user_id'],
                'project_id' => $validated['project_id'] ?? null,
                'title' => $validated['title'] ?? 'Investor Document',
                'doc_type' => $validated['doc_type'] ?? 'Legal Document',
                'issued_at' => $validated['issued_at'] ?? now()->toDateString(),
                'file_path' => $filePath,
            ]);
            $uploadedCount++;
        }

        if ($uploadedCount === 0) {
            return back()->withErrors(['document' => 'Please attach at least one valid file (PDF, JPG, PNG).']);
        }

        return back()->with('success', "{$uploadedCount} investor document(s) uploaded successfully.");
    }

    public function download(Request $request, InvestorDocument $document): Response
    {
        abort_unless($document->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->title.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    public function preview(Request $request, InvestorDocument $document): Response
    {
        abort_unless($document->user_id === $request->user()->id || $request->user()->role === 'admin', 403);

        if (!$document->file_path || !Storage::disk('local')->exists($document->file_path)) {
            $html = "<html><body style='font-family:Arial,sans-serif;background:#f1f5f9;color:#0f172a;padding:40px;'>
                <div style='background:#fff;border:1px solid #cbd5e1;border-radius:16px;padding:30px;max-width:650px;margin:0 auto;'>
                    <h2 style='margin:0 0 12px;'>".e($document->title)."</h2>
                    <p style='color:#b91c1c;font-weight:700;'>The uploaded file is unavailable. Please upload this document again.</p>
                    <p>Document type: <strong>".e($document->doc_type)."</strong></p>
                    <p>Investor: <strong>".e($document->investor?->name)."</strong> (".e($document->investor?->email).")</p>
                </div>
            </body></html>";
            return response($html, 404, ['Content-Type' => 'text/html']);
        }

        $fullPath = Storage::disk('local')->path($document->file_path);
        $mime = Storage::disk('local')->mimeType($document->file_path) ?? 'application/pdf';

        if (str_starts_with($mime, 'image/')) {
            if ($request->boolean('raw')) {
                return response()->file($fullPath, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline; filename="'.basename($fullPath).'"',
                ]);
            }

            $imageUrl = e($request->fullUrlWithQuery(['raw' => 1]));
            $imageTitle = e($document->title);
            $html = "<!doctype html><html><head><meta name='viewport' content='width=device-width,initial-scale=1'><style>
                *{box-sizing:border-box}html,body{width:100%;height:100%;margin:0;background:#f1f5f9}
                body{display:grid;place-items:center;overflow:auto;padding:24px}
                img{display:block;width:auto;height:auto;max-width:none;max-height:none;object-fit:contain;box-shadow:0 8px 28px rgba(15,23,42,.18)}
                </style></head><body><img id='document-image' src='{$imageUrl}' alt='{$imageTitle}'>
                <script>
                (()=>{const image=document.getElementById('document-image');const fit=()=>{if(!image.naturalWidth||!image.naturalHeight)return;const scale=Math.min(2.2,(innerWidth*.85)/image.naturalWidth,(innerHeight*.85)/image.naturalHeight);image.style.width=Math.round(image.naturalWidth*scale)+'px';image.style.height=Math.round(image.naturalHeight*scale)+'px'};image.addEventListener('load',fit);if(image.complete)fit();addEventListener('resize',fit)})();
                </script></body></html>";

            return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($fullPath).'"'
        ]);
    }
}
