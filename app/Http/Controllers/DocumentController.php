<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'latest');
        $query = Document::with('case.client')->select('documents.*');
        $query->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)));

        $query->when($request->filled('document_type'), fn ($query) => $query->where('document_type', $request->query('document_type')));
        $query->when($request->filled('client_id'), fn ($query) => $query->whereHas('case', fn ($case) => $case->where('client_id', $request->query('client_id'))));
        $query->when($request->filled('case_id'), fn ($query) => $query->where('case_id', $request->query('case_id')));

        match ($sort) {
            'type' => $query->orderBy('document_type')->latest('documents.created_at'),
            'case' => $query->leftJoin('cases', 'cases.id', '=', 'documents.case_id')->orderBy('cases.case_number')->latest('documents.created_at'),
            'client' => $query->leftJoin('cases', 'cases.id', '=', 'documents.case_id')
                ->leftJoin('clients', 'clients.id', '=', 'cases.client_id')
                ->orderBy('clients.full_name')
                ->latest('documents.created_at'),
            default => $query->latest('documents.created_at'),
        };

        $documents = $query->paginate(25)->withQueryString();
        $clients = Client::query()
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('cases', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->orderBy('full_name')
            ->get();
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::query())
            ->orderBy('case_number')
            ->get();
        $types = Document::query()->whereNotNull('document_type')->distinct()->orderBy('document_type')->pluck('document_type');

        return view('documents.index', compact('documents', 'sort', 'clients', 'cases', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireRole('admin', 'staff');
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with('client'))->orderBy('case_number')->get();

        return view('documents.create', compact('cases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff');
        $data = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'document_name' => 'required|string|max:255',
            'document_type' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents');
            $data['file_path'] = $path;
        }

        $document = Document::create($data);
        $this->logActivity('Document uploaded', "Uploaded document {$document->document_name}.", $document);

        return redirect()->route('documents.show', $document->id)->with('success', 'Document created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $document->load('case.client');
        $this->authorizeCaseAccess($document->case);

        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        $this->requireRole('admin', 'staff');
        $document->load('case');
        $this->authorizeCaseAccess($document->case);
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with('client'))->orderBy('case_number')->get();

        return view('documents.edit', compact('document', 'cases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $this->requireRole('admin', 'staff');
        $data = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'document_name' => 'required|string|max:255',
            'document_type' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        $document->load('case');
        $this->authorizeCaseAccess($document->case);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        if ($request->hasFile('file')) {
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
            $path = $request->file('file')->store('documents');
            $data['file_path'] = $path;
        }

        $document->update($data);
        $this->logActivity('Document updated', "Updated document {$document->document_name}.", $document);

        return redirect()->route('documents.show', $document->id)->with('success', 'Document updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $this->requireRole('admin');
        $this->logActivity('Document deleted', "Deleted document {$document->document_name}.", $document);
        if ($document->file_path && Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document deleted.');
    }

    public function download(Document $document): StreamedResponse
    {
        $document->load('case');
        $this->authorizeCaseAccess($document->case);

        abort_unless($document->file_path && Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path, $document->document_name);
    }
}
