<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Timetable Conflict Controls
    |--------------------------------------------------------------------------
    |
    | When enabled, faculty time overlap checks block assigning a faculty
    | to multiple concurrent classes. Set to false to allow parallel options
    | while still relying on slot_active for final active/inactive selection.
    |
    */
  'faculty_time_awareness' => env('TIMETABLE_FACULTY_TIME_AWARENESS', false),
];
