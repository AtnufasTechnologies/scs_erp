<?php

use App\Http\Controllers\StaticController;
use App\Models\UserHasRole;
use Illuminate\Support\Facades\Auth;

$userId = Auth::user()->id;
$roleType = UserHasRole::where('user_id', $userId)->value('role_name');
?>
<!--start sidebar -->
<aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">

    <div class="logo-text">
      SCMS
    </div>
    <div class=" toggle-icon ms-auto">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>
  </div>
  <!--navigation ADMIN -->
  <ul class="metismenu" id="menu">
    @if($roleType == 'dept-admin-erp')
    <li>
      <a href="{{route('department.dashboard')}}">
        <div class="parent-icon">
          <i class="fal fa-chart-pie"></i>
        </div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>
    @elseif($roleType == 'faculty')

    @elseif($roleType == 'student')
    @else
    <li>
      <a href="{{url('erp/admin/dashboard')}}">
        <div class="parent-icon">
          <i class="fal fa-chart-pie"></i>
        </div>
        <div class="menu-title">Dashboard</div>
      </a>
    </li>
    @endif
    <!-- Master -->
    @if (StaticController::mainMenuRights('master') )
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <ion-icon name="grid-outline"></ion-icon>
        </div>
        <div class="menu-title">Master</div>
      </a>
      <ul>
        @if(StaticController::subMenuRights('batch-master'))
        <li>
          <a href="{{url('erp/admin/master/batch')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Batches</div>
          </a>
        </li>
        @endif

        @if(StaticController::subMenuRights('blood-group-master'))
        <li>
          <a href="{{url('erp/admin/master/blood-group')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Blood Group</div>
          </a>
        </li>
        @endif
        @if(StaticController::subMenuRights('campus-master'))
        <li>
          <a href="{{url('erp/admin/master/campus')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Campuses </div>
          </a>
        </li>
        @endif
        @if(StaticController::subMenuRights('cognitive-level-master'))
        <li>
          <a href="{{url('erp/admin/master/cognitive-lvl')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Cognitive Level</div>
          </a>
        </li>
        @endif
        @if(StaticController::subMenuRights('deanery-master'))
        <li>
          <a href="{{url('erp/admin/master/deanery')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Deneary</div>
          </a>
        </li>
        @endif
        @if(StaticController::subMenuRights('department-master'))
        <li>
          <a href="{{url('erp/admin/master/departments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Old Departments</div>
          </a>
        </li>
        @endif

        @if(StaticController::subMenuRights('main-program-master'))
        <li>
          <a href="{{url('erp/admin/master/programs')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Programs </div>
          </a>
        </li>
        @endif
        @if(StaticController::subMenuRights('program-group-master'))
        <li>
          <a href="{{url('erp/admin/master/program-group')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Program Group </div>
          </a>
        </li>
        @endif
        @if(StaticController::subMenuRights('hours-master'))
        <li>
          <a href="{{url('erp/admin/master/hour')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Hours</div>
          </a>
        </li>
        @endif


        @if(StaticController::subMenuRights('lecturehall-master'))
        <li>
          <a href="{{url('erp/admin/master/lecturehalls')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Lecture Halls</div>
          </a>
        </li>
        @endif

        @if(StaticController::subMenuRights('rooms-master'))
        <li>
          <a href="{{url('erp/admin/master/rooms')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Rooms</div>
          </a>
        </li>
        @endif

        @if(StaticController::subMenuRights('religion-master'))
        <li>
          <a href="{{url('erp/admin/master/religion')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Religion</div>
          </a>
        </li>
        @endif

        @if(StaticController::subMenuRights('semester-master'))
        <li>
          <a href="{{url('erp/admin/master/semester')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Semester</div>
          </a>
        </li>
        @endif
      </ul>
    </li>
    @endif


    <!-- Admission Portal -->
    @if (StaticController::mainMenuRights('admission-pg') || StaticController::mainMenuRights('admission-ug'))
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-sparkles"></i>
        </div>
        <div class="menu-title"> Admissions</div>
      </a>
      <ul>
        <!--Admission PG Master -->
        @if (StaticController::mainMenuRights('admission-pg') )
        <!-- <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon">
              <i class="fas fa-badge"></i>
            </div>
            <div class="menu-title">PG Admissions </div>
          </a>
          <ul>
            @if (StaticController::subMenuRights('admission-registration-pg') )
            <li>
              <a href="{{url('erp/admin/master/subjects')}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">New Registrations </div>
              </a>
            </li>
            @endif
            @if (StaticController::subMenuRights('admission-application-pg') )
            <li>
              <a href="{{url('erp/admin/master/subject-type')}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Applications </div>
              </a>
            </li>
            @endif
            @if (StaticController::subMenuRights('admission-selection1-pg') )
            <li>
              <a href="{{url('erp/admin/academics/program-objectives')}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Selection Phase 1</div>
              </a>
            </li>
            @endif
            @if (StaticController::subMenuRights('admission-selection2-pg') )
            <li>
              <a href="{{url('erp/admin/academics/program-objectives')}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Selection Phase 2</div>
              </a>
            </li>
            @endif

          </ul>
        </li> -->
        @endif
        <!--Admission UG Master -->
        @if (StaticController::mainMenuRights('admission-ug') )
        <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon">
              <i class="fas fa-certificate"></i>
            </div>
            <div class="menu-title">UG Admissions</div>
          </a>
          <ul>
            @if (StaticController::subMenuRights('admission-registration-ug') )
            <li>
              <a href="{{route('admission.registration', ['type' => 'UG'])}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">New Registrations </div>
              </a>
            </li>
            @endif
            @if (StaticController::subMenuRights('admission-application-ug') )
            <li>
              <a href="{{route('admission.ug.applications')}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Applications </div>
              </a>
            </li>
            @endif
            @if (StaticController::subMenuRights('admission-selection1-ug') )
            <li>
              <a href="{{route('admission.ug.phase1')}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Selection Phase 1</div>
              </a>
            </li>
            @endif
            @if (StaticController::subMenuRights('admission-selection2-ug') )
            <li>
              <a href="{{route('admission.ug.phase2')}}">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Selection Phase 2</div>
              </a>
            </li>
            @endif

          </ul>
        </li>
        @endif


        <li>
          <a href="https://erpsalesiancollege.sdbinc.org/erp/new-admission/registration" target="_blank">
            <div class=" parent-icon">
              <i class="fas fa-pencil-alt"></i>
            </div>
            <div class="menu-title "> Admission Portal</div>
          </a>
        </li>

        <li>
          <a href="{{route('admission.settings')}}">
            <div class=" parent-icon">
              <i class="fas fa-cog"></i>
            </div>
            <div class="menu-title "> Admission Settings</div>
          </a>
        </li>

      </ul>
    </li>
    @endif




    <!--Faculty Master -->
    @if (StaticController::subMenuRights('student-master-sonada') || StaticController::subMenuRights('student-master-siliguri') )
    <li>
      <a href="{{url('erp/admin/faculty-master')}}">
        <div class="parent-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="menu-title">Faculty Master</div>
      </a>
    </li>
    @endif


    @if (StaticController::subMenuRights('student-master-sonada') || StaticController::subMenuRights('student-master-siliguri') )
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-user-tie"></i>
        </div>
        <div class="menu-title"> Student Master</div>
      </a>
      <ul>
        <!--Student Master -->
        @if (StaticController::subMenuRights('student-master-sonada') )
        <li>
          <a href="{{url('erp/admin/std-master-sonada')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title"> Sonada</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('student-master-siliguri') )
        <li>
          <a href="{{url('erp/admin/std-master-siliguri')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Siliguri</div>
          </a>
        </li>
        @endif
      </ul>
    </li>
    @endif



    <!--Academic Master -->
    @if (StaticController::mainMenuRights('academics') )
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-books"></i>
        </div>
        <div class="menu-title">Academics </div>
      </a>
      <ul>
        @if (StaticController::subMenuRights('subject-master') )
        <li>
          <a href="{{url('erp/admin/master/subjects')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Academic Departments </div>
          </a>
        </li>
        @endif
        @if (StaticController::subMenuRights('subject-type-master') )
        <li>
          <a href="{{url('erp/admin/master/subject-type')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Course Type </div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('course-combination-master') )
        <li>
          <a href="{{route('course-combination.master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Course Combination Master </div>
          </a>
        </li>
        @endif
        @if (StaticController::subMenuRights('program-course-master') )
        <li>
          <a href="{{route('program-course.master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Program Course Master </div>
          </a>
        </li>
        @endif
        @if (StaticController::subMenuRights('program-objective-master') )
        <!-- <li>
          <a href="">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Program Objectives</div>
          </a>
        </li> -->
        @endif
        @if (StaticController::subMenuRights('course-specific-objective') )
        <!-- <li>
          <a href="">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Course Objectives</div>
          </a>
        </li> -->
        @endif

        @if (StaticController::subMenuRights('course-specific-objective') )
        <!-- <li>
          <a href="{{url('erp/admin/academics/course-specific-objective')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Course Specific Objectives</div>
          </a>
        </li> -->
        @endif
        @if (StaticController::subMenuRights('question-bank-master') )
        <!-- <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Question Bank</div>
          </a>
        </li> -->
        @endif
        @if (StaticController::subMenuRights('attendance-master') )
        <!-- <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Record Attendance</div>
          </a>
        </li>
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Faculty Attendance</div>
          </a>
        </li>
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Student Attendance</div>
          </a>
        </li> -->
        @endif

      </ul>
    </li>
    @endif


    <!--Accounts Master -->
    @if (StaticController::mainMenuRights('accounts') )
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fa fa-calculator"></i>
        </div>
        <div class="menu-title">Accounts Office</div>
      </a>
      <ul>
        <li>
          <a href="{{url('erp/admin/accounts/late-fee-exemptions')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Late Fee Exemptions</div>
          </a>
        </li>
        @if (StaticController::subMenuRights('bank-master') )



        <li>
          <a href="{{url('erp/admin/accounts/bankinfo')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Bank Accounts</div>
          </a>
        </li>
        @endif
        @if (StaticController::subMenuRights('fee-head-master') )
        <li>
          <a href="{{url('erp/admin/accounts/fee-heads')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Heads </div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('fee-course-master') )
        <li>
          <a href="{{url('erp/admin/accounts/fee-course-master')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Course Master </div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('fee-structure-master') )
        <li>
          <a href="{{url('erp/admin/accounts/fee-structure')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Structure</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('fee-collection-master') )
        <li>
          <a href="{{url('erp/admin/accounts/std-fee-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Collection</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('fee-allpayments') )
        <li>
          <a href="{{url('erp/admin/accounts/all-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">All Payments</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('admission-application-fee') )
        <!-- <li>
          <a href="{{url('erp/admin/accounts/all-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Admission Application Fee</div>
          </a>
    </li> -->
        @endif


        @if (StaticController::subMenuRights('faculty-pay-roll') )
        <!-- <li>
          <a href="{{url('erp/admin/accounts/all-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Faculty Pay Roll</div>
          </a>
        </li> -->
        @endif
        @if(StaticController::subMenuRights('fee-defaulter-list'))
        <li>
          <a href="{{route('defaulters-list')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Defaulters List</div>
          </a>
        </li>
        @endif

      </ul>
    </li>
    @endif


    <!--HR Master -->
    <!-- @if (StaticController::mainMenuRights('hr') )
    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="far fa-users-class"></i>
        </div>
        <div class="menu-title">Human Resource</div>
      </a>
      <ul>
        @if (StaticController::subMenuRights('apr-report') )
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">APR Report</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('faculty-master') )
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Faculty Master</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('faculty-applications') )
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">New Applications</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('grievances') )
        <li>
          <a href="#">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Grievances</div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('roles-and-departments') )
        <li>
          <a href="{{url('erp/admin/accounts/all-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Roles and Departments</div>
          </a>
        </li>
        @endif

      </ul>
    </li>
    @endif -->

    @if (StaticController::mainMenuRights('pre-exam') || StaticController::mainMenuRights('during-exam') || StaticController::mainMenuRights('post-exam') )
    <!--Examination Master -->

    <!-- <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="far fa-analytics"></i>
        </div>
        <div class="menu-title">Examination </div>
      </a>
      <ul> -->
    <!-- pre examination -->
    @if (StaticController::mainMenuRights('pre-exam') )
    <!-- <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon">
              <i class="fas fa-cog"></i>
            </div>
            <div class="menu-title">Pre Examination </div>
          </a>
          <ul>

            @if (StaticController::subMenuRights('paper-setup') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Paper Setup </div>
              </a>
            </li>
            @endif


            @if (StaticController::subMenuRights('exam-creation') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">New Exam </div>
              </a>
            </li>
            @endif


            @if (StaticController::subMenuRights('student-exam-registration') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Exam Registrations </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('hall-ticket-generation') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Hall Ticket Generation </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('exam-enrollment-manager') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Enrollment Manager </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('exam-timetable-manager') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Timetable Manager </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('exam-seating-arrangement') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Seating Arrangement </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('exam-invigilators-assign') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Invigilators Assign </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('exam-contournement-request') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Contournement Request </div>
              </a>
            </li>
            @endif

          </ul>
        </li> -->
    @endif


    <!-- during examination -->
    @if (StaticController::mainMenuRights('during-exam') )
    <!-- <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon">
              <i class="fal fa-chart-line"></i>
            </div>
            <div class="menu-title">During Examination </div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/accounts/std-fee-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Fee Collection</div>
          </a>
        </li>
        <li>
          <a href="{{url('erp/admin/accounts/all-payments')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">All Payments</div>
          </a>
        </li>

        <li>
          <a href="{{url('erp/admin/accounts/late-fee-exemptions')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Late Fee Exemptions</div>
          </a>
        </li>



      </ul>
    </li>
    @endif

    <!-- post examination -->
    @if (StaticController::mainMenuRights('post-exam') )
    <!-- <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon">
              <i class="far fa-person-carry"></i>
            </div>
            <div class="menu-title">Post Examination </div>
          </a>
          <ul>
            @if (StaticController::subMenuRights('moderation-and-evaluation-management') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Moderation and Evaluation Management </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('marks-entry-management') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title">Marks Entry Management </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('re-evaluation-management') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Re-Evaluation Management </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('result-publication-management') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Result Publication </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('backlog-management') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Backlog Management </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('promotion-management') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Promotion Management </div>
              </a>
            </li>
            @endif

            @if (StaticController::subMenuRights('student-academic-history-access') )
            <li>
              <a href="#">
                <div class="parent-icon">
                  <i class="fas fa-arrow-alt-circle-right"></i>
                </div>
                <div class="menu-title"> Student Academic History Access</div>
              </a>
            </li>
            @endif
          </ul>
        </li> -->
    @endif



    <!-- </ul>
  </li> -->
    @endif


    @if (StaticController::mainMenuRights('access-control') )

    <li>
      <a class="has-arrow" href="javascript:;">
        <div class="parent-icon">
          <i class="fas fa-shield"></i>
        </div>
        <div class="menu-title">Security and Authorization </div>
      </a>
      <ul>
        <!--     User Access Management -->
        @if (StaticController::subMenuRights('user-list') )
        <li>
          <a href="{{route('admin.user.management')}}">
            <div class="parent-icon">
              <i class="fas fa-user"></i>
            </div>
            <div class="menu-title">User Control </div>
          </a>
        </li>
        @endif
        <!--Departmental Admission Access Control -->
        @if (StaticController::subMenuRights('admission-dept-access') )
        <li>
          <a href="{{route('dept.erp.access-list')}}">
            <div class="parent-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="menu-title">Dept Access </div>
          </a>
        </li>
        @endif
        <!--User Type Access Control -->
        @if (StaticController::subMenuRights('user-type-auth') )
        <li>
          <a href="{{route('admin.user-types')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">User Types </div>
          </a>
        </li>
        @endif

        @if (StaticController::subMenuRights('dev-auth') )
        <li>
          <a href="{{route('admin.menu-access-types')}}">
            <div class="parent-icon">
              <i class="fas fa-arrow-alt-circle-right"></i>
            </div>
            <div class="menu-title">Menu Access Types </div>
          </a>
        </li>
        @endif

      </ul>


      @endif

      <!-- logout -->
    <li>
      <a href="{{url('logout')}}">
        <div class="parent-icon">
          <i class="fas fa-sign-out-alt"></i>
        </div>
        <div class="menu-title">Logout </div>
      </a>
    </li>
    <!--end navigation-->
</aside>
<!--end sidebar -->

<!--start top header-->
<header class="top-header">
  <nav class="navbar navbar-expand gap-3">
    <div class="mobile-menu-button">
      <ion-icon name="menu-sharp"></ion-icon>
    </div>

    <div class="top-navbar-right ms-auto">

      <ul class="navbar-nav align-items-center">
        <!-- <li class="nav-item mobile-search-button">
              <a class="nav-link" href="javascript:;">
                <div class="">
                  <ion-icon name="search-sharp"></ion-icon>
                </div>
              </a>
            </li> -->
        <li class="nav-item">
          <a class="nav-link dark-mode-icon" href="javascript:;">
            <div class="mode-icon">
              <ion-icon name="moon-sharp"></ion-icon>
            </div>
          </a>
        </li>



      </ul>

    </div>
  </nav>
</header>
<!--end top header-->

<!-- start page content wrapper-->
<div class="page-content-wrapper">
  <!-- start page content-->
  <div class="page-content">

    @if ($errors->any())

    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    @endif