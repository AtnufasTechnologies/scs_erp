@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <h4>Admission Applications</h4>

  <table class="table table-bordered" id="myTable">
    <thead>
      <tr>
        <th>#</th>
        <th>Type|Campus </th>
        <th>Code</th>
        <th>Applicant Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Course Applied</th>
        <th>Payment Status</th>
        <th>Action</th>

      </tr>
    </thead>
    <tbody>
      @foreach($data as $application)
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $application->registrationmaster->application_type ?? '' }} | {{ $application->registrationmaster->campus_id == 1 ? 'Sonada' : 'Siliguri' }}</td>
        <td>{{ $application->application_code }}</td>
        <td>{{ $application->registrationmaster->first_name ?? '' }} {{ $application->registrationmaster->last_name ?? '' }}</td>
        <td>{{ $application->registrationmaster->mail_id ?? ''}}</td>
        <td>{{ $application->registrationmaster->mobile_no ?? '' }}</td>
        <td>{{ $application->stdCourseMaster->code ?? '' }} -{{ $application->stdCourseMaster->name ?? ''}}</td>
        <td>{{ $application->payment_gateway_status ?? '' }}</td>
        <td>@if ($application->payment_gateway_status == 'success')
          <a href="{{ route('itcell.admission.verify.payment', $application->id) }}">
            <button class="badge badge-success">Verify</button>
          </a>
          @else
          <!-- Button trigger modal -->
          <button type="button" class="badge bg-warning" data-bs-toggle="modal" data-bs-target="#updatePayment{{$application->id}}">
            Update Payment
          </button>

          <!-- Modal -->
          <div class="modal fade" id="updatePayment{{$application->id}}" tabindex="-1" aria-labelledby="updatePaymentLabel{{$application->id}}" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Update</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" id="applicationId{{$application->id}}" value="{{$application->id}}">
                  <div class="mb-3">
                    <div class="mb-3">
                      <div class="alert alert-info">
                        <p>Update the Application Code as per the Paid Merchant Transaction ID in the Payment gateway Portal to <strong>FIX API</strong></p>
                      </div>
                    </div>
                    <label>Application Code </label>
                    <input type="text" class="form-control" value="{{$application->application_code}}">

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Update </button>
                  </div>
                </div>
              </div>
            </div>


            @endif
        </td>

      </tr>
      @endforeach
    </tbody>
  </table>


</div>

@include('includes.footer')