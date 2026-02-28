<?php

namespace App\Faculty\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
  public function index()
  {
    return view('faculty.timetable');
  }
}
