<?php

namespace App\Http\Controllers;

use App\Models\AccountOfficePermission;
use App\Models\AdmissionApplicationPaymentLog;
use App\Models\Faculty;
use App\Models\FinancialYearMaster;
use App\Models\MenuMaster;
use App\Models\StudentPayment;
use App\Models\User;
use App\Models\UserCampusSetting;
use App\Models\UserHasRole;
use App\Models\UserMenuPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountOfficeController extends Controller
{
  /**
   * Account Office Incharge Dashboard
   */
  function dashboard(Request $request)
  {
    $activeFinancialYear = FinancialYearMaster::query()
      ->where('is_active', true)
      ->orderByDesc('id')
      ->first();

    $financialYears = FinancialYearMaster::query()
      ->orderByDesc('start_date')
      ->orderByDesc('id')
      ->get(['id', 'title', 'start_date', 'end_date', 'is_active']);

    $selectedFinancialYearId = (int) $request->query('financial_year_id', 0);
    $selectedFinancialYear = null;

    if ($selectedFinancialYearId > 0) {
      $selectedFinancialYear = $financialYears->firstWhere('id', $selectedFinancialYearId);
    }

    if (!$selectedFinancialYear) {
      $selectedFinancialYear = $activeFinancialYear;
      $selectedFinancialYearId = (int) ($activeFinancialYear->id ?? 0);
    }

    $activeFinancialYearLabel = $selectedFinancialYear->title ?? 'Current active financial year';

    $applyActiveFinancialYearFilter = function ($query, string $dateColumn) use ($selectedFinancialYear) {
      if (!$selectedFinancialYear) {
        return $query;
      }

      return $query
        ->whereDate($dateColumn, '>=', $selectedFinancialYear->start_date)
        ->whereDate($dateColumn, '<=', $selectedFinancialYear->end_date);
    };

    // Assistants summary
    $assistants = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'account-office-assistant');
    })->with('userroletype')->latest()->get();

    $totalAssistants    = $assistants->count();
    $activeAssistants   = $assistants->where('status', 'ACTIVE')->count();
    $inactiveAssistants = $assistants->where('status', 'INACTIVE')->count();

    // Account modules
    $accountModules = MenuMaster::where('module_type', 'accounts')->where('status', 'active')->get();
    // Fee payment analytics
    $studentFeeBaseQuery = StudentPayment::query()->where('status', 'success');
    $applyActiveFinancialYearFilter($studentFeeBaseQuery, 'transaction_date');
    $totalStudentFeeCollected = (clone $studentFeeBaseQuery)->sum('amount');
    $totalLateFineCollected = (clone $studentFeeBaseQuery)->sum('late_fee_amount');

    $todayCollectionQuery = StudentPayment::query()->where('status', 'success')
      ->whereDate('transaction_date', today());
    $applyActiveFinancialYearFilter($todayCollectionQuery, 'transaction_date');
    $todayCollection = $todayCollectionQuery->sum('amount');

    // Student fee trend — last 30 days grouped by date
    $feePaymentTrendQuery = StudentPayment::query()->where('status', 'success')
      ->where('transaction_date', '>=', now()->subDays(29)->toDateString())
      ->selectRaw('transaction_date as date, SUM(amount) as total, COUNT(*) as count')
      ->groupBy('transaction_date')
      ->orderBy('transaction_date');
    $applyActiveFinancialYearFilter($feePaymentTrendQuery, 'transaction_date');
    $feePaymentTrend = $feePaymentTrendQuery->get();

    // Admission application payment analytics
    $admissionFeeBaseQuery = AdmissionApplicationPaymentLog::query()->where('status', 'success');
    $applyActiveFinancialYearFilter($admissionFeeBaseQuery, 'created_at');
    $totalAdmissionFeeCollected = (clone $admissionFeeBaseQuery)->sum('amount');

    $admissionPaymentTrendQuery = AdmissionApplicationPaymentLog::query()->where('status', 'success')
      ->where('created_at', '>=', now()->subDays(29))
      ->selectRaw('DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count')
      ->groupBy('date')
      ->orderBy('date');
    $applyActiveFinancialYearFilter($admissionPaymentTrendQuery, 'created_at');
    $admissionPaymentTrend = $admissionPaymentTrendQuery->get();

    // Faculty count
    $totalFaculty = Faculty::where('IS_LEFT', 0)->count();

    // Recent 10 successful student fee transactions
    $recentTransactionsQuery = StudentPayment::with('studentmaster')
      ->where('status', 'success')
      ->orderBy('transaction_date', 'desc')
      ->limit(10);
    $applyActiveFinancialYearFilter($recentTransactionsQuery, 'transaction_date');
    $recentTransactions = $recentTransactionsQuery->get();

    // Recent 5 admission payments
    $recentAdmissionPaymentsQuery = AdmissionApplicationPaymentLog::with('applicationmaster.registrationmaster')
      ->where('status', 'success')
      ->latest()
      ->limit(10);
    $applyActiveFinancialYearFilter($recentAdmissionPaymentsQuery, 'created_at');
    $recentAdmissionPayments = $recentAdmissionPaymentsQuery->get();

    return view('admin.accounts.incharge-dashboard', [
      'assistants'                 => $assistants,
      'totalAssistants'            => $totalAssistants,
      'activeAssistants'           => $activeAssistants,
      'inactiveAssistants'         => $inactiveAssistants,
      'totalStudentFeeCollected'   => $totalStudentFeeCollected,
      'totalLateFineCollected'     => $totalLateFineCollected,
      'todayCollection'            => $todayCollection,
      'feePaymentTrend'            => $feePaymentTrend,
      'totalAdmissionFeeCollected' => $totalAdmissionFeeCollected,
      'admissionPaymentTrend'      => $admissionPaymentTrend,
      'activeFinancialYearLabel'   => $activeFinancialYearLabel,
      'selectedFinancialYearId'    => $selectedFinancialYearId,
      'financialYears'             => $financialYears,
      'totalFaculty'               => $totalFaculty,
      'recentTransactions'         => $recentTransactions,
      'recentAdmissionPayments'    => $recentAdmissionPayments,
      'accountModules'             => $accountModules,
    ]);
  }

  /**
   * Show assistant access management page
   */
  function assistantAccess()
  {
    $assistants = User::whereHas('userroletype', function ($q) {
      $q->where('role_name', 'account-office-assistant');
    })->with(['userroletype', 'menupermission.menu_master'])->latest()->get();

    $accountModules = MenuMaster::where('module_type', 'accounts')->where('status', 'active')->get();

    return view('admin.accounts.assistant-access', [
      'assistants' => $assistants,
      'accountModules' => $accountModules,
    ]);
  }

  /**
   * Create a new account office assistant user
   */
  function createAssistant(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:6',
      'modules' => 'required|array|min:1',
    ]);

    // Create user
    $rec = new User();
    $rec->name = $request->name;
    $rec->email = $request->email;
    $rec->password = Hash::make($request->password);
    $rec->otp_verification = 1;
    $rec->status = 'ACTIVE';
    $rec->save();

    // Assign role
    UserHasRole::create([
      'user_id' => $rec->id,
      'role_name' => 'account-office-assistant',
    ]);

    // Assign selected account module permissions
    foreach ($request->modules as $menuMasterId) {
      UserMenuPermission::create([
        'user_id' => $rec->id,
        'menu_master_id' => $menuMasterId,
        'permission_name' => 'accounts',
      ]);

      AccountOfficePermission::create([
        'assistant_user_id' => $rec->id,
        'granted_by_user_id' => Auth::id(),
        'menu_master_id' => $menuMasterId,
      ]);
    }

    return redirect()->back()->with('success', 'Account Office Assistant created successfully');
  }

  /**
   * Update assistant module permissions
   */
  function updateAssistantPermissions(Request $request, $id)
  {
    $request->validate([
      'modules' => 'nullable|array',
    ]);

    $assistant = User::findOrFail($id);
    $modules = $request->modules ?? [];

    // Remove old account module permissions
    $accountMenuIds = MenuMaster::where('module_type', 'accounts')->pluck('id');
    UserMenuPermission::where('user_id', $id)
      ->whereIn('menu_master_id', $accountMenuIds)
      ->delete();

    AccountOfficePermission::where('assistant_user_id', $id)->delete();

    // Re-assign selected permissions
    foreach ($modules as $menuMasterId) {
      UserMenuPermission::create([
        'user_id' => $id,
        'menu_master_id' => $menuMasterId,
        'permission_name' => 'accounts',
      ]);

      AccountOfficePermission::create([
        'assistant_user_id' => $id,
        'granted_by_user_id' => Auth::id(),
        'menu_master_id' => $menuMasterId,
      ]);
    }

    return redirect()->back()->with('success', 'Permissions updated successfully');
  }

  /**
   * Toggle assistant status (block/allow access)
   */
  function toggleAssistantStatus($id)
  {
    $user = User::findOrFail($id);
    $user->status = $user->status == 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
    $user->save();

    return redirect()->back()->with('success', 'Status updated successfully');
  }

  /**
   * Delete assistant access
   */
  function deleteAssistant($id)
  {
    $user = User::findOrFail($id);
    UserMenuPermission::where('user_id', $id)->delete();
    AccountOfficePermission::where('assistant_user_id', $id)->delete();
    UserHasRole::where('user_id', $id)->delete();
    UserCampusSetting::where('user_id', $id)->delete();
    $user->delete();

    return redirect()->back()->with('success', 'Assistant access removed successfully');
  }

  /**
   * Remove a single permission from assistant
   */
  function removeAssistantPermission($id)
  {
    $permission = UserMenuPermission::findOrFail($id);
    AccountOfficePermission::where('assistant_user_id', $permission->user_id)
      ->where('menu_master_id', $permission->menu_master_id)
      ->delete();
    $permission->delete();

    return redirect()->back()->with('success', 'Permission removed');
  }
}
