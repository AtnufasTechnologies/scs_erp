<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\SubjectFacultyMaster;
use Illuminate\Http\Request;

class FacultyApiController extends Controller
{
    function profile(int $userId)
    {
        $facultyId = SubjectFacultyMaster::where('access_id', $userId)->value('faculty_id');
        $faculty = Faculty::findOrFail($facultyId);
        if ($faculty) {
            return response()->json([
                'status' => 'success',
                'data' => $faculty
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }
    }
}
