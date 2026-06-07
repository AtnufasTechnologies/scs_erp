@include('includes.header')
@include('includes.dept-sidebar')

<!-- Main Content -->
<div class="main-content">
  <div class="container-fluid">

    <!-- Page Header -->
    <div class="row mb-4">
      <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="mb-1" style="font-weight: 700; color: #1a1a1a;">
              <i class="fas fa-users me-2" style="color: #5b4cdb;"></i>All Students
            </h3>
            Total: <strong>{{ $students->count() }}</strong>
            </p>
          </div>

          <div>
            <a href="{{ route('department.dashboard') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Batch Filter -->
    <div class="card shadow-sm mb-4" style="border-radius: 14px; border: none;">
      <div class="card-body py-3">
        <form method="GET" action="{{ route('department.all.students') }}" class="d-flex align-items-center gap-3 flex-wrap">
          <label class="fw-semibold mb-0" style="color: #6b7280;">
            <i class="fas fa-filter me-1"></i>Filter by Batch:
          </label>
          <select name="batch_id" class="form-select" style="width: 200px; border-radius: 10px; border: 1px solid #e5e7eb;" onchange="this.form.submit()">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
            <option value="{{ $batch->id }}" {{ $activeBatch == $batch->id ? 'selected' : '' }}>
              {{ $batch->batch_name }}
            </option>
            @endforeach
          </select>
          @if($activeBatch)
          <a href="{{ route('department.all.students') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">
            <i class="fas fa-times me-1"></i>Clear
          </a>
          @endif
        </form>
      </div>
    </div>

    <!-- Students Table -->
    <div class="card shadow-sm" style="border-radius: 16px; border: none;">
      <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 gap-3 flex-wrap" style="border-radius: 16px 16px 0 0; border-bottom: 1px solid #f0f0f0;">
        <h5 class="mb-0" style="font-weight: 700; color: #1a1a1a;">
          <i class="fas fa-list me-2" style="color: #5b4cdb;"></i>
          Student List
          <span id="visibleCount" class="badge ms-2" style="background: linear-gradient(135deg, #5b4cdb 0%, #7c3aed 100%); font-size: 13px; border-radius: 8px; padding: 4px 10px;">
            {{ $students->count() }}
          </span>
        </h5>
        <!-- Live Search -->
        <div style="position: relative; min-width: 260px;">
          <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; pointer-events: none;"></i>
          <input type="text" id="liveSearch" placeholder="Search by name or roll no…"
            autocomplete="off"
            style="width: 100%; padding: 8px 12px 8px 34px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 13px; outline: none; transition: border .2s;"
            onfocus="this.style.borderColor='#5b4cdb'" onblur="this.style.borderColor='#e5e7eb'">
        </div>
      </div>

      <div class="card-body p-0">
        @if($students->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover mb-0" id="studentsTable">
            <thead style="background: #f9fafb;">
              <tr>
                <th style="padding: 14px 16px; color: #fff; font-weight: 600; font-size: 13px; border-bottom: 2px solid #f0f0f0;">#</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Roll No / Profile</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Student Name</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Program</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Batch</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Mobile</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Email</th>
                <th style="color: #fff; font-weight: 600; font-size: 13px;">Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($students as $student)
              <tr class="student-row" data-search="{{ strtolower($student->roll_no . ' ' . $student->first_name . ' ' . $student->last_name) }}" style="border-bottom: 1px solid #f5f5f5;">
                <td>{{ $loop->iteration }}</td>
                <td>
                  <a href="{{ route('department.student.profile', ['id' => $student->id, 'rollno' => $student->roll_no]) }}"
                    style="color: #5b4cdb; font-weight: 700; font-size: 13px; text-transform: uppercase; text-decoration: none;">
                    {{ $student->roll_no ?? 'N/A' }}
                  </a>
                </td>
                <td style="color: #1a1a1a; font-size: 13px; text-transform: capitalize; font-weight: 500;">
                  {{ $student->first_name }} {{ $student->last_name }}
                </td>
                <td style="font-size: 13px;">
                  <span>
                    {{ $student->stdprogramenrolled->code ?? 'N/A' }} - {{ $student->stdprogramenrolled->name ?? 'N/A' }}
                  </span>
                </td>
                <td>
                  <span class="badge" style="background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%); padding: 4px 10px; border-radius: 6px; font-size: 11px;">
                    {{ $student->batchmaster->batch_name ?? 'N/A' }}
                  </span>
                </td>
                <td style="font-size: 13px;">
                  @if($student->mobile_no)
                  <a href="tel:{{ $student->mobile_no }}" style="color: #5b4cdb;">{{ $student->mobile_no }}</a>
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td style="font-size: 13px;">
                  @if($student->mail_id)
                  <a href="mailto:{{ $student->mail_id }}" style="color: #5b4cdb;">{{ Str::limit($student->mail_id, 25) }}</a>
                  @else
                  <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td>
                  @if(($student->is_left ?? '0') == '0')
                  <span class="badge bg-success" style="border-radius: 6px; padding: 4px 10px; font-size: 11px;">
                    <i class="fas fa-check-circle me-1"></i>Active
                  </span>
                  @else
                  <span class="badge bg-danger" style="border-radius: 6px; padding: 4px 10px; font-size: 11px;">
                    <i class="fas fa-times-circle me-1"></i>Left
                  </span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          <div id="noSearchResults" class="text-center py-5" style="display: none;">
            <i class="fas fa-search fa-2x mb-3" style="color: #e5e7eb;"></i>
            <p style="color: #6b7280;">No students match your search.</p>
          </div>
        </div>
        @else
        <div class="text-center py-5">
          <i class="fas fa-user-slash fa-3x mb-3" style="color: #e5e7eb;"></i>
          <p style="color: #6b7280;">No students found{{ $activeBatch ? ' for this batch' : '' }}.</p>
        </div>
        @endif
      </div>

    </div>

  </div>
</div>

<script>
  (function() {
    const input = document.getElementById('liveSearch');
    const countBadge = document.getElementById('visibleCount');
    const noResults = document.getElementById('noSearchResults');

    if (!input) return;

    input.addEventListener('input', function() {
      const q = this.value.trim().toLowerCase();
      const rows = document.querySelectorAll('#studentsTable .student-row');
      let visible = 0;

      rows.forEach(function(row) {
        const haystack = row.dataset.search || '';
        const match = !q || haystack.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
      });

      countBadge.textContent = visible;
      noResults.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
    });
  })();
</script>