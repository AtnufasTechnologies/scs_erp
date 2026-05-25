@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">


  @if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">
      <i class="fas fa-puzzle-piece"></i> Combination Master
    </h1>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCombinationModal">
      <i class="fas fa-plus"></i> Add New Combination
    </button>
  </div>

  @if (count($data) > 0)
  <div class="row">
    @foreach ($data as $subjectId => $combinations)
    @php
    $firstItem = $combinations->first();
    $subjectName = $firstItem->subjectmaster->title ?? 'Unknown Subject';
    @endphp
    <div class="col-lg-6 col-xl-4 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title mb-0">
            <i class="fas fa-book"></i> {{ $subjectName }}
          </h5>
          <small class="text-white-50">Subject ID: {{ $subjectId }}</small>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>Program</th>
                  <th>Campus</th>
                  <th>Batch</th>
                  <th>Seats</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($combinations as $item)
                <tr>
                  <td>
                    <small class="font-weight-bold"><span class="badge badge-warning">{{ $item->studentprograminfo->code ?? 'N/A' }}</span> {{ $item->studentprograminfo->name ?? 'N/A' }}</small>
                  </td>
                  <td>
                    <small>{{ $item->campusmaster->name ?? 'N/A' }}</small>
                  </td>
                  <td>
                    <small>{{ $item->batchmaster->batch_name ?? 'N/A' }} <span class="badge badge-info">{{ $item->program_type }}</span></small>
                  </td>
                  <td>
                    @if($item->total_seats)
                    <span class="badge badge-success">{{ $item->total_available_seats ?? 0 }}/{{ $item->total_seats }}</span>
                    @else
                    <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editCombinationModal{{ $item->id }}" title="Edit">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-danger" onclick="confirmDelete({{ $item->id }})" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-light">
          <small class="text-muted">Total Combinations: {{ $combinations->count() }}</small>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @else
  <div class="alert alert-info text-center">
    <i class="fas fa-info-circle"></i> No combination found. Click "Add New Combination" to create one.
  </div>
  @endif

</div>

<!-- Edit Modals for all combinations -->
@if (count($data) > 0)
@foreach ($data as $subjectId => $combinations)
@php
$firstItem = $combinations->first();
$subjectName = $firstItem->subjectmaster->title ?? 'Unknown Subject';
@endphp
@foreach ($combinations as $item)
<div class="modal fade" id="editCombinationModal{{ $item->id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Edit Combination</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.update.combination', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Subject *</label>
                <input type="text" class="form-control" value="{{ $subjectName }}" disabled>
                <input type="hidden" name="subject_id" value="{{ $item->subject_id }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Program *</label>
                <input type="text" class="form-control" value="{{ $item->studentprograminfo->name ?? 'N/A' }}" disabled>
                <input type="hidden" name="student_program_id" value="{{ $item->student_program_id }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Campus *</label>
                <input type="text" class="form-control" value="{{ $item->campusmaster->name ?? 'N/A' }}" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Batch *</label>
                <input type="text" class="form-control" value="{{ $item->batchmaster->batch_name ?? 'N/A' }}" disabled>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Program Type</label>
                <select name="program_type" class="form-control">
                  <option value="">--Select--</option>
                  <option value="UG" {{ $item->program_type == 'UG' ? 'selected' : '' }}>UG</option>
                  <option value="PG" {{ $item->program_type == 'PG' ? 'selected' : '' }}>PG</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Total Seats *</label>
                <input type="number" name="total_seats" class="form-control" value="{{ $item->total_seats }}" min="0" required>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Available Seats *</label>
                <input type="number" name="total_available_seats" class="form-control" value="{{ $item->total_available_seats }}" min="0" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">Update Combination</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
@endforeach
@endif

<!-- Add Combination Modal -->
<div class="modal fade" id="addCombinationModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Add New Combination</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.link.std.programs') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Subject *</label>
                <select name="subject_id" class="form-control dselect-example" required>
                  <option value="">--Select Subject--</option>
                  @foreach($subjects as $subject)
                  <option value="{{ $subject->id }}">{{ $subject->title }} - {{ $subject->campusmaster->name ?? '' }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Batch *</label>
                <select name="batch_id" class="form-control" required>
                  <option value="">--Select Batch--</option>
                  @foreach($batches as $batch)
                  <option value="{{ $batch->id }}">{{ $batch->batch_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Programs * (Select multiple)</label>
                <select name="programs[]" class="form-control dselect-example" multiple required size="8">
                  @foreach($programs as $program)
                  <option value="{{ $program->id }}">
                    {{ $program->name }}
                    @if($program->campusmaster)
                    - {{ $program->campusmaster->name }}
                    @endif
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Program Type *</label>
                <select name="program_type" class="form-control" required>
                  <option value="">--Select--</option>
                  <option value="UG">UG</option>
                  <option value="PG">PG</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Total Seats *</label>
                <input type="number" name="total_seats" class="form-control" min="0" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Combination</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Form (hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
  @csrf
  @method('DELETE')
</form>

<script>
  function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this combination? This action cannot be undone.')) {
      const form = document.getElementById('deleteForm');
      form.action = '{{ route("admin.delete.combination", "") }}/' + id;
      form.submit();
    }
  }
</script>

<style>
  .card {
    border: none;
    transition: transform 0.2s;
  }

  .card:hover {
    transform: translateY(-5px);
  }

  .table td,
  .table th {
    vertical-align: middle;
  }

  .badge-sm {
    font-size: 0.7rem;
  }

  .btn-group-sm>.btn {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
  }
</style>

@include('includes.footer')