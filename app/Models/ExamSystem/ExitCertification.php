<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExitCertification extends Model
{
  protected $table = 'exit_certifications';

  protected $fillable = [
    'exam_student_id',
    'program_id',
    'exit_level',
    'certificate_no',
    'total_credits_earned',
    'credits_required',
    'cgpa',
    'semesters_completed',
    'status',
    'issue_date',
    'approved_by',
    'issued_by',
    'credit_summary',
    'remarks',
  ];

  protected $casts = [
    'cgpa' => 'decimal:2',
    'issue_date' => 'date',
    'credit_summary' => 'array',
  ];

  public const LEVELS = [
    'certificate' => ['label' => 'Certificate', 'min_credits' => 40, 'min_semesters' => 2],
    'diploma' => ['label' => 'Diploma', 'min_credits' => 80, 'min_semesters' => 4],
    'degree' => ['label' => 'Degree', 'min_credits' => 120, 'min_semesters' => 6],
    'honors' => ['label' => 'Honours', 'min_credits' => 160, 'min_semesters' => 8],
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function program(): BelongsTo
  {
    return $this->belongsTo(Program::class, 'program_id');
  }

  public function approver(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'approved_by');
  }

  public function issuer(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'issued_by');
  }

  public static function getLevelConfig(string $level): ?array
  {
    return self::LEVELS[$level] ?? null;
  }

  public static function generateCertificateNo(string $level): string
  {
    $prefix = strtoupper(substr($level, 0, 3));
    $year = date('Y');
    $count = self::where('exit_level', $level)->whereYear('created_at', $year)->count() + 1;
    return "SCS-{$prefix}-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
  }
}
