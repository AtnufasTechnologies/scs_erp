<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monthly Work Diary Report - {{ $month->format('F Y') }}</title>
  <style>
    @page {
      margin: 20mm 15mm;
      size: A4;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'DejaVu Sans', 'Arial', sans-serif;
      font-size: 9px;
      line-height: 1.4;
      color: #2c3e50;
      background: #ffffff;
      padding: 5mm;
    }

    .report-container {
      padding: 0;
      max-width: 100%;
    }

    /* Enhanced Header */
    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 12px 15px;
      margin-bottom: 12px;
      border-radius: 6px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .header-content {
      text-align: center;
    }

    .header h1 {
      font-size: 18px;
      margin-bottom: 5px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }

    .header .month {
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 4px;
      opacity: 0.95;
    }

    .header .faculty-name {
      font-size: 9px;
      opacity: 0.85;
      margin-top: 3px;
    }

    .header .report-date {
      font-size: 8px;
      opacity: 0.75;
      margin-top: 5px;
      border-top: 1px solid rgba(255, 255, 255, 0.3);
      padding-top: 5px;
    }

    /* Enhanced Statistics Grid */
    .stats-container {
      margin-bottom: 12px;
    }

    .stats-grid {
      display: table;
      width: 100%;
      border-spacing: 5px;
    }

    .stats-row {
      display: table-row;
    }

    .stat-card {
      display: table-cell;
      width: 25%;
      padding: 10px 8px;
      text-align: center;
      border-radius: 6px;
      border: 2px solid #e8ecef;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .stat-card.total {
      border-color: #667eea;
      background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);
    }

    .stat-card.teaching {
      border-color: #10b981;
      background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%);
    }

    .stat-card.extra {
      border-color: #3b82f6;
      background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
    }

    .stat-card.substitution {
      border-color: #f59e0b;
      background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
    }

    .stat-label {
      font-size: 8px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      color: #64748b;
      margin-bottom: 4px;
    }

    .stat-value {
      font-size: 22px;
      font-weight: 700;
      color: #1e293b;
      margin: 5px 0;
      line-height: 1;
    }

    .stat-percentage {
      font-size: 8px;
      color: #64748b;
      font-weight: 500;
    }

    .stat-bar {
      height: 5px;
      background: #e2e8f0;
      border-radius: 3px;
      margin-top: 5px;
      overflow: hidden;
    }

    .stat-bar-fill {
      height: 100%;
      border-radius: 3px;
      transition: width 0.3s ease;
    }

    .stat-bar-fill.total {
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }

    .stat-bar-fill.teaching {
      background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .stat-bar-fill.extra {
      background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
    }

    .stat-bar-fill.substitution {
      background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    /* Section Styling */
    .section {
      margin-bottom: 12px;
      page-break-inside: avoid;
    }

    .section-header {
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 8px 12px;
      border-radius: 5px 5px 0 0;
      font-size: 11px;
      font-weight: 700;
      margin-bottom: 0;
      letter-spacing: 0.2px;
    }

    .section-title {
      font-size: 12px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 8px;
      padding: 6px 10px;
      background: linear-gradient(90deg, #f1f5f9 0%, #ffffff 100%);
      border-left: 3px solid #667eea;
      border-radius: 3px;
    }

    /* Enhanced Tables */
    .table-container {
      border: 1px solid #e2e8f0;
      border-radius: 5px;
      overflow: hidden;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }

    table th {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 6px 6px;
      text-align: left;
      font-size: 8.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.2px;
      border: none;
    }

    table td {
      padding: 5px 6px;
      font-size: 8px;
      border-bottom: 1px solid #f1f5f9;
      color: #334155;
    }

    table tr:last-child td {
      border-bottom: none;
    }

    table tr:nth-child(even) {
      background-color: #f8fafc;
    }

    table tbody tr:hover {
      background-color: #f1f5f9;
    }

    /* Enhanced Badges */
    .badge {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 7px;
      font-weight: 700;
      letter-spacing: 0.2px;
      text-transform: uppercase;
    }

    .badge-success {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #6ee7b7;
    }

    .badge-info {
      background: #dbeafe;
      color: #1e40af;
      border: 1px solid #93c5fd;
    }

    .badge-warning {
      background: #fef3c7;
      color: #92400e;
      border: 1px solid #fcd34d;
    }

    .badge-primary {
      background: #e0e7ff;
      color: #3730a3;
      border: 1px solid #a5b4fc;
    }

    /* Daily Entry Cards */
    .daily-entry {
      margin-bottom: 10px;
      border: 2px solid #e2e8f0;
      border-radius: 5px;
      overflow: hidden;
      page-break-inside: avoid;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    .daily-entry-header {
      background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);
      padding: 6px 10px;
      border-bottom: 2px solid #e2e8f0;
      font-weight: 700;
      font-size: 9px;
      color: #1e293b;
    }

    .daily-entry-content {
      padding: 0;
    }

    .daily-entry table {
      margin: 0;
    }

    /* Charts & Progress Bars */
    .chart-container {
      margin: 10px 0;
    }

    .progress-row {
      margin-bottom: 8px;
    }

    .progress-label {
      display: inline-block;
      width: 35%;
      font-size: 8px;
      font-weight: 600;
      color: #475569;
      vertical-align: middle;
    }

    .progress-bar-wrapper {
      display: inline-block;
      width: 45%;
      height: 12px;
      background: #f1f5f9;
      border-radius: 6px;
      overflow: hidden;
      vertical-align: middle;
      border: 1px solid #e2e8f0;
    }

    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
      border-radius: 6px;
      text-align: center;
      color: white;
      font-size: 7px;
      font-weight: 700;
      line-height: 12px;
    }

    .progress-value {
      display: inline-block;
      width: 18%;
      text-align: right;
      font-size: 8.5px;
      font-weight: 700;
      color: #1e293b;
      vertical-align: middle;
    }

    /* Utilities */
    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    .no-data {
      text-align: center;
      padding: 20px 15px;
      color: #94a3b8;
      font-style: italic;
      font-size: 9px;
      background: #f8fafc;
      border: 2px dashed #cbd5e1;
      border-radius: 5px;
    }

    /* Footer */
    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7px;
      color: #94a3b8;
      padding: 8px 0;
      background: white;
      border-top: 2px solid #e2e8f0;
    }

    .footer strong {
      color: #64748b;
    }

    /* Summary Box */
    .summary-box {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
      border: 2px solid #7dd3fc;
      border-radius: 5px;
      padding: 8px 10px;
      margin-bottom: 12px;
    }

    .summary-title {
      font-size: 9px;
      font-weight: 700;
      color: #0c4a6e;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .summary-content {
      font-size: 8px;
      color: #075985;
      line-height: 1.4;
    }
  </style>
</head>

<body>
  <div class="report-container">
    <!-- Enhanced Header -->
    <div class="header">
      <div class="header-content">
        <h1>Monthly Work Diary Report</h1>
        <div class="month">{{ $month->format('F Y') }}</div>
        @if(isset($faculty) && $faculty && isset($faculty->faculty))
        <div class="faculty-name">Faculty: {{ $faculty->faculty->name ?? 'N/A' }}</div>
        @endif
        <div class="report-date">Report Generated: {{ now()->format('F d, Y \a\t h:i A') }}</div>
      </div>
    </div>

    <!-- Summary Box -->
    @if($totalClasses > 0)
    <div class="summary-box">
      <div class="summary-title">📊 Monthly Summary</div>
      <div class="summary-content">
        <strong>{{ $totalClasses }}</strong> total work entries recorded for {{ $month->format('F Y') }}.
        This includes <strong>{{ $regularCount }}</strong> teaching classes,
        <strong>{{ $extraCount }}</strong> extra hours, and
        <strong>{{ $substitutionCount }}</strong> substitution classes.
      </div>
    </div>
    @endif

    <!-- Enhanced Statistics Grid -->
    <div class="stats-container">
      <div class="stats-grid">
        <div class="stats-row">
          <div class="stat-card total">
            <div class="stat-label">Total Classes</div>
            <div class="stat-value">{{ $totalClasses }}</div>
            <div class="stat-percentage">All Activities</div>
            <div class="stat-bar">
              <div class="stat-bar-fill total" style="width: 100%;"></div>
            </div>
          </div>
          <div class="stat-card teaching">
            <div class="stat-label">Teaching Classes</div>
            <div class="stat-value">{{ $regularCount }}</div>
            <div class="stat-percentage">{{ $totalClasses > 0 ? round(($regularCount / $totalClasses) * 100, 1) : 0
              }}% of Total</div>
            <div class="stat-bar">
              <div class="stat-bar-fill teaching"
                style="width: {{ $totalClasses > 0 ? ($regularCount / $totalClasses) * 100 : 0 }}%;"></div>
            </div>
          </div>
          <div class="stat-card extra">
            <div class="stat-label">Extra Classes</div>
            <div class="stat-value">{{ $extraCount }}</div>
            <div class="stat-percentage">{{ $totalClasses > 0 ? round(($extraCount / $totalClasses) * 100, 1) : 0 }}%
              of Total</div>
            <div class="stat-bar">
              <div class="stat-bar-fill extra"
                style="width: {{ $totalClasses > 0 ? ($extraCount / $totalClasses) * 100 : 0 }}%;"></div>
            </div>
          </div>
          <div class="stat-card substitution">
            <div class="stat-label">Substitutions</div>
            <div class="stat-value">{{ $substitutionCount }}</div>
            <div class="stat-percentage">{{ $totalClasses > 0 ? round(($substitutionCount / $totalClasses) * 100, 1) :
              0 }}% of Total</div>
            <div class="stat-bar">
              <div class="stat-bar-fill substitution"
                style="width: {{ $totalClasses > 0 ? ($substitutionCount / $totalClasses) * 100 : 0 }}%;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Remedial Classes Breakdown -->
    @if($extraCount > 0 && $workTypeBreakdown->count() > 0)
    <div class="section">
      <div class="section-title">💼 Remedial Classes Breakdown</div>
      <div class="chart-container">
        @foreach($workTypeBreakdown as $type => $count)
        <div class="progress-row">
          <span class="progress-label">{{ ucfirst($type ?: 'Not Specified') }}</span>
          <div class="progress-bar-wrapper">
            <div class="progress-bar-fill" style="width: {{ ($count / $extraCount) * 100 }}%;">
              {{ round(($count / $extraCount) * 100, 1) }}%
            </div>
          </div>
          <span class="progress-value">{{ $count }} {{ Str::plural('entry', $count) }}</span>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Methodology Breakdown -->
    @if($methodologyBreakdown->count() > 0)
    <div class="section">
      <div class="section-title">📚 Methodology Usage</div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Methodology</th>
              <th class="text-center" style="width: 80px;">Count</th>
              <th class="text-right" style="width: 100px;">Percentage</th>
            </tr>
          </thead>
          <tbody>
            @foreach($methodologyBreakdown->sortDesc()->take(10) as $methodology => $count)
            <tr>
              <td><strong>{{ $methodology }}</strong></td>
              <td class="text-center">
                <span class="badge badge-primary">{{ $count }}</span>
              </td>
              <td class="text-right">
                <strong>{{ round(($count / $totalClasses) * 100, 1) }}%</strong>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    <!-- Weekly Breakdown -->
    @if($weeklyBreakdown->count() > 0)
    <div class="section">
      <div class="section-title">📅 Weekly Breakdown</div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Week</th>
              <th class="text-center">Total</th>
              <th class="text-center">Teaching</th>
              <th class="text-center">Extra</th>
              <th class="text-center">Substitution</th>
            </tr>
          </thead>
          <tbody>
            @foreach($weeklyBreakdown as $weekNum => $data)
            <tr>
              <td><strong>Week {{ $weekNum }}</strong></td>
              <td class="text-center"><span class="badge badge-primary">{{ $data['total'] }}</span></td>
              <td class="text-center"><span class="badge badge-success">{{ $data['regular'] }}</span></td>
              <td class="text-center"><span class="badge badge-info">{{ $data['extra'] }}</span></td>
              <td class="text-center"><span class="badge badge-warning">{{ $data['substitution'] }}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    <!-- Daily Entries -->
    @if($dailyEntries->count() > 0)
    <div class="section">
      <div class="section-title">📝 Daily Work Entries</div>
      @foreach($dailyEntries as $date => $dayEntries)
      @php
      $dateObj = \Carbon\Carbon::parse($date);
      @endphp
      <div class="daily-entry">
        <div class="daily-entry-header">
          {{ $dateObj->format('l, F d, Y') }}
          <span style="float: right;">
            <span class="badge badge-primary">{{ $dayEntries->count() }}</span>
            @if($dayEntries->where('class_type', 'regular')->count() > 0)
            <span class="badge badge-success">{{ $dayEntries->where('class_type', 'regular')->count() }} T</span>
            @endif
            @if($dayEntries->where('class_type', 'extra')->count() > 0)
            <span class="badge badge-info">{{ $dayEntries->where('class_type', 'extra')->count() }} E</span>
            @endif
            @if($dayEntries->where('class_type', 'substitution')->count() > 0)
            <span class="badge badge-warning">{{ $dayEntries->where('class_type', 'substitution')->count() }} S</span>
            @endif
          </span>
        </div>
        <div class="daily-entry-content">
          <table>
            <thead>
              <tr>
                <th style="width: 50px;">Period</th>
                <th style="width: 70px;">Type</th>
                <th style="width: 70px;">Work Type</th>
                <th style="width: 90px;">Methodology</th>
                <th>Description</th>
              </tr>
            </thead>
            <tbody>
              @foreach($dayEntries->sortBy('hour') as $entry)
              <tr>
                <td><strong>H{{ $entry->hour }}</strong></td>
                <td>
                  @if($entry->class_type == 'regular')
                  <span class="badge badge-success">Teaching</span>
                  @elseif($entry->class_type == 'extra')
                  <span class="badge badge-info">Extra</span>
                  @elseif($entry->class_type == 'substitution')
                  <span class="badge badge-warning">Sub</span>
                  @else
                  [{{ $entry->class_type ?? 'null' }}]
                  @endif
                </td>
                <td>{{ $entry->work_type ? ucfirst($entry->work_type) : '—' }}</td>
                <td>{{ Str::limit($entry->methodology, 20) ?: '—' }}</td>
                <td>{{ Str::limit($entry->description, 100) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endforeach
    </div>
    @else
    <div class="no-data">
      📭 No work diary entries recorded for {{ $month->format('F Y') }}
    </div>
    @endif
  </div>

  <div class="footer">
    <strong>Work Diary Report</strong> - {{ $month->format('F Y') }} | Generated on {{ now()->format('F d, Y \a\t h:i
    A') }}
  </div>
</body>

</html>