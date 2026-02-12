@include('includes.header')
<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-12">
      <h2 class="fw-bold">Routine UI/UX</h2>
      <p class="text-muted">Visual routine overview for faculty and students.</p>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="card shadow-lg border-0">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="bg-gradient" style="background: linear-gradient(135deg, #1b329c 0%, #3a2351 100%); color: #fff;">
                <tr>
                  <th>Day</th>
                  <th>Hour</th>
                  <th>Subject</th>
                  <th>Batch</th>
                  <th>Semester</th>
                  <th>Lecture Hall</th>
                  <th>Course</th>
                  <th>Type</th>
                </tr>
              </thead>
              <tbody>
                @foreach($routine as $entry)
                <tr>
                  <td>{{ $entry['weekday'] ?? '-' }}</td>
                  <td>{{ $entry['hour'] ?? '-' }}</td>
                  <td>{{ $entry['subject'] ?? '-' }}</td>
                  <td>{{ $entry['batch'] ?? '-' }}</td>
                  <td>{{ $entry['semester'] ?? '-' }}</td>
                  <td>{{ $entry['lecture_hall'] ?? '-' }}</td>
                  <td>{{ $entry['course'] ?? '-' }}</td>
                  <td><span class="badge bg-info">{{ $entry['course_type'] ?? '-' }}</span></td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('includes.footer')