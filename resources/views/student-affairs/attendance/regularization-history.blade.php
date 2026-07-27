@include('includes.header')
<div class="wrapper">
  @include('student-affairs.sidebar')
  <main class="page-content">
    <div class="container-fluid py-3">
      <h3>Regularization History: {{ $regularization->request_no }}</h3>
      <div class="card shadow-sm">
        <div class="card-header">Audit Trail</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Student</th>
                <th>Date</th>
                <th>Original</th>
                <th>Effective</th>
                <th>Remarks</th>
                <th>Actioned At</th>
              </tr>
            </thead>
            <tbody>
              @foreach($regularization->items as $item)
              <tr>
                <td>{{ ($item->student->first_name ?? '') . ' ' . ($item->student->last_name ?? '') }}</td>
                <td>{{ optional($item->attendance_date)->format('d-M-Y') }}</td>
                <td><span class="badge bg-danger">{{ $item->original_status }}</span></td>
                <td><span class="badge bg-success">{{ $item->effective_status }}</span></td>
                <td>{{ $item->remarks }}</td>
                <td>{{ optional($item->actioned_at)->format('d-M-Y H:i') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
@include('includes.footer')