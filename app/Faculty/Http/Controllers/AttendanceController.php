<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
  public function index()
  {
    return view('faculty.attendance');
  }
}
