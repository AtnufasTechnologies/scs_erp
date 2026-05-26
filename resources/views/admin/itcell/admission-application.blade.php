@include('includes.header')
@include('admin.sidebar')

<div class="container-fluid p-4">
  <h4>Admission Applications</h4>

  <table class="table table-bordered">
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
        <td>{{ $application->status ?? '' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>


</div>

@include('includes.footer')