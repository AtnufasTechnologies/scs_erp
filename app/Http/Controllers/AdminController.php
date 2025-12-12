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
use App\Models\FeeCourseMaster;
use App\Models\FeeHead;
use App\Models\FeeQuarterMaster;
use App\Models\FeesStructure;
use App\Models\FeeStructureHasHead;
use App\Models\FeeStructureHasManyProgram;
use App\Models\HourMaster;
use App\Models\LectureHallMaster;
use App\Models\MainProgram;
use App\Models\ProgramGroup;
use App\Models\ProgramMaster;
use App\Models\ReligionMaster;
use App\Models\RoomMaster;
use App\Models\Semester;
use App\Models\StudentMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

            'acc_no' => $request->accno,
            'acc_name' => $request->accname,
            'bank_name' => $request->bank,
            'ifsc' => $request->ifsc,
            'branch' => $request->branch_name,
            'doc' => $filename,
        ]);
        return redirect()->back()->with('success', 'Update Success');
    }


    function feeStructure()
    {

        $data = FeesStructure::with([
            'program.campus',
            'batch',
            'feepvthead.head:id,head_name',
            'feecoursemaster:id,name',
            'programspivot.programgroupinfo.programInfo',
        ])->latest()->get();
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

        return redirect()->back()->with('success', 'Done');
    }

    function unlinkStdProgram($id)
    {
        FeeStructureHasManyProgram::find($id)->delete();
        return redirect()->back()->with('success', 'Done');
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
        $data = FeeHead::latest()->get();
        return view('admin.accounts.fee-heads', ['data' => $data]);
    }

    function addFeeHead(Request $request)
    {
        $request->validate([
            'head_name' => 'required|string|max:255',
        ]);
        $rec = new FeeHead();
        $rec->head_name = $request->head_name;
        $rec->save();
        return redirect()->back()->with('success', 'Done');
    }

    function updateFeeHead(Request $request)
    {
        $request->validate([
            'head_name' => 'required|string|max:255',
        ]);
        FeeHead::where('id', $request->id)->update([
            'head_name' => $request->head_name,
        ]);

        return redirect()->back()->with('success', 'Done');
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

        ]);
        $feeStructureId = $id;
        $heads = $request->heads;
        $amount = $request->amounts;

        //saviing heads
        if (count($heads)) {
            for ($i = 0; $i < count($heads); $i++) {
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

    function feeCourseMaster()
    {
        $data = FeeCourseMaster::latest()->get();
        return view('admin.accounts.fee-course-master', ['data' => $data]);
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
}
