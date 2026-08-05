<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Course Objectives PDF</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #111827;
      margin: 20px;
      line-height: 1.45;
    }

    .header {
      border: 1px solid #d1d5db;
      padding: 12px;
      margin-bottom: 14px;
      background: #f9fafb;
    }

    .title {
      font-size: 16px;
      font-weight: 700;
      margin-bottom: 6px;
    }

    .meta {
      font-size: 11px;
      color: #4b5563;
      margin-bottom: 2px;
    }

    .section-title {
      font-size: 13px;
      font-weight: 700;
      margin: 12px 0 8px;
      border-bottom: 1px solid #e5e7eb;
      padding-bottom: 4px;
    }

    .cso-card {
      border: 1px solid #e5e7eb;
      margin-bottom: 12px;
      padding: 10px;
      page-break-inside: avoid;
    }

    .cso-title {
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .badge {
      display: inline-block;
      padding: 2px 6px;
      font-size: 10px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      margin-left: 6px;
      color: #374151;
      background: #f3f4f6;
    }

    ul {
      margin: 6px 0 0 18px;
      padding: 0;
    }

    li {
      margin-bottom: 6px;
    }

    .taxonomy {
      margin-top: 2px;
      font-size: 10px;
      color: #1f2937;
    }

    .muted {
      color: #6b7280;
    }

    .footer {
      margin-top: 18px;
      font-size: 10px;
      color: #6b7280;
      text-align: right;
    }
  </style>
</head>

<body>
  @php
  $taxonomyDomainLabel = function ($level) {
  if (!$level) {
  return 'Cognitive';
  }

  $domain = trim((string) ($level->learning_domain ?? ''));
  return $domain !== '' ? $domain : 'Cognitive';
  };

  $taxonomyFrameworkLabel = function ($level) use ($taxonomyDomainLabel) {
  if (!$level) {
  return 'RBT';
  }

  $framework = trim((string) ($level->taxonomy_framework ?? ''));
  if ($framework !== '') {
  return $framework;
  }

  $domain = $taxonomyDomainLabel($level);
  if (strcasecmp($domain, 'Psychomotor') === 0) {
  return 'Dave';
  }

  if (strcasecmp($domain, 'Affective') === 0) {
  return 'Krathwohl';
  }

  return 'RBT';
  };
  @endphp

  <div class="header">
    <div class="title">Course Specific Objectives (CSO) Report</div>
    <div class="meta"><strong>Course Code:</strong> {{ $course->courseMaster->course_code ?? '-' }}</div>
    <div class="meta"><strong>Course Title:</strong> {{ $course->courseMaster->course_title ?? '-' }}</div>
    <div class="meta"><strong>Course Type:</strong> {{ $course->courseMaster->coursetypemaster->title ?? '-' }}</div>
    <div class="meta"><strong>Shift Filter:</strong> {{ $selectedShift === 'all' ? 'All (default shift view)' : ucfirst($selectedShift) }}</div>
  </div>

  <div class="section-title">CSO List</div>

  @forelse($filteredCsos as $cso)
  <div class="cso-card">
    <div class="cso-title">
      {{ $loop->iteration }}. {!! $cso->title !!}
      <span class="badge">Lectures: {{ (int) ($cso->lectures_needed ?? 0) }}</span>
      @if(!empty($cso->shift))
      <span class="badge">Shift: {{ ucfirst((string) $cso->shift) }}</span>
      @endif
    </div>

    @if(!empty($cso->csosubunits) && count($cso->csosubunits) > 0)
    <ul>
      @foreach($cso->csosubunits as $subunit)
      <li>
        <div><strong>{{ $subunit->title ?? '-' }}</strong></div>
        @php
        $taxonomyLabels = collect($subunit->taxonomies ?? [])
        ->map(function ($taxonomy) use ($taxonomyFrameworkLabel, $taxonomyDomainLabel) {
        $level = $taxonomy->rbtmaster ?? null;
        if (!$level) {
        return null;
        }

        return ($level->shortname ?? '-') . ' - ' . ($level->fullname ?? '-') . ' [' . $taxonomyFrameworkLabel($level) . '/' . $taxonomyDomainLabel($level) . ']';
        })
        ->filter()
        ->values();
        @endphp
        @if($taxonomyLabels->isNotEmpty())
        <div class="taxonomy"><strong>Taxonomy:</strong> {{ $taxonomyLabels->implode(', ') }}</div>
        @else
        <div class="taxonomy muted"><strong>Taxonomy:</strong> Not mapped</div>
        @endif
      </li>
      @endforeach
    </ul>
    @else
    <div class="muted">No subunits added for this CSO.</div>
    @endif
  </div>
  @empty
  <div class="muted">No objectives available for this course.</div>
  @endforelse

  <div class="footer">
    Generated on {{ date('d M Y, h:i A') }}
  </div>
</body>

</html>