<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Country;
use App\Models\MainProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdmissionController extends Controller
{
    function index()
    {
        $campus = Campus::all();
        $countries = Country::all();
        return view('admission.registration', [
            'campuses' => $campus,
            'countries' => $countries
        ]);
    }

    function admissionRegistration(Request $request)
    {
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'mobile_no' => 'required|digits:10|unique:users|regex:/^[0-9]+$/',
            'mail_id' => 'required|email|unique:users|max:255',
            'campus' => 'required',
            'applicationType' => 'required',
            'country' => 'required',
            'password' => 'required|min:6',
        ]);
    }

    function applicantLogin(Request $request)
    {
        $request->validate([

            'registered_no' => 'required',
            'registered_password' => 'required',
        ]);
    }


    function getMainPrograms(Request $request)
    {
        return MainProgram::where('campus_id', $request->campusId)->get();
    }


    function logout()
    {
        Auth::logout();
        return redirect('/');

        $this->applicantId = '';
    }
}
