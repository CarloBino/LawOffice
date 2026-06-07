<?php

namespace App\Http\Controllers;

use App\Models\CaseAction;
use App\Models\LegalCase;
use Illuminate\Http\Request;

class CaseActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $actions = CaseAction::with('case.client')
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->paginate(25);
        return view('case_actions.index', compact('actions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireRole('admin', 'staff');
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with('client'))->orderBy('case_number')->get();
        return view('case_actions.create', compact('cases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff');
        $data = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'action_type' => 'required|string',
            'action_description' => 'nullable|string',
            'responsible_person' => 'nullable|string',
            'due_date' => 'nullable|date',
            'date_completed' => 'nullable|date',
            'action_status' => 'nullable|string',
        ]);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        $action = CaseAction::create($data);
        $this->logActivity('Case action created', "Created action {$action->action_type}.", $action);
        return redirect()->route('case-actions.show', $action->id)->with('success','Action created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CaseAction $caseAction)
    {
        $caseAction->load('case.client');
        $this->authorizeCaseAccess($caseAction->case);
        return view('case_actions.show', compact('caseAction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CaseAction $caseAction)
    {
        $this->requireRole('admin', 'staff');
        $caseAction->load('case');
        $this->authorizeCaseAccess($caseAction->case);
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with('client'))->orderBy('case_number')->get();
        return view('case_actions.edit', compact('caseAction','cases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CaseAction $caseAction)
    {
        $this->requireRole('admin', 'staff');
        $data = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'action_type' => 'required|string',
            'action_description' => 'nullable|string',
            'responsible_person' => 'nullable|string',
            'due_date' => 'nullable|date',
            'date_completed' => 'nullable|date',
            'action_status' => 'nullable|string',
        ]);
        $caseAction->load('case');
        $this->authorizeCaseAccess($caseAction->case);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        $caseAction->update($data);
        $this->logActivity('Case action updated', "Updated action {$caseAction->action_type}.", $caseAction);
        return redirect()->route('case-actions.show', $caseAction->id)->with('success','Action updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CaseAction $caseAction)
    {
        $this->requireRole('admin');
        $this->logActivity('Case action deleted', "Deleted action {$caseAction->action_type}.", $caseAction);
        $caseAction->delete();
        return redirect()->route('case-actions.index')->with('success','Action deleted.');
    }
}
