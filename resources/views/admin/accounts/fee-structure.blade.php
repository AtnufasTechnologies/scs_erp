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
$studentprograms = StudentProgram::with('campusmaster')->orderby('code', 'ASC')->get();

$yearGradients = [
  1 => 'linear-gradient(-45deg, #1565c0, #42a5f5)',
  2 => 'linear-gradient(-45deg, #2e7d32, #66bb6a)',
  3 => 'linear-gradient(-45deg, #e65100, #ffca28)',
  4 => 'linear-gradient(-45deg, #880e4f, #f48fb1)',
  5 => 'linear-gradient(-45deg, #4a148c, #ce93d8)',
];

$batchColorPalette = [
  '#1565c0', // blue
  '#2e7d32', // green
  '#e65100', // deep orange
  '#880e4f', // pink/maroon
  '#4a148c', // purple
  '#00695c', // teal
  '#bf360c', // burnt orange
  '#37474f', // blue-grey
  '#f57f17', // amber
  '#1a237e', // indigo
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

            <div class="col-lg-12">
              <label for="">Major Type *</label>
              <select name="academic_pathway_id" class="form-control mb-3" required>
                <option value="">--Select--</option>
                <option value="1">Single Major</option>
                <option value="2">Dual Major</option>
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
  $batchId = $item->batch_id ?? ($item->batch->id ?? 0);
  $batchAccentColor = $batchColorPalette[$batchId % count($batchColorPalette)];
  $total = StaticController::feeStructureTotal($item->id);
  @endphp
  <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
    <div class="fee-card">

      {{-- Coloured top accent bar --}}
      <div class="fc-accent" style="background: {{ $batchAccentColor }};"></div>

      {{-- Payable status badge --}}
      <div class="fc-status">
        <a href="#"
          class="status-toggle-btn"
          data-fee-structure-id="{{ $item->id }}"
          data-current-status="{{ $item->is_payable }}"
          title="Click to toggle status">
          <span class="badge {{ $item->is_payable == 1 ? 'bg-success' : 'bg-warning text-dark' }}">
            <i class="fa {{ $item->is_payable == 1 ? 'fa-check-circle' : 'fa-ban' }} me-1"></i>
            {{ $item->is_payable == 1 ? 'Active' : 'Inactive' }}
          </span>
        </a>
      </div>

      {{-- Card body --}}
      <div class="fc-body">

        {{-- Batch badge --}}
        <span class="fc-batch-badge" style="background: {{ $batchAccentColor }};">
          #{{$item->id}} <i class="fa fa-layer-group me-1"></i>{{ $item->batch->batch_name }}
        </span>

        {{-- Program & course --}}
        <div class="fc-title">{{ $item->program->name ?? '—' }}</div>
        <div class="fc-subtitle">
          <i class="fa fa-map-marker-alt me-1"></i>{{ $item->program->campus->name ?? '—' }}
          &nbsp;&bull;&nbsp;
          <i class="fa fa-book me-1"></i>{{ $item->feecoursemaster->name ?? '—' }}
          <span class="badge {{$item->academic_pathway_id == 1 ? 'badge-paid' : 'badge-unpaid'}}"> {{$item->academic_pathway_id == 1 ? 'Single Major': 'Dual Major'}}</span>
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
                    <input type="text" class="form-control bg-light" value="{{ $item->feecoursemaster->name ?? '-'}}" readonly>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Major Type *</label>
                    <select name="academic_pathway_id" class="form-select" required>
                      <option value="1" {{ (int)($item->academic_pathway_id ?? 0) === 1 ? 'selected' : '' }}>Single Major</option>
                      <option value="2" {{ (int)($item->academic_pathway_id ?? 0) === 2 ? 'selected' : '' }}>Dual Major</option>
                    </select>
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

      <div class="modal fade" id="viewProgs{{$item->id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
            <div class="modal-header bg-success text-white">
              <div>
                <h5 class="modal-title"><i class="fa fa-users me-2"></i>Connected Programs</h5>
                <div style="color:rgba(255,255,255,0.8); font-size:0.8rem;">{{$item->name}}</div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">

              <div class="mb-3">
                <label for="searchLinkedPrograms{{$item->id}}" class="form-label fw-semibold text-muted small text-uppercase">Search Linked Programs</label>
                <input type="text" id="searchLinkedPrograms{{$item->id}}" class="form-control fcm-search-input" placeholder="Type to search..." data-target="linkedPrograms{{$item->id}}">
              </div>


              @if(count($item->programspivot))
              <div class="row g-3" id="linkedPrograms{{$item->id}}">
                @foreach ($item->programspivot as $s)
                <div class="col-lg-6 fcm-program-item" data-search-text="{{strtolower($s->studentprogram->code ?? '')}} {{strtolower($s->studentprogram->name ?? '')}} {{strtolower($s->studentprogram->campusmaster->name ?? '')}}" data-pivot-id="{{$s->id}}">
                  <div class="fcm-linked-card">
                    <div>
                      <div class="fcm-linked-code">{{ $s->studentprogram->code ?? '' }} – {{ $s->studentprogram->name ?? '-' }}
                      </div>
                      <div class="fcm-linked-name"> {{ $s->studentprogram->campusmaster->name ?? 'No Campus' }}</div>
                    </div>
                    <a href="#"
                      class="fcm-unlink-btn unlink-program-btn"
                      data-pivot-id="{{$s->id}}"
                      data-fee-structure-id="{{$item->id}}"
                      title="Unlink this program">
                      <i class="fa fa-times"></i>
                    </a>
                  </div>
                </div>
                @endforeach
              </div>
              @else
              <div class="fcm-empty">
                <i class="fa fa-unlink"></i>
                <p class="mb-0">No student programs linked yet.</p>
              </div>
              @endif

              <script>
                document.getElementById('searchLinkedPrograms{{$item->id}}').addEventListener('keyup', function() {
                  const searchText = this.value.toLowerCase().trim();
                  const targetId = this.getAttribute('data-target');
                  const container = document.getElementById(targetId);
                  const items = container.querySelectorAll('.fcm-program-item');
                  let visibleCount = 0;

                  items.forEach(function(item) {
                    const text = item.getAttribute('data-search-text');
                    if (text.includes(searchText)) {
                      item.style.display = '';
                      visibleCount++;
                    } else {
                      item.style.display = 'none';
                    }
                  });

                  if (visibleCount === 0 && items.length > 0) {
                    if (!container.querySelector('.fcm-no-results')) {
                      const noResults = document.createElement('div');
                      noResults.className = 'fcm-no-results alert alert-info';
                      noResults.innerHTML = '<i class="fa fa-search me-2"></i>No programs found.';
                      container.appendChild(noResults);
                    }
                  } else {
                    const noResults = container.querySelector('.fcm-no-results');
                    if (noResults) noResults.remove();
                  }
                });
              </script>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#linkProgModal{{$item->id}}">Link New Program</button>
            </div>
          </div>
        </div>
      </div>


      {{-- Link new program modal --}}
      <div class="modal fade" id="linkProgModal{{$item->id}}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg ">
          <div class="modal-content border-0 shadow-lg">
            <div class="modal-header ">
              <div>
                <h5 class="modal-title"><i class="fa fa-users me-2"></i>Link New Programs</h5>
                <div style="color:rgba(255,255,255,0.8); font-size:0.8rem;">{{$item->name}}</div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="linkProgramForm{{$item->id}}" class="link-program-form" data-fee-structure-id="{{$item->id}}">
              @csrf
              <div class="modal-body p-4">

                <input type="hidden" value="{{$item->id}}" name="id">
                <label for="">Select a Program to Connect</label>
                @if(count($studentprograms))
                <select name="selected_program" id="linkedProgramsSelect{{$item->id}}" class="dselect-example" required>
                  <option value="">-- Select a program --</option>
                  @foreach ($studentprograms as $s)
                  <option value="{{$s->id}}">{{$s->code ?? 'Unknown Code'}} - {{$s->name ?? 'Unknown Program'}} | {{$s->campusmaster->name ?? 'Unknown Campus'}}</option>
                  @endforeach
                </select>
                <div class="alert alert-info mt-2 d-none" id="linkMessage{{$item->id}}"></div>
                @else
                <div class="fcm-empty">
                  <i class="fa fa-unlink"></i>
                  <p class="mb-0">No student programs available.</p>
                </div>
                @endif


              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="linkSubmitBtn{{$item->id}}">
                  <span class="btn-text">Link Selected Program</span>
                  <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
              </div>
            </form>
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
<script>
  // Fee card live search (toolbar)
  document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('feeSearch');
    if (!searchInput) return;
    searchInput.addEventListener('input', function() {
      var query = this.value.toLowerCase();
      document.querySelectorAll('.fee-card').forEach(function(card) {
        var col = card.closest('[class*="col-"]');
        if (col) col.style.display = card.innerText.toLowerCase().includes(query) ? '' : 'none';
      });
    });

    // AJAX: Link Program Form Submission
    document.querySelectorAll('.link-program-form').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        const feeStructureId = this.dataset.feeStructureId;
        const submitBtn = document.getElementById('linkSubmitBtn' + feeStructureId);
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        const messageDiv = document.getElementById('linkMessage' + feeStructureId);
        const formData = new FormData(this);

        // Disable button and show spinner
        submitBtn.disabled = true;
        btnText.classList.add('d-none');
        spinner.classList.remove('d-none');
        messageDiv.classList.add('d-none');

        fetch("{{ route('connect.fees-structure.studentprogram') }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Show success message
              messageDiv.textContent = data.message;
              messageDiv.classList.remove('alert-danger', 'd-none');
              messageDiv.classList.add('alert-success');

              // Add the new program to the linked programs list
              const linkedProgramsContainer = document.getElementById('linkedPrograms' + feeStructureId);
              if (linkedProgramsContainer) {
                // Check if there's an empty state message and remove it
                const emptyState = linkedProgramsContainer.closest('.modal-body').querySelector('.fcm-empty');
                if (emptyState) {
                  emptyState.remove();
                  // Create the container if it doesn't exist
                  const newContainer = document.createElement('div');
                  newContainer.className = 'row g-3';
                  newContainer.id = 'linkedPrograms' + feeStructureId;
                  linkedProgramsContainer.parentElement.insertBefore(newContainer, linkedProgramsContainer);
                  linkedProgramsContainer.remove();
                }

                const programItem = document.createElement('div');
                programItem.className = 'col-lg-6 fcm-program-item';
                programItem.dataset.searchText = (data.program.code + ' ' + data.program.name + ' ' + data.program.campus).toLowerCase();
                programItem.dataset.pivotId = data.program.id;
                programItem.innerHTML = `
                <div class="fcm-linked-card">
                  <div>
                    <div class="fcm-linked-code">${data.program.code} – ${data.program.name}</div>
                    <div class="fcm-linked-name">${data.program.campus}</div>
                  </div>
                  <a href="#" 
                     class="fcm-unlink-btn unlink-program-btn" 
                     data-pivot-id="${data.program.id}"
                     data-fee-structure-id="${feeStructureId}"
                     title="Unlink this program">
                    <i class="fa fa-times"></i>
                  </a>
                </div>
              `;

                const container = document.getElementById('linkedPrograms' + feeStructureId);
                if (container) {
                  container.appendChild(programItem);

                  // Update the count badge in the main card
                  updateProgramCount(feeStructureId);
                }
              }

              // Reset form
              this.reset();

              // Auto-close modal after 1.5 seconds
              setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('linkProgModal' + feeStructureId));
                if (modal) modal.hide();
                messageDiv.classList.add('d-none');
              }, 1500);
            } else {
              // Show error message
              messageDiv.textContent = data.message;
              messageDiv.classList.remove('alert-success', 'd-none');
              messageDiv.classList.add('alert-danger');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            messageDiv.textContent = 'An error occurred. Please try again.';
            messageDiv.classList.remove('alert-success', 'd-none');
            messageDiv.classList.add('alert-danger');
          })
          .finally(() => {
            // Re-enable button and hide spinner
            submitBtn.disabled = false;
            btnText.classList.remove('d-none');
            spinner.classList.add('d-none');
          });
      });
    });

    // AJAX: Unlink Program
    document.addEventListener('click', function(e) {
      if (e.target.closest('.unlink-program-btn')) {
        e.preventDefault();

        const btn = e.target.closest('.unlink-program-btn');
        const pivotId = btn.dataset.pivotId;
        const feeStructureId = btn.dataset.feeStructureId;

        if (!confirm('Remove this student program?')) {
          return;
        }

        // Show loading state
        const icon = btn.querySelector('i');
        const originalIconClass = icon.className;
        icon.className = 'fa fa-spinner fa-spin';
        btn.style.pointerEvents = 'none';

        fetch(`{{ url('erp/admin/accounts/unlink/fee-structure-studentprogram') }}/${pivotId}`, {
            method: 'GET',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Remove the program item from DOM
              const programItem = document.querySelector(`.fcm-program-item[data-pivot-id="${pivotId}"]`);
              if (programItem) {
                programItem.style.transition = 'opacity 0.3s ease';
                programItem.style.opacity = '0';
                setTimeout(() => {
                  programItem.remove();

                  // Check if list is now empty
                  const container = document.getElementById('linkedPrograms' + feeStructureId);
                  if (container && container.children.length === 0) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'fcm-empty';
                    emptyState.innerHTML = `
                    <i class="fa fa-unlink"></i>
                    <p class="mb-0">No student programs linked yet.</p>
                  `;
                    container.parentElement.appendChild(emptyState);
                    container.remove();
                  }

                  // Update the count badge in the main card
                  updateProgramCount(feeStructureId);
                }, 300);
              }
            } else {
              alert('Failed to unlink program. Please try again.');
              icon.className = originalIconClass;
              btn.style.pointerEvents = '';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            icon.className = originalIconClass;
            btn.style.pointerEvents = '';
          });
      }
    });

    // Helper function to update program count badge
    function updateProgramCount(feeStructureId) {
      const container = document.getElementById('linkedPrograms' + feeStructureId);
      const count = container ? container.querySelectorAll('.fcm-program-item').length : 0;

      // Find and update the count badge in the footer
      const countBadge = document.querySelector(`a[data-bs-target="#viewProgs${feeStructureId}"]`);
      if (countBadge) {
        // Update the text, keeping the icon
        countBadge.innerHTML = `<i class="fa fa-link me-1"></i> ${count}`;
      }
    }

    // AJAX: Toggle Fee Structure Status
    document.addEventListener('click', function(e) {
      if (e.target.closest('.status-toggle-btn')) {
        e.preventDefault();

        const btn = e.target.closest('.status-toggle-btn');
        const feeStructureId = btn.dataset.feeStructureId;
        const badge = btn.querySelector('.badge');
        const icon = btn.querySelector('i');

        // Show loading state
        const originalBadgeHTML = badge.innerHTML;
        badge.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Updating...';
        btn.style.pointerEvents = 'none';

        fetch(`{{ url('erp/admin/accounts/update/feestructure-status') }}/${feeStructureId}`, {
            method: 'GET',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Update the badge based on new status
              const isActive = data.is_payable === 1;

              // Update badge classes
              badge.className = isActive ? 'badge bg-success' : 'badge bg-warning text-dark';

              // Update badge content
              badge.innerHTML = `<i class="fa ${isActive ? 'fa-check-circle' : 'fa-ban'} me-1"></i>${isActive ? 'Active' : 'Inactive'}`;

              // Update data attribute
              btn.dataset.currentStatus = data.is_payable;

              // Brief success flash
              badge.style.transform = 'scale(1.1)';
              setTimeout(() => {
                badge.style.transform = 'scale(1)';
              }, 200);
            } else {
              // Restore original state on error
              badge.innerHTML = originalBadgeHTML;
              alert('Failed to update status. Please try again.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            badge.innerHTML = originalBadgeHTML;
            alert('An error occurred. Please try again.');
          })
          .finally(() => {
            btn.style.pointerEvents = '';
            badge.style.transition = 'transform 0.2s ease';
          });
      }
    });
  });
</script>