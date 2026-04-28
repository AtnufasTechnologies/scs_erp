<?php

use App\Models\AdmissionSetting;
use App\Models\StudentMaster;

$admissioninfo = AdmissionSetting::find(1);
?>
@include('includes.header')

<header class="profile-header">
  <div class="header-content">
    <div class="profile-img-container">
      <img src="{{asset('admin/images/logo.png')}}" alt="logo" class="profile-img" style="background-color: white;">
    </div>
    <div class="profile-info">
      <h6><span class="text-uppercase">Application Submitted Successfully</span></h6>
      <h1 class="text-capitalize">Salesian College Autonomous</h1>
      <h2 class="text-capitalize">Sonada & Siliguri Campus</h2>
      <div class="contact-links">
        <a href="mailto:admissions@salesiancollege.net" aria-label="Email">
          <i class="fas fa-envelope"></i> admissionenquiry@salesiancollege.net
        </a>
        <a href="tel:+919933402478" target="_blank">
          <i class="fas fa-phone"></i> +91 99334 02478 / 0353 254 5622 (Siliguri Campus)
        </a>
      </div>
      <div class="contact-links">
        <a href="mailto:salesiancollegesonada@gmail.com" aria-label="">
          <i class="fas fa-envelope"></i> salesiancollegesonada@gmail.com (Sonada)
        </a>
        <a href="tel:+917602032968" target="_blank">
          <i class="fas fa-phone"></i> 76020 32968 / 99336 40168 (Sonada)
        </a>
      </div>
    </div>
  </div>
</header>

