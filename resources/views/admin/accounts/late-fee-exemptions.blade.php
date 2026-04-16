@include('includes.header')
@include('admin.accounts.sidebar')

<h3><span class="text-uppercase">Late Fee Exemptions Management</span></h3>



@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Success!</strong> {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Error!</strong>
  <ul>
    @foreach($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-3" id="exemptionTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button" role="tab">
      <i class="fas fa-users"></i> Students List
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="exemptions-tab" data-bs-toggle="tab" data-bs-target="#exemptions" type="button" role="tab">
      <i class="fas fa-list"></i> Exemptions List
    </button>
  </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="exemptionTabsContent">

  <!-- Students Tab -->
  <div class="tab-pane fade show active" id="students" role="tabpanel">
    <div class="card">
      <div class="card-header">
        <form action="{{ url('erp/admin/accounts/late-fee-exemptions') }}" method="GET" class="row g-3">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by Roll No or Name" value="{{ request('search') }}">
          </div>
          <div class="col-md-3">
            <select name="batch_filter" class="form-select">
              <option value="">-- All Batches --</option>
              @php
              $batches = App\Models\BatchMaster::orderBy('batch_name', 'desc')->get();
              @endphp
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}" {{ request('batch_filter') == $batch->id ? 'selected' : '' }}>
                {{ $batch->batch_name }}
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
          </div>
          <div class="col-md-2">
            <a href="{{ url('erp/admin/accounts/late-fee-exemptions') }}" class="btn btn-secondary"><i class="fa fa-refresh"></i> Reset</a>
          </div>
        </form>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Roll No</th>
              <th>Student Name</th>
              <th>Batch</th>
              <th>Program</th>
              <th>Year</th>
              <th>Exemption Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($students as $index => $student)
            <tr>
              <td>{{ ($students->currentPage() - 1) * $students->perPage() + $index + 1 }}</td>
              <td>{{ $student->roll_no }}</td>
              <td>{{ $student->first_name }} {{ $student->last_name }}</td>
              <td>{{ $student->batchmaster->batch_name ?? 'N/A' }}</td>
              <td>{{ $student->programgroup->program_code ?? 'N/A' }}</td>
              <td>{{ $student->current_year }}</td>
              <td>
                @php
                $exemptionData = $exemptionCounts->get($student->id);
                @endphp
                @if($exemptionData)
                @if($exemptionData->has_blanket)
                <span class="badge bg-warning text-dark">Blanket Exemption</span>
                @else
                <span class="badge bg-info">{{ $exemptionData->count }} Exemption(s)</span>
                @endif
                @else
                <span class="badge bg-secondary">No Exemption</span>
                @endif
              </td>
              <td>
                <button class="btn btn-sm btn-primary"
                  data-roll-no="{{ $student->roll_no }}"
                  data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                  onclick="openExemptionModal(this.dataset.rollNo, this.dataset.studentName)">
                  <i class="fa fa-plus"></i> Grant
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="8" class="text-center">No students found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        {{ $students->appends(request()->query())->links() }}
      </div>
    </div>
  </div>

  <!-- Exemptions Tab -->
  <div class="tab-pane fade" id="exemptions" role="tabpanel">
    <div class="card">
      <div class="card-body">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Student Roll No</th>
              <th>Student Name</th>
              <th>Exemption Type</th>
              <th>Fee Structure</th>
              <th>Fixed Late Fee (₹)</th>
              <th>Reason</th>
              <th>Approved By</th>
              <th>Approved Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($exemptions as $index => $exemption)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $exemption->student->roll_no ?? 'N/A' }}</td>
              <td>{{ ($exemption->student->first_name ?? '') . ' ' . ($exemption->student->last_name ?? '') }}</td>
              <td>
                @if(is_null($exemption->fee_structure_id))
                <span class="badge bg-warning text-dark">Blanket Exemption (All Fees)</span>
                @else
                <span class="badge bg-info">Specific Fee Structure</span>
                @endif
              </td>
              <td>{{ $exemption->feeStructure->quarter_title ?? 'All Fees' }}</td>
              <td>
                @if(!is_null($exemption->fixed_late_fee))
                ₹{{ number_format($exemption->fixed_late_fee, 2) }}
                @else
                <span class="text-muted">N/A</span>
                @endif
              </td>
              <td>{{ $exemption->reason }}</td>
              <td>{{ $exemption->approver->name ?? 'N/A' }}</td>
              <td>{{ $exemption->approved_at ? $exemption->approved_at->format('d-M-Y') : 'N/A' }}</td>
              <td>
                @if($exemption->is_active)
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Revoked</span>
                @endif
              </td>
              <td>
                @if($exemption->is_active)
                <form action="{{ url('erp/admin/accounts/late-fee-exemption/' . $exemption->id . '/revoke') }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to revoke this exemption?');">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fa fa-ban"></i> Revoke
                  </button>
                </form>
                @else
                <span class="text-muted">Revoked</span>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="text-center">No exemptions found</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-3">
      {{ $exemptions->links() }}
    </div>
  </div>

