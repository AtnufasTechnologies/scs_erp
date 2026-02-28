<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
  public function index()
  {
    return view('faculty.payroll');
  }

  public function download()
  {
    // Implement payroll download logic here
    return response()->download(storage_path('app/payroll/sample-payroll.pdf'));
  }
}
