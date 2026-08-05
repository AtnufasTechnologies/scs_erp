<?php

use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\ProgramGroup;
use App\Models\StudentMaster;
use App\Models\StudentProgram;

$batches = BatchMaster::all();

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

<h3 class="mt-3"><strong>Fee Payments</strong></h3>
<p>Total Students: {{ $data->total() }}</p>
<div class="row mb-3">
  <div class="col-lg-8">
    <form action="{{url('erp/admin/accounts/std-fee-payments')}}" method="get">
      <div class="row">
        <div class="col-lg-7">
          <select id="filter_pgr" name="filter_pgr" class="form-control dselect-example">
            <option value="">--Select Group--</option>
            @foreach ($studentPrograms as $prg)
            <option value="{{$prg->id}}" {{ request('filter_pgr') == $prg->id ? 'selected' : '' }}>{{$prg->code}} - {{$prg->name}} | {{$prg->campusmaster->name ?? ''}} </option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-3">
          <div class="input-group">
            <select id="filter_batch" name="filter_batch" class="form-control select-example">
              <option value="">--Select Batch--</option>
              @foreach ($batches as $b)
              <option value="{{$b->id}}" {{ request('filter_batch') == $b->id ? 'selected' : '' }}>{{$b->batch_name}} </option>
              @endforeach
            </select>
            <button class="btn btn-info"><i class="fa fa-search"></i></button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="col-lg-3">
    <form action="{{url('erp/admin/accounts/std-fee-payments')}}" method="get">
      <div class="input-group">
        <input type="text" name="roll_no" class="form-control" placeholder="Search Rollno">
        <button type="submit" class="btn btn-info"><i class="fa fa-search"></i></button>
      </div>
    </form>
  </div>
  <div class="col-lg-1">
    <a href="{{url('erp/admin/accounts/std-fee-payments')}}"><button class="btn btn-outline-success"><i class="fas fa-redo-alt"></i></button></a>
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