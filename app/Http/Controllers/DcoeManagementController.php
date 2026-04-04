<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\DcoeMenuPermission;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DcoeManagementController extends Controller
{
  /**
   * COE sidebar menu items that can be assigned as DCOE permissions.
   * Each key is a slug, value is the display label.
   */
  public static function availableMenuItems(): array
  {
    return [
      'exam-management'       => 'Exam Management',
      'exam-registrations'    => 'Exam Registrations',
      'seating-allocation'    => 'Seating Allocation',
      'dummy-numbers'         => 'Dummy Numbers',
      'admit-cards'           => 'Admit Cards',
      'regulation-master'     => 'Regulation Master',
      'attendance'            => 'Attendance',
      'marks-entry'           => 'Marks Entry',
      'packet-management'     => 'Packet Management',
      'invigilation-duties'   => 'Invigilation Duties',
      'evaluation'            => 'Evaluation',
      'moderation'            => 'Moderation',
      'results'               => 'Results',
      'backlogs'              => 'Backlogs',
      'promotions'            => 'Promotions',
      'exit-certification'    => 'Exit Certification',
      'student-credits'       => 'Student Credits (ABC)',
      'remuneration'          => 'Remuneration',
      'reports'               => 'Reports',
    ];
  }

  /**
   * List all DCOE users
   */
  public function index()
  {
    $dcoeUsers = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'dcoe');
    })
      ->with(['campuspermission.campus', 'dcoeMenuPermissions'])
      ->get();

    $campuses = Campus::all();
    $menuItems = self::availableMenuItems();

    return view('coe.dcoe-management.index', compact('dcoeUsers', 'campuses', 'menuItems'));
  }

  /**
   * Store a new DCOE user
   */
  public function store(Request $request)
  {
    $request->validate([
      'name'      => 'required|string|max:255',
      'email'     => 'required|string|email|max:255|unique:users,email',
      'password'  => 'required|string|min:6',
      'campus_id' => 'required|exists:campuses,id',
      'menus'     => 'required|array|min:1',
      'menus.*'   => 'string|in:' . implode(',', array_keys(self::availableMenuItems())),
    ]);

    $user = User::create([
      'name'     => $request->name,
      'email'    => $request->email,
      'password' => Hash::make($request->password),
      'status'   => 'ACTIVE',
      'otp_verification' => 1,
    ]);

    // Assign dcoe role
    UserHasRole::create([
      'user_id'   => $user->id,
      'role_name' => 'dcoe',
    ]);

    // Assign campus
    UserCampusSetting::create([
      'user_id'   => $user->id,
      'campus_id' => $request->campus_id,
    ]);

    // Assign menu permissions
    foreach ($request->menus as $slug) {
      DcoeMenuPermission::create([
        'user_id'   => $user->id,
        'menu_slug' => $slug,
      ]);
    }

    return redirect()->route('coe.dcoe.index')->with('success', 'Deputy COE account created successfully.');
  }

  /**
   * Show edit form for a DCOE user
   */
  public function edit($id)
  {
    $dcoeUser = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'dcoe');
    })
      ->with(['campuspermission.campus', 'dcoeMenuPermissions'])
      ->findOrFail($id);

    $campuses = Campus::all();
    $menuItems = self::availableMenuItems();
    $assignedMenus = $dcoeUser->dcoeMenuPermissions->pluck('menu_slug')->toArray();

    return view('coe.dcoe-management.edit', compact('dcoeUser', 'campuses', 'menuItems', 'assignedMenus'));
  }

  /**
   * Update permissions and campus for a DCOE user
   */
  public function update(Request $request, $id)
  {
    $dcoeUser = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'dcoe');
    })->findOrFail($id);

    $request->validate([
      'campus_id' => 'required|exists:campuses,id',
      'menus'     => 'required|array|min:1',
      'menus.*'   => 'string|in:' . implode(',', array_keys(self::availableMenuItems())),
    ]);

    // Update campus
    UserCampusSetting::updateOrCreate(
      ['user_id' => $dcoeUser->id],
      ['campus_id' => $request->campus_id]
    );

    // Sync menu permissions
    DcoeMenuPermission::where('user_id', $dcoeUser->id)->delete();
    foreach ($request->menus as $slug) {
      DcoeMenuPermission::create([
        'user_id'   => $dcoeUser->id,
        'menu_slug' => $slug,
      ]);
    }

    return redirect()->route('coe.dcoe.index')->with('success', 'Deputy COE permissions updated successfully.');
  }

  /**
   * Toggle DCOE active/inactive status
   */
  public function toggleStatus($id)
  {
    $dcoeUser = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'dcoe');
    })->findOrFail($id);

    $dcoeUser->status = $dcoeUser->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    $dcoeUser->save();

    return redirect()->route('coe.dcoe.index')->with('success', 'Deputy COE status updated.');
  }

  /**
   * Delete a DCOE user
   */
  public function destroy($id)
  {
    $dcoeUser = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'dcoe');
    })->findOrFail($id);

    DcoeMenuPermission::where('user_id', $dcoeUser->id)->delete();
    UserCampusSetting::where('user_id', $dcoeUser->id)->delete();
    UserHasRole::where('user_id', $dcoeUser->id)->delete();
    $dcoeUser->delete();

    return redirect()->route('coe.dcoe.index')->with('success', 'Deputy COE account deleted.');
  }
}
