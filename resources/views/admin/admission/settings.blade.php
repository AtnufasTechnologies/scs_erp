<?php

use App\Models\BatchMaster;
use Illuminate\Bus\Batch;

$batch = BatchMaster::where('admission_active_batch', 1)->first();
?>
@include('includes.header')
@include('admin.sidebar')
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
                <input type="date" name="admission_open" id="admission_open" class="form-control" value="{{$data->open_date_ug}}">
              </div>
              <div class=" col-lg-4 col-md-6 mb-3">
                <label for="admission_deadline" class="form-label">Admission Closes</label>
                <input type="date" name="admission_deadline" id="admission_deadline" class="form-control" value="{{$data->close_date_ug}}">
              </div>
              <div class="col-lg-4 col-md-6 mb-3">
                <label for="application_fee_ug" class="form-label">Application Fee Amount</label>
                <input type="number" name="application_fee_ug" id="application_fee_ug" class="form-control" value="{{$data->application_fee_ug}}">
              </div>
            </div>

            <div class="mb-3">
              <h6 class="mb-0">Instructions for Candidate</h6>
              <textarea name="instructions" class="editor">{{$data->instructions_ug}}</textarea>
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
                <input type="date" name="admission_open_pg" id="admission_open_pg" class="form-control" value="{{$data->open_date_pg}}">
              </div>
              <div class=" col-lg-4 col-md-6 mb-3">
                <label for="admission_deadline_pg" class="form-label">Admission Closes</label>
                <input type="date" name="admission_deadline_pg" id="admission_deadline_pg" class="form-control" value="{{$data->close_date_pg}}">
              </div>
              <div class="col-lg-4 col-md-6 mb-3">
                <label for="application_fee_pg" class="form-label">Application Fee Amount</label>
                <input type="number" name="application_fee_pg" id="application_fee_pg" class="form-control" value="{{$data->application_fee_pg}}">
              </div>
            </div>

            <div class="mb-3">
              <h6 class="mb-0">Instructions for Candidate</h6>
              <textarea name="instructions_pg" class="editor2">{{$data->instructions_pg}}</textarea>
            </div>
            <button type="submit" class="btn btn-success w-100">Update Settings</button>
          </form>
        </div>
      </div>


    </div>
  </div>

</div>
@include('includes.footer')