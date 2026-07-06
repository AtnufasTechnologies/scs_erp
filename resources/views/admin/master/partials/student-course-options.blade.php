@foreach($availableCourses as $semId => $semCourses)
<optgroup label="{{ $semCourses->first()?->semestermaster?->title ?? 'Semester '.$semId }}" data-sem="{{ $semId }}">
  @foreach($semCourses as $ac)
  <option value="{{ $ac->id }}">{{ $ac->course_code }} - {{ $ac->course_title }}{{ $ac->coursetypemaster ? ' ('.$ac->coursetypemaster->title.')' : '' }}</option>
  @endforeach
</optgroup>
@endforeach