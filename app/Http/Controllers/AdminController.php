<?php

namespace App\Http\Controllers;

use App\Models\AcademicBlock;
use App\Models\AcademicDepartment;
use App\Models\BatchMaster;
use App\Models\BloodGroupMaster;
use App\Models\Campus;
use App\Models\CognitiveLevelMaster;
use App\Models\CollegeBankAccount;
use App\Models\Deanery;
use App\Models\Department;
use App\Models\DepartmentMaster;
use App\Models\Faculty;
use App\Models\FeeCourseMaster;
use App\Models\FeeHead;
use App\Models\FeeQuarterMaster;
use App\Models\FeesStructure;
use App\Models\FeeStructureGroup;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\HourMaster;
use App\Models\LateFee;
use App\Models\LectureHallMaster;
use App\Models\MainProgram;
use App\Models\MenuMaster;
use App\Models\ProgramGroup;
use App\Models\ProgramMaster;
use App\Models\ReligionMaster;
use App\Models\RoomMaster;
use App\Models\Semester;
use App\Models\StudentMaster;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasPermission;
use App\Models\UserHasRole;
use App\Models\UserMenuPermission;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;

class AdminController extends Controller
{
    function index()
    {
        return view('admin.dashboard');
    }

    function stdMasterSonada()
    {
        $data = StudentMaster::with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'programgroup'

        ])->where('campus_id', 1)->get();

        return view('admin.students.student-master', ['data' => $data]);
    }

    function stdMasterSiliguri()
    {
        $data = StudentMaster::with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'programgroup.programInfo'

        ])->where('campus_id', 2)->get();

        return view('admin.students.student-master', ['data' => $data]);
    }

    function stdprofile($id, $rollno)
    {
        $data = StudentMaster::where('id', $id)->with([
            'religionmaster:id,name',
            'deptmaster:id,department_code,name',
            'campusmaster:id,slug,name',
            'nationalitymaster:id,name',
            'usertype:id,name',
            'bloodgroup',
            'batchmaster:id,batch_name',
            'programgroup.programInfo',
            'feepayment.feepaymentinfo:id,quarter_title',
            'feepayment.gatewaytype'

        ])->firstOrFail();

        return view('admin.master.student-profile', ['data' => $data]);
    }

    function batchMaster()
    {
        $data = BatchMaster::get();
        return view('admin.master.batch', ['data' => $data]);
    }

    function updateAdmBatchStatus($id)
    {

        $data = BatchMaster::findOrFail($id);

        if ($data->admission_active_batch == 1) {
            BatchMaster::where('id', $id)->update([
                'admission_active_batch' => 0,
            ]);
        } else {
            BatchMaster::where('admission_active_batch', 1)->update([
                'admission_active_batch' => 0,
            ]);
            BatchMaster::where('id', $id)->update([
                'admission_active_batch' => 1,
            ]);
        }
        return redirect()->back()->with('success', 'Done');
    }

    function addBatch(Request $request)
    {
        $request->validate([
            'batch_name' => 'required|max_digits:4|min_digits:4',
            'fees' => 'required',

        ]);

        $check = BatchMaster::where('batch_name', $request->batch_name)->first();
        if ($check == null) {
            $rec = new BatchMaster();
            $rec->batch_name = $request->batch_name;
            $rec->admn_fee_amount = $request->fees;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Batch already in list');
        }
    }

    function hourMaster()
    {
        $data = HourMaster::get();
        return view('admin.master.hour', ['data' => $data]);
    }

    function addHour(Request $request)
    {
        $request->validate([
            'hour' => 'required',

        ]);

        $check = HourMaster::where('title', $request->hour)->first();
        if ($check == null) {
            $rec = new HourMaster();
            $rec->title = $request->hour;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Item already in list');
        }
    }

    function delHour($id)
    {
        HourMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Done');
    }

    function bloodGroupMaster()
    {
        $data = BloodGroupMaster::get();
        return view('admin.master.blood-group', ['data' => $data]);
    }

    function addBloodGroup(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $check = BloodGroupMaster::where('name', $request->name)->first();
        if ($check == null) {
            $rec = new BloodGroupMaster();
            $rec->name = $request->name;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Item already in list');
        }
    }

    function campusMaster()
    {
        $data = Campus::get();
        return view('admin.master.campus', ['data' => $data]);
    }

    function cognitiveLvl()
    {
        $data = CognitiveLevelMaster::get();
        return view('admin.master.cognitive-lvl', ['data' => $data]);
    }

    function addCognitiveLvl(Request $request)
    {
        $request->validate([
            'short_name' => 'required',
            'full_name' => 'required',
        ]);

        $check = CognitiveLevelMaster::where('fullname', $request->full_name)->first();
        if ($check == null) {
            $rec = new CognitiveLevelMaster();
            $rec->shortname = $request->short_name;
            $rec->fullname = $request->full_name;
            $rec->save();

            return redirect()->back()->with('success', 'Done');
        } else {
            return redirect()->back()->with('success', 'Item already in list');
        }
    }

    function delCogLvl($id)
    {
        CognitiveLevelMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Done');
    }


    function departmentMaster()
    {
        $data = DepartmentMaster::with('campusmaster')->latest()->get();
        return view('admin.master.department', ['data' => $data]);
    }


    function roomTypeMaster()
    {
        $data = RoomMaster::latest()->get();
        return view('admin.master.rooms', ['data' => $data]);
    }

    function addRoomTypeMaster(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $rec = new RoomMaster();
        $rec->title = ucfirst($request->title);
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function updateRoomTypeMaster(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        RoomMaster::where('id', $request->id)->update([
            'title' => ucfirst($request->title)
        ]);

        return redirect()->back()->with('success', 'Update Done');
    }

    function programMaster()
    {
        $data = MainProgram::with('campus')->get();
        return view('admin.master.programs', ['data' => $data]);
    }

    function programGroup()
    {
        $data = ProgramGroup::with([
            'campus',
            'programInfo',
        ])->get();
        return view('admin.master.program-group', ['data' => $data]);
    }

    function updateProgramGroup(Request $request)
    {

        return $record = ProgramGroup::findOrFail($request->id);
    }

    //lecture hall

    function lectureHalls()
    {
        $data = LectureHallMaster::with([
            'acblockmaster:id,title',
            'roomtypemaster:id,title'
        ])->get();

        return view('admin.master.lecture-halls', ['data' => $data]);
    }

    function addLectureHall(Request $request)
    {
        $request->validate([
            'acblock_id' => 'required',
            'title' => 'required|string|max:190',
            'roomtype_id' => 'required',
        ]);

        $rec = new LectureHallMaster();
        $rec->acblock_id = $request->acblock_id;
        $rec->roomtype_id = $request->roomtype_id;
        $rec->title = $request->title;
        $rec->save();

        return redirect()->back()->with('succes', 'Done');
    }


    function semesters()
    {
        $data = Semester::latest()->get();
        return view('admin.master.semesters', ['data' => $data]);
    }

    function religionMaster()
    {
        $data = ReligionMaster::latest()->get();
        return view('admin.master.religion', ['data' => $data]);
    }

    function addReligionMaster(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $rec = new ReligionMaster();
        $rec->name = ucfirst($request->name);
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function delReligion($id)
    {
        ReligionMaster::find($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function deanery(Request $request)
    {

        if (!empty($request->campus)) {
            $campus_id = $request->campus;
            $deanery = Deanery::with([
                'program.campus',
                'deanerydeptpivot.department:id,name'
            ])->whereHas('program.campus', function ($q) use ($campus_id) {
                $q->where('id', $campus_id);
            })->latest()->get();
        } else {
            $deanery = Deanery::with([
                'program.campus',
                'deanerydeptpivot.department:id,name'
            ])->latest()->get();
        }

        $programs = MainProgram::with('campus')->get();
        return view('admin.master.deanery', compact('deanery', 'programs'));
    }

    function addDeanery(Request $request)
    {
        $request->validate([
            'program_id' => 'required',
            'title' => 'required'
        ]);

        $rec = new Deanery();
        $rec->program_id = $request->program_id;
        $rec->slug = Str::slug($request->title);
        $rec->title = $request->title;
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function academicDept()
    {
        $data = AcademicDepartment::with([
            'campus',
            'program',
            'deptmaster'
        ])->latest()->get();
        return view('admin.master.academic-dept', ['data' => $data]);
    }

    // function addAcademicDept(Request $request)
    // {

    //     $request->validate([
    //         'batch' => 'required',
    //         'program_id' => 'required',
    //         'short_name' => 'required|string|max:255',
    //         'full_name' => 'required|string|max:255'
    //     ]);

    //     $record = MainProgram::find($request->program_id);

    //     $rec = new AcademicDepartment();
    //     $rec->campus_id = $record->campus_id;
    //     $rec->session_id = $request->batch;
    //     $rec->main_program_id = $request->program_id;
    //     $rec->short_name = Str::slug($request->full_name);
    //     $rec->name = $request->full_name;
    //     $rec->save();



    //     return redirect()->back()->with('success', 'Done');
    // }


    function connectAcademicToDept(Request $request)
    {
        $request->validate([
            'dept' => 'required',
            'id' => 'required'
        ]);

        AcademicDepartment::where('id', $request->id)->update([
            'dept_id' => $request->dept
        ]);
        return redirect()->back()->with('success', 'Connected Successfully');
    }


    function bankAccounts()
    {
        $data = CollegeBankAccount::latest()->get();
        return view('admin.accounts.banks', ['data' => $data]);
    }

    function addBankInfo(Request $request)
    {
        $request->validate([
            'acclabel' => 'required',
            'accname' => 'required',
            'accno' => 'required',
            'bank' => 'required',
            'ifsc' => 'required',
            'branch_name' => 'required',
        ]);

        if (!empty($request->doc)) {
            $doc = $request->doc;
            $filename = StaticController::s3_file_uploader($doc, 'collegebankaccounts');
        } else {
            $filename = null;
        }

        $rec = new CollegeBankAccount();
        $rec->acc_label = $request->acclabel;
        $rec->acc_no = $request->accno;
        $rec->acc_name = $request->accname;
        $rec->bank_name = $request->bank;
        $rec->ifsc = $request->ifsc;
        $rec->branch = $request->branch_name;
        $rec->doc = $filename;
        $rec->save();

        return redirect()->back()->with('success', 'Done');
    }


    function updateBankInfo(Request $request)
    {
        $request->validate([
            'acclabel' => 'required',
            'accname' => 'required',
            'accno' => 'required',
            'bank' => 'required',
            'ifsc' => 'required',
            'branch_name' => 'required',
        ]);
        $data = CollegeBankAccount::find($request->id);

        if (!empty($request->doc)) {
            $doc = $request->doc;
            $filename = StaticController::s3_file_uploader($doc, 'collegebankaccounts');
        } else {
            if ($data->doc == null) {
                $filename = null;
            } else {
                $filename = $data->doc;
            }
        }

        CollegeBankAccount::where('id', $request->id)->update([
            'acc_label' => $request->acclabel,
            'acc_no' => $request->accno,
            'acc_name' => $request->accname,
            'bank_name' => $request->bank,
            'ifsc' => $request->ifsc,
            'branch' => $request->branch_name,
            'doc' => $filename,
        ]);
        return redirect()->back()->with('success', 'Update Success');
    }


    function feeStructure(Request $request)
    {
        if (!empty($request->keyword)) {
            $keyword = $request->keyword;
            $searchValues = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);
            $data = FeesStructure::with([
                'program.campus',
                'batch',
                'feepvthead.head:id,head_name',
                'feecoursemaster:id,name',
                'programspivot.programgroupinfo.programInfo',
            ])->whereHas('feecoursemaster', function ($q) use ($searchValues) {
                foreach ($searchValues as $value) {
                    $q->where('name', 'LIKE', "%$value%");
                }
            })->latest()->get();
        } else {
            $data = FeesStructure::with([
                'program.campus',
                'batch',
                'feepvthead.head:id,head_name',
                'feecoursemaster:id,name',
                'programspivot.programgroupinfo.programInfo',
            ])->latest()->get();
        }


        return view('admin.accounts.fee-structure', ['data' => $data]);
    }


    function addFeeStructure(Request $request)
    {
        $request->validate([
            'program' => 'required',
            'batch' => 'required',
            'course' => 'required',
            'heads' => 'required|array|min:1',
            'amounts' => 'required|array|min:1',
            'reminder_date' => 'required',
            'due_date' => 'required',
            'quarter_title' => 'required|string|max:255',
            'applicable_year' => 'required',
            'yearly_pay_order' => 'required',


        ]);
        $rec = new FeesStructure();
        $rec->program_id = $request->program;
        $rec->batch_id = $request->batch;
        $rec->course_name = $request->course;
        $rec->reminder_date = $request->reminder_date;
        $rec->due_date = $request->due_date;
        $rec->quarter_title = $request->quarter_title;
        $rec->yearly_pay_order = $request->yearly_pay_order;
        $rec->std_current_year = $request->applicable_year;
        $rec->save();

        $feeStructureId = $rec->id;
        $heads = $request->heads;
        $amount = $request->amounts;

        //saviing heads
        for ($i = 0; $i < count($heads); $i++) {
            $pvt = new FeeStructureHasHead();
            $pvt->fee_structure_id = $feeStructureId;
            $pvt->fee_head_id = $heads[$i];
            $pvt->amount = $amount[$i];
            $pvt->save();
        }

        $course = $request->course;
        $progs = FeeStructureGroup::where('fee_course_master_id', $course)->get();
        //connect course group
        for ($i = 0; $i < count($progs); $i++) {
            $pg = new FeeStructureHasManyProgram();
            $pg->fee_structure_id = $rec->id;
            $pg->std_program_id = $progs[$i]->program_group_id;
            $pg->save();
        }

        return redirect()->back()->with('success', 'Done');
    }

    function unlinkStdProgram($id)
    {
        FeeStructureHasManyProgram::find($id)->delete();
        return redirect()->back()->with('success', 'Done');
    }


    function addCourseMasterGroup(Request $request)
    {

        $request->validate([
            'progs' => 'required|array|min:1',
        ]);
        $courseMasterId =  $request->coursemasterId;
        $progs = $request->progs;
        for ($i = 0; $i < count($progs); $i++) {

            if (FeeStructureGroup::where('fee_course_master_id', $courseMasterId)->where('program_group_id', $progs[$i])->exists()) {
                continue;
            }

            $rec = new FeeStructureGroup();
            $rec->fee_course_master_id = $courseMasterId;
            $rec->program_group_id = $progs[$i];
            $rec->save();
        }
        //find if any fee structure exist
        $feeStructures = FeesStructure::where('course_name', $courseMasterId)->get();
        foreach ($feeStructures as $fs) {
            //link programs to fee structure
            for ($j = 0; $j < count($progs); $j++) {
                if (FeeStructureHasManyProgram::where('fee_structure_id', $fs->id)->where('std_program_id', $progs[$j])->exists()) {
                    continue;
                }
                $pvt = new FeeStructureHasManyProgram();
                $pvt->fee_structure_id = $fs->id;
                $pvt->std_program_id = $progs[$j];
                $pvt->save();
            }
        }


        return redirect()->back()->with('success', 'Group Created');
    }

    function feeStructureGroupUnlink($id)
    {
        FeeStructureGroup::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function linkProgramtoFeeStructure(Request $request)
    {
        $request->validate([
            'progs' => 'required|array|min:1',
        ]);

        $progs = $request->progs;
        for ($i = 0; $i < count($progs); $i++) {
            $rec = new FeeStructureHasManyProgram();
            $rec->fee_structure_id = $request->feeStructureId;
            $rec->std_program_id = $progs[$i];
            $rec->save();
        }
        return redirect()->back()->with('success', 'Programs Group Linked to Fee Structure');
    }

    function feeHeads()
    {
        $data = FeeHead::with('bankmaster')->latest()->get();
        return view('admin.accounts.fee-heads', ['data' => $data]);
    }

    function addFeeHead(Request $request)
    {
        $request->validate([
            'head_name' => 'required|string|max:255',
            'bank' => 'required'
        ]);
        $rec = new FeeHead();
        $rec->head_name = $request->head_name;
        $rec->bank_acc_id = $request->bank;

        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function updateFeeHead(Request $request)
    {
        $request->validate([
            'head_name' => 'required|string|max:255',
        ]);

        $data =  FeeHead::find($request->id);

        if (!empty($request->bank)) {
            $bank = $request->bank;
        } else {
            $bank = $data->bank_acc_id;
        }

        FeeHead::where('id', $request->id)->update([
            'head_name' => $request->head_name,
            'bank_acc_id' => $bank
        ]);

        return redirect()->back()->with('success', 'Update Done');
    }

    function delFeeHead($id)
    {
        FeeHead::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function delFeeHeadPvt($id)
    {
        FeeStructureHasHead::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }


    function updateHeadSingle(Request $request)
    {


        $request->validate([
            'amount' => 'required',
        ]);

        FeeStructureHasHead::where('id', $request->id)->update([
            'amount' => $request->amount
        ]);
        return redirect()->back()->with('success', 'Updated');
    }

    function updateFeeStructure(Request $request)
    {
        $request->validate([
            'program' => 'required',
            'batch' => 'required',
        ]);
        $id = $request->id;

        FeesStructure::where('id', $id)->update([
            'program_id' => $request->program,
            'batch_id' => $request->batch,
            'reminder_date' => $request->reminder_date,
            'due_date' => $request->due_date,
        ]);

        $amount = $request->amounts;
        $heads = $request->heads;
        $feeStructureId = $id;
        //saviing heads if added
        if (!empty($heads) && is_array($heads) && !empty($amount) && is_array($amount)) {
            for ($i = 0; $i < count($heads); $i++) {
                // Skip if head_id is null or amount is not set
                if (empty($heads[$i]) || !isset($amount[$i])) {
                    continue;
                }

                $check = FeeStructureHasHead::where('fee_structure_id', $feeStructureId)->where('fee_head_id', $heads[$i])->count();
                if ($check == 0) {
                    $pvt = new FeeStructureHasHead();
                    $pvt->fee_structure_id = $id;
                    $pvt->fee_head_id = $heads[$i];
                    $pvt->amount = $amount[$i];
                    $pvt->save();
                }
            }
        }



        return redirect()->back()->with('success', 'Fee Structure Updated');
    }

    function feeCourseMaster(Request $request)
    {
        if (!empty($request->coursemaster)) {
            $data = FeeCourseMaster::where('id', $request->coursemaster)->latest()->get();
        } else {
            $data = FeeCourseMaster::latest()->get();
        }

        $allcourses = FeeCourseMaster::latest()->get();
        return view('admin.accounts.fee-course-master', ['data' => $data, 'allcourses' => $allcourses]);
    }

    function addCourseFeeMaster(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $rec = new FeeCourseMaster();
        $rec->name = $request->name;
        $rec->save();

        return redirect()->back()->with('success', 'Done');
    }

    function updateCourseFeeMaster(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        FeeCourseMaster::where('id', $request->id)->update([
            'name' => $request->name,
        ]);
        return redirect()->back()->with('success', 'Done');
    }

    function updateFeeStructureStatus($id)
    {

        $data =  FeesStructure::findOrFail($id);

        if ($data->is_payable == 1) {
            FeesStructure::where('id', $id)->update([
                'is_payable' => 0,
            ]);
        } else {
            FeesStructure::where('id', $id)->update([
                'is_payable' => 1,
            ]);
        }

        return redirect()->back()->with('success', 'Status Updated');
    }

    function delFeeCourseMaster($id)
    {
        FeeCourseMaster::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }

    function deleteFeeStructure($id)
    {
        FeeStructureHasHead::where('fee_Structure_id', $id)->delete();
        FeeStructureHasManyProgram::where('fee_Structure_id', $id)->delete();
        FeesStructure::findOrFail($id)->delete();
        // OR permanent delete
        // $fS->forceDelete();

        return redirect()
            ->back()
            ->with('success', 'Fee Structure deleted successfully.');
    }

    function facultyMaster()
    {
        $data = Faculty::with([
            'nationality'
        ])->get();
        return view('admin.academics.faculty', ['data' => $data]);
    }

    function updateFaculty(Request $request)
    {

        Faculty::where('id', $request->id)->update([
            'USER_CODE' => $request->empid,
            'FIRST_NAME' => $request->fname,
            'LAST_NAME' => $request->lname,
            'DOB' => $request->dob,
            'GENDER' => $request->gender,
            'MOBILE_NO' => $request->mobile_no,
            'MAIL_ID' => $request->mail_id,
        ]);

        return redirect()->back()->with('success', 'Updated');
    }

    function userList()
    {
        $data = User::with('menupermission')
            ->with('userroletype')
            ->with('campuspermission.campus:id,name')
            ->get();
        return view('admin.user-manager.access-management', ['data' => $data]);
    }

    function createNewUser(Request $request)
    {


        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $rec = new User();
        $rec->name = $request->name;
        $rec->email = $request->email;
        $rec->password = Hash::make($request->password);
        $rec->status = 'ACTIVE';
        $rec->otp_verification = 1;
        $rec->save();

        $userId = $rec->id;
        if ($request->user_type == 'super-admin' || $request->user_type == 'principal') {
            $roles = MenuMaster::pluck('id')->toArray();
        } else {

            $request->validate([
                'roles' => 'required|array|min:1'
            ]);

            $roles = $request->roles;

            //check CAMPUS ASSIGNMENT
            if (!empty($request->campus)) {
                $campus = new UserCampusSetting();
                $campus->user_id = $userId;
                $campus->campus_id = $request->campus;
                $campus->save();
            }
        }
        //adding permissions
        for ($i = 0; $i < count($roles); $i++) {
            $data = MenuMaster::find($roles[$i]);
            if ($data) {
                $permission = new UserMenuPermission();
                $permission->user_id = $userId;
                $permission->menu_master_id = $data->id;
                $permission->permission_name = $data->slug;
                $permission->save();
            }
        }

        //adding role_type
        $userType = new UserHasRole();
        $userType->user_id = $userId;
        $userType->role_name = $request->user_type; //default to admin
        $userType->save();



        return redirect()->back()->with('success', 'New User Created');
    }

    function updatePermission(Request $request)
    {

        $request->validate([
            'roles' => 'required|array|min:1',
            'user_id' => 'required',
        ]);

        $userId = $request->user_id;

        $roles = $request->roles;

        for ($i = 0; $i < count($roles); $i++) {

            $duplicateCheck = UserMenuPermission::where('user_id', $userId)->where('menu_master_id', $roles[$i])->first();
            if ($duplicateCheck == null) {

                $record = MenuMaster::find($roles[$i]);

                $permission = new UserMenuPermission();
                $permission->user_id = $userId;
                $permission->menu_master_id = $roles[$i];
                $permission->permission_name = $record->slug;
                $permission->save();
            }
        }

        return redirect()->back()->with('success', 'Permissions Updated');
    }

    function removeUserPermission($id)
    {
        UserHasPermission::find($id)->delete();
        return redirect()->back()->with('success', 'Permission Removed');
    }

    function latefee()
    {
        $data = LateFee::find(1);
        return view('admin.accounts.latefee', ['data' => $data]);
    }

    function smsData($msgid)
    {
        $data = StaticController::fetchMessageData($msgid);
        return $data;
    }

    function deleteUserAccess($id)
    {
        User::findOrFail($id)->delete();
        //delete user campus setting
        UserCampusSetting::where('user_id', $id)->delete();
        //delete user menu permission
        UserMenuPermission::where('user_id', $id)->delete();
        //delete user role
        UserHasRole::where('user_id', $id)->delete();
        //delete user
        return redirect()->back()->with('success', 'User Deleted');
    }

    function userTypes()
    {
        $data = UserType::latest()->get();
        return view('admin.user-manager.user-types', ['data' => $data]);
    }

    function addUserType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        UserType::where('slug', $slug)->first();
        $check = UserType::where('slug', $slug)->first();
        if ($check !== null) {
            return redirect()->back()->with('error', 'User type already exists');
        } else {
            $rec = new UserType();
            $rec->name = $request->name;
            $rec->slug = $slug;
            $rec->is_active = 1;
            $rec->save();
        }

        return redirect()->back()->with('success', 'Done');
    }

    function menuAccessTypes()
    {

        $data = MenuMaster::latest()->get();
        return view('admin.user-manager.menu-rights', ['data' => $data]);
    }

    function addMenuAccessType(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'module_type' => 'required|string|max:255',

        ]);
        $slug = Str::slug($request->name);
        MenuMaster::where('slug', $slug)->first();
        $check = MenuMaster::where('slug', $slug)->first();
        if ($check !== null) {
            return redirect()->back()->with('error', 'Permission already exists');
        }
        $rec = new MenuMaster();
        $rec->menu_name = $request->name;
        $rec->slug = $slug;
        $rec->module_type = $request->module_type;
        $rec->save();
        //add permission to super admin
        $superAdmins = User::whereHas('userroletype', function ($q) {
            $q->orWhere('role_name', 'super-admin');
            $q->orWhere('role_name', 'principal');
        })->get();

        foreach ($superAdmins as $sa) {
            $permission = new UserMenuPermission();
            $permission->user_id = $sa->id;
            $permission->menu_master_id = $rec->id;
            $permission->permission_name = $rec->slug;
            $permission->save();
        }

        return redirect()->back()->with('success', 'Done');
    }

    function deleteUserPermission($id)
    {
        UserMenuPermission::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Deleted');
    }
}
