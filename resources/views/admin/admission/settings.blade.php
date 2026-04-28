<?php

use App\Models\BatchMaster;

$batch = BatchMaster::where('admission_active_batch', 1)->first();
?>
@include('includes.header')
@include('admin.admission.sidebar')
<h3>Admission Settings </h3>

<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow-sm border-0 mb-4">
        <h1>For {{ $batch->batch_name ?? 'None' }} - UG</h1>

        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Admission Settings</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('update.admission.settings.ug') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-lg-4 col-md-6 mb-3">
                <label for="admission_open" class="form-label">Admission Opens</label>
                <input type="date" name="open_date_ug" id="admission_open" class="form-control" value="{{$data->open_date_ug}}">
              </div>
              <div class=" col-lg-4 col-md-6 mb-3">
                <label for="admission_deadline" class="form-label">Admission Closes</label>
                <input type="date" name="close_date_ug" id="admission_deadline" class="form-control" value="{{$data->close_date_ug}}">
              </div>
              <div class="col-lg-4 col-md-6 mb-3">
                <label for="application_fee_ug" class="form-label">Application Fee Amount</label>
                <input type="number" name="application_fee_ug" id="application_fee_ug" class="form-control" value="{{$data->application_fee_ug}}">
              </div>
            </div>

            <div class="mb-3">
              <h6 class="mb-0">Instructions for Candidate</h6>
              <textarea name="instructions_ug" class="phase1-ug"><?php echo $data->instructions_ug; ?></textarea>
            </div>

            <div class="mb-3">
              <h6 class="mb-0">Phase 1 Selection - Instructions for Candidate</h6>
              <textarea name="phase1_inst_ug" class="editor2"><?php echo $data->phase1_inst_ug; ?></textarea>
            </div>


            <div class="mb-3">
              <h6 class="mb-0">Phase 2 Selection - Instructions for Candidate</h6>
              <textarea name="phase2_inst_ug" class="editor2"><?php echo $data->phase2_inst_ug; ?></textarea>
            </div>

            <button type="submit" class="btn btn-success w-100">Update Settings</button>
          </form>
        </div>
      </div>


    </div>

    <div class="col-md-12">
      <div class="card shadow-sm border-0 mb-4">
        <h1>For {{ $batch->batch_name ?? 'None' }} - PG</h1>

        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Admission Settings</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('update.admission.settings.pg') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-lg-4 col-md-6 mb-3">
                <label for="admission_open_pg" class="form-label">Admission Opens</label>
                <input type="date" name="open_date_pg" id="admission_open_pg" class="form-control" value="{{$data->open_date_pg}}">
              </div>
              <div class=" col-lg-4 col-md-6 mb-3">
                <label for="admission_deadline_pg" class="form-label">Admission Closes</label>
                <input type="date" name="close_date_pg" id="admission_deadline_pg" class="form-control" value="{{$data->close_date_pg}}">
              </div>
              <div class="col-lg-4 col-md-6 mb-3">
                <label for="application_fee_pg" class="form-label">Application Fee Amount</label>
                <input type="number" name="application_fee_pg" id="application_fee_pg" class="form-control" value="{{$data->application_fee_pg}}">
              </div>
            </div>

            <div class="mb-3">
              <h6 class="mb-0">Instructions for Candidate</h6>
              <textarea name="instructions_pg" class="editor2"><?php echo $data->instructions_pg; ?></textarea>
            </div>

            <div class="mb-3">
              <h6 class="mb-0">Phase 1 Selection - Instructions for Candidate</h6>
              <textarea name="phase1_inst_pg" class="editor2"><?php echo $data->phase1_inst_pg; ?></textarea>
            </div>

            <div class="mb-3">
              <h6 class="mb-0">Phase 2 Selection - Instructions for Candidate</h6>
              <textarea name="phase2_inst_pg" class="editor2"><?php echo $data->phase2_inst_pg; ?></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100">Update Settings</button>
          </form>
        </div>
      </div>


    </div>
  </div>

</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Check if ClassicEditor is available
    if (typeof ClassicEditor === 'undefined') {
      console.error('CKEditor ClassicEditor is not loaded!');
      return;
    }

    // Configuration for CKEditor
    const editorConfig = {
      removePlugins: [
        "CKFinderUploadAdapter",
        "CKFinder",
        "EasyImage",
        "Image",
        "ImageCaption",
        "ImageStyle",
        "ImageToolbar",
        "ImageUpload",
        "MediaEmbed",
      ],
    };

    // Initialize CKEditor for phase1-ug
    const phase1UgElement = document.querySelector(".phase1-ug");
    if (phase1UgElement) {
      ClassicEditor.create(phase1UgElement, editorConfig)
        .then((editor) => {
          console.log('Phase 1 UG editor initialized');
        })
        .catch((error) => {
          console.error('Error initializing Phase 1 UG editor:', error);
        });
    }

    // Initialize CKEditor for all editor2 textareas
    const editor2Elements = document.querySelectorAll('.editor2');
    if (editor2Elements.length > 0) {
      editor2Elements.forEach((textarea, index) => {
        ClassicEditor.create(textarea, editorConfig)
          .then((editor) => {
            console.log('Editor ' + (index + 1) + ' initialized');
          })
          .catch((error) => {
            console.error('Error initializing editor ' + (index + 1) + ':', error);
          });
      });
    } else {
      console.warn('No .editor2 elements found');
    }
  });
</script>