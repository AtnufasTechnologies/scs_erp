<?php

namespace App\Http\Controllers\InternationalOffice;

use App\Http\Controllers\Controller;
use App\Models\InternationalOfficeInstitution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InstitutionController extends Controller
{
  public function index()
  {
    $institutions = InternationalOfficeInstitution::orderByDesc('id')->get();

    return view('international-office.institutions.index', [
      'institutions' => $institutions,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'institution_name' => 'required|string|max:255',
      'contact_person' => 'nullable|string|max:150',
      'contact_number' => 'nullable|string|max:100',
      'email' => 'nullable|email|max:255',
      'address' => 'nullable|string|max:1000',
      'has_mou' => 'nullable|boolean',
      'mou_document' => 'required_if:has_mou,1|nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
      'remarks' => 'nullable|string|max:1000',
    ]);

    $mouDocumentPath = null;
    if ($request->boolean('has_mou') && $request->hasFile('mou_document')) {
      $mouDocumentPath = $request->file('mou_document')->store('international-office/institutions/mou', 'public');
    }

    InternationalOfficeInstitution::create([
      'institution_name' => $validated['institution_name'],
      'contact_person' => $validated['contact_person'] ?? null,
      'contact_number' => $validated['contact_number'] ?? null,
      'email' => $validated['email'] ?? null,
      'address' => $validated['address'] ?? null,
      'has_mou' => $request->boolean('has_mou'),
      'mou_document_path' => $mouDocumentPath,
      'remarks' => $validated['remarks'] ?? null,
      'created_by_user_id' => Auth::id(),
    ]);

    return redirect()->route('international-office.institutions.index')->with('success', 'Institution information added successfully.');
  }

  public function update(Request $request, $id)
  {
    $institution = InternationalOfficeInstitution::findOrFail($id);

    $validated = $request->validate([
      'institution_name' => 'required|string|max:255',
      'contact_person' => 'nullable|string|max:150',
      'contact_number' => 'nullable|string|max:100',
      'email' => 'nullable|email|max:255',
      'address' => 'nullable|string|max:1000',
      'has_mou' => 'nullable|boolean',
      'mou_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
      'remarks' => 'nullable|string|max:1000',
    ]);

    $mouDocumentPath = $institution->mou_document_path;
    if ($request->boolean('has_mou') && $request->hasFile('mou_document')) {
      if ($mouDocumentPath) {
        Storage::disk('public')->delete($mouDocumentPath);
      }
      $mouDocumentPath = $request->file('mou_document')->store('international-office/institutions/mou', 'public');
    }

    if (!$request->boolean('has_mou') && $mouDocumentPath) {
      Storage::disk('public')->delete($mouDocumentPath);
      $mouDocumentPath = null;
    }

    $institution->update([
      'institution_name' => $validated['institution_name'],
      'contact_person' => $validated['contact_person'] ?? null,
      'contact_number' => $validated['contact_number'] ?? null,
      'email' => $validated['email'] ?? null,
      'address' => $validated['address'] ?? null,
      'has_mou' => $request->boolean('has_mou'),
      'mou_document_path' => $mouDocumentPath,
      'remarks' => $validated['remarks'] ?? null,
    ]);

    return redirect()->route('international-office.institutions.index')->with('success', 'Institution information updated successfully.');
  }

  public function destroy($id)
  {
    $institution = InternationalOfficeInstitution::findOrFail($id);

    if ($institution->mou_document_path) {
      Storage::disk('public')->delete($institution->mou_document_path);
    }

    $institution->delete();

    return redirect()->route('international-office.institutions.index')->with('success', 'Institution information deleted successfully.');
  }
}
