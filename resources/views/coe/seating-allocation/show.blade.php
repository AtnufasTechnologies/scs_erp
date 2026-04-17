@include('includes.header')
<div class="wrapper">
  @include('coe.sidebar')

  <div class="p-4 mb-4 bg-gradient-primary text-white rounded-3 shadow">
    <div class="container-fluid py-3">
      <h1 class="display-6 fw-bold">Seating Allocation Details</h1>
      <p class="fs-6 mb-0 text-dark">View seating allocation information</p>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-10">
        <div class="card shadow-sm">
          <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-chair"></i> Seating Allocation Information</h5>
            <a href="{{ route('admin.seating-allocation.edit', $allocation->id) }}" class="btn btn-warning btn-sm">
              <i class="fa fa-edit"></i> Edit
            </a>
          </div>
          <div class="card-body">
            <div class="row">
              <!-- Left Column -->
              <div class="col-md-6">
                <div class="card mb-3 border-primary">
                  <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fa fa-clipboard-list"></i> Exam & Location Details</h6>
                  </div>
                  <div class="card-body">
                    <table class="table table-sm">
                      <tr>
                        <th width="40%">Exam:</th>
                        <td>
                          <strong>{{ $allocation->exam->name ?? 'N/A' }}</strong><br>
                          <span class="badge bg-secondary">{{ $allocation->exam->exam_type ?? '' }}</span>
                        </td>
                      </tr>
                      <tr>
                        <th>Exam Date:</th>
                        <td>{{ $allocation->exam->exam_date ?? 'Not Set' }}</td>
                      </tr>
                      <tr>
                        <th>Room:</th>
                        <td>
                          <strong>{{ $allocation->room->name ?? 'N/A' }}</strong><br>
                          <small class="text-muted">Block: {{ $allocation->room->block ?? 'N/A' }}</small>
                        </td>
                      </tr>
                      <tr>
                        <th>Room Capacity:</th>
                        <td>{{ $allocation->room->capacity ?? 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Seat Number:</th>
                        <td>
                          <span class="badge bg-primary fs-5">{{ $allocation->seat_no }}</span>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Right Column -->
              <div class="col-md-6">
                <div class="card mb-3 border-success">
                  <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fa fa-user-graduate"></i> Student Details</h6>
                  </div>
                  <div class="card-body">
                    <table class="table table-sm">
                      <tr>
                        <th width="40%">Student Name:</th>
                        <td>
                          <strong class="text-capitalize">
                            {{ $allocation->student->first_name ?? '' }}
                            {{ $allocation->student->last_name ?? '' }}
                          </strong>
                        </td>
                      </tr>
                      <tr>
                        <th>Registration No:</th>
                        <td>{{ $allocation->student->register_no ?? 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Roll Number:</th>
                        <td><strong>{{ $allocation->student->roll_no ?? 'N/A' }}</strong></td>
                      </tr>
                      <tr>
                        <th>Campus:</th>
                        <td>
                          <i class="fa fa-building"></i> {{ $allocation->student->campusmaster->name ?? 'N/A' }}
                        </td>
                      </tr>
                      <tr>
                        <th>Department:</th>
                        <td>{{ $allocation->student->department->name ?? 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Program:</th>
                        <td>{{ $allocation->student->program->name ?? 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Email:</th>
                        <td>{{ $allocation->student->email ?? 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Phone:</th>
                        <td>{{ $allocation->student->phone ?? 'N/A' }}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Information -->
            <div class="card border-warning">
              <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="fa fa-info-circle"></i> Additional Information</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <p class="mb-1">
                      <strong>Created At:</strong>
                      {{ $allocation->created_at ? $allocation->created_at->format('d M Y, h:i A') : 'N/A' }}
                    </p>
                  </div>
                  <div class="col-md-6">
                    <p class="mb-1">
                      <strong>Last Updated:</strong>
                      {{ $allocation->updated_at ? $allocation->updated_at->format('d M Y, h:i A') : 'N/A' }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mt-4">
              <a href="{{ route('admin.seating-allocation.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
              </a>
              <div>
                <a href="{{ route('admin.seating-allocation.edit', $allocation->id) }}" class="btn btn-warning">
                  <i class="fa fa-edit"></i> Edit
                </a>
                <form action="{{ route('admin.seating-allocation.destroy', $allocation->id) }}"
                  method="POST" class="d-inline"
                  onsubmit="return confirm('Are you sure you want to delete this seating allocation?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('includes.footer')