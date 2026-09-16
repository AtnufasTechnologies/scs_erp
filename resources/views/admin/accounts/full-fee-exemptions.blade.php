@include('includes.header')
@include('admin.accounts.sidebar')

<h3><span class="text-uppercase">Full Course Fee Exemptions</span></h3>

<div class="mb-3 d-flex gap-2">
  <a href="{{ route('full.fee.exemptions.history') }}" class="btn btn-outline-primary btn-sm">
    <i class="fa fa-history me-1"></i>Full Exemption History
  </a>
  <a href="{{ route('quarter.fee.exemptions.history') }}" class="btn btn-outline-info btn-sm">
    <i class="fa fa-list-alt me-1"></i>Quarterly Exemption History
  </a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger">
  <ul class="mb-0">
    @foreach($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div class="card mb-3">
  <div class="card-body">
    <form action="{{ url('erp/admin/accounts/full-fee-exemptions') }}" method="GET" class="row g-2 align-items-end">
      <div class="col-lg-4">
        <label class="form-label">Search Student</label>
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Roll no or student name">
      </div>
      <div class="col-lg-3">
        <label class="form-label">Batch</label>
        <select name="batch_filter" class="form-select">
          <option value="">All Batches</option>
          @foreach($batches as $batch)
          <option value="{{ $batch->id }}" {{ (string)request('batch_filter') === (string)$batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-2">
        <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search me-1"></i>Filter</button>
      </div>
      <div class="col-lg-2">
        <a href="{{ url('erp/admin/accounts/full-fee-exemptions') }}" class="btn btn-secondary w-100"><i class="fa fa-refresh me-1"></i>Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-light">
    <strong>Student List</strong>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-hover mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Batch</th>
            <th>Current Year</th>
            <th>Academic Pathway</th>
            <th>Degree Track</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($students as $index => $student)
          @php
          $fullExemption = $activeExemptions->get($student->id);
          $studentQuarterExemptions = $activeQuarterExemptions->get($student->id, collect());
          $quarterExemptCount = $studentQuarterExemptions->count();
          $quarterExemptedFeeIds = $studentQuarterExemptions->pluck('fee_structure_id')->implode(',');
          @endphp
          <tr>
            <td>{{ $students->firstItem() + $index }}</td>
            <td class="text-uppercase">{{ $student->roll_no }}</td>
            <td>{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}</td>
            <td>{{ $student->batchmaster->batch_name ?? 'N/A' }}</td>
            <td>{{ $student->current_year ?? 'N/A' }}</td>
            <td>{{ $student->academicpathway->name ?? 'N/A' }}</td>
            <td>{{ $student->degreetrack->name ?? 'N/A' }}</td>
            <td>
              @if($fullExemption)
              <span class="badge bg-success">Exempted</span>
              @else
              <span class="badge bg-secondary">Not Exempted</span>
              @endif
              @if($quarterExemptCount > 0)
              <span class="badge bg-info text-dark">{{ $quarterExemptCount }} Quarterly</span>
              @endif
            </td>
            <td>
              <div class="d-flex gap-2">
                <button
                  type="button"
                  class="btn btn-sm {{ $fullExemption ? 'btn-warning' : 'btn-primary' }}"
                  data-bs-toggle="modal"
                  data-bs-target="#fullFeeExemptionModal"
                  data-student-id="{{ $student->id }}"
                  data-roll-no="{{ $student->roll_no }}"
                  data-student-name="{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}"
                  data-reason="{{ $fullExemption->reason ?? '' }}">
                  <i class="fa {{ $fullExemption ? 'fa-edit' : 'fa-shield-alt' }} me-1"></i>{{ $fullExemption ? 'Update Full' : 'Grant Full' }}
                </button>

                <button
                  type="button"
                  class="btn btn-sm btn-outline-primary"
                  data-bs-toggle="modal"
                  data-bs-target="#quarterFeeExemptionModal"
                  data-student-id="{{ $student->id }}"
                  data-roll-no="{{ $student->roll_no }}"
                  data-student-name="{{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}"
                  data-exempted-fee-ids="{{ $quarterExemptedFeeIds }}">
                  <i class="fa fa-list-alt me-1"></i>Quarterly
                </button>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="9" class="text-center">No students found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    {{ $students->links('pagination::bootstrap-5') }}
  </div>
</div>

<div class="modal fade" id="fullFeeExemptionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Grant / Update Full Course Fee Exemption</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ url('erp/admin/accounts/full-fee-exemption/grant') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Roll No</label>
            <input type="text" name="roll_no" id="exemptionRollNo" class="form-control text-uppercase" readonly required>
          </div>
          <div class="mb-3">
            <label class="form-label">Student Name</label>
            <input type="text" id="exemptionStudentName" class="form-control" readonly>
          </div>
          <div>
            <label class="form-label">Special Grounds / Reason</label>
            <textarea name="reason" id="exemptionReason" rows="4" class="form-control" required maxlength="500" placeholder="Provide approval note for full course fee exemption"></textarea>
          </div>

          <hr>
          <div>
            <label class="form-label mb-2"><strong>Fees Being Exempted (Breakdown)</strong></label>
            <div id="fullFeeBreakdownWrap" class="border rounded p-2">
              <div class="text-muted">Select a student to load fee breakdown.</div>
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2 justify-content-end">
              <span class="badge bg-secondary" id="fullFeeCourseTotal">Course Total: Rs 0.00</span>
              <span class="badge bg-info text-dark" id="fullFeePaidTotal">Already Paid: Rs 0.00</span>
              <span class="badge bg-dark" id="fullFeeBreakdownTotal">To Be Exempted: Rs 0.00</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Exemption</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="quarterFeeExemptionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Grant Quarterly Fee Exemption</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ url('erp/admin/accounts/quarter-fee-exemption/grant') }}" method="POST">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="roll_no" id="quarterExemptionRollNo" required>
          <div class="mb-2"><strong id="quarterExemptionStudentName"></strong></div>
          <div class="mb-3 text-muted small">Select one or multiple quarterly fee structures to exempt.</div>
          <div id="quarterFeeStructureList" class="border rounded p-2" style="max-height:260px; overflow:auto;">
            <div class="text-muted">Loading fee structures...</div>
          </div>
          <div class="mt-3">
            <label class="form-label">Special Grounds / Reason</label>
            <textarea name="reason" id="quarterExemptionReason" rows="3" class="form-control" required maxlength="500" placeholder="Provide approval note for quarterly fee exemption"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Quarterly Exemption</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('fullFeeExemptionModal');
    const quarterModal = document.getElementById('quarterFeeExemptionModal');
    if (!modal) {
      return;
    }

    modal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      if (!button) {
        return;
      }

      const studentId = button.getAttribute('data-student-id') || '';
      document.getElementById('exemptionRollNo').value = button.getAttribute('data-roll-no') || '';
      document.getElementById('exemptionStudentName').value = button.getAttribute('data-student-name') || '';
      document.getElementById('exemptionReason').value = button.getAttribute('data-reason') || '';

      const wrap = document.getElementById('fullFeeBreakdownWrap');
      const totalEl = document.getElementById('fullFeeBreakdownTotal');
      const courseTotalEl = document.getElementById('fullFeeCourseTotal');
      const paidTotalEl = document.getElementById('fullFeePaidTotal');
      wrap.innerHTML = '<div class="text-muted">Loading fee breakdown...</div>';
      totalEl.textContent = 'To Be Exempted: Rs 0.00';
      courseTotalEl.textContent = 'Course Total: Rs 0.00';
      paidTotalEl.textContent = 'Already Paid: Rs 0.00';

      fetch(`{{ url('erp/api/students') }}/${studentId}/fee-exemption-breakdown`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          const rows = Array.isArray(data.fees) ? data.fees : [];
          if (rows.length === 0) {
            wrap.innerHTML = '<div class="text-muted">No applicable fee structures found.</div>';
            totalEl.textContent = 'To Be Exempted: Rs 0.00';
            courseTotalEl.textContent = 'Course Total: Rs 0.00';
            paidTotalEl.textContent = 'Already Paid: Rs 0.00';
            return;
          }

          let html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-1"><thead><tr><th>Quarter</th><th>Year</th><th>Base</th><th>Late Fee</th><th>Line Total</th><th>Status</th><th>Exemptable</th></tr></thead><tbody>';

          rows.forEach(function(row) {
            const isPaid = Boolean(row.is_paid);
            const isQuarterEx = Boolean(row.is_quarter_fee_exempted);
            let statusText = Number(row.is_payable) === 1 ? 'Active' : 'Inactive';
            if (isPaid) {
              statusText = 'Paid';
            } else if (isQuarterEx) {
              statusText = 'Quarter Exempted';
            }

            const exemptable = Number(row.exemptable_amount || 0);
            html += '<tr>';
            html += '<td>' + (row.fee_structure_name || 'N/A') + '</td>';
            html += '<td>' + (row.year || '-') + '</td>';
            html += '<td>Rs ' + Number(row.base_amount || 0).toLocaleString('en-IN', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }) + '</td>';
            html += '<td>Rs ' + Number(row.late_fee || 0).toLocaleString('en-IN', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }) + '</td>';
            html += '<td>Rs ' + Number(row.total_payable || 0).toLocaleString('en-IN', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }) + '</td>';
            html += '<td>' + statusText + '</td>';
            html += '<td>Rs ' + exemptable.toLocaleString('en-IN', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            }) + '</td>';
            html += '</tr>';
          });

          html += '</tbody></table></div>';
          wrap.innerHTML = html;

          const summary = data.summary || {};
          const courseTotal = Number(summary.total_course_amount || 0);
          const paidTotal = Number(summary.total_paid_amount || 0);
          const exemptTotal = Number(summary.total_to_be_exempted || 0);

          courseTotalEl.textContent = 'Course Total: Rs ' + courseTotal.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          paidTotalEl.textContent = 'Already Paid: Rs ' + paidTotal.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
          totalEl.textContent = 'To Be Exempted: Rs ' + exemptTotal.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          });
        })
        .catch(function() {
          wrap.innerHTML = '<div class="text-danger">Unable to load fee breakdown. Please try again.</div>';
          totalEl.textContent = 'To Be Exempted: Rs 0.00';
          courseTotalEl.textContent = 'Course Total: Rs 0.00';
          paidTotalEl.textContent = 'Already Paid: Rs 0.00';
        });
    });

    if (!quarterModal) {
      return;
    }

    quarterModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      if (!button) {
        return;
      }

      const studentId = button.getAttribute('data-student-id') || '';
      const rollNo = button.getAttribute('data-roll-no') || '';
      const studentName = button.getAttribute('data-student-name') || '';
      const exemptedIdsRaw = button.getAttribute('data-exempted-fee-ids') || '';
      const exemptedIds = exemptedIdsRaw ? exemptedIdsRaw.split(',').map(v => String(v).trim()).filter(Boolean) : [];

      document.getElementById('quarterExemptionRollNo').value = rollNo;
      document.getElementById('quarterExemptionStudentName').textContent = studentName + ' (' + rollNo + ')';
      document.getElementById('quarterExemptionReason').value = '';

      const listEl = document.getElementById('quarterFeeStructureList');
      listEl.innerHTML = '<div class="text-muted">Loading fee structures...</div>';

      fetch(`{{ url('erp/api/students') }}/${studentId}/fee-structures`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(rows => {
          if (!Array.isArray(rows) || rows.length === 0) {
            listEl.innerHTML = '<div class="text-muted">No applicable fee structures found.</div>';
            return;
          }

          let html = '';
          rows.forEach(function(fs) {
            const feeId = String(fs.id);
            const isChecked = exemptedIds.includes(feeId) ? 'checked' : '';
            const title = (fs.quarter_title || 'Untitled') + ' (Year ' + (fs.std_current_year || '-') + ')';
            const payableTag = Number(fs.is_payable) === 1 ? 'Active' : 'Inactive';
            const isPaid = Number(fs.is_paid) === 1;
            const paidTag = isPaid ? 'Paid' : 'Unpaid';
            const disabledAttr = isPaid ? 'disabled' : '';
            html += '<label class="d-flex align-items-center gap-2 border rounded px-2 py-1 mb-1">';
            html += '<input type="checkbox" name="fee_structure_ids[]" value="' + feeId + '" ' + isChecked + ' ' + disabledAttr + '>';
            html += '<span>' + title + ' <small class="text-muted">[' + payableTag + ' | ' + paidTag + ']</small></span>';
            html += '</label>';
          });

          listEl.innerHTML = html;
        })
        .catch(function() {
          listEl.innerHTML = '<div class="text-danger">Unable to load fee structures. Please try again.</div>';
        });
    });
  });
</script>