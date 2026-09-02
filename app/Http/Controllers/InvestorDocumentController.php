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
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'doc_type' => ['required', 'string', 'max:100'],
            'issued_at' => ['nullable', 'date'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        abort_unless(User::whereKey($data['user_id'])->where('role', 'investor')->exists(), 422);
        $data['file_path'] = $request->file('document')->store('investor-documents/'.$data['user_id'], 'local');
        unset($data['document']);

        InvestorDocument::create($data);

        return back()->with('success', 'Investor document uploaded securely.');
    }

    public function download(Request $request, InvestorDocument $document): Response
    {
        abort_unless($document->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->title.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
    }
}
