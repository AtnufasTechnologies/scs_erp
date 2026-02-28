<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FacultyDashboardController extends Controller
{
  public function index()
  {
    return view('faculty.dashboard');
  }
}
