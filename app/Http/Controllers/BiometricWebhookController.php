<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiometricWebhookController extends Controller
{
    public function receiveAttendance(Request $request)
    {
        // 1. Raw body / form payload capture
        $rawContent = $request->getContent();
        $payload = $request->all();

        // If sent as nested JSON in event_log key
        if ($request->has('event_log')) {
            $payload = json_decode($request->input('event_log'), true);
        }

        Log::channel('single')->info('Hikvision Punch Event:', [
            'payload' => $payload,
            'raw' => $rawContent
        ]);

        // 2. Extract Event Details
        $accessEvent = $payload['AccessControllerEvent'] ?? $payload['AcsEvent'] ?? $payload;
        $employeeId = $accessEvent['employeeNoString'] ?? null;
        $punchTime = $accessEvent['dateTime'] ?? $accessEvent['time'] ?? now();

        if ($employeeId) {
            // Save to attendance table in ERP
            /*
            \App\Models\Attendance::create([
                'employee_id' => $employeeId,
                'punch_time' => $punchTime,
            ]);
            */
        }

        // 3. Return 200 OK
        return response()->json(['status' => 'OK'], 200);
    }
}
