<?php

use App\Http\Controllers\StaticController;

$fetchPrograms = StaticController::fetchProgramGroupNew();

?>
@include('includes.header')
@include('admin.accounts.sidebar')

{{-- Add New Modal --}}
<div class="modal fade" id="add" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
      <div class="modal-header fcm-modal-header-add">
        <h5 class="modal-title" id="addModalLabel"><i class="fa fa-plus-circle me-2"></i>New Fee Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{url('erp/admin/accounts/fee-course-master')}}" method="post">
        @csrf
        <div class="modal-body p-4">
          <label class="form-label fw-semibold text-muted small text-uppercase">Course Title <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. B.Sc Semester Fee">
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success px-4"><i class="fa fa-check me-1"></i>Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Page Header --}}
<div class="fcm-page-header">
  <h4>
    <i class="fa fa-layer-group me-2 text-primary"></i>Fee Course Master
    <small>Manage fee courses and their linked program groups</small>
  </h4>
  <button class="fcm-action-btn fcm-btn-add px-3 py-2" style="font-size:0.9rem;" data-bs-toggle="modal" data-bs-target="#add">
    <i class="fa fa-plus-circle"></i> Add New Course
  </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert" style="border-radius:10px;">
  <i class="fa fa-check-circle"></i> {{ session('success') }}
  <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert" style="border-radius:10px;">
  <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
  <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card fcm-card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <span class="fw-semibold text-muted small text-uppercase" style="letter-spacing:.06em;">
      <i class="fa fa-list me-1"></i> Showing {{ count($data) }} record(s)
    </span>
    <form action="{{url('erp/admin/accounts/fee-course-master')}}" method="get" class="fcm-filter-form">
      <div class="input-group" style="width:280px;">
        <span class="input-group-text bg-white border-end-0" style="border-radius:8px 0 0 8px;"><i class="fa fa-search text-muted"></i></span>
        <select name="coursemaster" class="form-select border-start-0" style="border-radius:0 8px 8px 0;">
          <option value="">Filter by course…</option>
          @foreach ($allcourses as $c)
          <option value="{{$c->id}}" @if(request('coursemaster')==$c->id) selected @endif>{{$c->name}}</option>
          @endforeach
        </select>
        @if(request('coursemaster'))
        <a href="{{url('erp/admin/accounts/fee-course-master')}}" class="btn btn-outline-secondary" style="border-radius:0 8px 8px 0;" title="Clear filter"><i class="fa fa-times"></i></a>
        @else
        <button type="submit" class="btn btn-primary" style="border-radius:0 8px 8px 0;">Go</button>
        @endif
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table fcm-table mb-0">
      <thead>
        <tr>
          <th style="width:60px;">ID</th>
          <th>Course Name</th>
          <th>Program Groups</th>
          <th>Link Programs</th>
          <th style="width:80px;">Edit</th>
          <th style="width:80px;">Delete</th>
        </tr>
      </thead>
      <tbody>
        @if (count($data))
        @foreach ($data as $item)
        <?php $courseGroup = StaticController::fetchCourseMasterGroups($item->id); ?>
        <tr data-course-id="{{$item->id}}" data-course-name="{{strtolower($item->name)}}">
          <td><span class="fcm-id-badge">#{{$item->id}}</span></td>
          <td><span class="fcm-course-name">{{$item->name}}</span></td>

          {{-- Program Groups Cell --}}
          <td>
            @if(count($courseGroup))
            <button type="button" class="fcm-group-badge" data-bs-toggle="modal" data-bs-target="#viewProgs{{$item->id}}">
              <i class="fa fa-users"></i> {{count($courseGroup)}} group{{ count($courseGroup) > 1 ? 's' : '' }}
            </button>
            @else
            <span class="fcm-group-empty"><i class="fa fa-minus-circle me-1"></i>None</span>
            @endif

            {{-- View Linked Programs Modal --}}
            <div class="modal fade" id="viewProgs{{$item->id}}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
                  <div class="modal-header fcm-modal-header-view">
                    <div>
                      <h5 class="modal-title"><i class="fa fa-users me-2"></i>Linked Program Groups</h5>
                      <div style="color:rgba(255,255,255,0.8); font-size:0.8rem;">{{$item->name}}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-4">
                    @if(count($courseGroup))
                    <div class="row g-3">
                      @foreach ($courseGroup as $s)
                      <div class="col-lg-6">
                        <div class="fcm-linked-card">
                          <div>
                            <div class="fcm-linked-code">{{$s->programgroupinfo->programInfo->code ?? '—'}}</div>
                            <div class="fcm-linked-name">{{$s->programgroupinfo->programInfo->name ?? 'Unknown Program'}}</div>
                            <div class="fcm-linked-campus"><i class="fa fa-map-marker-alt me-1"></i>{{$s->programgroupinfo->campus->name ?? '—'}}</div>
                          </div>
                          <a href="{{url('erp/admin/accounts/unlink/fee-structure-group/'.$s->id)}}" class="fcm-unlink-btn" title="Unlink this program" onclick="return confirm('Remove this program group?')">
                            <i class="fa fa-times"></i>
                          </a>
                        </div>
                      </div>
                      @endforeach
                    </div>
                    @else
                    <div class="fcm-empty">
                      <i class="fa fa-unlink"></i>
                      <p class="mb-0">No program groups linked yet.</p>
                    </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </td>

          {{-- Link Programs Cell --}}
          <td>
            <button class="fcm-action-btn fcm-btn-add" data-bs-target="#linkAddModal{{$item->id}}" data-bs-toggle="modal">
              <i class="fa fa-link"></i> Link Groups
            </button>

            <div class="modal fade" id="linkAddModal{{$item->id}}" aria-hidden="true" tabindex="-1">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
                  <div class="modal-header fcm-modal-header-link">
                    <div>
                      <h5 class="modal-title"><i class="fa fa-link me-2"></i>Link Program Groups</h5>
                      <div style="color:rgba(255,255,255,0.8); font-size:0.8rem;">{{$item->name}}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="{{route('link.coursemaster.prggroup')}}" method="post">
                    @csrf
                    <div class="modal-body p-4">
                      <label class="form-label fw-semibold text-muted small text-uppercase">Select Program Groups <span class="text-danger">*</span></label>
                      <div class="text-muted small mb-2">Hold Ctrl / Cmd to select multiple groups.</div>
                      <select name="progs[]" class="select-multiple form-select" multiple style="min-height:180px;">
                        @foreach ($fetchPrograms as $p)
                        <option value="{{$p->id}}">{{$p->programInfo->code ?? ''}} — {{$p->programInfo->name ?? ''}} | {{$p->campus->name ?? ''}}</option>
                        @endforeach
                      </select>
                      <input type="hidden" name="coursemasterId" value="{{$item->id}}">
                    </div>
                    <div class="modal-footer border-0 pt-0">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary px-4"><i class="fa fa-check me-1"></i>Link Selected</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </td>

          {{-- Edit Cell --}}
          <td>
            <button type="button" class="fcm-action-btn fcm-btn-edit" data-bs-toggle="modal" data-bs-target="#edit{{$item->id}}" title="Edit">
              <i class="fa fa-edit"></i>
            </button>

            <div class="modal fade" id="edit{{$item->id}}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
                  <div class="modal-header fcm-modal-header-edit">
                    <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Edit Course Name</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form action="{{url('erp/admin/accounts/update-fee-course-master')}}" method="post">
                    @csrf
                    <div class="modal-body p-4">
                      <label class="form-label fw-semibold text-muted small text-uppercase">Course Name <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control form-control-lg" value="{{$item->name}}">
                      <input type="hidden" name="id" value="{{$item->id}}">
                    </div>
                    <div class="modal-footer border-0 pt-0">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i>Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </td>

          {{-- Delete Cell --}}
          <td>
            <a href="{{url('erp/admin/accounts/del-feecourse-master/'.$item->id)}}"
              onclick="return confirm('Delete \'{{$item->name}}\'? This cannot be undone.')"
              class="fcm-action-btn fcm-btn-delete" style="text-decoration:none;" title="Delete">
              <i class="fa fa-trash-alt"></i>
            </a>
          </td>
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan="6">
            <div class="fcm-empty">
              <i class="fa fa-database"></i>
              <p class="mb-0">No fee course records found.</p>
            </div>
          </td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
</div>

@include('includes.footer')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearchCards');
    if (searchInput) {
      searchInput.addEventListener('keyup', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#courseTable tbody tr').forEach(row => {
          const id = (row.getAttribute('data-course-id') || '').toLowerCase();
          const name = (row.getAttribute('data-course-name') || '').toLowerCase();
          row.style.display = (id.includes(term) || name.includes(term)) ? '' : 'none';
        });
      });
    }
  });

  function filterPrograms(input, tableId) {
    const term = input.value.toLowerCase().trim();
    document.querySelectorAll('#' + tableId + ' tbody tr.program-item').forEach(row => {
      const text = (row.getAttribute('data-program-text') || '').toLowerCase();
      row.style.display = text.includes(term) ? '' : 'none';
    });
  }
</script>