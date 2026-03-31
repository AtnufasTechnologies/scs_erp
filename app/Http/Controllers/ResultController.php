<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResultController extends Controller
{
  // Stub methods for API routes
  public function calculate(Request $request)
  {
    return response()->json(['message' => 'Result calculation endpoint (stub)']);
  }
}
