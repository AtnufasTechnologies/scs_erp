<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bulk Admit Cards</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Times New Roman', Times, serif;
      font-size: 12pt;
      line-height: 1.4;
    }

    .page-break {
      page-break-after: always;
    }

    .admit-card {
      border: 3px solid #000;
      padding: 20px;
      margin: 20px;
    }

    .header {
      text-align: center;
      border-bottom: 2px solid #000;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .header h1 {
      font-size: 24pt;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .header h2 {
      font-size: 18pt;
      font-weight: normal;
      margin-bottom: 10px;
    }

    .header h3 {
      font-size: 14pt;
      font-weight: bold;
      text-decoration: underline;
    }

    .section {
      margin-bottom: 20px;
    }

    .section-title {
      font-size: 14pt;
      font-weight: bold;
      background-color: #f0f0f0;
      padding: 8px;
      margin-bottom: 10px;
      border-left: 5px solid #000;
    }

    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .info-table td {
      padding: 8px;
      border: 1px solid #000;
    }

    .info-table td:first-child {
      font-weight: bold;
      width: 35%;
      background-color: #f9f9f9;
    }

    .subjects-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .subjects-table th,
    .subjects-table td {
      border: 1px solid #000;
      padding: 8px;
      text-align: left;
    }

    .subjects-table th {
      background-color: #f0f0f0;
      font-weight: bold;
    }

    .highlight-box {
      background-color: #ffffcc;
      border: 2px solid #000;
      padding: 15px;
      margin: 15px 0;
      text-align: center;
    }

    .highlight-box .label {
      font-size: 12pt;
      font-weight: bold;
    }

    .highlight-box .value {
      font-size: 18pt;
      font-weight: bold;
      color: #cc0000;
      margin-top: 5px;
    }

    .instructions {
      margin-top: 20px;
      padding: 15px;
      border: 1px solid #000;
      background-color: #f9f9f9;
    }

    .instructions h4 {
      font-size: 12pt;
      margin-bottom: 10px;
      text-decoration: underline;
    }

    .instructions ol {
      margin-left: 20px;
    }

    .instructions li {
      margin-bottom: 5px;
    }

    .footer {
      margin-top: 30px;
      padding-top: 15px;
      border-top: 2px solid #000;
      text-align: right;
    }

    .signature-line {
      display: inline-block;
      border-top: 2px solid #000;
      padding-top: 5px;
      margin-top: 50px;
      min-width: 200px;
    }

    @media print {
      body {
        margin: 0;
        padding: 0;
      }

      .admit-card {
        margin: 0;
        page-break-after: always;
      }
    }
  </style>
</head>

<body>
  @foreach($registrations as $registration)
  <div class="admit-card {{ !$loop->last ? 'page-break' : '' }}">
    <!-- Header -->
    <div class="header">
      <h1>SALESIAN COLLEGE, SILIGURI</h1>
      <h2>Autonomous</h2>
      <h3>EXAMINATION ADMIT CARD</h3>
      <p style="margin-top: 10px;">
        <strong>{{ $registration->examSession->name ?? 'N/A' }} ({{ $registration->examSession->program_type ?? '' }})</strong>
      </p>
    </div>

    <!-- Student Information -->
    <div class="section">
      <div class="section-title">Student Information</div>
      <table class="info-table">
        <tr>
          <td>Name of the Candidate</td>
          <td><strong>{{ strtoupper($registration->student->full_name ?? 'N/A') }}</strong></td>
        </tr>
        <tr>
          <td>Registration Number</td>
          <td><strong>{{ $registration->student->register_no ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
          <td>Roll Number</td>
          <td><strong>{{ $registration->student->roll_no ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
          <td>Programme</td>
          <td>{{ $registration->student->programgroup->programInfo->name ?? 'N/A' }}</td>
        </tr>
        <tr>
          <td>Department</td>
          <td>{{ $registration->student->deptmaster->name ?? 'N/A' }}</td>
        </tr>
        <tr>
          <td>Semester</td>
          <td>Semester {{ $registration->examSession->semester ?? 'N/A' }}</td>
        </tr>
      </table>
    </div>

    <!-- Exam Details -->
    <div class="section">
      <div class="section-title">Examination Details</div>
      <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
        <div class="highlight-box" style="width: 48%;">
          <div class="label">EXAMINATION HALL</div>
          <div class="value">Room {{ $registration->seatingAllocation->room_no ?? 'N/A' }}</div>
        </div>
        <div class="highlight-box" style="width: 23%;">
          <div class="label">SEAT NO.</div>
          <div class="value">{{ $registration->seatingAllocation->seat_no ?? 'N/A' }}</div>
        </div>
        <div class="highlight-box" style="width: 23%;">
          <div class="label">DUMMY NO.</div>
          <div class="value">{{ $registration->dummyNumber->dummy_number ?? 'N/A' }}</div>
        </div>
      </div>
    </div>

    <!-- Subjects -->
    <div class="section">
      <div class="section-title">Registered Subjects</div>
      <table class="subjects-table">
        <thead>
          <tr>
            <th style="width: 10%;">Sl. No.</th>
            <th style="width: 25%;">Subject Code</th>
            <th style="width: 55%;">Subject Name</th>
            <th style="width: 10%;">Credits</th>
          </tr>
        </thead>
        <tbody>
          @if($registration->subjects && $registration->subjects->count() > 0)
          @foreach($registration->subjects as $index => $subject)
          <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td>{{ $subject->subject_code }}</td>
            <td>{{ $subject->name }}</td>
            <td style="text-align: center;">{{ $subject->credits ?? '-' }}</td>
          </tr>
          @endforeach
          @else
          <tr>
            <td colspan="4" style="text-align: center;">No subjects registered</td>
          </tr>
          @endif
        </tbody>
      </table>
    </div>

    <!-- Instructions -->
    <div class="instructions">
      <h4>IMPORTANT INSTRUCTIONS:</h4>
      <ol>
        <li>Candidates must bring this admit card to the examination hall.</li>
        <li>Candidates must occupy the seat number mentioned on this admit card.</li>
        <li>Use only the dummy number on answer sheets. Do NOT write your name or registration number.</li>
        <li>Candidates must carry their college ID card along with this admit card.</li>
        <li>Candidates should report to the examination hall at least 15 minutes before the exam starts.</li>
        <li>Mobile phones and electronic devices are strictly prohibited in the examination hall.</li>
        <li>Any malpractice will lead to cancellation of the examination.</li>
      </ol>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div style="text-align: right;">
        <div>Date: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        <div class="signature-line">
          Controller of Examinations
        </div>
      </div>
    </div>
  </div>
  @endforeach
</body>

</html>