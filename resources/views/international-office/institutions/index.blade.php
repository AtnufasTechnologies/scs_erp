@include('includes.header')
@include('international-office.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Institution MoU Registry</h4>
      <small class="text-muted">Simple entry for institution information and MoU upload.</small>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
      <h6 class="mb-0">Add Institution</h6>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('international-office.institutions.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Institution Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="institution_name" value="{{ old('institution_name') }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact Person</label>
            <input type="text" class="form-control" name="contact_person" value="{{ old('contact_person') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact Number</label>
            <input type="text" class="form-control" name="contact_number" value="{{ old('contact_number') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
          </div>
          <div class="col-md-8">
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address" rows="2" maxlength="1000">{{ old('address') }}</textarea>
          </div>

          <div class="col-md-3">
            <label class="form-label">Has MoU?</label>
            <select class="form-select" name="has_mou" id="has_mou">
              <option value="1" {{ old('has_mou', '1') === '1' ? 'selected' : '' }}>Yes</option>
              <option value="0" {{ old('has_mou') === '0' ? 'selected' : '' }}>No</option>
            </select>
          </div>
          <div class="col-md-9" id="mou_upload_wrap">
            <label class="form-label">MoU Document Upload</label>
            <input type="file" class="form-control" name="mou_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <small class="text-muted">Required when Has MoU is Yes.</small>
          </div>

          <div class="col-md-12">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" rows="2" maxlength="1000">{{ old('remarks') }}</textarea>
          </div>
        </div>

        <div class="text-end mt-3">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Institution</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
      <h6 class="mb-0">Institution List</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Institution</th>
              <th>Contact</th>
              <th>Email</th>
              <th>Has MoU</th>
              <th>MoU File</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($institutions as $item)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <strong>{{ $item->institution_name }}</strong>
                @if($item->address)
                <div class="small text-muted">{{ $item->address }}</div>
                @endif
              </td>
              <td>
                <div>{{ $item->contact_person ?: '-' }}</div>
                <div class="small text-muted">{{ $item->contact_number ?: '-' }}</div>
              </td>
              <td>{{ $item->email ?: '-' }}</td>
              <td>
                <span class="badge {{ $item->has_mou ? 'bg-success' : 'bg-secondary' }}">{{ $item->has_mou ? 'Yes' : 'No' }}</span>
              </td>
              <td>
                @if($item->mou_document_path)
                <a href="{{ asset('storage/' . $item->mou_document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                @else
                -
                @endif
              </td>
              <td class="d-flex gap-2">
                <button class="btn btn-sm btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#editInstitutionModal{{ $item->id }}">Edit</button>
                <form method="POST" action="{{ route('international-office.institutions.destroy', $item->id) }}" onsubmit="return confirm('Delete this institution entry?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="editInstitutionModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Institution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="{{ route('international-office.institutions.update', $item->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Institution Name <span class="text-danger">*</span></label>
                          <input type="text" class="form-control" name="institution_name" value="{{ $item->institution_name }}" required>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Contact Person</label>
                          <input type="text" class="form-control" name="contact_person" value="{{ $item->contact_person }}">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Contact Number</label>
                          <input type="text" class="form-control" name="contact_number" value="{{ $item->contact_number }}">
                        </div>

                        <div class="col-md-4">
                          <label class="form-label">Email</label>
                          <input type="email" class="form-control" name="email" value="{{ $item->email }}">
                        </div>
                        <div class="col-md-8">
                          <label class="form-label">Address</label>
                          <textarea class="form-control" name="address" rows="2" maxlength="1000">{{ $item->address }}</textarea>
                        </div>

                        <div class="col-md-3">
                          <label class="form-label">Has MoU?</label>
                          <select class="form-select" name="has_mou">
                            <option value="1" {{ $item->has_mou ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !$item->has_mou ? 'selected' : '' }}>No</option>
                          </select>
                        </div>
                        <div class="col-md-9">
                          <label class="form-label">Replace MoU File</label>
                          <input type="file" class="form-control" name="mou_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                          @if($item->mou_document_path)
                          <small class="text-muted">Current file available in list view.</small>
                          @endif
                        </div>

                        <div class="col-md-12">
                          <label class="form-label">Remarks</label>
                          <textarea class="form-control" name="remarks" rows="2" maxlength="1000">{{ $item->remarks }}</textarea>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted">No institution records found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  (function() {
    const hasMouEl = document.getElementById('has_mou');
    const mouUploadWrap = document.getElementById('mou_upload_wrap');

    function updateMouUploadVisibility() {
      if (!hasMouEl || !mouUploadWrap) {
        return;
      }
      mouUploadWrap.style.display = hasMouEl.value === '1' ? '' : 'none';
    }

    if (hasMouEl) {
      hasMouEl.addEventListener('change', updateMouUploadVisibility);
      updateMouUploadVisibility();
    }
  })();
</script>

@include('includes.footer')