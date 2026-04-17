<?php

use App\Helpers\Qs;
use App\Models\Campus;
use App\Models\MainProgram;

$programs = Qs::getProgramGroups();
$campus = MainProgram::with('campus')->get();
?>
@include('includes.header')
@include('admin.admission.sidebar')
<h3>UG - Applications </h3>



<div class="container-fluid">
  <div class="row">
    <div class="col-lg-8">
      <form method="POST" action="{{ route('send.phase1.notification') }}">
        @csrf
        <div class="row ">
          <div class="col-lg-6">
            <label for="programGroup" class="form-label">Select Program</label>
            <select name="programs[]" class="form-select select-multiple" multiple>
              <option value="">-- Select Program Group--</option>
              @foreach($programs as $program)
              <option value="{{ $program->id }}">
                {{$program->code}} - {{ $program->name }} ({{ count($program->applicationCount)  }})
              </option>
              @endforeach
            </select>
          </div>

          <div class="col-lg-6">
            <label for="interviewDate" class="form-label">Interview Date</label>
            <div class="input-group">
              <input type="datetime-local" name="interview_time" class="form-control" required>
              <button type="submit" class="btn btn-main">
                <i class="fas fa-sms"></i> Send Interview SMS
              </button>
            </div>

          </div>
        </div>
      </form>
    </div>
  </div>
</div>



<div class="container-fluid mt-4">


  <table class="table table-hover" id="exportTable">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Campus</th>
        <th>Batch</th>
        <th>Code #</th>
        <th>Applicant Name</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Academic Dept</th>
        <th>Selected Combination </th>
        <th> Status</th>
        <th>Applied On</th>
        <th>Actions</th>
        <th>Interview</th>
        <th>Transfer</th>
        <th>Payment</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data as $item)
      <tr>
        <td>{{ $loop->iteration  }}</td>
        <td>{{$item->registrationmaster->campusmaster->name}}</td>
        <td>{{$item->registrationmaster->batch}}</td>
        <td><a href="{{ route('download.admission.application-form', $item->application_code) }}" class="btn btn-main " title="View">{{ $item->application_code }}</a></td>
        <td><span class="text-capitalize">{{ $item->registrationmaster->first_name }} {{ $item->registrationmaster->last_name }}</span></td>
        <td>{{ $item->registrationmaster->mail_id ?? '-' }}</td>
        <td>{{ $item->registrationmaster->mobile_no ?? '-' }}</td>
        <td>{{$item->academicdepartmentinfo->title ?? '-'}}</td>
        <td>{{ $item->stdCourseMaster->code ?? '-' }} -{{ $item->stdCourseMaster->name ?? '-' }}</td>
        <td>
          @if($item->payment_gateway_status == null)
          <span class="badge bg-danger">Not Paid</span>
          @elseif($item->payment_gateway_status == 'success')
          <span class="badge bg-success">Paid</span>
          @else
          <span class="badge bg-info">Form Saved</span>
          @endif
        </td>
        <td>{{ $item->created_at->format('d M Y') }}</td>
        <td>
          <a href="{{ route('admission.edit.application', $item->id) }}" class="btn  btn-dark mb-3" title="Edit">
            <i class="fas fa-edit"></i>
          </a>

        </td>

        <td>
          <!-- Button to trigger modal -->
          <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#sendSmsModal{{ $item->id }}" title="Send Interview SMS">
            <i class="fas fa-sms"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="sendSmsModal{{ $item->id }}" tabindex="-1" aria-labelledby="sendSmsModalLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog">
              <form method="POST" action="{{ route('send.phase1.notification.single') }}">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="sendSmsModalLabel{{ $item->id }}">Send Interview SMS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Interview Date & Time</label>
                      <input type="datetime-local" name="interview_time" class="form-control" required>
                    </div>
                    <input type="hidden" name="id" value="{{ $item->registrationmaster->id }}">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-main"><i class="fas fa-sms"></i> Send SMS</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </td>

        <td>
          <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#campusShifter{{ $item->id }}" title="Campus Shifter">
            <i class="fas fa-exchange-alt"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="campusShifter{{ $item->id }}" tabindex="-1" aria-labelledby="campusShifterLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog">
              <form method="POST" action="{{ route('applicant.campus.shifter') }}">
                @csrf
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="campusShifterLabel{{ $item->id }}"> Shift <span class="text-capitalize">{{ $item->registrationmaster->first_name }} {{ $item->registrationmaster->last_name }}</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label for="">Select Campus <span class="text-danger">*</span></label>
                      <select name="campus" class="form-select" required id="transferCampus{{ $item->id }}">
                        <option value="">--Select--</option>
                        @foreach ($campus as $c)
                        <option value="{{ $c->id }}">
                          {{$c->campus->name}} - {{ $c->name }}
                        </option>
                        @endforeach

                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="">Select Department <span class="text-danger">*</span></label>
                      <select name="department" class="form-select" required id="transferDepartment{{ $item->id }}">
                        <option value="">--Select--</option>
                      </select>
                    </div>

                    <script>
                      document.getElementById('transferCampus{{ $item->id }}').addEventListener('change', function() {
                        const campusId = this.value;
                        const departmentSelect = document.getElementById('transferDepartment{{ $item->id }}');

                        if (campusId) {
                          fetch("{{ route('get.departments.by.campusprogram', '') }}/" + campusId)
                            .then(response => response.json())
                            .then(data => {
                              departmentSelect.innerHTML = '<option value="">--Select--</option>';
                              data.forEach(dept => {
                                const option = document.createElement('option');
                                option.value = dept.id;
                                option.textContent = dept.title;
                                departmentSelect.appendChild(option);
                              });
                            })
                            .catch(error => console.error('Error:', error));
                        }
                      });
                    </script>

                    <div class="mb-3">
                      <label for="">Select Course <span class="text-danger">*</span></label>
                      <select name="course" class="form-select" required id="transferCourse{{ $item->id }}">
                        <option value="">--Select--</option>
                      </select>
                    </div>

                    <script>
                      document.getElementById('transferDepartment{{ $item->id }}').addEventListener('change', function() {
                        const departmentId = this.value;
                        const campusId = document.getElementById('transferCampus{{ $item->id }}').value;
                        const courseSelect = document.getElementById('transferCourse{{ $item->id }}');

                        if (departmentId) {
                          fetch("{{ route('get.programs.bydepartment', ['', '']) }}/" + departmentId + "/" + campusId)
                            .then(response => response.json())
                            .then(data => {
                              courseSelect.innerHTML = '<option value="">--Select--</option>';
                              data.forEach(program => {
                                const option = document.createElement('option');
                                option.value = program.student_program_id;
                                option.textContent = program.studentprograminfo.code + ' - ' + program.studentprograminfo.name;
                                courseSelect.appendChild(option);
                              });
                            })
                            .catch(error => console.error('Error:', error));
                        }
                      });
                    </script>
                    <input type="hidden" name="application_id" value="{{ $item->id }}">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Initiate Shift</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </td>

        <td>
          <a href="{{ route('admission.verify.payment', $item->id) }}">
            <button class="btn btn-primary">Verify</button>
          </a>
        </td>
      </tr>
      @endforeach

    </tbody>
  </table>


</div>




@include('includes.footer')