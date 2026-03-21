<?php

namespace App\Console\Commands;

use App\Models\AnnualSession;
use App\Models\Faculty;
use App\Models\FacultyLoan;
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

    // Get all active faculties
    $faculties = Faculty::where('IS_LEFT', 0)->get();
    $this->info("Found {$faculties->count()} active faculty members.");

    $created = 0;
    $skipped = 0;
    $errors = 0;

    $progressBar = $this->output->createProgressBar($faculties->count());
    $progressBar->start();

    foreach ($faculties as $faculty) {
      try {
        // Check if slip already exists
        $exists = FacultySalarySlip::where('faculty_id', $faculty->id)
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
          FacultySalarySlip::where('faculty_id', $faculty->id)
            ->where('year', $year)
            ->where('month', $month)
            ->delete();
        }

        // Get last salary slip for this faculty to use as template
        $lastSlip = FacultySalarySlip::where('faculty_id', $faculty->id)
          ->orderBy('year', 'desc')
          ->orderBy('month', 'desc')
          ->first();

        $basicSalary = $lastSlip ? $lastSlip->basic_salary : 30000;

        // Get active loan EMI
        $loanDeduction = 0;
        $activeLoan = FacultyLoan::where('faculty_id', $faculty->id)
          ->active()
          ->first();

        if ($activeLoan) {
          $loanDeduction = $activeLoan->emi_amount;
        }

        // Generate salary slip number
        $slipNumber = 'SAL-' . $year . $month . '-' . str_pad($faculty->id, 4, '0', STR_PAD_LEFT);

        // Create salary slip
        $salarySlip = new FacultySalarySlip();
        $salarySlip->faculty_id = $faculty->id;
        $salarySlip->annual_session_id = $currentSession?->id;
        $salarySlip->month = $month;
        $salarySlip->year = $year;
        $salarySlip->salary_slip_number = $slipNumber;

        // Copy from last slip or use defaults
        if ($lastSlip) {
          $salarySlip->basic_salary = $basicSalary;
          $salarySlip->da = $lastSlip->da;
          $salarySlip->hra = $lastSlip->hra;
          $salarySlip->ta = $lastSlip->ta;
          $salarySlip->medical_allowance = $lastSlip->medical_allowance;
          $salarySlip->special_allowance = $lastSlip->special_allowance;
          $salarySlip->other_allowances = $lastSlip->other_allowances;

          $salarySlip->pf = $lastSlip->pf;
          $salarySlip->esi = $lastSlip->esi;
          $salarySlip->professional_tax = $lastSlip->professional_tax;
          $salarySlip->tds = $lastSlip->tds;
          $salarySlip->other_deductions = $lastSlip->other_deductions;
        } else {
          $salarySlip->basic_salary = $basicSalary;
          // Set default allowances as percentage of basic
          $salarySlip->da = $basicSalary * 0.10; // 10% DA
          $salarySlip->hra = $basicSalary * 0.20; // 20% HRA
          $salarySlip->ta = 1000; // Fixed TA

          // Set default deductions
          $salarySlip->pf = $basicSalary * 0.12; // 12% PF
        }

        $salarySlip->loan_deduction = $loanDeduction;
        $salarySlip->working_days = Carbon::parse("{$year}-{$month}-01")->daysInMonth;
        $salarySlip->present_days = $salarySlip->working_days;
        $salarySlip->leave_days = 0;
        $salarySlip->status = 'draft';

        $salarySlip->calculateTotals();
        $salarySlip->save();

        // Deduct EMI from loan
        if ($activeLoan && $loanDeduction > 0) {
          $activeLoan->deductEMI();
        }

        $created++;
      } catch (\Exception $e) {
        $this->error("\nError processing faculty {$faculty->FIRST_NAME}: " . $e->getMessage());
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
