<?php

use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\DegreeTrackMaster;
use App\Models\ProgramGroup;
use App\Models\StudentMaster;
use App\Models\StudentProgram;

$batches = BatchMaster::all();
$campuses = Campus::orderBy('name')->get();
$degreeTracks = DegreeTrackMaster::orderBy('name')->get();

// $programgroups = ProgramGroup::with(['programInfo', 'campus'])->where('campus_id', 2)->get();
$studentPrograms = StudentProgram::with('campusmaster')->get();

$batchProgramMap = StudentMaster::query()
  ->select('batch', 'new_program_id')
  ->whereNotNull('batch')
  ->whereNotNull('new_program_id')
  ->distinct()
  ->get()
  ->groupBy('batch')
  ->map(fn($rows) => $rows->pluck('new_program_id')->values()->all())
  ->toArray();
?>
@include('includes.header')
@include('admin.accounts.sidebar')

@php
$activeFilterCount = collect([
request('roll_no'),
request('filter_pgr'),
request('filter_batch'),
request('filter_campus'),
request('filter_year'),
request('filter_pathway'),
request('filter_degree_track'),
request('payment_state'),
request('sort_by') && request('sort_by') !== 'name' ? request('sort_by') : null,
request('sort_dir') && request('sort_dir') !== 'asc' ? request('sort_dir') : null,
])->filter()->count();
@endphp

