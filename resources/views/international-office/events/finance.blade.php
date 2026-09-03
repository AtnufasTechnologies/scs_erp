@include('includes.header')
@include('international-office.sidebar')

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-0">Event Finance Ledger</h4>
      <small class="text-muted">Track debit and credit notes separately for this event.</small>
    </div>
    <a href="{{ route('international-office.events.index') }}" class="btn btn-outline-secondary btn-sm">Back to Events</a>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3"><strong>Event:</strong> {{ optional($event->activityType)->title ?: '-' }}</div>
        <div class="col-md-5"><strong>Institution:</strong> {{ $event->visiting_institution_name }}</div>
        <div class="col-md-4"><strong>Trip:</strong> {{ optional($event->trip_start_date)->format('d M Y') }} - {{ optional($event->trip_end_date)->format('d M Y') }}</div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Total Debit</div>
          <div class="h4 mb-0 text-danger">{{ number_format($totalDebit, 2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Total Credit</div>
          <div class="h4 mb-0 text-success">{{ number_format($totalCredit, 2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small">Net Expense (Debit - Credit)</div>
          <div class="h4 mb-0 {{ $netExpense >= 0 ? 'text-primary' : 'text-warning' }}">{{ number_format($netExpense, 2) }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
      <h6 class="mb-0">Add Debit/Credit Note</h6>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('international-office.events.finances.store', $event->id) }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label">Type <span class="text-danger">*</span></label>
            <select name="entry_type" class="form-select" required>
              <option value="debit" {{ old('entry_type', 'debit') === 'debit' ? 'selected' : '' }}>Debit</option>
              <option value="credit" {{ old('entry_type') === 'credit' ? 'selected' : '' }}>Credit</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" min="0.01" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="note_date" class="form-control" value="{{ old('note_date', now()->format('Y-m-d')) }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Reference No</label>
            <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}" maxlength="100">
          </div>
          <div class="col-md-12">
            <label class="form-label">Note</label>
            <textarea name="note_text" class="form-control" rows="2" maxlength="2000">{{ old('note_text') }}</textarea>
          </div>
        </div>
        <div class="text-end mt-3">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Add Note</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
      <h6 class="mb-0">Finance Notes</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Reference</th>
              <th>Note</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($notes as $note)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ optional($note->note_date)->format('d M Y') }}</td>
              <td><span class="badge {{ $note->entry_type === 'debit' ? 'bg-danger' : 'bg-success' }}">{{ ucfirst($note->entry_type) }}</span></td>
              <td>{{ number_format((float) $note->amount, 2) }}</td>
              <td>{{ $note->reference_no ?: '-' }}</td>
              <td>{{ $note->note_text ?: '-' }}</td>
              <td class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editFinanceNoteModal{{ $note->id }}">Edit</button>
                <form method="POST" action="{{ route('international-office.events.finances.destroy', [$event->id, $note->id]) }}" onsubmit="return confirm('Delete this note?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>

            <div class="modal fade" id="editFinanceNoteModal{{ $note->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Finance Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="{{ route('international-office.events.finances.update', [$event->id, $note->id]) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="entry_type" class="form-select" required>
                          <option value="debit" {{ $note->entry_type === 'debit' ? 'selected' : '' }}>Debit</option>
                          <option value="credit" {{ $note->entry_type === 'credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" min="0.01" step="0.01" name="amount" class="form-control" value="{{ (float) $note->amount }}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="note_date" class="form-control" value="{{ optional($note->note_date)->format('Y-m-d') }}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label">Reference No</label>
                        <input type="text" name="reference_no" class="form-control" value="{{ $note->reference_no }}" maxlength="100">
                      </div>
                      <div class="mb-0">
                        <label class="form-label">Note</label>
                        <textarea name="note_text" class="form-control" rows="3" maxlength="2000">{{ $note->note_text }}</textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            @empty
            <tr>
              <td colspan="7" class="text-center text-muted">No finance notes found.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')