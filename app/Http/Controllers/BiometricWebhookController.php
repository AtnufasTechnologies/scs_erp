<?php

namespace App\Http\Controllers;

use App\Models\BiometricAttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BiometricWebhookController extends Controller
{
    public function receiveAttendance(Request $request)
    {
        $rawContent = $request->getContent();
        $payload = $request->all();

        if ($request->has('event_log')) {
            $decoded = json_decode((string) $request->input('event_log'), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        Log::channel('single')->info('Hikvision Punch Event:', [
            'payload' => $payload,
            'raw' => $rawContent
        ]);

        $accessEvent = $payload['AccessControllerEvent'] ?? $payload['AcsEvent'] ?? $payload;

        $employeeNo = $accessEvent['employeeNoString']
            ?? $accessEvent['employeeNo']
            ?? $accessEvent['cardNo']
            ?? null;

        $punchTimeRaw = $accessEvent['dateTime']
            ?? $accessEvent['time']
            ?? $payload['dateTime']
            ?? null;

        $punchTime = null;
        if (!empty($punchTimeRaw)) {
            try {
                $punchTime = Carbon::parse((string) $punchTimeRaw);
            } catch (\Throwable $e) {
                $punchTime = now();
            }
        }

        BiometricAttendanceLog::create([
            'employee_no' => $employeeNo,
            'punch_time' => $punchTime,
            'event_type' => $accessEvent['eventType'] ?? $accessEvent['majorEventType'] ?? null,
            'device_ip' => $accessEvent['ipAddress'] ?? $payload['ipAddress'] ?? null,
            'device_name' => $accessEvent['deviceName'] ?? $payload['deviceName'] ?? null,
            'verify_mode' => $accessEvent['currentVerifyMode'] ?? $accessEvent['verifyMode'] ?? null,
            'door_no' => isset($accessEvent['doorNo']) ? (string) $accessEvent['doorNo'] : null,
            'source_ip' => $request->ip(),
            'payload' => is_array($payload) ? $payload : null,
            'raw_payload' => $rawContent,
        ]);

        return response()->json([
            'status' => 'OK',
            'saved' => true,
            'employee_no' => $employeeNo,
        ], 200);
    }
}
