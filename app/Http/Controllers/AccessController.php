<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use App\Models\Subject;
use App\Models\SubjectHasDeptAdmin;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserCampusSetting;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccessController extends Controller
{
    function deptAccess()
    {

        $departments = Subject::with('campusmaster:id,name')->get();
        $data = User::whereHas('userroletype', function ($q) {
            //Head of Department Admin Role
            $q->where('role_name', 'dept-admin-erp');
        })->with('subjectdeptadmin.subject')->latest()->get();
        return view('admin.user-manager.dept-access', ['departments' => $departments, 'data' => $data]);
    }


    function assignDeptAccess(Request $request)
    {

        $request->validate([
            'department' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        $data = Subject::where('id', $request->department)->first();

        $departmentId = $data->id;
        //create user
        $rec = new User();
        $rec->name =  $request->name;
        $rec->email = $request->email;
        $rec->password = Hash::make($request->password);
        $rec->otp_verification = 1;
        $rec->status = 'ACTIVE';
        $rec->save();


        //assign campus access permission
        UserHasRole::create([
            'user_id' => $rec->id,
            'role_name' =>  'dept-admin-erp', //Department Admin
        ]);

        //assign department to user
        SubjectHasDeptAdmin::create([
            'subject_id' => $departmentId,
            'user_id' => $rec->id,
        ]);

        //add Campus Seetings permission
        UserCampusSetting::create([
            'user_id' => $rec->id,
            'campus_id' =>  $data->campus_id ?? 0, //Campus Settings Access
        ]);


        return back()->with('success', 'Departmental Access Created.');
    }

    function revokeDeptAccess($id)
    {
        $user = User::find($id);
        if ($user) {
            if ($user->status == 'ACTIVE') {


                User::where('id', $id)->update([
                    'status' => 'INACTIVE',
                ]);
            } else {
                User::where('id', $id)->update([
                    'status' => 'ACTIVE',
                ]);
            }
        }

        return back()->with('success', 'Done.');
    }

    function impersonateUser($id)
    {
        // Store the current admin's ID in session before impersonating
        session(['impersonate_admin_id' => auth()->id()]);

        // Log in as the target user
        $user = User::findOrFail($id);
        auth()->login($user);
        return redirect('erp/admin/dashboard')->with('success', 'You are now impersonating ' . $user->name);
    }
    function smsTemplates()
    {
        $templates = SmsTemplate::latest()->get();
        return view('admin.sms.template', ['templates' => $templates]);
    }

    function smsTemplateStore(Request $request)
    {
        $request->validate([
            'template_name' => 'required|string|max:255',
            'template_content' => 'required|string',
        ]);

        SmsTemplate::create([
            'template_name' => $request->template_name,
            'template_content' => $request->template_content,
        ]);

        return back()->with('success', 'SMS Template Created Successfully.');
    }

    function smsTemplateDelete($id)
    {
        $template = SmsTemplate::find($id);
        if ($template) {
            $template->delete();
            return back()->with('success', 'SMS Template Deleted Successfully.');
        }
        return back()->with('error', 'SMS Template Not Found.');
    }

    function userActivityLogs(Request $request)
    {
        $query = UserActivityLog::with('user:id,name,email')->latest('id');

        if (!empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        }

        if (!empty($request->event)) {
            $query->where('event', $request->event);
        }

        if (!empty($request->from_date)) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if (!empty($request->to_date)) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if (!empty($request->keyword)) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('auditable_type', 'like', "%{$keyword}%")
                    ->orWhere('auditable_id', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('url', 'like', "%{$keyword}%");
            });
        }

        $logs = $query->paginate(50)->appends($request->query());
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        return view('admin.user-manager.activity-logs', [
            'logs' => $logs,
            'users' => $users,
        ]);
    }

    function activityLogsDashboard()
    {
        // Total stats
        $totalActivityCount = UserActivityLog::count();
        $totalCreated = UserActivityLog::where('event', 'created')->count();
        $totalUpdated = UserActivityLog::where('event', 'updated')->count();
        $totalDeleted = UserActivityLog::where('event', 'deleted')->count();

        // Today's activity
        $todayActivity = UserActivityLog::whereDate('created_at', today())->count();
        $todayCreated = UserActivityLog::where('event', 'created')->whereDate('created_at', today())->count();
        $todayUpdated = UserActivityLog::where('event', 'updated')->whereDate('created_at', today())->count();
        $todayDeleted = UserActivityLog::where('event', 'deleted')->whereDate('created_at', today())->count();

        // This week activity
        $weekStart = now()->startOfWeek();
        $weekActivity = UserActivityLog::where('created_at', '>=', $weekStart)->count();

        // Most active users (last 7 days)
        $mostActiveUsers = UserActivityLog::select('user_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('user_id')
            ->with('user:id,name,email')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'user' => $log->user ? $log->user->name : 'System',
                    'count' => $log->count(),
                ];
            });

        // Activity by model (last 30 days)
        $activityByModel = UserActivityLog::select('auditable_type')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('auditable_type')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(15)
            ->get()
            ->groupBy(function ($item) {
                $model = class_basename($item->auditable_type);
                return $model;
            })
            ->map(function ($items) {
                return $items->count();
            });

        // Recent activities
        $recentActivities = UserActivityLog::with('user:id,name,email')
            ->latest('id')
            ->limit(10)
            ->get();

        // Activity timeline (last 7 days)
        $activityTimeline = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = UserActivityLog::whereDate('created_at', $date)->count();
            $activityTimeline->push([
                'date' => $date->format('M d'),
                'count' => $count,
            ]);
        }

        return view('admin.user-manager.activity-logs-dashboard', [
            'totalActivityCount' => $totalActivityCount,
            'totalCreated' => $totalCreated,
            'totalUpdated' => $totalUpdated,
            'totalDeleted' => $totalDeleted,
            'todayActivity' => $todayActivity,
            'todayCreated' => $todayCreated,
            'todayUpdated' => $todayUpdated,
            'todayDeleted' => $todayDeleted,
            'weekActivity' => $weekActivity,
            'mostActiveUsers' => $mostActiveUsers,
            'activityByModel' => $activityByModel,
            'recentActivities' => $recentActivities,
            'activityTimeline' => $activityTimeline,
        ]);
    }
}
