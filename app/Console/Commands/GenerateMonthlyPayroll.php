<?php

namespace App\Console\Commands;

use App\Models\AnnualSession;
use App\Models\Faculty;
use App\Models\FacultyLoan;
use App\Models\FacultySalaryMaster;
use App\Models\FacultySalarySlip;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyPayroll extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'payroll:generate-monthly 
                          {--month= : Month (01-12, default: current month)} 
                          {--year= : Year (default: current year)}
                          {--force : Force regeneration even if slips exist}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate monthly salary slips for all active faculty members';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $month = $this->option('month') ?? Carbon::now()->format('m');
    $year = $this->option('year') ?? Carbon::now()->format('Y');
    $force = $this->option('force');

    $this->info("Generating salary slips for {$year}-{$month}...");

    // Get current active annual session
    $currentSession = AnnualSession::where('status', 1)->first();

    // Get all faculties with active salary masters
    $salaryMasters = FacultySalaryMaster::with('faculty')
      ->active()
      ->whereHas('faculty', function ($q) {
        $q->where('IS_LEFT', 0);
      })
      ->get();

    $this->info("Found {$salaryMasters->count()} faculty members with active salary masters.");

    $created = 0;
    $skipped = 0;
    $errors = 0;

    $progressBar = $this->output->createProgressBar($salaryMasters->count());
    $progressBar->start();

    foreach ($salaryMasters as $salaryMaster) {
      try {
        // Check if slip already exists
        $exists = FacultySalarySlip::where('faculty_id', $salaryMaster->faculty_id)
          ->where('year', $year)
          ->where('month', $month)
          ->exists();

        if ($exists && !$force) {
          $skipped++;
          $progressBar->advance();
          continue;
        }

        if ($exists && $force) {
          // Delete existing slip
          FacultySalarySlip::where('faculty_id', $salaryMaster->faculty_id)
            ->where('year', $year)
            ->where('month', $month)
            ->delete();
        }

        // Get all active loans for this faculty
        $activeLoans = FacultyLoan::where('faculty_id', $salaryMaster->faculty_id)
          ->active()
          ->get();

        $totalLoanDeduction = $activeLoans->sum('emi_amount');

        // Generate salary slip number
        $slipNumber = 'SAL-' . $year . $month . '-' . str_pad($salaryMaster->faculty_id, 4, '0', STR_PAD_LEFT);

        // Create salary slip from salary master
        $salarySlip = new FacultySalarySlip();
        $salarySlip->faculty_id = $salaryMaster->faculty_id;
        $salarySlip->annual_session_id = $currentSession?->id;
        $salarySlip->month = $month;
        $salarySlip->year = $year;
        $salarySlip->salary_slip_number = $slipNumber;

        // Copy from salary master
        $salarySlip->basic_salary = $salaryMaster->basic_salary;
        $salarySlip->da = $salaryMaster->da;
        $salarySlip->hra = $salaryMaster->hra;
        $salarySlip->ta = $salaryMaster->ta;
        $salarySlip->medical_allowance = $salaryMaster->medical_allowance;
        $salarySlip->special_allowance = $salaryMaster->special_allowance;
        $salarySlip->other_allowances = $salaryMaster->other_allowances;

        $salarySlip->pf = $salaryMaster->pf;
        $salarySlip->esi = $salaryMaster->esi;
        $salarySlip->professional_tax = $salaryMaster->professional_tax;
        $salarySlip->tds = $salaryMaster->tds;
        $salarySlip->loan_deduction = $totalLoanDeduction;
        $salarySlip->other_deductions = $salaryMaster->other_deductions;

        $salarySlip->working_days = Carbon::parse("{$year}-{$month}-01")->daysInMonth;
        $salarySlip->present_days = $salarySlip->working_days;
        $salarySlip->leave_days = 0;
        $salarySlip->status = 'draft';

        $salarySlip->calculateTotals();
        $salarySlip->save();

        // Deduct EMI from all active loans
        foreach ($activeLoans as $loan) {
          $loan->deductEMI();
        }

        $created++;
      } catch (\Exception $e) {
        $facultyName = $salaryMaster->faculty->FIRST_NAME ?? 'Unknown';
        $this->error("\nError processing faculty {$facultyName}: " . $e->getMessage());
        $errors++;
      }

      $progressBar->advance();
    }

    $progressBar->finish();
    $this->newLine(2);

    $this->info("Salary slip generation completed!");
    $this->table(
      ['Status', 'Count'],
      [
        ['Created', $created],
        ['Skipped', $skipped],
        ['Errors', $errors],
        ['Total', $faculties->count()],
      ]
    );

    return Command::SUCCESS;
  }
}
