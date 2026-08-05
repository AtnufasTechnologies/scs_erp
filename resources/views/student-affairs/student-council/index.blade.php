@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Student Council</h3>

      <div class="card mb-3 shadow-sm">
        <div class="card-header">Create Council</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dean.student-council.store') }}" class="row g-2">
            @csrf
            <div class="col-md-4"><input name="title" class="form-control" placeholder="Council title" required></div>
            <div class="col-md-3">
              <select name="academic_year" class="form-select" required>
                <option value="">Select Batch</option>
                @foreach($batches as $batch)
                <option value="{{ $batch->batch_name }}" {{ old('academic_year') === $batch->batch_name ? 'selected' : '' }}>
                  {{ $batch->batch_name }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3"><input name="constituted_on" type="date" class="form-control"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Create</button></div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Councils</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered table-sm">
            <thead>
              <tr>
                <th>Title</th>
                <th>Year</th>
                <th>Members</th>
                <th>Meetings</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($councils as $council)
              <tr>
                <td>{{ $council->title }}</td>
                <td>{{ $council->academic_year }}</td>
                <td>{{ $council->members_count }}</td>
                <td>{{ $council->meetings_count }}</td>
                <td>{{ $council->status }}</td>
                <td>
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('dean.student-council.members', $council->id) }}">Members</a>
                  <a class="btn btn-sm btn-outline-secondary" href="{{ route('dean.student-council.meetings', $council->id) }}">Meetings</a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          {{ $councils->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')