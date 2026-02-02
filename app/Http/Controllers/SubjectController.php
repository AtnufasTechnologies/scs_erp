<?php

namespace App\Http\Controllers;

use App\Models\BatchMaster;
use App\Models\Campus;
use App\Models\CognitiveLevelMaster;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\LectureHallMaster;
use App\Models\StudentProgram;
use App\Models\Subject;
use App\Models\SubjectHasCombination;
use App\Models\SubjectHasRoutine;
use App\Models\SubjectHasSemester;
use App\Models\SubjectHasStudentProgam;
use App\Models\SubjectHasSyllabus;
use App\Models\SubjectTypeMaster;
use App\Models\SyllabusHasFaculty;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    function index()
    {


        $data = Subject::with([
            'campusmaster'
        ])->latest()->get();
        return view('admin.master.subject', ['data' => $data]);
    }

    function subjectType()
    {
        $data = SubjectTypeMaster::get();
        return view('admin.master.subject-type', ['data' => $data]);
    }

    function addSubject(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'campus' => 'required',
        ]);
        $slug = Str::slug($request->title);
        $check = Subject::where('slug', $slug)->count();
        if ($check > 0) {
            return response()->json(['msg' => 'Subject already exists', 'status' => 'error']);
        } else {

            if ($request->campus == 3) {
                $campuses = Campus::all();
                foreach ($campuses as $campus) {
                    $rec = new Subject();
                    $rec->campus_id = $campus->id;
                    $rec->main_program_type = 'PG';
                    $rec->slug =   $slug;
                    $rec->code = Str::upper($request->code);
                    $rec->title = Str::lower($request->title);
                    $rec->save();
                }
            } else {
                $rec = new Subject();
                $rec->campus_id = $request->campus;
                $rec->main_program_type = 'UG';
                $rec->slug =   $slug;
                $rec->code = Str::upper($request->code);
                $rec->title = Str::lower($request->title);
                $rec->save();
            }


            return redirect()->back()->with('success', 'Created');
        }
    }

    function subjectSingle(Request $request)
    {

        $id = $request->id;

        if (!empty($request->batch)) {
            $batchmaster = BatchMaster::where('id', $request->batch)->first();
            $batchId = $batchmaster->id;
        } else {
            $batchmaster = BatchMaster::where('admission_active_batch', 1)->first();
            $batchId = $batchmaster->id;
        }
        $query = Subject::find($id);

        $query->with(['combinations', 'semesters.syllabus'])->with('syllabus', function ($q) use ($batchId) {
            $q->where('session_id', $batchId);
        })->where('id', $id)
            ->first();

        $data = $query;
        $programs = StudentProgram::where('campus_id', $query->campus_id)->latest()->get();
        return view('admin.subject.subject-single', [
            'data' => $data,
            'batchmaster' => $batchmaster,
            'programs' => $programs
        ]);
    }


    function viewRoutine($id)
    {
        $data =  SubjectHasSyllabus::where('subject_id', $id)->with([
            'sessionmaster:id,title',
            'semestermaster:id,title',
            'subtypemaster:id,title',
            'timetable.weekdaymaster:id,title',
            'timetable.hourmaster:id,title',
            'timetable.lecturehallmaster.acblockmaster:id,title',
        ])->get();
        return response()->json(['data' => $data]);
    }

    function addSyllabus(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'batch' => 'required',
            'semester_id' => 'required',
            'title' => 'required',
            'desc' => 'required',
            'subject_type_id' => 'required'
        ]);



        $rec = new SubjectHasSyllabus();
        $rec->dept_id = $request->dept_id;
        $rec->subject_id = $request->subject_id;
        $rec->session_id = $request->session_id;
        $rec->semester_id = $request->semester_id;
        $rec->title = $request->title;
        $rec->content = $request->desc;
        $rec->subject_type_id = $request->subject_type_id;
        $rec->save();
        return response()->json(['msg' => 'Data Added', 'status' => 'success']);
    }

    function cogLevelMaster()
    {
        $data = CognitiveLevelMaster::latest()->get();
        return response()->json(['data' => $data]);
    }
    function addCognitiveLevel(Request $request)
    {
        $validator  =  Validator::make($request->all(), [
            'shortname' => 'required',
            'fullname' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'status' => false], 400);
        }

        $rec = new CognitiveLevelMaster();
        $rec->shortname = $request->shortname;
        $rec->fullname = $request->fullname;
        $rec->save();
        return response()->json(['message' => 'Cognitive Level Created'], 201);
    }


    function addSubjectTimeTable(Request $request)
    {
        $validator  =  Validator::make($request->all(), [
            'syllabus_id' => 'required',
            'weekday_id' => 'required',
            'hour_id' => 'required',
            'lecturehall_id' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'status' => false], 400);
        }

        $rec = new SubjectHasRoutine();
        $rec->syllabus_id = $request->syllabus_id;
        $rec->weekday_id = $request->weekday_id;
        $rec->hour_id = $request->hour_id;
        $rec->lecturehall_id = $request->lecturehall_id;
        $rec->save();
        return response()->json(['message' => 'TimeTable Created'], 201);
    }

    function addSemesterToSubject(Request $request)
    {
        $semester = $request->semester;
        $subject_id = $request->subject_id;
        $batch = $request->batch;

        SubjectHasSemester::create([
            'subject_id' => $subject_id,
            'semester_id' => $semester,
            'session_id' => $batch,
        ]);

        return redirect()->back()->with('success', 'Semester Added');
    }

    function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return redirect()->back()->with('success', 'Subject Deleted');
    }

    function linkStdPrograms(Request $request)
    {
        $validator  =  $request->validate([
            'subject_id' => 'required',
            'batch_id' => 'required',
            'programs' => 'required|array|min:1',
        ]);

        $programs = $request->programs;
        $subject_id = $request->subject_id;
        $data = Subject::find($subject_id);

        for ($i = 0; $i < count($programs); $i++) {
            $subject = new SubjectHasStudentProgam();
            $subject->subject_id = $subject_id;
            $subject->batch_id = $request->batch_id;
            $subject->student_program_id = $programs[$i];
            $subject->campus_id = $data->campus_id;
            $subject->save();
        }

        return redirect()->back()->with('success', 'Combinations linked successfully');
    }
}
