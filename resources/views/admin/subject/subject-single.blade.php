<?php

use App\Models\BatchMaster;

$batches = BatchMaster::get();
?>
@include('includes.header')
@include('admin.sidebar')

<div class="p-5 mb-4 profile-header-sub text-white rounded-3 shadow">
  <div class="container-fluid py-3">
    <h1 class="display-5 fw-bold text-light text-capitalize">{{ $data->title }} </h1>
    <p class="col-md-8 fs-4">
      Program: <span class="fw-semibold">{{ $data->program_master->title }}</span>
    </p>
    <p class="fs-6">
      Academic Batch: <span class="fw-semibold text-warning">{{ $batchmaster->batch_name }}</span>
    </p>
    <div class="row">
      <div class="col-lg-2">
        <form action="{{url('erp/admin/master/view-subject')}}" method="get">
          <input type="hidden" name="id" value="{{$data->id}}">
          <input type="hidden" name="slug" value="{{$data->slug}}">
          <div class="input-group">
            <select name="batch" class="form-select">
              @foreach ($batches as $batch)
              <option value="{{$batch->id}}" {{ $batchmaster->id == $batch->id ? 'selected' : ''}}>{{$batch->batch_name}}</option>
              @endforeach
            </select>
            <button type="submit" class="btn btn-outline-light"><i class="fa fa-search"></i></button>
          </div>
        </form>
      </div>
    </div>


  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="container my-5">
      <h2 class="h3 text-dark border-bottom pb-2 mb-4">Course Syllabus</h2>

      {{-- Group syllabus items by semester for cleaner display --}}
      @php
      $syllabiBySemester = collect($data['syllabus'])->groupBy('semester_id');
      @endphp

      <div class="accordion" id="syllabusAccordion">
        @foreach ($syllabiBySemester as $semesterId => $syllabi)
        {{-- Accordion Item: Semester Header --}}
        <div class="accordion-item shadow-sm mb-3">
          <h2 class="accordion-header" id="heading-{{ $semesterId }}">
            <button class="accordion-button fs-5 @if($loop->first) @else collapsed @endif" type="button"
              data-bs-toggle="collapse" data-bs-target="#collapse-{{ $semesterId }}"
              aria-expanded="@if($loop->first) true @else false @endif" aria-controls="collapse-{{ $semesterId }}">
              📚 {{ $syllabi[0]['semestermaster']['title'] }}
            </button>
          </h2>

          {{-- Accordion Content: Syllabus Cards --}}
          <div id="collapse-{{ $semesterId }}" class="accordion-collapse collapse @if($loop->first) show @endif"
            aria-labelledby="heading-{{ $semesterId }}" data-bs-parent="#syllabusAccordion">
            <div class="accordion-body bg-light">
              <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

                @foreach ($syllabi as $item)
                <div class="col">
                  <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                      {{-- Badge for Subject Type --}}
                      @php
                      $badgeClass = 'bg-secondary';
                      if ($item['subtypemaster']['title'] == 'CORE') {
                      $badgeClass = 'bg-indigo'; // Custom class or utility-color for indigo
                      } elseif ($item['subtypemaster']['title'] == 'MDC') {
                      $badgeClass = 'bg-success';
                      }
                      @endphp
                      <span class="badge {{ $badgeClass }} mb-2">{{ $item['subtypemaster']['title'] }}</span>

                      <h5 class="card-title fw-bold">{{ $item['title'] }}</h5>
                      <p class="card-text text-muted">{{ $item['content'] }}</p>

                      @if (isset($item['timetable']))
                      <div class="mt-3 pt-3 border-top">
                        <p class="card-subtitle text-sm fw-medium text-dark">Timetable Info:</p>
                        <ul class="list-unstyled small text-muted mt-1">
                          <li>Weekday ID: **{{ $item->timetable->weekdaymaster->title }}**</li>
                          <li>Hour ID: **{{ $item->timetable->hourmaster->title }}**</li>
                          <li>Lecture Hall ID: **{{ $item->timetable->lecturehallmaster->acblockmaster->title }}**</li>
                        </ul>
                      </div>
                      @else
                      <p class="mt-3 small text-danger">No timetable assigned.</p>
                      @endif
                    </div>
                  </div>
                </div>
                @endforeach

              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="container my-5">
      <h2 class="h3 text-dark border-bottom pb-2 mb-4">Faculty</h2>
    </div>
  </div>
</div>



@include('includes.footer')