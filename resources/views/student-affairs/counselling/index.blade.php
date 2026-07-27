@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Counselling & Welfare</h3>

      <div class="card mb-3 shadow-sm">
        <div class="card-header">Create Counselling Referral</div>
        <div class="card-body">
          <form method="POST" action="{{ route('dean.counselling.store') }}" class="row g-2">
            @csrf
            <div class="col-md-3">
              <select name="student_id" class="dselect-example" required>
                <option value="">Select student</option>
                @foreach($students as $student)
                <option value="{{ $student->id }}">{{ $student->roll_no }} - {{ $student->first_name }} {{ $student->last_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <select name="risk_level" class="form-select" required>
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </select>
            </div>
            <div class="col-md-2"><input name="referral_source" class="form-control" value="mentoring" required placeholder="referral source"></div>
            <div class="col-md-2"><input type="date" name="referred_on" class="form-control"></div>
            <div class="col-md-3">
              <select name="concern_category_id" class="form-select">
                <option value="">Select concern category</option>
                @foreach($concernCategories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12"><input name="summary" class="form-control" placeholder="Referral summary" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Create Case</button></div>
          </form>
          <div class="mt-2">
            <a href="{{ route('dean.concern-categories.index') }}" class="btn btn-sm btn-outline-secondary">Concern Category Master</a>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Counselling Cases</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Case No</th>
                <th>Student</th>
                <th>Risk</th>
                <th>Status</th>
                <th>Concern Category</th>
                <th>Referred On</th>
                <th>Summary</th>
              </tr>
            </thead>
            <tbody>
              @foreach($cases as $case)
              <tr>
                <td>{{ $case->case_no }}</td>
                <td>{{ ($case->student->first_name ?? '') . ' ' . ($case->student->last_name ?? '') }}</td>
                <td>{{ strtoupper($case->risk_level) }}</td>
                <td>{{ strtoupper($case->status) }}</td>
                <td>{{ $case->concern_category ?: '-' }}</td>
                <td>{{ optional($case->referred_on)->format('d-M-Y') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($case->summary, 100) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          {{ $cases->links() }}
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')