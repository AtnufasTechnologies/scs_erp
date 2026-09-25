<?php

use App\Models\Subject;
use App\Models\StudentMaster;
use App\Models\BatchMaster;
use App\Models\AcademicPathwayMaster;
use App\Models\DegreeTrackMaster;
use App\Models\Semester;
use App\Models\UserHasRole;
use Illuminate\Support\Facades\Auth;

$batches = BatchMaster::all();
$pathways = AcademicPathwayMaster::orderBy('id')->get();
$degreeTracks = DegreeTrackMaster::orderBy('name')->get();
$semesters = Semester::orderBy('id')->get(['id', 'title']);
$roleType = (string) UserHasRole::where('user_id', Auth::id())->value('role_name');
$normalizedRoleType = strtolower(trim($roleType));
$isAccountOfficeRole = strpos($normalizedRoleType, 'account-office') === 0;
$isItcellRole = $normalizedRoleType === 'super-admin'
  || $normalizedRoleType === 'itcell'
  || strpos($normalizedRoleType, 'itcell-') === 0;
?>
@include('includes.header')
@if($isAccountOfficeRole)
@include('admin.accounts.sidebar')
@else
@include('admin.sidebar')
@endif

<style>
  .student-master-container {
    padding: 20px;
    background: transparent;
    min-height: 100vh;
  }

  .page-header {
    background: linear-gradient(135deg, #620fb6 0%, #12b2e7 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  }

  .page-header h2 {
    margin: 0;
    font-weight: 600;
    font-size: 32px;
    color: #fff;
  }

  .search-container {
    background: white;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .search-box {
    position: relative;
    margin-bottom: 20px;
  }

  .search-box input {
    width: 100%;
    padding: 15px 50px 15px 20px;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
  }

  .search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .search-box i {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 20px;
  }

  .search-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #64748b;
    font-size: 14px;
  }

  .search-filters {
    display: grid;
    grid-template-columns: 2fr repeat(6, 1fr);
    gap: 12px;
    margin-bottom: 16px;
  }

  .search-filters select {
    width: 100%;
    padding: 15px 12px;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fff;
  }

  .search-filters select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
  }

  .student-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e1e8ed;
  }

  .student-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
  }

  .student-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
  }

  .student-info {
    flex: 1;
  }

  .student-name {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
    text-transform: capitalize;
  }

  .student-roll {
    display: inline-block;
    background: linear-gradient(135deg, #4cbe9c 0%, #15d1b8 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    text-transform: uppercase;
    transition: all 0.3s ease;
  }

  .student-roll:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white;
    text-decoration: none;
  }

  .gender-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .gender-male {
    background: #dbeafe;
    color: #1e40af;
  }

  .gender-female {
    background: #fce7f3;
    color: #be185d;
  }

  .student-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
  }

  .detail-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
  }

  .detail-value {
    font-size: 14px;
    color: #1e293b;
    font-weight: 500;
  }

  .detail-value a {
    color: #667eea;
    text-decoration: none;
  }

  .detail-value a:hover {
    text-decoration: underline;
  }

  .academic-info {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #f1f5f9;
  }

  .academic-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .academic-tag {
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    color: #475569;
    font-weight: 500;
  }

  .no-results {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .no-results i {
    font-size: 64px;
    color: #cbd5e1;
    margin-bottom: 20px;
  }

  .no-results h3 {
    color: #64748b;
    font-weight: 600;
    margin-bottom: 10px;
  }

  .no-results p {
    color: #94a3b8;
  }

  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 15px;
    z-index: 10;
  }

  .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  .search-box.loading i {
    animation: pulse 1.5s ease-in-out infinite;
  }

  @keyframes pulse {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: 0.5;
    }
  }

  @media (max-width: 768px) {
    .search-filters {
      grid-template-columns: 1fr;
    }

    .cards-grid {
      grid-template-columns: 1fr;
    }

    .student-details {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="student-master-container">
  <div class="page-header">
    <h2>👨‍🎓 Student Master</h2>
    <p style="margin: 10px 0 0 0; opacity: 0.9;">Manage and view all student records</p>

    <!-- Button trigger modal -->
    <!-- <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#promotion">
      Promote Students
    </button> -->

    @if(!$isAccountOfficeRole)
    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#semesterPromotion" style="margin-left:8px;">
      <i class="fas fa-arrow-right"></i> Promote Semester
    </button>

    <!-- Modal -->
    <!-- <div class="modal fade" id="promotion" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-dark" id="exampleModalLabel">Annual Promotion Management</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{route('promotion.prepare.list')}}" method="get">
            <div class="modal-body">
              <div class="alert alert-info">
                <p>Semester wise promotion is automatic once the student is mapped with course enrollment</p>
              </div>

              <div class="row">
                <div class="col-lg-6"><label for="" class="text-dark">Select Student's Batch *</label>
                  <select name="batch" class="form-control ">
                    <option value="">--Select--</option>
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                    @endforeach
                  </select>
                  @error('batch')
                  <span class="text-danger">{{$message}}</span>
                  @enderror
                </div>
                <div class="col-lg-6">
                  <label for="" class="text-dark">Campus *</label>
                  <select name="campus" class="form-control">
                    <option value="">-- Required --</option>
                    <option value="1">-- Sonada --</option>
                    <option value="2">-- Siliguri --</option>
                  </select>
                  @error('campus')
                  <span class="text-danger">{{$message}}</span>
                  @enderror
                </div>

              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Generate List</button>
            </div>
          </form>
        </div>
      </div>
    </div> -->

    <div class="modal fade" id="semesterPromotion" tabindex="-1" aria-labelledby="semesterPromotionLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-dark" id="semesterPromotionLabel">Bulk Semester Promotion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{ route('semester.promotion.prepare.list') }}" method="get">
            <div class="modal-body">
              <div class="alert alert-info">
                <p>Generate a batch-wise student list and promote semester in bulk.</p>
              </div>

              <div class="row">
                <div class="col-lg-6"><label for="" class="text-dark">Select Student's Batch *</label>
                  <select name="batch" class="form-control ">
                    <option value="">--Select--</option>
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-lg-6">
                  <label for="" class="text-dark">Campus *</label>
                  <select name="campus" class="form-control">
                    <option value="">-- Required --</option>
                    <option value="1">-- Sonada --</option>
                    <option value="2">-- Siliguri --</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Generate List</button>
            </div>
          </form>
        </div>
      </div>
    </div>


    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#generateLibraryCode">
      <i class="fas fa-books"></i> Generate Library Code
    </button>

    <div class="modal fade" id="generateLibraryCode" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-dark" id="exampleModalLabel">Library Code Generator</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{route('itcell.generate.librarycode')}}" method="post">
            @csrf
            <div class="modal-body">
              <div class="alert alert-info">
                <p> 4Digit code is auto generated keeping unique combination </p>
              </div>

              <div class="row">
                <div class="col-lg-4"><label for="" class="text-dark">Select Student's Batch *</label>
                  <select name="batch" class="form-control ">
                    <option value="">--Select--</option>
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                    @endforeach
                  </select>
                  @error('batch')
                  <span class="text-danger">{{$message}}</span>
                  @enderror
                </div>
                <div class="col-lg-4">
                  <label for="" class="text-dark">Campus *</label>
                  <select name="campus" class="form-control">
                    <option value="">-- Required --</option>
                    <option value="1">-- Sonada --</option>
                    <option value="2">-- Siliguri --</option>
                  </select>
                  @error('campus')
                  <span class="text-danger">{{$message}}</span>
                  @enderror
                </div>

                <div class="col-lg-4">
                  <label for="" class="text-dark">Action Type</label>
                  <select name="action_type" class="form-control">

                    <option value="generate">-- Generate --</option>
                    <option value="download">-- Download --</option>
                  </select>
                </div>

              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif


    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#expotData">
      <i class="fas fa-file-excel"></i> Export
    </button>

    <div class="modal fade" id="expotData" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-dark" id="exampleModalLabel">Export Data to Excel</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="{{route('itcell.generate.excel.studentdata')}}" method="post">
            @csrf
            <div class="modal-body">
              <div class="alert alert-info">
                <p> Full Student Data will be Exported in Spreadsheet Format </p>
              </div>

              <div class="row">
                <div class="col-lg-4"><label for="" class="text-dark">Select Student's Batch *</label>
                  <select name="batch" class="form-control ">
                    <option value="">--Select--</option>
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                    @endforeach
                  </select>
                  @error('batch')
                  <span class="text-danger">{{$message}}</span>
                  @enderror
                </div>
                <div class="col-lg-4">
                  <label for="" class="text-dark">Campus *</label>
                  <select name="campus" class="form-control">
                    <option value="">-- Required --</option>
                    <option value="1">-- Sonada --</option>
                    <option value="2">-- Siliguri --</option>
                  </select>
                  @error('campus')
                  <span class="text-danger">{{$message}}</span>
                  @enderror
                </div>

              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Export</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="search-container">
    <?php $selectedBatchId = request()->input('batch_id'); ?>
    <?php $selectedPathwayId = request()->input('academic_pathway_id'); ?>
    <?php $selectedDegreeTrackId = request()->input('degree_track_id'); ?>
    <?php $selectedLibraryCodeFilter = request()->input('library_code_filter'); ?>
    <?php $selectedCurrentYear = request()->input('current_year'); ?>
    <?php $selectedSemesterId = request()->input('semester_id'); ?>
    <div class="search-filters">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search by name, roll no, register no, email..." autocomplete="off">
        <i class="fas fa-search"></i>
      </div>
      <div>
        <select id="batchFilter" class="form-control" aria-label="Filter by batch">
          <option value="">All Batches</option>
          @foreach ($batches as $batch)
          <option value="{{ $batch->id }}" {{ (string)$selectedBatchId === (string)$batch->id ? 'selected' : '' }}>{{ $batch->batch_name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <select id="pathwayFilter" class="form-control" aria-label="Filter by academic pathway">
          <option value="">All Pathways</option>
          @foreach ($pathways as $pathway)
          <option value="{{ $pathway->id }}" {{ (string)$selectedPathwayId === (string)$pathway->id ? 'selected' : '' }}>{{ $pathway->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <select id="degreeTrackFilter" class="form-control" aria-label="Filter by degree track">
          <option value="">All Degree Tracks</option>
          @foreach ($degreeTracks as $track)
          <option value="{{ $track->id }}" {{ (string)$selectedDegreeTrackId === (string)$track->id ? 'selected' : '' }}>{{ $track->name }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <select id="libraryCodeFilter" class="form-control" aria-label="Filter by library code availability">
          <option value="">All Library Code</option>
          <option value="missing" {{ (string)$selectedLibraryCodeFilter === 'missing' ? 'selected' : '' }}>Library Code Missing</option>
        </select>
      </div>
      <div>
        <select id="currentYearFilter" class="form-control" aria-label="Filter by current year">
          <option value="">All Current Years</option>
          @for ($year = 1; $year <= 6; $year++)
            <option value="{{ $year }}" {{ (string)$selectedCurrentYear === (string)$year ? 'selected' : '' }}>Year {{ $year }}</option>
            @endfor
        </select>
      </div>
      <div>
        <select id="semesterFilter" class="form-control" aria-label="Filter by semester">
          <option value="">All Semesters</option>
          @foreach ($semesters as $semesterOption)
          <option value="{{ $semesterOption->id }}" {{ (string)$selectedSemesterId === (string)$semesterOption->id ? 'selected' : '' }}>{{ $semesterOption->title }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="search-stats">
      <span id="resultCount">Showing <strong>{{ count($data) }}</strong> students</span>
      <span id="searchStatus"></span>
    </div>
    <input type="hidden" id="totalStudents" value="{{ $data->total() ?? count($data) }}">
  </div>

  <div class="cards-grid" id="studentsGrid">
    @if (count($data))
    @foreach ($data as $item)
    <?php $semester = $item->activeSemesterConfig->semester_id ?? null; ?>

    <div class="student-card" data-search-content="{{ strtolower($item->first_name . ' ' . $item->last_name . ' ' . $item->roll_no . ' ' . $item->register_no . ' ' . $item->mail_id . ' ' . ($item->deptmaster != null ? $item->deptmaster->name : '') . ' ' . ($item->stdprogramenrolled != null ? $item->stdprogramenrolled->code : '') . ' ' . ($item->stdprogramenrolled != null ? $item->stdprogramenrolled->name : '') . ' ' . ($item->campusmaster != null ? $item->campusmaster->name : '') . ' ' . ($item->batchmaster != null ? $item->batchmaster->batch_name : '')) }}">

      <div class="student-card-header">
        <div class="student-info">
          <div class="student-name">{{ $item->first_name }} {{ $item->last_name }}</div>
          @if($isAccountOfficeRole)
          <span class="student-roll">{{ $item->roll_no }}</span>
          @else
          <a href="{{ url('erp/admin/'.$item->id.'/std-profile/'.$item->roll_no) }}" class="student-roll">
            {{ $item->roll_no }}
          </a>
          @endif
          <span class="badge badge-warning">📚 {{$item->library_code}}</span>
          <span class="badge badge-primary">{{ $item->academicpathway->name ?? ''}} - {{ $item->degreetrack->name ?? '' }}</span>
          <span class="badge badge-primary">{{ $item->singleselection->title ?? '' }}</span>
        </div>
        <span class="gender-badge {{ $item->gender == '1' ? 'gender-male' : 'gender-female' }}">
          {{ $item->gender == '1' ? 'Male' : 'Female' }}
        </span>


      </div>

      <div class="academic-info">
        <div class="academic-tags">
          <span class="academic-tag">
            🎯 Program: {{ $item->stdprogramenrolled != null ? ($item->stdprogramenrolled->code . ' - ' . $item->stdprogramenrolled->name) : 'Not Mapped' }}
          </span>
          <span class="academic-tag">📅 Batch: {{ $item->batchmaster != null ? $item->batchmaster->batch_name : 'N/A' }}</span>
          <span class="academic-tag">📖 Active Sem: {{ $semester ?? 'Not Set' }} </span>
          <span class="academic-tag">📊 Year: {{ $item->current_year }}</span>
          @if($isItcellRole)
          <form method="POST" action="{{ route('itcell.generate.librarycode.individual', $item->id) }}" class="d-inline"
            onsubmit="return confirm('Generate library code for this student?')">
            @csrf
            <!-- <button
              type="submit"
              class="btn btn-sm btn-outline-secondary"
              {{ !empty($item->library_code) ? 'disabled' : '' }}>
              {{ !empty($item->library_code) ? 'Library Code Exists' : 'Generate Library Code' }}
            </button> -->
          </form>
          <form method="POST" action="{{ route('itcell.allot.next.librarycode.individual', $item->id) }}" class="d-inline"
            onsubmit="return confirm('Allot next sequence library code to this student?')">
            @csrf
            <button
              type="submit"
              class="btn btn-sm btn-outline-primary">
              Fix Library Code
            </button>
          </form>
          <!-- <button
            type="button"
            class="btn btn-sm btn-outline-secondary correct-library-code-btn"
            data-bs-toggle="modal"
            data-bs-target="#correctLibraryCodeModal"
            data-student-id="{{ $item->id }}"
            data-student-name="{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) }}"
            data-roll-no="{{ $item->roll_no }}"
            data-library-code="{{ $item->library_code ?? '' }}">
            Manual Correct Code
          </button> -->
          @endif
          @if(!$isAccountOfficeRole)
          <button
            type="button"
            class="btn btn-sm btn-outline-danger demote-semester-btn"
            data-student-id="{{ $item->id }}"
            data-student-name="{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) }}"
            data-semester="{{ $semester ?? 0 }}"
            {{ (int)($semester ?? 0) <= 1 ? 'disabled' : '' }}>
            Demote Semester
          </button>
          @endif
        </div>
      </div>
    </div>
    @endforeach
    @else
    <div class="no-results">
      <i class="fas fa-user-slash"></i>
      <h3>No Students Found</h3>
      <p>There are no student records available at the moment.</p>
    </div>
    @endif


  </div>
  {{$data->links('vendor.pagination.bootstrap-5')}}

  <div id="noResultsMessage" class="no-results" style="display: none;">
    <i class="fas fa-search"></i>
    <h3>No Results Found</h3>
    <p>Try adjusting your search terms</p>
  </div>

  @if($isItcellRole)
  <div class="modal fade" id="correctLibraryCodeModal" tabindex="-1" aria-labelledby="correctLibraryCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="correctLibraryCodeModalLabel">Correct Library Code</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" id="correctLibraryCodeForm" action="">
          @csrf
          <div class="modal-body">
            <p class="mb-2" id="correctLibraryCodeStudentInfo"></p>
            <label for="correctLibraryCodeInput" class="form-label">Library Code (4 digits)</label>
            <input
              type="text"
              class="form-control"
              id="correctLibraryCodeInput"
              name="library_code"
              maxlength="4"
              pattern="\d{4}"
              inputmode="numeric"
              required>
            <small class="text-muted">Use a unique 4-digit numeric code.</small>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Code</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif
</div>



<script>
  // AJAX-based live search functionality
  const searchInput = document.getElementById('searchInput');
  const studentsGrid = document.getElementById('studentsGrid');
  const resultCount = document.getElementById('resultCount');
  const searchStatus = document.getElementById('searchStatus');
  const noResultsMessage = document.getElementById('noResultsMessage');
  const searchBox = document.querySelector('.search-box');
  const batchFilter = document.getElementById('batchFilter');
  const pathwayFilter = document.getElementById('pathwayFilter');
  const degreeTrackFilter = document.getElementById('degreeTrackFilter');
  const libraryCodeFilter = document.getElementById('libraryCodeFilter');
  const currentYearFilter = document.getElementById('currentYearFilter');
  const semesterFilter = document.getElementById('semesterFilter');
  const isAccountOfficeRole = @json($isAccountOfficeRole);
  const isItcellRole = @json($isItcellRole);
  const studentMasterContainer = document.querySelector('.student-master-container');

  let searchTimeout;
  let isSearching = false;
  const currentUrl = window.location.pathname;
  const campusId = currentUrl.includes('sonada') ? 1 : 2;
  const initialBatchId = new URLSearchParams(window.location.search).get('batch_id') || '';
  const initialPathwayId = new URLSearchParams(window.location.search).get('academic_pathway_id') || '';
  const initialDegreeTrackId = new URLSearchParams(window.location.search).get('degree_track_id') || '';
  const initialLibraryCodeFilter = new URLSearchParams(window.location.search).get('library_code_filter') || '';
  const initialCurrentYear = new URLSearchParams(window.location.search).get('current_year') || '';
  const initialSemesterId = new URLSearchParams(window.location.search).get('semester_id') || '';
  if (batchFilter && initialBatchId) {
    batchFilter.value = initialBatchId;
  }
  if (pathwayFilter && initialPathwayId) {
    pathwayFilter.value = initialPathwayId;
  }
  if (degreeTrackFilter && initialDegreeTrackId) {
    degreeTrackFilter.value = initialDegreeTrackId;
  }
  if (libraryCodeFilter && initialLibraryCodeFilter) {
    libraryCodeFilter.value = initialLibraryCodeFilter;
  }
  if (currentYearFilter && initialCurrentYear) {
    currentYearFilter.value = initialCurrentYear;
  }
  if (semesterFilter && initialSemesterId) {
    semesterFilter.value = initialSemesterId;
  }
  const totalStudents = parseInt(document.getElementById('totalStudents').value) || 0;

  function showLoading() {
    searchBox.classList.add('loading');
    searchStatus.innerHTML = '<span style="color: #667eea;">🔄 Searching...</span>';
  }

  function hideLoading() {
    searchBox.classList.remove('loading');
  }

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

  function renderStudentCard(student) {
    return `
      <div class="student-card" style="opacity: 0; transform: translateY(20px);">
        <div class="student-card-header">
          <div class="student-info">
            <div class="student-name">${student.first_name} ${student.last_name}</div>
            ${isAccountOfficeRole ? `<span class="student-roll">${student.roll_no}</span>` : `<a href="/erp/admin/${student.id}/std-profile/${student.roll_no}" class="student-roll">${student.roll_no}</a>`}
                         ${student.library_code ? `<span class="badge badge-warning">📚 ${student.library_code}</span>` : ''}
            
              ${student.academicpathway?.name || student.degreetrack?.name ? `<span class="badge badge-primary">${student.academicpathway?.name || ''}${student.academicpathway?.name && student.degreetrack?.name ? ' - ' : ''}${student.degreetrack?.name || ''}</span>` : ''}
              ${student.singleselection?.title ? `<span class="badge badge-primary">${student.singleselection.title}</span>` : ''}
           
          </div>
          <span class="gender-badge ${student.gender == '1' ? 'gender-male' : 'gender-female'}">
            ${student.gender == '1' ? 'Male' : 'Female'}
          </span>
        </div>

        <div class="academic-info">
          <div class="academic-tags">
            <span class="academic-tag">🎯 Program: ${student.stdprogramenrolled ? `${student.stdprogramenrolled.code} - ${student.stdprogramenrolled.name}` : 'Not Mapped'}</span>
            <span class="academic-tag">📅 Batch: ${student.batchmaster?.batch_name || 'N/A'}</span>
            <span class="academic-tag">📖 Active Sem: ${student.current_semester || 'Not Set'}</span>
            <span class="academic-tag">📊 Year: ${student.current_year || 'N/A'}</span>
            ${isItcellRole ? `<form method="POST" action="/erp/admin/itcell-generate-librarycode/${student.id}" class="d-inline" onsubmit="return confirm('Generate library code for this student?')">
              <input type="hidden" name="_token" value="${csrfToken}">
             
            </form>
            <form method="POST" action="/erp/admin/itcell-allot-next-librarycode/${student.id}" class="d-inline" onsubmit="return confirm('Allot next sequence library code to this student?')">
              <input type="hidden" name="_token" value="${csrfToken}">
              <button type="submit" class="btn btn-sm btn-outline-primary">
               Fix Lib Code
              </button>
            </form>
            ` : ''}
            ${isAccountOfficeRole ? '' : `<button
              type="button"
              class="btn btn-sm btn-danger "
              data-student-id="${student.id}"
              data-student-name="${(student.first_name || '')} ${(student.last_name || '')}" 
              data-semester="${student.current_semester || 0}"
              ${(parseInt(student.current_semester || 0, 10) <= 1) ? 'disabled' : ''}>
              Demote Semester
            </button>`}
          </div>
        </div>
      </div>
    `;
  }

  function animateCards() {
    const cards = document.querySelectorAll('.student-card');
    cards.forEach((card, index) => {
      setTimeout(() => {
        card.style.transition = 'all 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 50);
    });
  }

  function performSearch(searchTerm) {
    if (isSearching) return;

    isSearching = true;
    showLoading();

    const selectedBatch = batchFilter ? batchFilter.value : '';
    const selectedPathway = pathwayFilter ? pathwayFilter.value : '';
    const selectedDegreeTrack = degreeTrackFilter ? degreeTrackFilter.value : '';
    const selectedLibraryCode = libraryCodeFilter ? libraryCodeFilter.value : '';
    const selectedCurrentYear = currentYearFilter ? currentYearFilter.value : '';
    const selectedSemester = semesterFilter ? semesterFilter.value : '';
    const queryParams = new URLSearchParams({
      search: searchTerm,
      campus_id: campusId
    });

    if (selectedBatch) {
      queryParams.append('batch_id', selectedBatch);
    }

    if (selectedPathway) {
      queryParams.append('academic_pathway_id', selectedPathway);
    }

    if (selectedDegreeTrack) {
      queryParams.append('degree_track_id', selectedDegreeTrack);
    }

    if (selectedLibraryCode) {
      queryParams.append('library_code_filter', selectedLibraryCode);
    }

    if (selectedCurrentYear) {
      queryParams.append('current_year', selectedCurrentYear);
    }

    if (selectedSemester) {
      queryParams.append('semester_id', selectedSemester);
    }

    fetch(`/erp/admin/student-search?${queryParams.toString()}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        hideLoading();
        isSearching = false;

        if (data.students && data.students.length > 0) {
          studentsGrid.innerHTML = data.students.map(student => renderStudentCard(student)).join('');
          studentsGrid.style.display = 'grid';
          noResultsMessage.style.display = 'none';

          resultCount.innerHTML = `Showing <strong>${data.students.length}</strong> of <strong>${data.total}</strong> students`;
          searchStatus.innerHTML = '<span style="color: #10b981;">✓ Search complete</span>';

          // Animate cards
          animateCards();
        } else {
          studentsGrid.style.display = 'none';
          noResultsMessage.style.display = 'block';
          resultCount.innerHTML = `Showing <strong>0</strong> of <strong>${data.total}</strong> students`;
          searchStatus.innerHTML = '<span style="color: #ef4444;">😔 No matches</span>';
        }

        // Clear status after 2 seconds
        setTimeout(() => {
          if (!searchInput.value.trim()) {
            searchStatus.textContent = '';
          }
        }, 2000);
      })
      .catch(error => {
        console.error('Search error:', error);
        hideLoading();
        isSearching = false;
        searchStatus.innerHTML = '<span style="color: #ef4444;">❌ Search failed</span>';
      });
  }

  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);

    const searchTerm = this.value.trim();

    searchTimeout = setTimeout(() => {
      performSearch(searchTerm);
    }, 500); // Debounce for 500ms
  });

  function handleFilterChange() {
    const queryParams = new URLSearchParams(window.location.search);

    const selectedBatch = batchFilter ? batchFilter.value : '';
    const selectedPathway = pathwayFilter ? pathwayFilter.value : '';
    const selectedDegreeTrack = degreeTrackFilter ? degreeTrackFilter.value : '';
    const selectedLibraryCode = libraryCodeFilter ? libraryCodeFilter.value : '';
    const selectedCurrentYear = currentYearFilter ? currentYearFilter.value : '';
    const selectedSemester = semesterFilter ? semesterFilter.value : '';

    if (selectedBatch) {
      queryParams.set('batch_id', selectedBatch);
    } else {
      queryParams.delete('batch_id');
    }

    if (selectedPathway) {
      queryParams.set('academic_pathway_id', selectedPathway);
    } else {
      queryParams.delete('academic_pathway_id');
    }

    if (selectedDegreeTrack) {
      queryParams.set('degree_track_id', selectedDegreeTrack);
    } else {
      queryParams.delete('degree_track_id');
    }

    if (selectedLibraryCode) {
      queryParams.set('library_code_filter', selectedLibraryCode);
    } else {
      queryParams.delete('library_code_filter');
    }

    if (selectedCurrentYear) {
      queryParams.set('current_year', selectedCurrentYear);
    } else {
      queryParams.delete('current_year');
    }

    if (selectedSemester) {
      queryParams.set('semester_id', selectedSemester);
    } else {
      queryParams.delete('semester_id');
    }

    const nextUrl = queryParams.toString() ? `${currentUrl}?${queryParams.toString()}` : currentUrl;
    window.history.replaceState({}, '', nextUrl);

    clearTimeout(searchTimeout);
    performSearch(searchInput.value.trim());
  }

  if (batchFilter) {
    batchFilter.addEventListener('change', handleFilterChange);
  }

  if (pathwayFilter) {
    pathwayFilter.addEventListener('change', handleFilterChange);
  }

  if (degreeTrackFilter) {
    degreeTrackFilter.addEventListener('change', handleFilterChange);
  }

  if (libraryCodeFilter) {
    libraryCodeFilter.addEventListener('change', handleFilterChange);
  }

  if (currentYearFilter) {
    currentYearFilter.addEventListener('change', handleFilterChange);
  }

  if (semesterFilter) {
    semesterFilter.addEventListener('change', handleFilterChange);
  }

  const correctLibraryCodeModal = document.getElementById('correctLibraryCodeModal');
  const correctLibraryCodeForm = document.getElementById('correctLibraryCodeForm');
  const correctLibraryCodeInput = document.getElementById('correctLibraryCodeInput');
  const correctLibraryCodeStudentInfo = document.getElementById('correctLibraryCodeStudentInfo');

  if (correctLibraryCodeModal && correctLibraryCodeForm && correctLibraryCodeInput && correctLibraryCodeStudentInfo) {
    correctLibraryCodeModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      if (!button) return;

      const studentId = button.getAttribute('data-student-id') || '';
      const studentName = button.getAttribute('data-student-name') || '';
      const rollNo = button.getAttribute('data-roll-no') || '';
      const libraryCode = button.getAttribute('data-library-code') || '';

      correctLibraryCodeForm.action = `/erp/admin/itcell-update-librarycode/${studentId}`;
      correctLibraryCodeInput.value = '';
      correctLibraryCodeStudentInfo.textContent = `${studentName} (${rollNo}) | Current: ${libraryCode || 'N/A'} | Loading next code...`;

      fetch('/erp/admin/itcell-next-librarycode', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(async response => {
          const payload = await response.json();
          if (!response.ok || !payload.status || !payload.library_code) {
            throw new Error(payload.message || 'Failed to fetch next library code.');
          }
          return payload;
        })
        .then(payload => {
          correctLibraryCodeInput.value = payload.library_code;
          correctLibraryCodeStudentInfo.textContent = `${studentName} (${rollNo}) | Current: ${libraryCode || 'N/A'} | Suggested New: ${payload.library_code}`;
        })
        .catch(() => {
          correctLibraryCodeInput.value = libraryCode || '';
          correctLibraryCodeStudentInfo.textContent = `${studentName} (${rollNo}) | Current: ${libraryCode || 'N/A'} | Could not fetch next code.`;
        });
    });

    correctLibraryCodeForm.addEventListener('submit', function(event) {
      const val = (correctLibraryCodeInput.value || '').trim();
      if (!/^\d{4}$/.test(val)) {
        event.preventDefault();
        alert('Library code must be exactly 4 digits.');
      }
    });
  }

  studentsGrid.addEventListener('click', function(event) {
    const demoteButton = event.target.closest('.demote-semester-btn');
    if (!demoteButton) return;

    const studentId = demoteButton.dataset.studentId;
    const studentName = (demoteButton.dataset.studentName || '').trim() || 'this student';
    const semester = parseInt(demoteButton.dataset.semester || '0', 10);

    if (semester <= 1) {
      alert('Semester cannot be demoted below 1.');
      return;
    }

    const doDemotion = () => {
      demoteButton.disabled = true;

      fetch(`/erp/admin/student/${studentId}/semester-demote`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: JSON.stringify({})
        })
        .then(async response => {
          const payload = await response.json();
          if (!response.ok) {
            throw new Error(payload.message || 'Failed to demote semester.');
          }
          return payload;
        })
        .then(payload => {
          searchStatus.innerHTML = `<span style="color: #10b981;">✓ ${payload.message}</span>`;

          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: 'Done',
              text: payload.message,
              timer: 1400,
              showConfirmButton: false
            });
          }

          performSearch(searchInput.value.trim());
        })
        .catch(error => {
          demoteButton.disabled = false;

          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Demotion Failed',
              text: error.message || 'Failed to demote semester.'
            });
          } else {
            alert(error.message || 'Failed to demote semester.');
          }
        });
    };

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        title: 'Confirm Demotion',
        html: `<strong>${studentName}</strong><br>Semester ${semester} → ${semester - 1}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Demote',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          doDemotion();
        }
      });
    } else {
      const confirmed = confirm(`Demote ${studentName} from semester ${semester} to ${semester - 1}?`);
      if (confirmed) {
        doDemotion();
      }
    }
  });

  // Initial animation for cards on page load
  document.addEventListener('DOMContentLoaded', function() {
    animateCards();
  });

  // Handle modal data population from data attributes
  const addDtsModal = document.getElementById('addDtsModal');
  if (addDtsModal) {
    addDtsModal.addEventListener('show.bs.modal', function(event) {
      // Button that triggered the modal
      const button = event.relatedTarget;

      // Extract info from data-* attributes
      const studentId = button.getAttribute('data-student-id');
      const studentName = button.getAttribute('data-student-name');
      const rollNo = button.getAttribute('data-roll-no');
      const program = button.getAttribute('data-program');

      // Update the modal's content
      document.getElementById('modalStudentId').value = studentId;
      document.getElementById('modalStudentName').textContent = studentName;
      document.getElementById('modalRollNo').textContent = rollNo;
      document.getElementById('modalProgram').textContent = program;

      // Update form action with student ID
      const form = document.getElementById('dtsForm');
      form.action = `/erp/admin/student/${studentId}/select-dts-course`;
    });
  }
</script>

@include('includes.footer')