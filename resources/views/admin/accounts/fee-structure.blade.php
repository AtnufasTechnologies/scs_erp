<?php

use App\Http\Controllers\StaticController;
use App\Models\BatchMaster;
use App\Models\FeeCourseMaster;
use App\Models\FeeHead;
use App\Models\LateFee;
use App\Models\MainProgram;
use App\Models\ProgramGroup;
use App\Models\StudentProgram;

$batches = BatchMaster::all();
$programs = MainProgram::with('campus')->get();
$feeheads = FeeHead::latest()->get();
$feecoursemaster = FeeCourseMaster::latest()->get();
$programgroups = ProgramGroup::with(['programInfo'])->get();
$latefee = LateFee::find(1);

$yearGradients = [
  1 => 'linear-gradient(-45deg, #1565c0, #42a5f5)',
  2 => 'linear-gradient(-45deg, #2e7d32, #66bb6a)',
  3 => 'linear-gradient(-45deg, #e65100, #ffca28)',
  4 => 'linear-gradient(-45deg, #880e4f, #f48fb1)',
  5 => 'linear-gradient(-45deg, #4a148c, #ce93d8)',
];
?>
@include('includes.header')
@include('admin.accounts.sidebar')

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h4 class="fw-bold mb-0"><i class="fa fa-layer-group text-primary me-2"></i>Fee Structure</h4>
    <small class="text-muted">Manage all fee structures</small>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#cloneAllModal">
      <i class="fa fa-clone me-1"></i> Clone All to New Batch
    </button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add">
      <i class="fa fa-plus-circle me-1"></i> Add New Structure
    </button>
  </div>
</div>

<!-- Clone All Modal -->
<div class="modal fade" id="cloneAllModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(-45deg,#2e7d32,#66bb6a); color:#fff;">
        <h5 class="modal-title"><i class="fa fa-clone me-2"></i>Clone All Fee Structures</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ url('erp/admin/accounts/clone-all-feestructures') }}" method="post">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info py-2 small mb-3">
            <i class="fa fa-info-circle me-1"></i>
            All fee structures belonging to the <strong>source batch</strong> will be duplicated
            into the <strong>target batch</strong> with the dates you specify.
            Cloned structures start as <span class="badge bg-warning text-dark">Inactive</span>.
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Source Batch <span class="text-danger">*</span></label>
            <select name="source_batch_id" class="form-select" required>
              <option value="">-- Select source batch --</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
              @endforeach
            </select>
            <div class="form-text">Fee structures from this batch will be copied.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Target Batch <span class="text-danger">*</span></label>
            <select name="batch_id" class="form-select" required>
              <option value="">-- Select target batch --</option>
              @foreach($batches as $batch)
              <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
              @endforeach
            </select>
            <div class="form-text">Cloned structures will be created under this batch.</div>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-semibold">Activation Date <span class="text-danger">*</span></label>
              <input type="date" name="reminder_date" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
              <input type="date" name="due_date" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success btn-sm">
            <i class="fa fa-clone me-1"></i>Clone All Structures
          </button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- Toolbar -->
