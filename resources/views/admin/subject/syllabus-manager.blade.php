@include('includes.header')
@include('includes.dept-sidebar')
<!-- Main Content -->
<div class="main-content">
  <h3>Syllabus Manager - {{$data['slug']}}</h3>
  <!-- Button trigger modal -->
  <button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addSyllabus">
    <span class="button-decor"></span>
    <div class="button-content">
      <div class="button__icon">
        <i class="fa fa-plus-circle"></i>
      </div>
      <span class="button__text">Add New</span>
    </div>
  </button>

  <!-- Modal -->
  <div class="modal fade" id="addSyllabus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Design New Syllabus - {{$data['slug']}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('department.create.syllabus')}}" method="post">
          @csrf
          <div class="modal-body">
            <div class="row">
              <div class="row">
                <div class="col-lg-6">
                  <label for="">Select Batch *</label>
                  <select name="batch" class="form-select">
                    <option value="" selected>--Select--</option>
                    @foreach ($batches as $batch)
                    <option value="{{$batch->id}}">{{$batch->batch_name}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-lg-6">
                  <label for="">Select Semester *</label>
                  <select name="semester" class="form-select mb-3">
                    <option value="" selected>--Select--</option>
                    @foreach ($semesters as $sem)
                    <option value="{{$sem->id}}">{{$sem->title}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-lg-12">
                  <label for="">Select Course *</label>
                  <select name="co_id" id="course_objective" class="form-select mb-3">
                    <option value="" selected>--Select--</option>
                    @foreach ($cos as $item)
                    <option value="{{$item->course_master_id}}">
                      ({{$item->courseMaster->course_code ?? '-'}})
                      {{$item->courseMaster->course_title ?? '-'}}
                      - ({{$item->courseMaster->coursetypemaster->title ?? '-'}})
                    </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-lg-12">
                  <label for="">Select CSO *</label>
                  <select name="cso" id="cso_select" class="form-select mb-3">
                    <option value="" selected>--Select Course First--</option>
                  </select>
                </div>

                <div class="col-lg-12">
                  <label for="">Select CSO Sub Unit(s)</label>
                  <div id="cso_subunit_checkboxes" class="border p-3 rounded mb-3" style="max-height: 300px; overflow-y: auto;">
                    <p class="text-muted">--Select CSO First--</p>
                  </div>
                </div>
              </div>

              <input type="hidden" name="subject_id" value="{{$data['id']}}">

            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@include('includes.footer')

<!-- CSO AJAX Script -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('course_objective');
    const csoSelect = document.getElementById('cso_select');
    const csoSubunitCheckboxes = document.getElementById('cso_subunit_checkboxes');

    if (courseSelect) {
      courseSelect.addEventListener('change', function() {
        const courseId = this.value;

        // Reset CSO and sub unit checkboxes
        csoSelect.innerHTML = '<option value="" selected>--Select--</option>';
        csoSubunitCheckboxes.innerHTML = '<p class="text-muted">--Select CSO First--</p>';

        if (!courseId) {
          csoSelect.innerHTML = '<option value="" selected>--Select Course First--</option>';
          return;
        }

        // Show loading state
        csoSelect.innerHTML = '<option value="" selected>Loading CSOs...</option>';

        // Fetch CSOs for the selected course
        fetch(`/erp/deptartment/course/${courseId}/cso-list`)
          .then(response => response.json())
          .then(data => {

            if (data.length > 0) {
              csoSelect.innerHTML = '<option value="" selected>--Select CSO--</option>';
              data.forEach(function(cso) {
                const option = document.createElement('option');
                option.value = cso.id;
                option.textContent = `${cso.title} (Lectures: ${cso.lectures_needed})`;
                option.dataset.cso = JSON.stringify(cso);
                csoSelect.appendChild(option);
              });
            } else {
              csoSelect.innerHTML = '<option value="" selected>No CSOs found for this course</option>';
            }
          })
          .catch(() => {
            csoSelect.innerHTML = '<option value="" selected>Failed to load CSOs</option>';
          });
      });
    }

    if (csoSelect) {
      csoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        // Reset sub unit checkboxes
        csoSubunitCheckboxes.innerHTML = '';

        if (!this.value || !selectedOption.dataset.cso) {
          csoSubunitCheckboxes.innerHTML = '<p class="text-muted">--Select CSO First--</p>';
          return;
        }

        try {
          const cso = JSON.parse(selectedOption.dataset.cso);

          if (cso.csosubunits && cso.csosubunits.length > 0) {
            // Add Select All checkbox
            const selectAllDiv = document.createElement('div');
            selectAllDiv.className = 'form-check mb-3 pb-2 border-bottom';

            const selectAllCheckbox = document.createElement('input');
            selectAllCheckbox.className = 'form-check-input';
            selectAllCheckbox.type = 'checkbox';
            selectAllCheckbox.id = 'select_all_subunits';

            const selectAllLabel = document.createElement('label');
            selectAllLabel.className = 'form-check-label fw-bold';
            selectAllLabel.htmlFor = 'select_all_subunits';
            selectAllLabel.textContent = 'Select All';

            selectAllDiv.appendChild(selectAllCheckbox);
            selectAllDiv.appendChild(selectAllLabel);
            csoSubunitCheckboxes.appendChild(selectAllDiv);

            // Add individual subunit checkboxes
            cso.csosubunits.forEach(function(subunit, index) {
              const checkboxDiv = document.createElement('div');
              checkboxDiv.className = 'form-check mb-2';

              const checkbox = document.createElement('input');
              checkbox.className = 'form-check-input cso-subunit-checkbox';
              checkbox.type = 'checkbox';
              checkbox.name = 'cso_subunit[]';
              checkbox.value = subunit.id;
              checkbox.id = `cso_subunit_${subunit.id}`;

              const label = document.createElement('label');
              label.className = 'form-check-label';
              label.htmlFor = `cso_subunit_${subunit.id}`;
              label.textContent = `${subunit.title} (${subunit.taxomonylevel ? subunit.taxomonylevel.fullname : 'N/A'})`;

              checkboxDiv.appendChild(checkbox);
              checkboxDiv.appendChild(label);
              csoSubunitCheckboxes.appendChild(checkboxDiv);
            });

            // Select All functionality
            selectAllCheckbox.addEventListener('change', function() {
              const subunitCheckboxes = document.querySelectorAll('.cso-subunit-checkbox');
              subunitCheckboxes.forEach(cb => cb.checked = this.checked);
            });

            // Update Select All state when individual checkboxes change
            const subunitCheckboxes = document.querySelectorAll('.cso-subunit-checkbox');
            subunitCheckboxes.forEach(function(checkbox) {
              checkbox.addEventListener('change', function() {
                const allChecked = Array.from(subunitCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
              });
            });
          } else {
            csoSubunitCheckboxes.innerHTML = '<p class="text-muted">No sub units found for this CSO</p>';
          }
        } catch (e) {
          csoSubunitCheckboxes.innerHTML = '<p class="text-muted text-danger">Error loading sub units</p>';
        }
      });
    }
  });
</script>