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
      <th>Document Verified</th>
      <th>Proficiency Test Status</th>
      <th>Proficiency Test Remarks</th>
      <th>Department Interview</th>
      <th>Dept Remark</th>
      <th>Management Interview</th>
      <th>Management Remark</th>
      <th>Final Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $index => $item)
    <tr>
      <td>{{ $index + 1 }}</td>
      <td>{{ $item->applicationinfo->application_code ?? 'N/A' }}</td>
      <td>{{ $item->registrationmaster->first_name ?? '' }} {{ $item->registrationmaster->last_name ?? '' }}</td>
      <td>{{ $item->registrationmaster->mobile_no ?? 'N/A' }}</td>
      <td>{{ $item->registrationmaster->mail_id ?? 'N/A' }}</td>
      <td>{{ $item->applicationinfo->stdprogramMaster->code ?? 'N/A' }} - {{ $item->applicationinfo->stdprogramMaster->name ?? 'N/A' }}</td>
      <td>{{ $item->interview_datetime ?? 'N/A' }}</td>
      <td>{{ $item->document_verified == 1 ? 'Verified' : 'Not Verified' }}</td>
      <td>{{ $item->proficiency_test_status == 1 ? 'Done' : 'Pending' }}</td>
      <td>{{ $item->proficiency_test_remarks ?? 'N/A' }}</td>
      <td>{{ $item->dept_interview == 1 ? 'Completed' : 'Pending' }}</td>
      <td>{{ $item->dept_interview_remark ?? 'N/A' }}</td>
      <td>{{ $item->mgt_interview_status == 1 ? 'Completed' : 'Pending' }}</td>
      <td>{{ $item->mgt_interview_remark ?? 'N/A' }}</td>
      <td>{{ $item->final_status == 1 ? 'Selected' : 'Pending' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>