<div class="card shadow-sm mb-3">
  <div class="card-body py-2">
    <div class="row g-2 align-items-center">
      <div class="col-md-3">
        <form action="{{url('erp/admin/accounts/fee-structure')}}" method="get" class="d-flex gap-2 align-items-center">
          <strong>Batch Filter</strong>
          <select name="batch_id" class="form-select form-select-sm" style="max-width:180px;" onchange="this.form.submit()">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
            <option value="{{$batch->id}}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>{{$batch->batch_name}}</option>
            @endforeach
          </select>
        </form>
      </div>
      <div class="col-md-3">

        <input type="text" id="feeSearch" class="form-control form-control-sm" placeholder="Type to filter...">
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const searchInput = document.getElementById('feeSearch');
          searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('.fee-card').forEach(function(card) {
              const text = card.innerText.toLowerCase();
              card.parentElement.style.display = text.includes(query) ? '' : 'none';
            });
          });
        });
      </script>
      <div class="col-md-6 d-flex justify-content-end">
        <form action="" method="post" class="d-flex align-items-center gap-2">
          @csrf
          <span class="text-muted small fw-semibold"><i class="fa fa-clock me-1"></i>Late Fee:</span>
          <div class="input-group input-group-sm" style="max-width:280px">
            <span class="input-group-text bg-white"><i class="fa fa-rupee-sign text-secondary"></i></span>
            <input type="number" name="late_fee_amount" class="form-control" value="{{$latefee->late_fee_amount}}" placeholder="Amount">
            <select name="status" class="form-select">
              <option value="1" {{$latefee->status == '1' ? 'selected' : ''}}>Active</option>
              <option value="0" {{$latefee->status == '0' ? 'selected' : ''}}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-success">Update</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/accounts/fee-structure')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="row">


            <div class="col-lg-4">
              <label for="">Select Program *</label>
              <select name="program" class="form-control mb-3">
                @foreach ($programs as $program)
                <option value="{{$program->id}}">{{$program->name}} - {{$program->campus->name}}</option>
                @endforeach
              </select>
            </div>


            <div class="col-lg-2">

              <label for="">Select Batch *</label>
              <select name="batch" class="form-control mb-3">
                <option value="">--Select--</option>
                @foreach ($batches as $batch)
                <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="student start getting message">Activation Date *</label>
              <input type="date" name="reminder_date" class="form-control mb-3">
            </div>
            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="final msg to student">Due Date *</label>
              <input type="date" name="due_date" class="form-control mb-3">
            </div>


            <div class="col-lg-12">
              <label for="">Course Name *</label>
              <select name="course" class="form-control mb-3">
                <option value="">--Select--</option>
                @foreach ($feecoursemaster as $fcm)
                <option value="{{$fcm->id}}">{{$fcm->name}}</option>
                @endforeach
              </select>
            </div>

            <div class="col-lg-6">
              <label for="">Quarter Title *</label>
              <input type="text" name="quarter_title" class="form-control mb-3" placeholder="example : Admisson Time July ">
            </div>

            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="structure applicable to which year students">Studying in Year *</label>
              <select name="applicable_year" class="form-control">
                <option value="">--Select--</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
            </div>

            <div class="col-lg-3">
              <label for="" data-bs-toggle="tooltip" data-bs-placement="top" title="fee display in order for student">Yearly Payment Order *</label>
              <select name="yearly_pay_order" class="form-control">
                <option value="">--Select--</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
            </div>

            <hr>

            <div class="col-lg-12">
              <label for="">Select Fee Heads</label>
              <div id="morefeehead">
                <div class="row mb-3">
                  <div class="col-lg-7">
                    <select name="heads[]" class="form-select ">
                      @foreach ($feeheads as $feehead)
                      <option value="{{$feehead->id}}">{{$feehead->head_name}}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-lg-3">
                    <input type="number" class="form-control" name="amounts[]" placeholder="Amount">
                  </div>

                  <div class="col-lg-1">
                    <button class="btn btn-danger" onClick="$(this).parent().parent().remove();"><i class=" fa fa-trash-alt"></i></button>
                  </div>
                </div>
              </div>
            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="addmorebtn">Add More Head</button>
          <button type="submit" class="btn btn-success">Submit</button>
        </div>

      </form>
    </div>
  </div>
</div>

