<table>
  <thead>
    <tr>
      <th>S.No</th>
      <th>Application Code</th>
      <th>Name</th>
      <th>Mobile</th>
      <th>Email</th>
      <th>Program</th>
      <th>Interview Date</th>
      <th>Roll No</th>
      <th>Document Validated</th>
      <th>Subject Selected</th>
      <th>Uniform Applied</th>
      <th>Fee Paid</th>
      <th>I-Card Generated</th>
      <th>Contract Signed</th>
      <th>Enrollment Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $index => $item)
    @php
    $rollNo = \App\Models\StudentMaster::where('user_code', $item->applicationinfo->application_code ?? null)->value('roll_no');
    @endphp
    <tr>
      <td>{{ $index + 1 }}</td>
      <td>{{ $item->applicationinfo->application_code ?? 'N/A' }}</td>
      <td>{{ $item->registrationmaster->first_name ?? '' }} {{ $item->registrationmaster->last_name ?? '' }}</td>
      <td>{{ $item->registrationmaster->mobile_no ?? 'N/A' }}</td>
      <td>{{ $item->registrationmaster->mail_id ?? 'N/A' }}</td>
      <td>{{ $item->applicationinfo->stdCourseMaster->code ?? 'N/A' }} - {{ $item->applicationinfo->stdCourseMaster->name ?? 'N/A' }}</td>
      <td>{{ $item->interview_datetime ?? 'N/A' }}</td>
      <td>{{ $rollNo ?? 'Not Generated' }}</td>
      <td>{{ $item->is_doc_validated == 1 ? 'Yes' : 'No' }}</td>
      <td>{{ $item->is_subject_selected == 1 ? 'Yes' : 'No' }}</td>
      <td>{{ $item->uniform_applied == 1 ? 'Yes' : 'No' }}</td>
      <td>{{ $item->fee_paid == 1 ? 'Yes' : 'No' }}</td>
      <td>{{ $item->icard_generated == 1 ? 'Yes' : 'No' }}</td>
      <td>{{ $item->contract_signed == 1 ? 'Yes' : 'No' }}</td>
      <td>{{ $item->enroll_status == 1 ? 'Enrolled' : 'Pending' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>