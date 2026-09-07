<?php

namespace App\Http\Controllers;

use App\Models\InvestorDocument;
use App\Models\Project;
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
            'projects' => Project::orderBy('title')->get(),
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
            $html = "<html><body style='font-family:sans-serif;background:#0f172a;color:#fff;padding:40px;text-align:center;'>
                <div style='background:#1e293b;border:1px solid #334155;border-radius:16px;padding:30px;max-width:550px;margin:0 auto;'>
                    <h2 style='color:#38bdf8;margin-bottom:10px;'>".e($document->title)."</h2>
                    <p style='color:#94a3b8;'>Document Type: <strong style='color:#fff;'>".e($document->doc_type)."</strong></p>
                    <p style='color:#94a3b8;'>Investor: <strong>".e($document->investor?->name)."</strong> (".e($document->investor?->email).")</p>
                    <p style='color:#94a3b8;'>Issue Date: <strong>".($document->issued_at?->format('d M Y') ?? 'N/A')."</strong></p>
                    <hr style='border-color:#334155;margin:20px 0;'>
                    <span style='background:rgba(16,185,129,0.2);color:#34d399;padding:6px 14px;border-radius:999px;font-weight:700;font-size:12px;'>Official Vetted Copy</span>
                </div>
            </body></html>";
            return response($html, 200, ['Content-Type' => 'text/html']);
        }

        $fullPath = Storage::disk('local')->path($document->file_path);
        $mime = Storage::disk('local')->mimeType($document->file_path) ?? 'application/pdf';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($fullPath).'"'
        ]);
    }
}
