{{-- Course type badge colours --}}
@php
$ctColors = [
'CC' => ['bg'=>'#e8eaf6','color'=>'#1a237e'],
'GE' => ['bg'=>'#e8f5e9','color'=>'#1b5e20'],
'SEC' => ['bg'=>'#fff3e0','color'=>'#e65100'],
'DSE' => ['bg'=>'#fce4ec','color'=>'#880e4f'],
'AECC' => ['bg'=>'#e3f2fd','color'=>'#0d47a1'],
'MDC' => ['bg'=>'#f3e5f5','color'=>'#4a148c'],
'MAJ' => ['bg'=>'#e0f7fa','color'=>'#006064'],
'MIN' => ['bg'=>'#fff8e1','color'=>'#f57f17'],
];
$defaultCt = ['bg'=>'#f5f5f5','color'=>'#555'];
$enrolledProgramName = $data->programgroup?->programInfo?->name ?? '—';
$enrolledProgramCode = $data->programgroup?->programInfo?->code ?? ($data->programgroup?->program_code ?? '—');
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem;">
  <div>
    <div style="font-size:1rem;font-weight:700;color:#1a1a2e;">
      <i class="fas fa-book-open me-1" style="color:#1a237e;"></i>
      Enrolled Courses
      <span class="sp-count ms-2">{{ $studentCourses->count() }}</span>
    </div>
    <div style="font-size:.8rem;color:#4b5563;margin-top:.25rem;">
      <i class="fas fa-graduation-cap me-1" style="color:#1a237e;"></i>
      Enrolled Program: <strong>{{ $enrolledProgramName }}</strong> ({{ $enrolledProgramCode }})
    </div>
  </div>
</div>

@if($coursesBySemester->isEmpty())
<div class="sp-card">
  <div class="sp-empty"><i class="fas fa-book"></i>No courses enrolled yet.</div>
</div>
@else
@foreach($coursesBySemester as $semLabel => $courses)
<div class="sp-card" style="margin-bottom:1rem;">
  <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;padding-bottom:.6rem;border-bottom:1px solid #f0f0f0;">
    <span style="background:#e8eaf6;color:#1a237e;border-radius:6px;padding:.25rem .8rem;font-weight:700;font-size:.82rem;">
      <i class="fas fa-layer-group me-1"></i>{{ $semLabel }}
    </span>
    <span class="sp-count">{{ $courses->count() }} courses</span>
  </div>
  <div style="overflow-x:auto;">
    <table class="sp-table" style="min-width:600px;">
      <thead>
        <tr>
          <th>#</th>
          <th>RefID</th>
          <th>Code</th>
          <th>Course Title</th>
          <th>Type</th>
          <th>Delivery</th>
          <th>Offered By</th>
          <th>Cr.</th>
        </tr>
      </thead>
      <tbody>
        @foreach($courses as $i => $course)
        @php
        $typeTitle = $course->coursemaster?->coursetypemaster?->title ?? '';
        $ctKey = preg_replace('/\s.*/', '', $typeTitle);
        $ct = $ctColors[$ctKey] ?? $defaultCt;
        $deliveryKey = (string) ($course->semester ?? $course->coursemaster?->semester_id ?? '') . '_' . (string) ($course->course_id ?? '');
        $deliveryType = $courseDeliveryMap[$deliveryKey] ?? ($studentMajorDeliveryType ?? 'COMMON');
        $offeredBySubject = $courseOfferingSubjectMap[$deliveryKey] ?? ($programOfferingSubjectTitle ?? '—');
        @endphp
        <tr>
          <td style="color:#adb5bd;">{{ $i+1 }}</td>
          <td>{{ $course->id ?? '—' }}</td>
          <td>
            <span style="background:#e8eaf6;color:#3949ab;border-radius:4px;padding:.1rem .45rem;font-size:.78rem;font-weight:600;">
              {{ $course->coursemaster?->course_code ?? '—' }}
            </span>
          </td>
          <td style="font-weight:500;">{{ $course->coursemaster?->course_title ?? '—' }}</td>
          <td>
            @if($typeTitle)
            <span style="background:{{ $ct['bg'] }};color:{{ $ct['color'] }};border-radius:4px;padding:.1rem .5rem;font-size:.76rem;font-weight:700;white-space:nowrap;">
              {{ $typeTitle }}
            </span>
            @else —
            @endif
          </td>
          <td>
            <span style="background:#e3f2fd;color:#1565c0;border-radius:4px;padding:.1rem .5rem;font-size:.74rem;font-weight:700;white-space:nowrap;">
              {{ $deliveryType }}
            </span>
          </td>
          <td style="font-size:.78rem;color:#374151;font-weight:600;white-space:nowrap;">{{ $offeredBySubject }}</td>
          <td>{{ $course->coursemaster?->credits ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endforeach
@endif