<?php

namespace App\Services\ExamSystem;

use App\Models\ExamSystem\ProgramRegulation;

class ProgramRegulationService
{
  protected $regulation;

  public function __construct(ProgramRegulation $regulation)
  {
    $this->regulation = $regulation;
  }

  public function getWeightage(): array
  {
    switch ($this->regulation->regulation_type) {
      case 'NEP':
        return [
          'core' => 60,
          'elective' => 30,
          'project' => 10,
        ];
      case 'AICTE':
        return [
          'core' => 70,
          'internal' => 20,
          'viva' => 10,
        ];
      case 'PG':
        return [
          'dissertation' => 40,
          'research' => 30,
          'theory' => 30,
        ];
      case 'ITEP':
        return [
          'teaching_practice' => 40,
          'core' => 60,
        ];
      default:
        return [];
    }
  }

  public function getPromotionRule(): string
  {
    switch ($this->regulation->regulation_type) {
      case 'NEP':
        return 'Credit-based promotion';
      case 'AICTE':
        return 'Strict pass with ATKT';
      case 'PG':
        return 'Dissertation and research required';
      case 'ITEP':
        return 'Teaching practice mandatory';
      default:
        return 'Standard promotion';
    }
  }

  public function isExitAllowed(): bool
  {
    switch ($this->regulation->regulation_type) {
      case 'NEP':
        return true;
      case 'AICTE':
      case 'PG':
      case 'ITEP':
        return false;
      default:
        return false;
    }
  }

  public function getBacklogRule(): string
  {
    switch ($this->regulation->regulation_type) {
      case 'NEP':
        return 'Flexible backlog attempts';
      case 'AICTE':
        return 'ATKT (Allowed to Keep Terms)';
      case 'PG':
        return 'Strict, limited attempts';
      case 'ITEP':
        return 'No backlog in teaching practice';
      default:
        return 'Standard backlog rule';
    }
  }
}
