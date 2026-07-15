<?php

use App\Models\StudentProgramTypeMaster;
use App\Models\Subject;

$program_types = StudentProgramTypeMaster::latest()->get();
$subjects = Subject::latest()->get();
?>
@include('includes.header')
@include('admin.sidebar')
<h3>Student Program Master</h3>
<p>Note: Do not create duplicate code while adding programs</p>
<!-- Button trigger modal -->
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#exampleModal">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">New Program</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('admin.add.new.student-program')}}" method="post">
        @csrf
        <div class="modal-body">


          <div class="row">
            <div class="col-lg-4">
              <label for=""> Campus *</label>
              <select name="campus" class="form-control" required>
                <option value="" selected>--Select--</option>
                <option value="1">Sonada</option>
                <option value="2">Siliguri Campus</option>
              </select>
            </div>
            <div class="col-lg-4">
              <label for=""> Shift *</label>
              <select name="shift" class="form-control" required>
                @foreach(($shiftOptions ?? []) as $shift)
                <option value="{{$shift->slug}}" {{$shift->slug === 'common' ? 'selected' : ''}}>{{$shift->title}}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-4">
              <label for=""> Program Code *</label>
              <input type="text" name="code" class="form-control" required>
            </div>

            <div class="col-lg-4 mt-2">
              <label for=""> No of Semesters *</label>
              <input type="number" name="semester_count" class="form-control" min="1" required>
            </div>


            <div class="col-lg-12">
              <label for="">Program Name *</label>
              <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="col-lg-12">
              <label for="">Description *</label>
              <textarea name="description" id="description" class="form-control"></textarea>
            </div>


          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-lg-4 col-md-6">
      <label for="programLiveSearch" class="form-label">Live Search</label>
      <input
        type="text"
        id="programLiveSearch"
        class="form-control"
        placeholder="Search by code, name, campus, shift, roll-up details...">
    </div>
  </div>

  <table class="table table-hover">
    <thead>
      <tr>
        <th>#</th>
        <th>Campus</th>
        <th>Program Code</th>
        <th>Program Name</th>
        <th>Shift</th>
        <th>Description</th>
        <th>No of Semesters</th>
        <th>Total Enrolled Students</th>
        <th>Program Type</th>
        <th>Combo Map</th>
        <th>Edit</th>
      </tr>
    </thead>

    <tbody>
      @if (count($data))

      @foreach ($data as $d)
      <tr class="program-row" data-program-id="{{$d->id}}">
        <td>{{$loop->iteration}}</td>
        <td class="js-campus">{{$d->campus_id == 1 ? 'Sonada' : 'Siliguri Campus'}}</td>
        <td class="js-code">{{$d->code}}</td>
        <td class="js-name">{{$d->name}}</td>
        <td class="js-shift"><span class="badge badge-info">{{ucfirst($d->shiftmaster->title ?? $d->shift ?? 'common')}}</span></td>
        <td class="js-description">{{$d->description}}</td>
        <td class="js-semester-count">{{$d->semester_count}}</td>
        <td>{{$d->student_count}}</td>
        <td class="js-program-type">{{$d->programtypemaster->name ?? 'Unknown'}}</td>
        <td class="js-combo-map">
          @if($d->programtypemaster != null)
          @if($d->programtypemaster->name == 'UGC')
          <span class="badge badge-success">
            {{$d->combomap->combo1->title ?? 'Unknown'}} - {{$d->combomap->combo2->title ?? 'Unknown'}}
          </span>
          @else
          <span class="badge badge-info">N/A for AICTE</span>
          @endif

          @endif
        </td>

        <td>
          <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#edit{{$d->id}}">
            <i class="fa fa-edit"></i>
          </button>

          <!-- Modal -->
          <div class="modal fade" id="edit{{$d->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Program</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.update.student-program',$d->id)}}" method="post" class="js-program-edit-form" data-program-id="{{$d->id}}" data-modal-id="edit{{$d->id}}">
                  @csrf
                  <input type="hidden" name="id" value="{{$d->id}}">
                  <div class="alert alert-danger d-none js-form-error" role="alert"></div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-lg-4 mb-3">
                        <label for=""> Campus *</label>
                        <select name="campus" class="form-control" required>
                          <option value="" selected>--Select--</option>
                          <option value="1" {{$d->campus_id == 1 ? 'selected' : ''}}>Sonada</option>
                          <option value="2" {{$d->campus_id == 2 ? 'selected' : ''}}>Siliguri Campus</option>
                        </select>
                      </div>
                      <div class="col-lg-4 mb-3">
                        <label for=""> Shift *</label>
                        <select name="shift" class="form-control" required>
                          @foreach(($shiftOptions ?? []) as $shift)
                          <option value="{{$shift->slug}}" {{$d->shift == $shift->slug ? 'selected' : ''}}>{{$shift->title}}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-lg-4 mb-3">
                        <label for=""> Program Code *</label>
                        <input type="text" name="code" value="{{$d->code}}" class="form-control" required>
                      </div>

                      <div class="col-lg-4 mb-3">
                        <label for=""> No of Semesters *</label>
                        <input type="number" name="semester_count" value="{{$d->semester_count}}" class="form-control" min="1" required>
                      </div>
                      <div class="col-lg-12 mb-3">
                        <label for="">Program Name *</label>
                        <input type="text" name="name" value="{{$d->name}}" class="form-control" required>
                      </div>
                      <div class="col-lg-12 mb-3">
                        <label for="">Description *</label>
                        <textarea name="description" class="form-control" required>{{$d->description}}</textarea>
                      </div>

                      <div class="row mb-3">
                        <div class="col-lg-4">
                          <label for="">Program Type</label>
                          <select name="program_type" class="form-control">
                            <option value="" selected>--Select--</option>
                            @foreach ($program_types as $type)
                            <option value="{{$type->id}}" {{$d->program_type == $type->id ? 'selected' : ''}}>{{$type->name}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-lg-4">
                          <label for="">Combo Map 1</label>
                          <select name="combo_id_1" class="dselect-example">
                            <option value="" selected>--Select--</option>
                            @foreach ($subjects as $subject)
                            <option value="{{$subject->id}}" {{$d->combomap != null ? $d->combomap->combo_id_1 == $subject->id ? 'selected' : '' : ''}}>{{$subject->title}} - {{$subject->campus_id == 1 ?'Sonada': 'Siliguri'}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="col-lg-4">
                          <label for="">Combo Map 2</label>
                          <select name="combo_id_2" class=" dselect-example">
                            <option value="" selected>--Select--</option>
                            @foreach ($subjects as $subject)
                            <option value="{{$subject->id}}" {{$d->combomap != null ? $d->combomap->combo_id_2 == $subject->id ? 'selected' : '' : ''}}>{{$subject->title}} -{{$subject->campus_id == 1 ?'Sonada': 'Siliguri'}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary js-save-btn">Save changes</button>
                    </div>
                </form>
              </div>
            </div>
          </div>
        </td>
      </tr>
      @endforeach
      @else
      <tr>
        <td colspan="10" class="text-center">No data found</td>
      </tr>
      @endif
    </tbody>
  </table>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('programLiveSearch');
    const rows = Array.from(document.querySelectorAll('tr.program-row'));

    if (!searchInput || rows.length === 0) {
      return;
    }

    searchInput.addEventListener('input', function() {
      const keyword = searchInput.value.trim().toLowerCase();

      rows.forEach(function(row) {
        const rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(keyword) ? '' : 'none';
      });
    });

    const editForms = Array.from(document.querySelectorAll('.js-program-edit-form'));

    function flattenErrors(errorPayload) {
      if (!errorPayload || !errorPayload.errors) {
        return ['Unable to update program. Please try again.'];
      }
      return Object.values(errorPayload.errors).flat();
    }

    function updateProgramRow(programId, payload) {
      const row = document.querySelector(`tr.program-row[data-program-id="${programId}"]`);
      if (!row || !payload) {
        return;
      }

      const campusNode = row.querySelector('.js-campus');
      const codeNode = row.querySelector('.js-code');
      const nameNode = row.querySelector('.js-name');
      const shiftNode = row.querySelector('.js-shift');
      const descriptionNode = row.querySelector('.js-description');
      const semesterNode = row.querySelector('.js-semester-count');
      const programTypeNode = row.querySelector('.js-program-type');
      const comboNode = row.querySelector('.js-combo-map');

      if (campusNode) campusNode.textContent = payload.campus_label || 'N/A';
      if (codeNode) codeNode.textContent = payload.code || '';
      if (nameNode) nameNode.textContent = payload.name || '';
      if (shiftNode) {
        shiftNode.innerHTML = `<span class="badge badge-info">${payload.shift || 'Common'}</span>`;
      }
      if (descriptionNode) descriptionNode.textContent = payload.description || '';
      if (semesterNode) semesterNode.textContent = payload.semester_count ?? '';
      if (programTypeNode) programTypeNode.textContent = payload.program_type || 'Unknown';
      if (comboNode) {
        if ((payload.program_type || '').toUpperCase() === 'UGC') {
          comboNode.innerHTML = `<span class="badge badge-success">${payload.combo_label || 'Unknown - Unknown'}</span>`;
        } else {
          comboNode.innerHTML = `<span class="badge badge-info">${payload.combo_label || 'N/A for AICTE'}</span>`;
        }
      }
    }

    editForms.forEach(function(form) {
      form.addEventListener('submit', async function(event) {
        event.preventDefault();

        const errorBox = form.querySelector('.js-form-error');
        const saveBtn = form.querySelector('.js-save-btn');
        const programId = form.dataset.programId;
        const modalId = form.dataset.modalId;

        if (errorBox) {
          errorBox.classList.add('d-none');
          errorBox.innerHTML = '';
        }

        if (saveBtn) {
          saveBtn.disabled = true;
          saveBtn.textContent = 'Saving...';
        }

        try {
          const formData = new FormData(form);
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
            body: formData,
          });

          const data = await response.json();

          if (!response.ok || !data.success) {
            const errors = flattenErrors(data);
            if (errorBox) {
              errorBox.innerHTML = errors.join('<br>');
              errorBox.classList.remove('d-none');
            }
            return;
          }

          updateProgramRow(programId, data.data || {});

          const modalEl = document.getElementById(modalId);
          if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            const modalInstance = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
            modalInstance.hide();
          }
        } catch (error) {
          if (errorBox) {
            errorBox.textContent = 'Something went wrong while saving. Please retry.';
            errorBox.classList.remove('d-none');
          }
        } finally {
          if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save changes';
          }
        }
      });
    });
  });
</script>
@include('includes.footer')