<?php

use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\ProgramGroup;

$batches = BatchMaster::all();

$programgroups = ProgramGroup::with(['programInfo', 'campus'])->where('campus_id', 2)->get();

?>
@include('includes.header')
@include('admin.sidebar')

<h3 class="mt-3"><strong>Fee Payments</strong></h3>
<p>Total Students: {{ $data->total() }}</p>
<div class="row mb-3">
  <div class="col-lg-4">
    <form action="{{url('erp/admin/accounts/std-fee-payments')}}" method="get">
      <div class="row">
        <div class="col-lg-6">
          <select name="filter_pgr" class="form-control dselect-example">
            <option value="">--Select Group--</option>
            @foreach ($programgroups as $pgr)
            <option value="{{$pgr->id}}">{{$pgr->program_code}} - {{$pgr->programInfo->name}} </option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-6">
          <div class="input-group">
            <select name="filter_batch" class="form-control select-example">
              <option value="">--Select Batch--</option>
              @foreach ($batches as $b)
              <option value="{{$b->id}}">{{$b->batch_name}} </option>
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
          <div class="meta-line">{{ $item['programgroup'] }} • Batch {{ $item['batch'] }}</div>
          <div class="meta-line">Current Year: {{ $item['current_year'] }}</div>
        </div>

        <div>
          <a href="{{ url('erp/admin/accounts/invoice/'.$item['studentinfo']['rollno']) }}"
            class="btn btn-outline-primary btn-sm">
            View Invoice
          </a>
        </div>
      </div>

      <hr>

      {{-- FEE STRUCTURES --}}
      @foreach($item['fee_status'] as $fee)
      <div class="fee-row">

        {{-- LEFT SIDE --}}
        <div>
          <div class="fee-title">{{ $fee['quarter'] }}</div>
          <div class="text-muted">Amount: ₹{{ number_format($fee['total_amount']) }}</div>
        </div>

        {{-- RIGHT SIDE --}}
        <div>
          @if($fee['status'] === 'success')
          <a href="{{ url('erp/admin/accounts/print-feereciept/'.$item['studentinfo']['id'].'/'.$fee['fee_structure_id']) }}"
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
            data-amount="{{ $fee['total_amount'] }}"
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
<script>
  document.addEventListener("DOMContentLoaded", () => {
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

@include('admin.accounts.manual-payment-modal')
@include('includes.footer')