@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Dashboard</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">My Subjects</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <h5 class="mb-1">My Subjects</h5>
                <p class="mb-0 text-muted">Subjects assigned to you organized by batch</p>

              </div>

              <div>
                <span class="badge bg-primary">Total Batches: {{ count($batchWiseSubjects) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @forelse($batchWiseSubjects as $batchName => $batchData)
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header bg-light">
            <div class="d-flex align-items-center justify-content-between">
              <h6 class="mb-0">
                <i class="bi bi-people-fill text-primary me-2"></i>
                {{ $batchName }}
              </h6>
              <span class="badge bg-info">{{ count($batchData) }} Subject(s)</span>
            </div>
          </div>
          <div class="card-body">
            <div class="accordion" id="accordion{{ str_replace(' ', '', $batchName) }}">
              @foreach($batchData as $index => $subject)
              <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="heading{{ str_replace(' ', '', $batchName) }}{{ $index }}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ str_replace(' ', '', $batchName) }}{{ $index }}" aria-expanded="false">
                    <div class="d-flex align-items-center justify-content-between w-100">
                      <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-secondary">{{ $subject->syllabus->courseLink->courseMaster->coursetypemaster->title ?? 'N/A' }}</span>
                        <span class="text-muted">|</span>
                        <span class="fw-bold">{{ $subject->syllabus->subject->title ?? 'N/A' }}</span>
                        <span class="text-muted">|</span>
                        <span class="text-muted">{{ $subject->syllabus->courseLink->courseMaster->course_code ?? 'N/A' }} - {{ $subject->syllabus->courseLink->courseMaster->course_title ?? 'N/A' }}</span>

                      </div>
                      <div class="d-flex align-items-center gap-2 me-3">
                        <span class="badge bg-info">{{ $subject->syllabus->semestermaster->title ?? 'N/A' }}</span>
                        <span class="badge bg-warning text-dark">Credits: {{ $subject->syllabus->courseLink->courseMaster->credits ?? 0 }}</span>
                        <small class="text-muted">Int: {{ $subject->syllabus->courseLink->courseMaster->internal ?? 0 }} | Ext: {{ $subject->syllabus->courseLink->courseMaster->external ?? 0 }}</small>
                      </div>
                    </div>
                  </button>
                </h2>
                <div id="collapse{{ str_replace(' ', '', $batchName) }}{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#accordion{{ str_replace(' ', '', $batchName) }}">
                  <div class="accordion-body">
                    @if($subject->syllabus->courseLink && $subject->syllabus->courseLink->courseMaster && $subject->syllabus->courseLink->courseMaster->csos && $subject->syllabus->courseLink->courseMaster->csos->count() > 0)
                    <div class="mb-4">
                      <h6 class="text-primary mb-3">
                        <i class="bi bi-book me-2"></i>
                        Course: {{ $subject->syllabus->courseLink->courseMaster->course_code }} - {{ $subject->syllabus->courseLink->courseMaster->course_title }}
                      </h6>

                      @foreach($subject->syllabus->courseLink->courseMaster->csos as $cso)
                      <div class="card mb-3 border-start border-primary border-3">
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-1">
                              <span class="badge bg-primary me-2">CSO #{{ $cso->id }}</span>
                              {{ $cso->title }}
                            </h6>
                            <span class="badge bg-info">{{ $cso->lectures_needed ?? 0 }} Lectures</span>
                          </div>

                          @if($cso->csosubunits && $cso->csosubunits->count() > 0)
                          <div class="mt-3">
                            <p class="mb-2 fw-bold text-muted small">CSO Subunits:</p>
                            <div class="table-responsive">
                              <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                  <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 60%;">Subunit Title</th>
                                    <th style="width: 20%;">Taxonomy </th>
                                    <th style="width: 15%;">Status</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($cso->csosubunits as $subunit)
                                  <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $subunit->title }}</td>
                                    <td>
                                      @if($subunit->taxomonylevel)
                                      <span class="badge bg-secondary">{{ $subunit->taxomonylevel->shortname }}</span>
                                      @else
                                      -
                                      @endif
                                    </td>
                                    <td>
                                      <a href="{{ route('faculty.toggle.subunitcompletion', $subunit->id) }}"> <button type="button"
                                          class="btn btn-sm  {{ $subunit->is_completed == 1 ? 'btn-success' : 'btn-warning' }}"
                                          title="Click to toggle completion">

                                          {{$subunit->is_completed }}
                                        </button>
                                      </a>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                              </table>
                            </div>
                          </div>
                          @else
                          <p class="text-muted small mb-0 mt-2">No subunits available</p>
                          @endif
                        </div>
                      </div>
                      @endforeach
                    </div>
                    @else
                    <div class="alert alert-info mb-0">
                      <i class="bi bi-info-circle me-2"></i>
                      No syllabus data available for this subject
                    </div>
                    @endif
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <h5 class="mt-3 text-muted">No Subjects Assigned</h5>
            <p class="text-muted">You don't have any subjects assigned yet.</p>
          </div>
        </div>
      </div>
    </div>
    @endforelse

  </main>
  <!--end main wrapper-->
</div>



@include('includes.footer')