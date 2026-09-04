<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Models\Faculty;
use App\Models\PasswordReset;
use App\Models\RoleMaster;
use App\Models\Subject;
use App\Models\SubjectFacultyMaster;
use App\Models\SubjectHasDeptAdmin;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    function index()
    {
        return view('auth.login');
    }

    function login(Request $request)
    {

        $user = User::where('email', $request->email)->where('status', 'ACTIVE')->first();

        if ($user) {

            if (Hash::check($request->password, $user->password)) {
                Auth::login($user, true);
                $roleNames = $this->getUserRoleNames($user);
                if ($roleNames->isEmpty()) {
                    Auth::logout();
                    return redirect('/')->with('error', 'No role assigned. Please contact Admin.');
                }

                $dashboardOptions = $roleNames
                    ->map(function ($roleName) {
                        return [
                            'role' => $roleName,
                            'route' => $this->resolveDashboardRouteNameForRole($roleName),
                            'label' => $this->displayRoleLabel($roleName),
                        ];
                    })
                    ->filter(fn($entry) => !empty($entry['route']))
                    ->unique('route')
                    ->values();

                if ($dashboardOptions->isEmpty()) {
                    return redirect('erp/admin/dashboard')->with('success', 'Login Success');
                }

                session([
                    'dashboard_role_options' => $dashboardOptions->all(),
                    'active_dashboard_role' => (string) ($dashboardOptions->first()['role'] ?? ''),
                ]);

                if ($dashboardOptions->count() > 1) {
                    return redirect()->route('dashboard.switcher')->with('success', 'Login Success');
                }

                $singleRoute = (string) ($dashboardOptions->first()['route'] ?? '');
                if ($singleRoute !== '') {
                    return redirect()->route($singleRoute)->with('success', 'Login Success');
                }

                return redirect('erp/admin/dashboard')->with('success', 'Login Success');
            } else {
                return redirect('/')->with('error', 'Password Incorrect');
            }
        } else {
            return redirect('/')->with('error', 'User Not Found');
        }
    }

    function userAuth(int $id)
    {
        $user = User::find($id);
        if ($user) {
            if ($user->status == 'ACTIVE') {
                return response()->json([
                    'status' => true,
                    'user' => $user
                ]);
            }
            return response()->json(['message' => 'Signing Out', 'signoutaction' => true], 401);
        } else {
            return response()->json(['signoutaction' => true, 'message' => 'User Not Found...Siging Out'], 404);
        }
    }

    function addfacultyAccess(Request $request)
    {

        $validate = $request->validate([
            'faculty_id' => 'required',
            'password' => 'required|string|min:6|max:190',
        ]);

        $facultyRecord = Faculty::find($request->faculty_id);

        $fullname = $facultyRecord->fname . ' ' .  $facultyRecord->lname;
        $rec = new User();
        $rec->name = $fullname;
        $rec->email = $facultyRecord->email;
        $rec->phone = $facultyRecord->phone;
        $rec->decrypted_password = $request->password;
        $rec->password = Hash::make($request->password);
        $rec->otp_verification = 1;
        $rec->status = 'ACTIVE';
        $rec->save();

        $user = $rec->id;

        //Add user role
        $role = new UserHasRole();
        $role->user_id = $user;
        $role->role_type = 'teacher';
        $role->save();

        return response()->json(['message' => 'Faculty Access Created for ' . $fullname]);
    }

    function erpLogin(Request $request)
    {

        $user = User::where('email', $request->email)->where('status', 'ACTIVE')->first();

        if ($user) {

            if (Hash::check($request->password, $user->password)) {

                //checking user role
                $userRole = UserHasRole::where('user_id', $user->id)->first();

                if ($userRole->role_type == 'admin') {
                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                    $data['campus_auth'] = $userRole->campus; //1 = Sonada,2 = Siliguri

                } else if ($userRole->role_type == 'teacher') {

                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                } else if ($userRole->role_type == 'office') {

                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                } else {
                    //applicatant --- future student
                    $data['user'] = $user;
                    $data['user_role'] = $userRole->role_type;
                }


                return response()->json([
                    'status' => true,
                    'message' => 'Login Successful',
                    'data' => $data,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Password Incorrect',

                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',

            ], 404);
        }
    }

    function forgotPassword()
    {
        return redirect()->route('login', ['panel' => 'forgot']);
    }

    public function dashboardSwitcher()
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Please login to continue.');
        }

        $currentUrl = url()->current();
        $requestedReturnTo = trim((string) request()->query('return_to', ''));

        if ($requestedReturnTo !== '' && $requestedReturnTo !== $currentUrl) {
            $host = (string) parse_url(config('app.url'), PHP_URL_HOST);
            $returnHost = (string) parse_url($requestedReturnTo, PHP_URL_HOST);

            if ($host !== '' && $returnHost === $host) {
                session(['dashboard_switch_return_to' => $requestedReturnTo]);
            }
        }

        $returnTo = (string) session('dashboard_switch_return_to', route('admin.dashboard'));

        $roleOptions = collect(session('dashboard_role_options', []));
        if ($roleOptions->isEmpty()) {
            $roleOptions = $this->getUserRoleNames(Auth::user())
                ->map(function ($roleName) {
                    return [
                        'role' => $roleName,
                        'route' => $this->resolveDashboardRouteNameForRole($roleName),
                        'label' => $this->displayRoleLabel($roleName),
                    ];
                })
                ->filter(fn($entry) => !empty($entry['route']))
                ->unique('route')
                ->values();

            session(['dashboard_role_options' => $roleOptions->all()]);
        }

        if ($roleOptions->count() <= 1) {
            $routeName = (string) ($roleOptions->first()['route'] ?? 'admin.dashboard');
            return redirect()->route($routeName);
        }

        return view('auth.dashboard-switcher', [
            'roleOptions' => $roleOptions,
            'activeRole' => (string) session('active_dashboard_role', ''),
            'returnTo' => $returnTo,
        ]);
    }

    public function switchDashboard(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Please login to continue.');
        }

        $request->validate([
            'role' => 'required|string',
        ]);

        $selectedRole = trim((string) $request->input('role'));
        $roleOptions = collect(session('dashboard_role_options', []));
        $matched = $roleOptions->first(fn($option) => (string) ($option['role'] ?? '') === $selectedRole);

        if (!$matched) {
            return redirect()->route('dashboard.switcher')->with('error', 'Selected dashboard is not available for your account.');
        }

        session(['active_dashboard_role' => $selectedRole]);
        $routeName = (string) ($matched['route'] ?? '');

        if ($routeName === '') {
            return redirect()->route('dashboard.switcher')->with('error', 'Unable to resolve dashboard for selected role.');
        }

        return redirect()->route($routeName);
    }

    function sendPasswordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        $email =   $request->email;
        $userdata = User::where('email', $email)->first();

        if ($userdata != null) {
            $code = sha1(uniqid());
            $rec = new PasswordReset();
            $rec->email = $request->email;
            $rec->token = $code;
            $rec->status = 1;
            $rec->save();

            $details = [
                'token' =>  $code,
            ];

            Mail::to($email)->send(new PasswordResetOtpMail($details));

            return redirect()->back()->with('success', 'Reset Link Sent on Email');
        } else {
            return redirect()->back()->with('error', 'Email not found');
        }
    }

    function verifyResetToken($code)
    {
        $data =  PasswordReset::where('token', $code)->where('status', 1)->first();
        if ($data) {
            return view('auth.update-password', ['data' => $data]);
        } else {
            return redirect('login')->with('error', 'Link Expired... Please Reset Again');
        }
    }

    function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|max:190',
            'confirm_password' => 'required|same:password',
        ]);

        $data = User::where('email', $request->email)->first();
        if ($data) {
            $data->password = Hash::make($request->password);
            $data->save();

            //invalidate the token
            PasswordReset::where('email', $request->email)->update(['status' => 0]);

            return redirect()->route('login')->with('success', 'Password Updated Successfully. Please Login');
        } else {
            return redirect()->route('login')->with('info', 'User Not Found');
        }
    }
    function resetPassword()
    {
        return view('auth.reset-password');
    }


    function logout()
    {
        Auth::logout();
        return redirect('/')->with('success', 'Signed Out ');
    }



    /** Api Routes */

    function facultyLogin(Request $request)
    {
        $user = User::where('email', $request->email)->where('status', 'ACTIVE')->first();

        if ($user) {

            if (Hash::check($request->password, $user->password)) {
                $hasFacultyRole = UserHasRole::where('user_id', $user->id)
                    ->where('role_name', 'faculty')
                    ->exists();

                if ($hasFacultyRole) {

                    return response()->json([
                        'status' => true,
                        'message' => 'Login Successful',
                        'data' => [
                            'user' => $user,
                            'role' => 'faculty',
                        ],
                    ], 200);
                } else {

                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized Access',
                    ], 403);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Password Incorrect',
                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User Not Found',
            ], 404);
        }
    }

    private function getUserRoleNames(User $user)
    {
        $baseRoles = UserHasRole::query()
            ->where('user_id', $user->id)
            ->pluck('role_name')
            ->map(fn($role) => strtolower(trim((string) $role)))
            ->filter(fn($role) => $role !== '')
            ->values();

        if (Schema::hasColumn('user_has_roles', 'role_id')) {
            $roleIds = UserHasRole::query()
                ->where('user_id', $user->id)
                ->whereNotNull('role_id')
                ->pluck('role_id')
                ->map(fn($id) => (int) $id)
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->values();

            if ($roleIds->isNotEmpty()) {
                $roleSlugs = RoleMaster::query()
                    ->whereIn('id', $roleIds->all())
                    ->pluck('slug')
                    ->map(fn($slug) => strtolower(trim((string) $slug)))
                    ->filter(fn($slug) => $slug !== '')
                    ->values();

                $baseRoles = $baseRoles->merge($roleSlugs)->values();
            }
        }

        return $baseRoles->unique()->values();
    }

    private function resolveDashboardRouteNameForRole(string $roleName): ?string
    {
        $role = strtolower(trim($roleName));

        switch ($role) {
            case 'hod':
            case 'dept-admin-erp':
                return $this->ensureDepartmentMappingForHodUser((int) Auth::id())
                    ? 'department.dashboard'
                    : null;

            case 'account-office-incharge':
            case 'account-office-assistant':
                return 'account-office.dashboard';

            case 'faculty':
                return 'faculty.dashboard';

            case 'coe':
            case 'dcoe':
                return 'coe.dashboard';

            case 'principal':
            case 'vice-principal':
            case 'bursar':
            case 'rector':
                return 'principal.dashboard';

            case 'hr':
                return 'hr.dashboard';

            case 'student':
                return 'student.dashboard';

            case 'admission-incharge':
                return 'admission.dashboard';

            case 'admission-test-incharge':
                return 'admission.testincharge.dashboard';

            case 'event-controller':
                return 'event-coordinator.dashboard';

            case 'dean-of-student-affairs':
            case 'dean-student-affairs':
            case 'student-affairs-dean':
                return 'dean.dashboard';

            case 'training-and-placement-officer':
                return 'tpo.training-placement.dashboard';

            case 'dean':
                return 'dean.office.dashboard';

            case 'central-office-incharge':
                return 'central-office.dashboard';

            case 'international-office':
            case 'international_office':
            case 'international-office-incharge':
            case 'international-office-officer':
                return 'international-office.dashboard';

            case 'receptionist':
            case 'reception':
            case 'front-office':
                return 'receptionist.dashboard';

            case 'super-admin':
            case 'admin':
            case 'itcell':
            default:
                return 'admin.dashboard';
        }
    }

    private function displayRoleLabel(string $roleName): string
    {
        $normalized = strtolower(trim($roleName));
        return ucwords(str_replace(['-', '_'], ' ', $normalized));
    }

    private function ensureDepartmentMappingForHodUser(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $existingSubjectId = (int) SubjectHasDeptAdmin::where('user_id', $userId)->value('subject_id');
        if ($existingSubjectId > 0) {
            return true;
        }

        $subjectId = (int) SubjectFacultyMaster::query()
            ->where('access_id', $userId)
            ->whereNotNull('subject_id')
            ->orderByDesc('id')
            ->value('subject_id');

        if ($subjectId <= 0) {
            return false;
        }

        SubjectHasDeptAdmin::updateOrCreate(
            ['subject_id' => $subjectId],
            ['user_id' => $userId]
        );

        $campusId = (int) Subject::where('id', $subjectId)->value('campus_id');
        if ($campusId > 0) {
            UserCampusSetting::updateOrCreate(
                ['user_id' => $userId],
                ['campus_id' => $campusId]
            );
        }

        return true;
    }
}