</div>

<!-- Add Exemption Modal -->
<div class="modal fade" id="addExemptionModal" tabindex="-1" aria-labelledby="addExemptionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExemptionModalLabel">Grant Late Fee Exemption</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ url('erp/admin/accounts/late-fee-exemption/grant') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="roll_no" class="form-label">Student <span class="text-danger">*</span></label>
            <input type="text" id="student_display" class="form-control" readonly>
            <input type="hidden" name="roll_no" id="roll_no" required>
          </div>

          <div class="mb-3">
            <label for="fee_structure_id" class="form-label">Select Unpaid Fee Structures <span class="text-danger">*</span></label>
            <div id="unpaid_fees_loading" class="text-center" style="display:none;">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              Loading unpaid fees...
            </div>
            <select name="fee_structure_id" id="fee_structure_id" class="form-select">
            </select>

          </div>

          <div class="mb-3">
            <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
            <textarea name="reason" id="reason" class="form-control" rows="4" required maxlength="500" placeholder="Enter the reason for granting this exemption..."></textarea>
            <small class="form-text text-muted">Max 500 characters</small>
          </div>
          <div class="mb-3">
            <label for="fixed_late_fee" class="form-label">Fixed Late Fee Amount (₹)</label>
            <input type="number" name="fixed_late_fee" id="fixed_late_fee" class="form-control" min="0" step="0.01" placeholder="Enter fixed late fee amount (optional)">
            <small class="form-text text-muted">Leave blank if not applicable.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Grant Exemption</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="{{asset('admin/js/BsMultiSelect.min.js')}}"></script>
<script>
  // Setup CSRF token for AJAX requests
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // Function to load unpaid fees for a student
  function loadUnpaidFees(rollNo) {
    const feeStructureSelect = $('#fee_structure_id');
    const loadingDiv = $('#unpaid_fees_loading');

    console.log('Loading unpaid fees for roll number:', rollNo);

    loadingDiv.show();
    feeStructureSelect.empty();
    feeStructureSelect.prop('disabled', true);

    // Use absolute URL to avoid issues with query parameters
    const baseUrl = window.location.origin;
    const url = baseUrl + '/erp/api/students/' + rollNo + '/unpaid-fees';
    console.log('API URL:', url);

    $.ajax({
      url: url,
      method: 'GET',
      success: function(data) {
        console.log('Unpaid fees data:', data);
        console.log('Data type:', typeof data);
        console.log('Is array:', Array.isArray(data));

        loadingDiv.hide();
        feeStructureSelect.prop('disabled', false);

        if (!data || data.length === 0) {
          feeStructureSelect.append('<option value="" disabled>No unpaid fees found</option>');
        } else {
          data.forEach(function(fs) {
            console.log('Processing fee structure:', fs);

            // Handle both direct fee structure objects and nested feesinfo
            const feeId = fs.fee_structure_id || fs.id;
            const quarterTitle = fs.fee_structure_name || fs.quarter_title;
            const year = fs.year || fs.std_current_year;
            const amount = fs.base_amount || fs.total_amount || fs.amount || 0;
            const lateDays = fs.late_days || 0;
            const lateFee = fs.late_fee || 0;

            console.log('Extracted values:', {
              feeId,
              quarterTitle,
              year,
              amount,
              lateDays,
              lateFee
            });

            let displayText = quarterTitle + ' - Year ' + year + ' - ₹' + amount;

            if (fs.due_date) {
              const dueDate = new Date(fs.due_date).toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
              });
              displayText += ' | Due: ' + dueDate;
            }

            if (lateDays > 0) {
              displayText += ' | ' + lateDays + ' days late (₹' + lateFee + ')';
            }

            console.log('Final displayText:', displayText);

            feeStructureSelect.append(
              '<option value="' + feeId + '">' + displayText + '</option>'
            );
          });
        }
      },
      error: function(xhr, status, error) {
        console.error('Error loading unpaid fees:', xhr.responseText);
        console.error('Status:', status);
        console.error('Error:', error);
        loadingDiv.hide();
        feeStructureSelect.prop('disabled', false);
        alert('Failed to load unpaid fees. Check console for details.');
      }
    });
  }

  // Function to open modal with pre-filled student data
  function openExemptionModal(rollNo, studentName) {
    // Set student info
    $('#roll_no').val(rollNo);
    $('#student_display').val(rollNo + ' - ' + studentName);

    // Reset form
    $('#fee_structure_id').empty();
    $('#reason').val('');

    // Load unpaid fees
    loadUnpaidFees(rollNo);

    // Show modal
    $('#addExemptionModal').modal('show');
  }
</script>

@include('includes.footer')