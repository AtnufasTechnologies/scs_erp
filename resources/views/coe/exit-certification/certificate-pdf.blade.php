<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Exit Certificate - {{ $record->certificate_no }}</title>
  <style>
    @page {
      margin: 0;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      margin: 0;
      padding: 0;
      color: #1a1a2e;
    }

    .certificate {
      width: 100%;
      min-height: 100vh;
      padding: 40px 60px;
      box-sizing: border-box;
      position: relative;
      background: #fff;
    }

    .border-outer {
      border: 3px solid #1a1a2e;
      padding: 8px;
      height: calc(100vh - 80px);
      box-sizing: border-box;
    }

    .border-inner {
      border: 1px solid #b8860b;
      padding: 30px 40px;
      height: 100%;
      box-sizing: border-box;
      position: relative;
    }

    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 120px;
      color: rgba(184, 134, 11, 0.06);
      font-weight: bold;
      letter-spacing: 10px;
      white-space: nowrap;
      pointer-events: none;
    }

    .header {
      text-align: center;
      margin-bottom: 15px;
    }

    .institution-name {
      font-size: 26px;
      font-weight: bold;
      margin: 0;
      color: #1a1a2e;
      letter-spacing: 2px;
    }

    .institution-sub {
      font-size: 12px;
      color: #555;
      margin: 5px 0 0;
    }

    .divider {
      border: none;
      border-top: 2px solid #b8860b;
      margin: 15px auto;
      width: 60%;
    }

    .cert-title {
      text-align: center;
      margin: 15px 0;
    }

    .cert-title h2 {
      font-size: 28px;
      margin: 0;
      color: #b8860b;
      text-transform: uppercase;
      letter-spacing: 4px;
    }

    .cert-title .level-badge {
      display: inline-block;
      background: #1a1a2e;
      color: #fff;
      padding: 5px 20px;
      font-size: 14px;
      letter-spacing: 3px;
      text-transform: uppercase;
      margin-top: 8px;
    }

    .cert-body {
      text-align: center;
      margin: 20px 0;
      font-size: 14px;
      line-height: 1.8;
    }

    .cert-body .student-name {
      font-size: 22px;
      font-weight: bold;
      color: #1a1a2e;
      border-bottom: 2px solid #b8860b;
      display: inline-block;
      padding: 0 20px 2px;
    }

    .cert-body .program-name {
      font-size: 16px;
      font-weight: bold;
      color: #333;
    }

    .details-table {
      width: 80%;
      margin: 15px auto;
      border-collapse: collapse;
      font-size: 12px;
    }

    .details-table td {
      padding: 6px 12px;
      border: 1px solid #ddd;
    }

    .details-table .label-cell {
      font-weight: bold;
      background: #f8f8f8;
      width: 35%;
      color: #444;
    }

    .details-table .value-cell {
      color: #1a1a2e;
      font-weight: 600;
    }

    .credit-table {
      width: 70%;
      margin: 10px auto;
      border-collapse: collapse;
      font-size: 11px;
    }

    .credit-table th {
      background: #1a1a2e;
      color: #fff;
      padding: 5px 10px;
      text-align: center;
      font-weight: 600;
    }

    .credit-table td {
      padding: 4px 10px;
      border: 1px solid #ddd;
      text-align: center;
    }

    .credit-table tr:nth-child(even) {
      background: #f9f9f9;
    }

    .signatures {
      position: absolute;
      bottom: 40px;
      left: 40px;
      right: 40px;
    }

    .signatures table {
      width: 100%;
    }

    .signatures td {
      text-align: center;
      font-size: 12px;
      padding-top: 50px;
      vertical-align: bottom;
    }

    .signatures .sig-line {
      border-top: 1px solid #333;
      display: inline-block;
      width: 150px;
      margin-bottom: 5px;
    }

    .signatures .sig-title {
      font-size: 10px;
      color: #666;
    }

    .cert-no {
      position: absolute;
      bottom: 15px;
      left: 40px;
      font-size: 9px;
      color: #999;
    }

    .issue-date {
      position: absolute;
      bottom: 15px;
      right: 40px;
      font-size: 9px;
      color: #999;
    }
  </style>
</head>

<body>
  <div class="certificate">
    <div class="border-outer">
      <div class="border-inner">
        <div class="watermark">{{ strtoupper($record->exit_level) }}</div>

        <div class="header">
          <p class="institution-name">SCS COLLEGE</p>
          <p class="institution-sub">Affiliated to the University | Recognized by UGC | Accredited by NAAC</p>
        </div>

        <hr class="divider">

        <div class="cert-title">
          <h2>Exit Certificate</h2>
          <div class="level-badge">
            @if($record->exit_level == 'certificate')
            Undergraduate Certificate
            @elseif($record->exit_level == 'diploma')
            Undergraduate Diploma
            @elseif($record->exit_level == 'degree')
            Bachelor's Degree
            @elseif($record->exit_level == 'honors')
            Honours / Research Degree
            @endif
          </div>
        </div>

        <div class="cert-body">
          <p>This is to certify that</p>
          <p class="student-name">{{ $record->student->enrollment_no ?? 'N/A' }}</p>
          <p>
            has successfully completed the requirements for the
            <strong>{{ ucfirst($record->exit_level) }}</strong> level exit under the
            National Education Policy (NEP) framework in the program
          </p>
          <p class="program-name">{{ $record->program->name ?? 'N/A' }}</p>
        </div>

        <table class="details-table">
          <tr>
            <td class="label-cell">Certificate Number</td>
            <td class="value-cell">{{ $record->certificate_no }}</td>
            <td class="label-cell">CGPA</td>
            <td class="value-cell">{{ number_format($record->cgpa, 2) }}</td>
          </tr>
          <tr>
            <td class="label-cell">Total Credits Earned</td>
            <td class="value-cell">{{ $record->total_credits_earned }} / {{ $record->credits_required }}</td>
            <td class="label-cell">Semesters Completed</td>
            <td class="value-cell">{{ $record->semesters_completed }}</td>
          </tr>
        </table>

        @if($record->credit_summary && count($record->credit_summary) > 0)
        <table class="credit-table">
          <thead>
            <tr>
              <th>Semester</th>
              <th>Credits Earned</th>
              <th>SGPA</th>
              <th>Result</th>
            </tr>
          </thead>
          <tbody>
            @foreach($record->credit_summary as $sem)
            <tr>
              <td>Sem {{ $sem['semester'] }}</td>
              <td>{{ $sem['credits_earned'] }}</td>
              <td>{{ isset($sem['sgpa']) ? number_format($sem['sgpa'], 2) : '—' }}</td>
              <td>{{ ucfirst($sem['result_status'] ?? '—') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif

        <div class="signatures">
          <table>
            <tr>
              <td>
                <div class="sig-line"></div><br>
                <strong>Controller of Examinations</strong><br>
                <span class="sig-title">SCS College</span>
              </td>
              <td>
                <div class="sig-line"></div><br>
                <strong>Principal</strong><br>
                <span class="sig-title">SCS College</span>
              </td>
              <td>
                <div class="sig-line"></div><br>
                <strong>Registrar</strong><br>
                <span class="sig-title">University</span>
              </td>
            </tr>
          </table>
        </div>

        <div class="cert-no">No: {{ $record->certificate_no }}</div>
        <div class="issue-date">Date: {{ $record->issue_date ? $record->issue_date->format('d/m/Y') : now()->format('d/m/Y') }}</div>
      </div>
    </div>
  </div>
</body>

</html>