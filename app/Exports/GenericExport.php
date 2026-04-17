<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GenericExport implements FromView
{
  protected $data;
  protected $view;

  public function __construct($data, $view)
  {
    $this->data = $data;
    $this->view = $view;
  }

  public function view(): View
  {
    return view($this->view, ['data' => $this->data]);
  }
}
