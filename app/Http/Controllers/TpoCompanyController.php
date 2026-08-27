<?php

namespace App\Http\Controllers;

use App\Models\TpoConnectedCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TpoCompanyController extends Controller
{
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));

    $companies = TpoConnectedCompany::query()
      ->when($search !== '', function ($query) use ($search) {
        $like = '%' . $search . '%';
        $query->where(function ($inner) use ($like) {
          $inner->where('company_name', 'like', $like)
            ->orWhere('primary_contact_name', 'like', $like)
            ->orWhere('primary_contact_email', 'like', $like)
            ->orWhere('mailing_email', 'like', $like)
            ->orWhere('nature_of_business', 'like', $like)
            ->orWhere('address', 'like', $like);
        });
      })
      ->latest()
      ->get();

    return view('tpo.training-placement.companies', compact('companies', 'search'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'company_name' => 'required|string|max:255',
      'address' => 'nullable|string',
      'primary_contact_name' => 'nullable|string|max:255',
      'primary_contact_phone' => 'nullable|string|max:50',
      'primary_contact_email' => 'nullable|email|max:255',
      'mailing_email' => 'required|email|max:255',
      'mailing_cc' => 'nullable|string',
      'mailing_bcc' => 'nullable|string',
      'nature_of_business' => 'nullable|string|max:255',
      'notes' => 'nullable|string',
      'is_active' => 'nullable|boolean',
    ]);

    TpoConnectedCompany::create([
      'company_name' => $validated['company_name'],
      'address' => $validated['address'] ?? null,
      'primary_contact_name' => $validated['primary_contact_name'] ?? null,
      'primary_contact_phone' => $validated['primary_contact_phone'] ?? null,
      'primary_contact_email' => $validated['primary_contact_email'] ?? null,
      'mailing_email' => $validated['mailing_email'],
      'mailing_cc' => $validated['mailing_cc'] ?? null,
      'mailing_bcc' => $validated['mailing_bcc'] ?? null,
      'nature_of_business' => $validated['nature_of_business'] ?? null,
      'notes' => $validated['notes'] ?? null,
      'is_active' => isset($validated['is_active']) ? 1 : 0,
      'created_by' => Auth::id(),
      'updated_by' => Auth::id(),
    ]);

    return back()->with('success', 'Connected company added successfully.');
  }

  public function update(Request $request, TpoConnectedCompany $company)
  {
    $validated = $request->validate([
      'company_name' => 'required|string|max:255',
      'address' => 'nullable|string',
      'primary_contact_name' => 'nullable|string|max:255',
      'primary_contact_phone' => 'nullable|string|max:50',
      'primary_contact_email' => 'nullable|email|max:255',
      'mailing_email' => 'required|email|max:255',
      'mailing_cc' => 'nullable|string',
      'mailing_bcc' => 'nullable|string',
      'nature_of_business' => 'nullable|string|max:255',
      'notes' => 'nullable|string',
      'is_active' => 'nullable|boolean',
    ]);

    $company->update([
      'company_name' => $validated['company_name'],
      'address' => $validated['address'] ?? null,
      'primary_contact_name' => $validated['primary_contact_name'] ?? null,
      'primary_contact_phone' => $validated['primary_contact_phone'] ?? null,
      'primary_contact_email' => $validated['primary_contact_email'] ?? null,
      'mailing_email' => $validated['mailing_email'],
      'mailing_cc' => $validated['mailing_cc'] ?? null,
      'mailing_bcc' => $validated['mailing_bcc'] ?? null,
      'nature_of_business' => $validated['nature_of_business'] ?? null,
      'notes' => $validated['notes'] ?? null,
      'is_active' => isset($validated['is_active']) ? 1 : 0,
      'updated_by' => Auth::id(),
    ]);

    return back()->with('success', 'Connected company updated successfully.');
  }

  public function destroy(TpoConnectedCompany $company)
  {
    $company->delete();

    return back()->with('success', 'Connected company removed successfully.');
  }
}