<div class="container mt-5 mb-5">
  <div class="row justify-content-center">
    <div class="col-md-12">
      <div class="card shadow p-4">
        <div class="card-body text-center">
          <p class="display-4">Hi, <span class="text-capitalize">{{ $data->registrationmaster->first_name }} {{ $data->registrationmaster->last_name }}</span></p>
          <p><i class="fa fa-envelope"></i> {{ $data->registrationmaster->mail_id }} | <i class="fa fa-phone"></i> {{ $data->registrationmaster->mobile_no }}</p>

          <p class="mb-4">Welcome! Your application has been received successfully. We will contact you shortly with details about your interview.</p>
          <p class="text-capitalize"> <b>{{$data->academicDeptMaster->title}}</b> <br>{{$data->stdCourseMaster->name}}</p>
          <h4>Your Application Number is</h4>
          <div class="d-inline-block mt-3 mb-4 p-3 bg-success text-white rounded" style="font-size: 24px; font-weight: 700; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            # {{ $data->application_code }}
          </div>
          <p>You Download the application form and invoice.</p>

          <a href="{{route('download.admission.application-form',$data->application_code)}}" class="btn btn-primary">View Application Form</a>
          <a href="{{route('download.admission.payment-invoice',$data->application_code)}}" class="btn btn-primary">View Invoice</a>
          <a href="{{route('admission.apply.logout')}}" class="btn btn-secondary">Logout</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mb-5">
  <div class="row ">
    @if ($data->phaseoneinfo != null)
    <div class="col-md-6">
      <div class="card shadow p-4">
        <div class="card-body text-center">
          <h3 class="mb-4">Interview Details</h3>
          <p>Your interview is scheduled on <strong>{{$data->phaseoneinfo->interview_datetime}}</strong> at
            <strong>Salesian College {{ $data->registrationmaster->campusmaster->name }}</strong>.
          </p>
          <p>Please be prepared and bring all necessary documents.</p>

          @php
          $needsUpdate = false;
          $updateMessages = [];

          // Check document verification status
          if($data->phaseoneinfo->document_verified == 0) {
          $needsUpdate = true;
          $updateMessages[] = 'Document verification is pending. Please ensure all documents are accurate and complete.';
          }

          // Check if there are remarks from department
          if(!empty($data->phaseoneinfo->dept_interview_remark)) {
          $needsUpdate = true;
          $updateMessages[] = 'Department Remark: ' . $data->phaseoneinfo->dept_interview_remark;
          }

          // Check if there are remarks from management
          if(!empty($data->phaseoneinfo->mgt_interview_remark)) {
          $needsUpdate = true;
          $updateMessages[] = 'Management Remark: ' . $data->phaseoneinfo->mgt_interview_remark;
          }

          // Check proficiency test remarks
          if(!empty($data->phaseoneinfo->proficiency_test_remarks)) {
          $needsUpdate = true;
          $updateMessages[] = 'Proficiency Test Remark: ' . $data->phaseoneinfo->proficiency_test_remarks;
          }
          @endphp

          @if($needsUpdate)
          <div class="alert alert-warning mt-3" role="alert">
            <h5 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Updates</h5>
            <hr>
            <ul class="text-start mb-0">

              <li>Document Verified
                @if($data->phaseoneinfo->document_verified == 1)
                <i class="fa fa-check-circle text-success fa-lg"></i>
                @else
                <i class="fa fa-times-circle text-danger fa-lg"></i>
                @endif
              </li>
              <li>Proficiency Test
                @if($data->phaseoneinfo->proficiency_test_status == 1)
                <i class="fa fa-check-circle text-success fa-lg"></i>
                @else
                <i class="fa fa-times-circle text-danger fa-lg"></i>
                @endif
              </li>
              <li>Proficiency Test Marks - {{ $data->phaseoneinfo->proficiency_test_remarks ?? 'Pending...' }}</li>

              </li>
              <li>
                Departmental Interview
                @if($data->phaseoneinfo->dept_interview == 1)
                <i class="fa fa-check-circle text-success fa-lg"></i>
                @else
                <i class="fa fa-times-circle text-danger fa-lg"></i>
                @endif
              </li>
              <li>
                Management Interview
                @if($data->phaseoneinfo->mgt_interview_status == 1)
                <i class="fa fa-check-circle text-success fa-lg"></i>
                @else
                <i class="fa fa-times-circle text-danger fa-lg"></i>
                @endif
              </li>
            </ul>
            <hr>
            <button class="btn {{ $data->phaseoneinfo->final_status  == 1 ? 'btn-success': 'btn-dark' }}"> Final Status: {{ $data->phaseoneinfo->final_status  == 1 ? 'Selected ': 'Pending...' }}</span>
          </div>
          @endif


          <button class="btn btn-warning mt-3" data-bs-toggle="modal" data-bs-target="#phase1Instruction">Instructions</button><br>
          <div class="modal fade" id="phase1Instruction" tabindex="-1" role="dialog" aria-labelledby="phase1InstructionLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="phase1InstructionLabel">Instructions for Interview</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php echo $admissioninfo->phase1_inst_ug; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    @if($data->phasetwoinfo != null)
    <div class="col-md-6">
      <div class="card shadow p-4">
        <div class="card-body text-center">
          <h3 class="mb-4">Admission Details</h3>
          <p><strong class="text-success">Congratulations! </strong> you have been <strong class="text-success">selected</strong> for admission.</p>
          <p>Visit College Office within 5 Days to Make Payment and Reserve Your Slot.
            <strong>Salesian College {{ $data->registrationmaster->campusmaster->name }}</strong>.
          </p>
          @if ($data->registrationmaster->is_enrolled == 1)
          <p class="alert alert-success"><i class="fa fa-check-circle"></i> You have completed the enrollment process.
            Welcome to the Salesian College family!</p>
          <?php $rollNo = StudentMaster::where('user_code', $data->application_code)->value('roll_no'); ?>
          Roll Number Generated
          <h1 class="alert alert-primary">{{$rollNo}}</h1>
          @endif
          <p>Login to the Student Portal with your roll number to <b>complete admission payment</b></p>
          <a href="{{url('erp/student/fee-payment')}}" class="btn btn-success">Go to Student Portal</a>


          @if ($data->registrationmaster->is_enrolled == 0)
          <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#phase2Instruction">Instructions</button><br>
          <div class="modal fade" id="phase2Instruction" tabindex="-1" role="dialog" aria-labelledby="phase2InstructionLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="phase2InstructionLabel">Instructions for Admission</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <?php echo $admissioninfo->phase2_inst_ug; ?>
                </div>
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif
  </div>
</div>





@include('includes.footer')