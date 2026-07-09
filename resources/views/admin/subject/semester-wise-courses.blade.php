<?php

use App\Models\ProgramCourseMaster;
use App\Models\Semester;

$semesters = Semester::all();
$papers = ProgramCourseMaster::all();
?>
@include('includes.header')
@include('includes.dept-sidebar')
<style>
  :root {
    --swc-bg: #f4f7fb;
    --swc-ink: #102a43;
    --swc-muted: #486581;
    --swc-primary: #0f766e;
    --swc-primary-soft: #dff8f2;
    --swc-accent: #7c3aed;
    --swc-card: #ffffff;
    --swc-border: #d9e2ec;
    --swc-shadow: 0 12px 28px rgba(16, 42, 67, 0.08);
  }

  .swc-page {
    background: radial-gradient(circle at 10% 0%, #e6f4ff 0%, #f4f7fb 45%, #f4f7fb 100%);
    min-height: 100vh;
    padding-bottom: 2rem;
  }

  .swc-headline {
    color: var(--swc-ink);
    font-weight: 800;
    letter-spacing: 0.04rem;
    margin-bottom: 0.5rem;
  }

  .swc-subtitle {
    color: var(--swc-muted);
    margin-bottom: 1.2rem;
  }

  .swc-info {
    background: linear-gradient(135deg, #e8fdf8 0%, #d7f4ff 100%);
    border: 1px solid #cdeee6;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    box-shadow: var(--swc-shadow);
  }

  .swc-form-card,
  .swc-list-card {
    background: var(--swc-card);
    border: 1px solid var(--swc-border);
    border-radius: 14px;
    box-shadow: var(--swc-shadow);
  }

  .swc-form-card {
    padding: 1rem;
    margin-top: 1rem;
  }

  .swc-list-card {
    margin-top: 1rem;
    overflow: hidden;
  }

  .swc-list-card .card-header {
    background: linear-gradient(135deg, #f5fbff 0%, #eef8ff 100%);
    border-bottom: 1px solid var(--swc-border);
    color: var(--swc-ink);
    font-weight: 700;
  }

  .swc-summary {
    display: inline-flex;
    gap: 0.5rem;
    align-items: center;
    font-size: 0.85rem;
  }

  .swc-pill {
    border-radius: 999px;
    padding: 0.3rem 0.65rem;
    font-weight: 700;
  }

  .swc-pill-total {
    background: #e3f2fd;
    color: #1e3a8a;
  }

  .swc-pill-compulsary {
    background: #dcfce7;
    color: #166534;
  }

  .swc-pill-elective {
    background: #ede9fe;
    color: #5b21b6;
  }

  .swc-type-badge {
    font-size: 0.78rem;
    padding: 0.25rem 0.55rem;
    border-radius: 999px;
    font-weight: 700;
    display: inline-block;
  }

  .swc-type-compulsary {
    background: #dcfce7;
    color: #166534;
  }

  .swc-type-elective {
    background: #ede9fe;
    color: #5b21b6;
  }

  .swc-empty {
    border: 1px dashed #b8c7d9;
    border-radius: 14px;
    background: #f8fbff;
    color: #486581;
    text-align: center;
    padding: 2rem 1rem;
    margin-top: 1rem;
  }

  .toggle-container {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
  }

  /* Hide the default checkbox */
  .toggle-input {
    display: none;
  }

  /* The track of the switch */
  .toggle-track {
    position: relative;
    width: 240px;
    height: 50px;
    background-color: #e2e8f0;
    border-radius: 25px;
    transition: background-color 0.3s ease;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  /* The sliding thumb/button */
  .toggle-thumb {
    position: absolute;
    top: 4px;
    left: 4px;
    width: 116px;
    height: 42px;
    background-color: #ffffff;
    border-radius: 21px;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    z-index: 2;
  }

  /* Text labels inside the track */
  .toggle-labels {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    box-sizing: border-box;
    font-weight: 600;
    font-size: 14px;
    z-index: 1;
  }

  .label-compulsory {
    color: #10b981;
    /* Red color for Compulsory */
    transition: color 0.3s ease;
    width: 90px;
    text-align: center;
  }

  .label-elective {
    color: #94a3b8;
    /* Muted color when inactive */
    transition: color 0.3s ease;
    width: 90px;
    text-align: center;
  }

  /* --- Checked / ON State (Elective) --- */

  /* Move the thumb to the right */
  .toggle-input:checked+.toggle-track .toggle-thumb {
    transform: translateX(116px);
  }

  /* Change background track color when checked if desired */
  .toggle-input:checked+.toggle-track {
    background-color: #f1f5f9;
  }

  /* Fade out the compulsory label colors */
  .toggle-input:checked+.toggle-track .label-compulsory {
    color: #94a3b8;
  }

  /* Highlight the elective label */
  .toggle-input:checked+.toggle-track .label-elective {
    color: #10b981;
  }

  @media (max-width: 991px) {
    .swc-form-card .row>div {
      margin-bottom: 0.75rem;
    }

    .swc-form-card .row>div:last-child {
      margin-bottom: 0;
    }
  }
</style>
<div class="main-content swc-page">
  <div class="container-fluid">
    <h3 class="swc-headline text-uppercase">Program Wise Course Enrollment</h3>
    <p class="swc-subtitle">Plan compulsory and elective papers semester-wise for this program combination.</p>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="swc-info">
      <h5 class="mb-1">Batch: {{$data->batchmaster->batch_name}} | {{$data->studentprograminfo->code}} - {{$data->studentprograminfo->name}}</h5>
      <p class="mb-0">Compulsary courses are auto-allotted during enrollment, while elective courses stay student-choice based.</p>
    </div>

    <div class="swc-form-card">
      <form action="{{route('store.program.semster.courses.mapping')}}" method="post">
        @csrf
        <div class="row align-items-center">
          <div class="col-lg-2">
            <select name="semester" class="form-control">
              <option value=""> --Select Semester-- </option>
              @foreach ($semesters as $semester)
              <option value="{{$semester->id}}">{{$semester->title}}</option>
              @endforeach
            </select>
            @error('semester')
            <span class="text-danger">{{$message}}</span>
            @enderror
          </div>
          <div class="col-lg-7">
            <select name="course[]" class="form-control select-multiple" multiple>
              <option value=""> --Select Courses-- </option>
              @foreach ($papers as $paper)
              <option value="{{$paper->id}}">{{$paper->course_code}} {{$paper->course_title}}</option>
              @endforeach
            </select>
            @error('course')
            <span class="text-danger">{{$message}}</span>
            @enderror
          </div>

          <div class="col-lg-2">
            <div class="form-check form-switch">
              <label class="toggle-container">
                <input type="checkbox" class="toggle-input" id="courseToggle" name="toggleType">
                <div class="toggle-track">
                  <div class="toggle-thumb"></div>
                  <div class="toggle-labels">
                    <span class="label-elective">Elective</span>
                    <span class="label-compulsory">Compulsory</span>

                  </div>
                </div>
              </label>
            </div>
          </div>

          <input type="hidden" name="id" value="{{$data->id}}">
          <input type="hidden" name="batch" value="{{$data->batchmaster->id}}">
          <div class="col-lg-1">
            <button type="submit" class="btn btn-success w-100">Add</button>
          </div>
        </div>
      </form>
    </div>

    @forelse(($coursesBySemester ?? collect()) as $semesterId => $semesterCourses)
    @php
    $semesterTitle = optional($semesterCourses->first()->semestermaster)->title ?? 'Semester ' . $semesterId;
    $compulsaryCount = $semesterCourses->where('course_type', 'compulsary')->count();
    $electiveCount = $semesterCourses->where('course_type', 'elective')->count();
    @endphp
    <div class="swc-list-card card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>{{ $semesterTitle }}</span>
        <span class="swc-summary">
          <span class="swc-pill swc-pill-total">Total: {{ $semesterCourses->count() }}</span>
          <span class="swc-pill swc-pill-compulsary">Compulsary: {{ $compulsaryCount }}</span>
          <span class="swc-pill swc-pill-elective">Elective: {{ $electiveCount }}</span>
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:70px">#</th>
                <th>Course</th>
                <th>Title</th>
                <th style="width:180px">Type</th>
                <th style="width:120px">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($semesterCourses as $index => $mappedCourse)
              @php
              $isElective = ($mappedCourse->course_type === 'elective');
              @endphp
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($mappedCourse->programinfo)->course_code ?? 'NA' }}</td>
                <td>{{ optional($mappedCourse->programinfo)->course_title ?? 'Untitled Course' }}</td>
                <td>
                  <span class="swc-type-badge {{ $isElective ? 'swc-type-elective' : 'swc-type-compulsary' }}">
                    {{ $isElective ? 'Elective' : 'Compulsary' }}
                  </span>
                </td>
                <td>
                  <form action="{{ route('delete.program.semster.courses.mapping', $mappedCourse->id) }}" method="POST" onsubmit="return confirm('Delete this mapping? Delete is blocked if marks or attendance already exist for mapped students.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @empty
    <div class="swc-empty">
      <h5 class="mb-1">No mapped courses yet</h5>
      <p class="mb-0">Use the form above to add course mappings semester-wise.</p>
    </div>
    @endforelse

  </div>
</div>

@include('includes.footer')