<style>
  .fee-toolbar {
    border: 1px solid #e3e8ef;
    border-radius: 14px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
    box-shadow: 0 10px 26px rgba(16, 24, 40, 0.06);
    overflow: hidden;
  }

  .fee-toolbar-head {
    padding: 12px 16px;
    border-bottom: 1px solid #edf1f6;
    background: linear-gradient(90deg, #f8fafc 0%, #f2f6fb 100%);
  }

  .fee-toolbar-title {
    font-size: 13px;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #22324d;
    font-weight: 700;
    margin: 0;
  }

  .fee-toolbar-chip {
    border: 1px solid #c8d5e6;
    color: #27476e;
    background: #eef4fb;
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 999px;
  }

  .fee-filter-group {
    border: 1px solid #edf1f6;
    border-radius: 10px;
    padding: 12px;
    background: #fff;
    height: 100%;
  }

  .fee-filter-label {
    font-size: 11px;
    letter-spacing: 0.25px;
    color: #5f6b7a;
    text-transform: uppercase;
    font-weight: 700;
    margin-bottom: 8px;
  }

  .fee-toolbar .form-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.2px;
    color: #5f6b7a;
    text-transform: uppercase;
    margin-bottom: 6px;
  }

  .fee-toolbar .form-control {
    border-color: #d7dfeb;
    min-height: 40px;
  }

  .fee-toolbar .btn-filter-apply {
    min-width: 170px;
    background: linear-gradient(90deg, #0a58ca 0%, #2b6ed2 100%);
    border: 0;
  }

  .fee-toolbar .btn-filter-reset {
    min-width: 130px;
  }

  @media (max-width: 991.98px) {
    .fee-filter-group {
      margin-bottom: 10px;
    }

    .fee-toolbar .btn-filter-apply,
    .fee-toolbar .btn-filter-reset {
      width: 100%;
      min-width: 0;
    }
  }
</style>

<div class="d-flex flex-wrap align-items-center justify-content-between mt-3 mb-2 gap-2">
  <div>
    <h3 class="mb-1" style="font-weight:700; color:#1f2d3d;">Fee Payments</h3>
    <p class="mb-0 text-muted">Total Students: <strong>{{ $data->total() }}</strong></p>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span class="badge bg-light text-dark border">Academic Pathway + Degree Track Aware</span>
    <span class="fee-toolbar-chip">{{ $activeFilterCount }} Active Filter{{ $activeFilterCount === 1 ? '' : 's' }}</span>
  </div>
</div>

<div class="row mb-3">
  <div class="col-12">
    <form action="{{url('erp/admin/accounts/std-fee-payments')}}" method="get" class="fee-toolbar">
      <div class="fee-toolbar-head d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="fee-toolbar-title"><i class="fa fa-sliders-h me-1"></i>Filter Workspace</h6>
        <small class="text-muted">Refine records by student, academics, payment status, and sorting controls</small>
      </div>

      <div class="p-3 p-lg-4">
        <div class="row g-3">
          <div class="col-12 col-lg-4">
            <div class="fee-filter-group">
              <div class="fee-filter-label">Student Search</div>
              <label class="form-label">Roll No or Name</label>
              <input type="text" name="roll_no" class="form-control" placeholder="Search by roll no, first name, last name" value="{{ request('roll_no') }}">
            </div>
          </div>

          <div class="col-12 col-lg-8">
            <div class="fee-filter-group">
              <div class="fee-filter-label">Academic Context</div>
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label">Program</label>
                  <select id="filter_pgr" name="filter_pgr" class="form-control dselect-example">
                    <option value="">All Programs</option>
                    @foreach ($studentPrograms as $prg)
                    <option value="{{$prg->id}}" {{ request('filter_pgr') == $prg->id ? 'selected' : '' }}>{{$prg->code}} - {{$prg->name}} | {{$prg->campusmaster->name ?? ''}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Batch</label>
                  <select id="filter_batch" name="filter_batch" class="form-control select-example">
                    <option value="">All Batches</option>
                    @foreach ($batches as $b)
                    <option value="{{$b->id}}" {{ request('filter_batch') == $b->id ? 'selected' : '' }}>{{$b->batch_name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Campus</label>
                  <select name="filter_campus" class="form-control">
                    <option value="">All Campuses</option>
                    @foreach ($campuses as $campus)
                    <option value="{{ $campus->id }}" {{ (string) request('filter_campus') === (string) $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="fee-filter-group">
              <div class="fee-filter-label">Applicability</div>
              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label">Current Year</label>
                  <select name="filter_year" class="form-control">
                    <option value="">All Years</option>
                    @for ($year = 1; $year <= 6; $year++)
                      <option value="{{ $year }}" {{ (string) request('filter_year') === (string) $year ? 'selected' : '' }}>Year {{ $year }}</option>
                      @endfor
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Major Type</label>
                  <select name="filter_pathway" class="form-control">
                    <option value="">All Major Types</option>
                    <option value="1" {{ request('filter_pathway') == '1' ? 'selected' : '' }}>Single Major</option>
                    <option value="2" {{ request('filter_pathway') == '2' ? 'selected' : '' }}>Dual Major</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Degree Track</label>
                  <select name="filter_degree_track" class="form-control">
                    <option value="">All Degree Tracks</option>
                    @foreach ($degreeTracks as $track)
                    <option value="{{ $track->id }}" {{ (string) request('filter_degree_track') === (string) $track->id ? 'selected' : '' }}>{{ $track->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="fee-filter-group">
              <div class="fee-filter-label">Payment and View Controls</div>
              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label">Payment State</label>
                  <select name="payment_state" class="form-control">
                    <option value="">All States</option>
                    <option value="paid" {{ request('payment_state') === 'paid' ? 'selected' : '' }}>Paid Students</option>
                    <option value="unpaid" {{ request('payment_state') === 'unpaid' ? 'selected' : '' }}>Unpaid Students</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Sort By</label>
                  <select name="sort_by" class="form-control">
                    <option value="name" {{ request('sort_by', 'name') === 'name' ? 'selected' : '' }}>Name</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Direction</label>
                  <select name="sort_dir" class="form-control">
                    <option value="asc" {{ request('sort_dir', 'asc') === 'asc' ? 'selected' : '' }}>Asc</option>
                    <option value="desc" {{ request('sort_dir') === 'desc' ? 'selected' : '' }}>Desc</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-lg-end gap-2 mt-1">
            <button type="submit" class="btn btn-primary btn-filter-apply"><i class="fa fa-filter me-1"></i>Apply Filters</button>
            <a href="{{url('erp/admin/accounts/std-fee-payments')}}" class="btn btn-outline-secondary btn-filter-reset"><i class="fas fa-redo-alt me-1"></i>Reset</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>


<hr>

{{-- STUDENT CARDS --}}
<div class="row">
  @foreach($data as $item)
  <div class="col-lg-4">
    <div class="student-card">

      {{-- HEADER --}}
      <div class="d-flex justify-content-between">
        <div>
          <div class="card-heading text-capitalize">{{ $item['studentinfo']['fullname'] }}</div>
          <td class="align-middle">
            <div class="d-flex align-items-center gap-2 text-uppercase text-rollno">
              {{ $item['studentinfo']['rollno'] }}
              <button
                class="copy-btn btn btn-sm btn-outline-secondary py-0"
                data-copy="{{ $item['studentinfo']['rollno'] }}">
                Copy
              </button>

            </div>
          </td>
          <div class="meta-line"> Batch {{ $item['batch'] }} | Current Year: {{ $item['current_year'] }} |</div>
          <div class="meta-line">{{ $item['stdprogramenrolled']->code ?? '—' }} - {{ $item['stdprogramenrolled']->name ?? '—' }} </div>
          <div class="meta-line">Major Type: {{ $item['academic_pathway_label'] ?? 'Not Set' }}</div>
          <div class="meta-line">Degree Track: {{ $item['degree_track_label'] ?? 'Not Set' }}</div>
        </div>

        <div>
          <a href="{{ url('erp/admin/accounts/invoice/'.$item['studentinfo']['rollno']) }}"
            class="btn btn-outline-primary btn-sm">
            All Invoices
          </a>
        </div>
      </div>

      <hr>

      {{-- FEE STRUCTURES --}}
      @foreach($item['fee_status'] as $fee)
      <div class="fee-row">

        {{-- LEFT SIDE --}}
        <div>
          <b> <i><small class="text-info"> #{{ $fee['fee_structure_id'] }} For Developer Reference Only</small> </i></b>
          <div class="fee-title">{{ $fee['quarter'] }} <span class="badge badge-{{ $fee['is_payable'] == 'Active' ? 'success' : 'warning' }}">{{ $fee['is_payable'] }}</span> </div>
          <div class="text-muted">Fee Amount: ₹{{ number_format($fee['total_amount']) }}</div>
          @if($fee['status'] === 'success')
          <div class="text-muted">Paid Base Amount: ₹{{ number_format($fee['paid_base_amount'] ?? 0, 2) }}</div>
          @if(!is_null($fee['paid_fixed_late_fee']))
          <div class="text-muted">
            Applied Fixed Late Fee (Exemption): ₹{{ number_format($fee['paid_fixed_late_fee'], 2) }}
            @if(($fee['paid_late_days'] ?? 0) > 0)
            / {{ $fee['paid_late_days'] }} days
            @endif
          </div>
          @elseif(($fee['display_paid_late_fee_amount'] ?? 0) > 0)
          <div class="text-muted">
            Applied Late Fee: ₹{{ number_format($fee['display_paid_late_fee_amount'], 2) }}
            @if(($fee['paid_late_days'] ?? 0) > 0)
            / {{ $fee['paid_late_days'] }} days
            @endif
          </div>
          @endif
          <div class="text-title text-success">Paid Amount <strong>₹{{ number_format($fee['display_paid_total_amount'] ?? 0, 2) }}</strong></div>
          @else
          @if(!empty($fee['is_late_fee_exempted']) && $fee['late_days'] > 0)
          <div class="text-muted">
            Late Fee: ₹{{ number_format($fee['late_fee'], 2) }}
            @if(!is_null($fee['fixed_late_fee']))
            <span class="badge bg-info text-dark">Fixed (Exemption)</span>
            @endif
            / {{ $fee['late_days'] }} days
          </div>
          @else
          <div class="text-muted">Late Fee: ₹{{ number_format($fee['late_fee'], 2) }} / {{ $fee['late_days'] }} days</div>
          @endif
          <div class="text-title">Total Payable Amount <strong>₹{{ number_format($fee['payable_amount'], 2) }}</strong> </div>
          @endif
        </div>

        {{-- RIGHT SIDE --}}
        <div>
          @if($fee['status'] === 'success')
          @if(!is_null($fee['paid_fixed_late_fee']))
          <div class="mb-1">
            <span class="badge badge-warning">Exemption Applied</span>
          </div>
          @endif
          <a href="{{ url('erp/admin/accounts/print-feereciept/'.$fee['paymentinfo']['id']) }}"
            target="_blank"
            class="badge-paid">
            PAID
          </a>
          @else
          <button class="btn btn-danger btn-sm manualPayBtn"
            data-student-id="{{ $item['studentinfo']['id'] }}"
            data-rollno="{{ $item['studentinfo']['rollno'] }}"
            data-student-name="{{ $item['studentinfo']['fullname'] }}"
            data-fee-id="{{ $fee['fee_structure_id'] }}"
            data-quarter="{{ $fee['quarter'] }}"
            data-amount="{{ $fee['payable_amount'] }}"
            data-late-fee="{{ $fee['late_fee'] }}"
            data-late-days="{{ $fee['late_days'] }}"
            data-bs-toggle="modal"
            data-bs-target="#manualPayModal">
            PAY
          </button>
          @endif
        </div>
      </div>
      @endforeach

    </div>
  </div>

  @endforeach
</div>
{{-- PAGINATION --}}
<div class="mt-3">
  {{ $data->links('pagination::bootstrap-5') }}
</div>


@include('admin.accounts.manual-payment-modal')
@include('includes.footer')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const batchProgramMap = @json($batchProgramMap);
    const batchSelect = document.getElementById("filter_batch");
    const programSelect = document.getElementById("filter_pgr");

    if (batchSelect && programSelect) {
      const defaultOption = {
        value: "",
        text: "--Select Group--"
      };

      const allProgramOptions = Array.from(programSelect.options)
        .filter(option => option.value !== "")
        .map(option => ({
          value: option.value,
          text: option.text,
        }));

      const renderProgramOptions = () => {
        const selectedBatch = batchSelect.value;
        const allowedPrograms = selectedBatch ?
          (batchProgramMap[selectedBatch] || []).map(String) :
          allProgramOptions.map(option => option.value);

        const existingSelection = programSelect.value;
        const fragment = document.createDocumentFragment();

        const placeholder = document.createElement("option");
        placeholder.value = defaultOption.value;
        placeholder.textContent = defaultOption.text;
        fragment.appendChild(placeholder);

        allProgramOptions.forEach(option => {
          if (allowedPrograms.includes(String(option.value))) {
            const optionEl = document.createElement("option");
            optionEl.value = option.value;
            optionEl.textContent = option.text;
            fragment.appendChild(optionEl);
          }
        });

        programSelect.innerHTML = "";
        programSelect.appendChild(fragment);

        const hasSelection = Array.from(programSelect.options).some(option => option.value === existingSelection);
        programSelect.value = hasSelection ? existingSelection : "";
        programSelect.dispatchEvent(new Event("change"));
      };

      batchSelect.addEventListener("change", renderProgramOptions);
      renderProgramOptions();
    }

    document.querySelectorAll(".copy-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        const text = btn.dataset.copy;

        navigator.clipboard.writeText(text).then(() => {
          btn.innerText = "Copied!";
          btn.classList.remove("btn-outline-secondary");
          btn.classList.add("btn-success");

          setTimeout(() => {
            btn.innerText = "Copy";
            btn.classList.remove("btn-success");
            btn.classList.add("btn-outline-secondary");
          }, 1500);
        });
      });
    });
  });
</script>