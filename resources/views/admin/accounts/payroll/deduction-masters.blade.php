@include('includes.header')
@include('admin.accounts.sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Deduction Masters</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.payroll.index') }}">Payroll</a></li>
            <li class="breadcrumb-item active">Deduction Masters</li>
          </ol>
        </nav>
      </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-3 mb-3">
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0">Create Standard Deduction Master</h6>
          </div>
          <div class="card-body">
            <form method="POST" action="{{ route('admin.payroll.deductions.masters.store') }}">
              @csrf
              <h6 class="mb-3 text-danger"><i class="fas fa-minus-circle"></i> Deductions</h6>
              <div class="mb-2">
                <label class="form-label">Deduction Name <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control">
              </div>
              <div class="row">
                <div class="col-lg-6">
                  <div class="mb-2">
                    <label class="form-label">EPF (Employee Provident Fund)</label>
                    <input type="number" name="epf" class="form-control" value="0" min="0">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-2">
                    <label class="form-label">PT ( Professional Tax )</label>
                    <input type="number" name="pt" class="form-control" value="0" min="0">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-2">
                    <label class="form-label">ESIC <small>Health Insuarance</small></label>
                    <input type="number" name="esic" class="form-control" value="0" min="0">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-2">
                    <label class="form-label">TDS % (Tax Deducted at Source)</label>
                    <input type="number" name="tds" class="form-control" value="0" min="0">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-2">
                    <label class="form-label">LWF (Labor Welfare Fund)</label>
                    <input type="number" name="lwf" class="form-control" value="0" min="0">
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="mb-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                      <option value="1">Active</option>
                      <option value="0">Inactive</option>
                    </select>
                  </div>
                </div>
              </div>


              <button class="btn btn-primary" type="submit">
                <i class="fas fa-plus me-1"></i>Create Master
              </button>
            </form>
            <div class="alert alert-info mt-3 mb-0">
              Supported deduction masters: TDS, EPF, PT, LWF, ESIC only.
            </div>
            <div class="alert alert-warning mt-2 mb-0">
              Leave-based salary deduction is monthly/manual during payroll generation. Do not assign it via deduction master.
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="row">
          <div class="col-lg-12">
            <div class="card mb-3">
              <div class="card-header">
                <h6 class="mb-0">Deduction Master List</h6>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped mb-0">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>EPF</th>
                        <th>PT</th>
                        <th>TDS %</th>
                        <th>ESIC</th>
                        <th>LWF</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($masters as $master)
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $master->title }}</td>
                        <td>{{ $master->EPF }}</td>
                        <td>{{ $master->PT }}</td>
                        <td>{{ $master->TDS  }}</td>
                        <td>{{ $master->ESIC  }}</td>
                        <td>{{ $master->LWF  }}</td>
                        <td>
                          <span class="badge bg-{{ $master->status === 1 ? 'success' : 'secondary' }}">{{ $master->status === 1 ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                          <form method="POST" action="{{ route('admin.payroll.deductions.masters.toggle', $master->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Toggle</button>
                          </form>
                        </td>
                      </tr>
                      @empty
                      <tr>
                        <td colspan="6" class="text-center py-3">No deduction masters created yet.</td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>


          </div>
          <div class="col-lg-12">

            <div class="card h-100">
              <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">Assign Deduction To Faculties</h6>
              </div>
              <div class="card-body">
                <form method="POST" action="{{ route('admin.payroll.deductions.assignments.store') }}">
                  @csrf
                  <div class="mb-2">
                    <label class="form-label">Deduction Master <span class="text-danger">*</span></label>
                    <select name="deduction_master_id" class="form-select" required>
                      <option value="">Select Deduction Master</option>
                      @foreach($masters as $master)
                      <option value="{{ $master->id }}">{{ $master->title }} </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Faculties <span class="text-danger">*</span></label>
                    <select name="faculty_ids[]" class="form-select select-multiple" multiple required>
                      @foreach($faculties as $faculty)
                      <option value="{{ $faculty->id }}">{{ $faculty->USER_CODE }} - {{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</option>
                      @endforeach
                    </select>

                  </div>


                  <div class="mt-2">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control"></textarea>
                  </div>
                  <button class="btn btn-warning mt-3" type="submit">
                    <i class="fas fa-user-check me-1"></i>Assign To Faculties
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="col-lg-6">

      </div>
      <div class="col-lg-6"></div>
    </div>




    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Faculty Deduction Assignments</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Faculty</th>
                <th>Deduction</th>
                <th>Override</th>
                <th>Effective</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($assignments as $assignment)
              <tr>
                <td>{{ optional($assignment->faculty)->FIRST_NAME }} {{ optional($assignment->faculty)->LAST_NAME }}</td>
                <td>
                  {{ optional($assignment->deductionMaster)->title }}
                  @php
                  $master = optional($assignment->deductionMaster);
                  $parts = [];
                  if ((float) ($master->TDS ?? $master->tds ?? 0) > 0) $parts[] = 'TDS';
                  if ((float) ($master->EPF ?? $master->epf ?? 0) > 0) $parts[] = 'EPF';
                  if ((float) ($master->PT ?? $master->pt ?? 0) > 0) $parts[] = 'PT';
                  if ((float) ($master->LWF ?? $master->lwf ?? 0) > 0) $parts[] = 'LWF';
                  if ((float) ($master->ESIC ?? $master->esic ?? 0) > 0) $parts[] = 'ESIC';
                  @endphp
                  @if(!empty($parts))
                  <small class="text-muted d-block">Applicable: {{ implode(', ', $parts) }}</small>
                  @endif
                </td>
                <td>
                  @if(!is_null($assignment->amount_override))
                  Rs {{ number_format($assignment->amount_override, 2) }}
                  @elseif(!is_null($assignment->percentage_override))
                  {{ number_format($assignment->percentage_override, 2) }}%
                  @else
                  -
                  @endif
                </td>
                <td>
                  {{ optional($assignment->effective_from)->format('d-m-Y') ?? '-' }}
                  to
                  {{ optional($assignment->effective_to)->format('d-m-Y') ?? 'Open' }}
                </td>
                <td><span class="badge bg-{{ $assignment->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($assignment->status) }}</span></td>
                <td>
                  <form method="POST" action="{{ route('admin.payroll.deductions.assignments.toggle', $assignment->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary">Toggle</button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center py-3">No assignments found.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')