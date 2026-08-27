@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Connected Companies</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Company Directory</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Please correct the highlighted fields in the form.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
              <h4 class="mb-1 fw-bold"><i class="fas fa-building me-2 text-primary"></i>Connected Companies</h4>
              <p class="text-muted mb-0">Maintain company profile, contact and mailing details for TPO communication.</p>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                <i class="fas fa-plus me-1"></i>Add Company
              </button>
              <a href="{{ route('tpo.training-placement.mailbox.index') }}" class="btn btn-primary">
                <i class="fas fa-inbox me-1"></i>Open Mail Inbox
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-9">
              <label class="form-label fw-semibold mb-1">Search Company</label>
              <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search by company, email, contact or business nature">
            </div>
            <div class="col-md-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Search</button>
              @if(!empty($search))
              <a href="{{ route('tpo.training-placement.companies.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
              <span class="fw-bold">Company List</span>
              <span class="badge bg-secondary">Total: {{ $companies->count() }}</span>
            </div>
            <div class="card-body">
              @forelse($companies as $company)
              <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                  <div>
                    <h6 class="mb-1 fw-bold">{{ $company->company_name }}</h6>
                    <div class="small text-muted">Address: {{ $company->address ?: 'N/A' }}</div>
                    <div class="small text-muted">Primary Contact: {{ $company->primary_contact_name ?: 'N/A' }} | {{ $company->primary_contact_phone ?: 'N/A' }}</div>
                    <div class="small text-muted">Contact Email: {{ $company->primary_contact_email ?: 'N/A' }}</div>
                    <div class="small text-muted">Mailing Email: {{ $company->mailing_email ?: 'N/A' }}</div>
                    <div class="small text-muted">CC: {{ $company->mailing_cc ?: 'N/A' }}</div>
                    <div class="small text-muted">BCC: {{ $company->mailing_bcc ?: 'N/A' }}</div>
                    <div class="small text-muted">Business: {{ $company->nature_of_business ?: 'N/A' }}</div>
                    <div class="small text-muted">Notes: {{ $company->notes ?: 'N/A' }}</div>
                    <span class="badge {{ $company->is_active ? 'bg-success' : 'bg-secondary' }} mt-2">{{ $company->is_active ? 'Active' : 'Inactive' }}</span>
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editCompany{{ $company->id }}">Edit</button>
                    <form action="{{ route('tpo.training-placement.companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('Delete this company?')">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  </div>
                </div>

                <div class="collapse mt-3" id="editCompany{{ $company->id }}">
                  <form action="{{ route('tpo.training-placement.companies.update', $company->id) }}" method="POST" class="border rounded p-3">
                    @csrf
                    @method('PUT')
                    <div class="row g-2">
                      <div class="col-md-6"><input type="text" class="form-control" name="company_name" value="{{ $company->company_name }}" required></div>
                      <div class="col-md-6"><input type="email" class="form-control" name="mailing_email" value="{{ $company->mailing_email }}" required></div>
                      <div class="col-md-6"><input type="text" class="form-control" name="primary_contact_name" value="{{ $company->primary_contact_name }}" placeholder="Primary contact"></div>
                      <div class="col-md-6"><input type="text" class="form-control" name="primary_contact_phone" value="{{ $company->primary_contact_phone }}" placeholder="Phone"></div>
                      <div class="col-md-6"><input type="email" class="form-control" name="primary_contact_email" value="{{ $company->primary_contact_email }}" placeholder="Contact email"></div>
                      <div class="col-md-6"><input type="text" class="form-control" name="nature_of_business" value="{{ $company->nature_of_business }}" placeholder="Nature of business"></div>
                      <div class="col-12"><input type="text" class="form-control" name="mailing_cc" value="{{ $company->mailing_cc }}" placeholder="CC"></div>
                      <div class="col-12"><input type="text" class="form-control" name="mailing_bcc" value="{{ $company->mailing_bcc }}" placeholder="BCC"></div>
                      <div class="col-12"><textarea name="address" class="form-control" rows="2" placeholder="Address">{{ $company->address }}</textarea></div>
                      <div class="col-12"><textarea name="notes" class="form-control" rows="2" placeholder="Notes">{{ $company->notes }}</textarea></div>
                      <div class="col-12">
                        <div class="form-check">
                          <input type="checkbox" name="is_active" value="1" class="form-check-input" id="activeCompany{{ $company->id }}" {{ $company->is_active ? 'checked' : '' }}>
                          <label class="form-check-label" for="activeCompany{{ $company->id }}">Active</label>
                        </div>
                      </div>
                      <div class="col-12"><button class="btn btn-sm btn-primary">Save Changes</button></div>
                    </div>
                  </form>
                </div>
              </div>
              @empty
              <div class="alert alert-info mb-0">No connected companies yet.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCompanyModalLabel">Add Connected Company</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('tpo.training-placement.companies.store') }}" method="POST">
        @csrf
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <div class="mb-2">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" required>
            @error('company_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address') }}</textarea>
            @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">Primary Contact Name</label>
            <input type="text" name="primary_contact_name" class="form-control @error('primary_contact_name') is-invalid @enderror" value="{{ old('primary_contact_name') }}">
            @error('primary_contact_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <label class="form-label">Contact Phone</label>
              <input type="text" name="primary_contact_phone" class="form-control @error('primary_contact_phone') is-invalid @enderror" value="{{ old('primary_contact_phone') }}">
              @error('primary_contact_phone')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Email</label>
              <input type="email" name="primary_contact_email" class="form-control @error('primary_contact_email') is-invalid @enderror" value="{{ old('primary_contact_email') }}">
              @error('primary_contact_email')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label">Mailing Email</label>
            <input type="email" name="mailing_email" class="form-control @error('mailing_email') is-invalid @enderror" value="{{ old('mailing_email') }}" required>
            @error('mailing_email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">CC (comma separated)</label>
            <input type="text" name="mailing_cc" class="form-control @error('mailing_cc') is-invalid @enderror" value="{{ old('mailing_cc') }}" placeholder="hr@company.com, ops@company.com">
            @error('mailing_cc')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">BCC (comma separated)</label>
            <input type="text" name="mailing_bcc" class="form-control @error('mailing_bcc') is-invalid @enderror" value="{{ old('mailing_bcc') }}">
            @error('mailing_bcc')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">Nature of Business</label>
            <input type="text" name="nature_of_business" class="form-control @error('nature_of_business') is-invalid @enderror" value="{{ old('nature_of_business') }}">
            @error('nature_of_business')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-2">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
            @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-check mb-1">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActiveCompanyModal" {{ old('is_active', 1) ? 'checked' : '' }}>
            <label class="form-check-label" for="isActiveCompanyModal">Active</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success" type="submit">Save Company</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if ($errors->any())
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('addCompanyModal');
    if (!modalEl) {
      return;
    }

    var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
    modalInstance.show();
  });
</script>
@endif

@include('includes.footer')