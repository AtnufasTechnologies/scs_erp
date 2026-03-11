<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>Syllabus - {{ $subject->title ?? 'N/A' }}</title>
  <style>
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 10px;
      line-height: 1.3;
      color: #000;
      margin: 15px;
    }

    .header {
      text-align: center;
      margin-bottom: 15px;
      border-bottom: 1px solid #000;
      padding-bottom: 8px;
    }

    .header h1 {
      margin: 0 0 5px 0;
      font-size: 16px;
      font-weight: bold;
    }

    .header p {
      margin: 2px 0;
      font-size: 9px;
    }

    .batch-title {
      font-weight: bold;
      font-size: 11px;
      margin: 10px 0 5px 0;
      padding: 3px 5px;
      background-color: #000;
      color: #fff;
    }

    .semester-title {
      font-weight: bold;
      font-size: 10px;
      margin: 8px 0 3px 0;
      border-bottom: 1px solid #000;
      padding-bottom: 2px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
      page-break-inside: avoid;
    }

    table,
    th,
    td {
      border: 1px solid #000;
    }

    th {
      background-color: #f0f0f0;
      padding: 4px;
      text-align: left;
      font-size: 9px;
      font-weight: bold;
    }

    td {
      padding: 4px;
      font-size: 9px;
      vertical-align: top;
    }

    .course-code {
      font-weight: bold;
      white-space: nowrap;
    }

    .cso-title {
      font-weight: bold;
      font-size: 9px;
      margin-bottom: 2px;
    }

    .units {
      margin: 2px 0 0 0;
      padding-left: 12px;
      font-size: 8px;
      line-height: 1.4;
    }

    .units li {
      margin-bottom: 2px;
    }

    .taxonomy {
      color: #555;
      font-size: 8px;
    }
  </style>
</head>

<body>

  <div class="header">
    <h1>SALESIAN COLLEGE AUTONOMOUS</h1>
    <div class="sub-text">Sonada • Siliguri Campus</div>
    <h1>{{ $subject->title ?? 'N/A' }}</h1>
    <p>Code: {{ $subject->code ?? 'N/A' }} | Program: {{ $subject->main_program_type ?? 'N/A' }} </p>
  </div>

  @foreach ($organized_syllabus as $batchName => $semesters)
  <div class="batch-title">{{ $batchName }}</div>

  @foreach ($semesters as $semesterName => $courses)
  <div class="semester-title">{{ $semesterName }}</div>

  <table>
    <thead>
      <tr>
        <th width="15%">Course Code</th>
        <th width="25%">Course Title</th>
        <th width="10%">Credits / Marks</th>
        <th width="50%">Course Objectives & Learning Units</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($courses as $courseKey => $courseData)
      <tr>
        <td class="course-code">{{ $courseData['course']->course_code ?? 'N/A' }}</td>
        <td>{{ $courseData['course']->course_title ?? 'Unknown Course' }}</td>
        <td>
          {{ $courseData['course']->credits ?? '0' }} Credits<br>
          Int: {{ $courseData['course']->internal ?? '-' }}<br>
          Ext: {{ $courseData['course']->external ?? '-' }}<br>
          Hrs: {{ $courseData['course']->total_alloted_hours ?? '-' }}
        </td>
        <td>
          @foreach ($courseData['csos'] as $syllabus)
          <div class="cso-title">{{ $syllabus->cso->title ?? 'N/A' }} ({{ $syllabus->cso->lectures_needed ?? '0' }} Lectures)</div>
          <ul class="units">
            @foreach ($syllabus->cso->csosubunits ?? [] as $subunit)
            <li>
              {{ $subunit->title }}
              <span class="taxonomy">[{{ $subunit->taxomonylevel->shortname ?? '-' }}]</span>
            </li>
            @endforeach
          </ul>
          @endforeach
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endforeach
  @endforeach

  @if(empty($organized_syllabus))
  <p style="text-align: center; margin-top: 50px; color: #999;">No syllabus data available.</p>
  @endif
</body>

</html>