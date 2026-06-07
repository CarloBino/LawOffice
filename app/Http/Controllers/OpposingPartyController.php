<?php

namespace App\Http\Controllers;

use App\Models\OpposingParty;
use App\Models\LegalCase;
use Illuminate\Http\Request;

class OpposingPartyController extends Controller
{
    public function index()
    {
        $parties = OpposingParty::with('case.client')
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->orderBy('created_at','desc')
            ->paginate(25);
        return view('opposing_parties.index', compact('parties'));
    }

    public function create()
    {
        $this->requireRole('admin', 'staff');
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with('client'))->orderBy('case_number')->get();
        return view('opposing_parties.create', compact('cases'));
    }

    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff');
        $data = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'opposing_party_name' => 'required|string|max:255',
            'opposing_counsel_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        $party = OpposingParty::create($data);
        $this->logActivity('Opposing party created', "Created opposing party {$party->opposing_party_name}.", $party);
        return redirect()->route('opposing-parties.show', $party->id)->with('success','Opposing party added.');
    }

    public function show(OpposingParty $opposingParty)
    {
        $opposingParty->load('case.client');
        $this->authorizeCaseAccess($opposingParty->case);
        return view('opposing_parties.show', ['party' => $opposingParty]);
    }

    public function edit(OpposingParty $opposingParty)
    {
        $this->requireRole('admin', 'staff');
        $opposingParty->load('case');
        $this->authorizeCaseAccess($opposingParty->case);
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with('client'))->orderBy('case_number')->get();
        return view('opposing_parties.edit', ['party' => $opposingParty, 'cases' => $cases]);
    }

    public function update(Request $request, OpposingParty $opposingParty)
    {
        $this->requireRole('admin', 'staff');
        $data = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'opposing_party_name' => 'required|string|max:255',
            'opposing_counsel_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);
        $opposingParty->load('case');
        $this->authorizeCaseAccess($opposingParty->case);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        $opposingParty->update($data);
        $this->logActivity('Opposing party updated', "Updated opposing party {$opposingParty->opposing_party_name}.", $opposingParty);
        return redirect()->route('opposing-parties.show', $opposingParty->id)->with('success','Opposing party updated.');
    }

    public function destroy(OpposingParty $opposingParty)
    {
        $this->requireRole('admin');
        $this->logActivity('Opposing party deleted', "Deleted opposing party {$opposingParty->opposing_party_name}.", $opposingParty);
        $opposingParty->delete();
        return redirect()->route('opposing-parties.index')->with('success','Opposing party removed.');
    }
}