@if(count($data))
<div class="row">
  @foreach ($data as $item)
  @php
  $gradient = $yearGradients[$item->std_current_year] ?? 'linear-gradient(-45deg, #82cbf9, #5f6498)';
  $accentColor = match((int)$item->std_current_year) {
  1 => '#1565c0',
  2 => '#2e7d32',
  3 => '#e65100',
  4 => '#880e4f',
  5 => '#4a148c',
  default => '#5f6498',
  };
  $total = StaticController::feeStructureTotal($item->id);
  @endphp
  <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
    <div class="fee-card">

      {{-- Coloured top accent bar --}}
      <div class="fc-accent" style="background: {{ $gradient }};"></div>

      {{-- Payable status badge --}}
      <div class="fc-status">
        @if($item->is_payable == 1)
        <a href="{{ url('erp/admin/accounts/update/feestructure-status/'.$item->id) }}">
          <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i>Active</span>
        </a>
        @else
        <a href="{{ url('erp/admin/accounts/update/feestructure-status/'.$item->id) }}">
          <span class="badge bg-warning text-dark"><i class="fa fa-ban me-1"></i>Inactive</span>
        </a>
        @endif
      </div>

      {{-- Card body --}}
      <div class="fc-body">

        {{-- Batch badge --}}
        <span class="fc-batch-badge" style="background: {{ $accentColor }};">
          <i class="fa fa-layer-group me-1"></i>{{ $item->batch->batch_name }}
        </span>

        {{-- Program & course --}}
        <div class="fc-title">{{ $item->program->name ?? '—' }}</div>
        <div class="fc-subtitle">
          <i class="fa fa-map-marker-alt me-1"></i>{{ $item->program->campus->name ?? '—' }}
          &nbsp;&bull;&nbsp;
          <i class="fa fa-book me-1"></i>{{ $item->feecoursemaster->name ?? '—' }}
        </div>

        {{-- Dates --}}
        <div class="fc-dates">
          <div class="fc-date-chip" style="background:#e0f2fe; color:#0369a1;">
            <span class="label">Activation</span>
            {{ date('d M Y', strtotime($item->reminder_date)) }}
          </div>
          <div class="fc-date-chip" style="background:#fef9c3; color:#92400e;">
            <span class="label">Due Date</span>
            {{ date('d M Y', strtotime($item->due_date)) }}
          </div>
        </div>

        {{-- Meta chips --}}
        <div class="fc-meta-row">
          <span class="fc-meta-chip" style="border-color:{{ $accentColor }}33; color:{{ $accentColor }};">
            <i class="fa fa-graduation-cap me-1"></i>Year {{ $item->std_current_year }}
          </span>
          <span class="fc-meta-chip">
            <i class="fa fa-align-left me-1"></i>{{ $item->quarter_title }}
          </span>
          <span class="fc-meta-chip">
            <i class="fa fa-sort-numeric-up me-1"></i>Order {{ $item->yearly_pay_order }}
          </span>
        </div>


        {{-- Fee heads --}}
        @if(count($item->feepvthead))
        <div class="fc-heads">
          @foreach($item->feepvthead as $f)
          <div class="fc-head-row">
            <span class="fc-head-name">{{ $f->head->head_name }}</span>
            <span class="fc-head-amount">₹{{ number_format($f->amount) }}</span>
            <span class="fc-head-actions">
              <a href="{{ url('erp/admin/accounts/del-headpvt/'.$f->id) }}"
                onclick="return confirm('Remove this head?')"
                title="Remove">
                <i class="fa fa-trash text-danger" style="font-size:11px;"></i>
              </a>
              <a data-bs-toggle="modal" data-bs-target="#edit{{ $f->id }}" title="Edit">
                <i class="fa fa-pen text-secondary" style="font-size:11px;"></i>
              </a>
            </span>
          </div>

          {{-- Edit head modal (kept outside the head-row for DOM cleanliness) --}}
          <div class="modal fade" id="edit{{ $f->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Fee Head</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ url('erp/admin/accounts/update-head-single') }}" method="post">
                  @csrf
                  <div class="modal-body">
                    <input type="text" value="{{ $f->head->head_name }}" readonly class="form-control mb-2 bg-light">
                    <input type="number" value="{{ $f->amount }}" name="amount" class="form-control mb-2" placeholder="Amount">
                    <input type="hidden" name="id" value="{{ $f->id }}">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        {{-- Bank account subtotals --}}
        @php
        $bankTotals = [];
        foreach ($item->feepvthead as $f) {
        $bank = $f->head->bankmaster ?? null;
        $key = $bank ? $bank->id : 0;
        if (!isset($bankTotals[$key])) {
        $bankTotals[$key] = [
        'label' => $bank ? $bank->acc_label : 'Unassigned',
        'bank' => $bank ? $bank->bank_name : '',
        'acc_no' => $bank ? ('xxxx' . substr($bank->acc_no, -4)) : '',
        'total' => 0,
        ];
        }
        $bankTotals[$key]['total'] += $f->amount;
        }
        @endphp
        @if(count($bankTotals))
        <div class="fc-bank-subtotals mb-2">
          <div class="fc-subtotal-header"><i class="fa fa-university me-1"></i>Bank-wise Subtotal</div>
          @foreach($bankTotals as $bt)
          <div class="fc-subtotal-row">
            <div class="fc-subtotal-name">
              <span class="fw-semibold">{{ $bt['label'] }}</span>
              @if($bt['bank'])<span class="text-muted ms-1" style="font-size:10px;">{{ $bt['bank'] }}{{ $bt['acc_no'] ? ' · '.$bt['acc_no'] : '' }}</span>@endif
            </div>
            <div class="fc-subtotal-amt">₹{{ number_format($bt['total']) }}</div>
          </div>
          @endforeach
        </div>
        @endif
        @else
        <p class="text-muted small fst-italic mb-2">No fee heads assigned.</p>
        @endif

        {{-- Total --}}
        <div class="fc-total">
          <span><i class="fa fa-rupee-sign me-1"></i>Total</span>
          <span>₹{{ number_format($total) }}</span>
        </div>

      </div>{{-- /fc-body --}}

      {{-- Footer actions --}}
      <div class="fc-footer">

        <a data-bs-toggle="modal" data-bs-target="#editCard{{ $item->id }}"
          class="btn btn-sm btn-outline-secondary" title="Edit structure">
          <i class="fa fa-edit me-1"></i>
        </a>

        <a data-bs-toggle="modal" data-bs-target="#viewProgs{{ $item->id }}"
          class="btn btn-sm btn-outline-primary" title="Linked programs">
          <i class="fa fa-link me-1"></i> {{ count($item->programspivot) }}
        </a>

        <a data-bs-toggle="modal" data-bs-target="#cloneCard{{ $item->id }}"
          class="btn btn-sm btn-outline-success" title="Clone to new batch">
          <i class="fa fa-copy me-1"></i>
        </a>

        <a href="{{ url('erp/admin/accounts/delete-feestructure/'.$item->id) }}"
          onclick="return confirm('Delete this fee structure?')"
          class="btn btn-sm btn-outline-danger ms-auto" title="Delete">
          <i class="fa fa-trash"></i>
        </a>

      </div>

      {{-- ── MODALS ── --}}

      {{-- Edit fee structure modal --}}
      <div class="modal fade" id="editCard{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Fee Structure</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('erp/admin/accounts/update-fee-structure') }}" method="post">
              @csrf
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-lg-6">
                    <label class="form-label">Program *</label>
                    <select name="program" class="form-select">
                      @foreach($programs as $program)
                      <option value="{{ $program->id }}"
                        {{ (isset($item->program) && $item->program && $item->program->id == $program->id) ? 'selected' : '' }}>
                        {{ $program->name }} – {{ $program->campus->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">Batch *</label>
                    <select name="batch" class="form-select">
                      @foreach($batches as $batch)
                      <option value="{{ $batch->id }}" {{ $item->batch->id == $batch->id ? 'selected' : '' }}>
                        {{ $batch->batch_name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">Activation Date</label>
                    <input type="date" name="reminder_date" class="form-control" value="{{ $item->reminder_date }}">
                  </div>
                  <div class="col-lg-6">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $item->due_date }}">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Course Name</label>
                    <input type="text" class="form-control bg-light" value="{{ $item->feecoursemaster->name }}" readonly>
                  </div>
                  <div class="col-12">
                    <hr class="my-1">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Add Fee Heads</label>
                    <div id="updatefeehead">
                      <div class="row g-2 mb-2">
                        <div class="col-7">
                          <select name="heads[]" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            @foreach($feeheads as $feehead)
                            <option value="{{ $feehead->id }}">{{ $feehead->head_name }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-3">
                          <input type="number" class="form-control form-control-sm" name="amounts[]" placeholder="Amount">
                        </div>
                        <div class="col-2">
                          <button type="button" class="btn btn-sm btn-danger w-100"
                            onclick="this.closest('.row').remove()"><i class="fa fa-trash"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="id" value="{{ $item->id }}">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" id="updatemorebtn">
                  <i class="fa fa-plus me-1"></i>Add Row
                </button>
                <button type="submit" class="btn btn-success btn-sm">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- Linked programs modal --}}
      <div class="modal fade" id="viewProgs{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Linked Programs</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              @if(count($item->programspivot))
              <div class="d-flex flex-wrap gap-2">
                @foreach($item->programspivot as $s)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="font-size:12px;">
                  {{ $s->studentprogram->code ?? ''}} – {{ $s->studentprogram->name ?? '' }}
                </span>
                @endforeach
              </div>
              @else
              <p class="text-center text-muted">No programs linked.</p>
              @endif
            </div>
          </div>
        </div>
      </div>

      {{-- Clone batch modal --}}
      <div class="modal fade" id="cloneCard{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><i class="fa fa-copy me-2 text-success"></i>Clone to New Batch</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('erp/admin/accounts/clone-feestructure/'.$item->id) }}" method="post">
              @csrf
              <div class="modal-body">
                <div class="alert alert-info py-2 small">
                  <strong>Copying from:</strong> {{ $item->batch->batch_name }} &bull;
                  {{ $item->feecoursemaster->name ?? '' }} &bull;
                  Year {{ $item->std_current_year }} &bull;
                  {{ count($item->feepvthead) }} head(s) &bull;
                  {{ count($item->programspivot) }} program(s)
                </div>
                <div class="mb-3">
                  <label class="form-label fw-semibold">New Batch <span class="text-danger">*</span></label>
                  <select name="batch_id" class="form-select" required>
                    <option value="">-- Select Batch --</option>
                    @foreach($batches as $batch)
                    <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label fw-semibold">Activation Date <span class="text-danger">*</span></label>
                    <input type="date" name="reminder_date" class="form-control" required>
                  </div>
                  <div class="col-6">
                    <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control" required>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-copy me-1"></i>Clone</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>{{-- /fee-card --}}
  </div>
  @endforeach
</div>
@else
<p class=" text-center display-4">No Records Found</p>
@endif



@include('includes.footer')