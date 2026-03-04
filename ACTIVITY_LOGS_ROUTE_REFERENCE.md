# User Activity Logs - Route & Operations Reference

## Exact Route Information

### Route Name

```
admin.user.activity-logs
```

### Route URL

```
GET /erp/admin/access-control/activity-logs
```

### Full URL (Production)

```
https://yourapp.com/erp/admin/access-control/activity-logs
```

### Route Definition (web.php)

```php
Route::get('activity-logs', [AccessController::class, 'userActivityLogs'])->name('admin.user.activity-logs');
```

### Controller Method

```php
AccessController@userActivityLogs()
```

---

## Access Path from Admin Panel

**Sidebar Menu Location:**

```
Security and Authorization → User Control → Activity Logs
```

**Navigation Trail:**

1. Admin Dashboard
2. Left Sidebar → Security and Authorization (shield icon)
3. Click → Activity Logs (history icon)

---

## Database Table

### Table Name

```
user_activity_logs
```

### Table Structure

```sql
CREATE TABLE user_activity_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT UNSIGNED (FK to users),
  event VARCHAR(20) -- 'created', 'updated', 'deleted'
  auditable_type VARCHAR(255) -- Model class (e.g., App\Models\Subject)
  auditable_id VARCHAR(255) -- ID of the record being tracked
  description VARCHAR(255) -- Human readable action
  ip_address VARCHAR(45),
  method VARCHAR(12) -- HTTP method (POST, PUT, DELETE, etc.)
  url TEXT -- Full request URL
  user_agent TEXT -- Browser/Client info
  old_values JSON -- Previous values (before change)
  new_values JSON -- New/changed values
  created_at TIMESTAMP

  INDEX: user_id, created_at
  INDEX: event, created_at
  INDEX: auditable_type, auditable_id
)
```

---

## Tracked Operations

### Event Types Supported

1. **created** - New record inserted
2. **updated** - Existing record modified
3. **deleted** - Record soft/hard deleted

### Captured Data Per Action

#### For CREATE:

```json
{
  "event": "created",
  "old_values": {},
  "new_values": { "all_fields_from_new_record" }
}
```

#### For UPDATE:

```json
{
    "event": "updated",
    "old_values": { "field1": "old_value", "field2": "old_value" },
    "new_values": { "field1": "new_value", "field2": "new_value" }
}
```

#### For DELETE:

```json
{
  "event": "deleted",
  "old_values": { "all_fields_from_deleted_record" },
  "new_values": {}
}
```

### Sensitive Fields (Automatically Masked)

The following fields are always redacted with `[REDACTED]`:

- `password`
- `remember_token`
- `token`
- `api_token`
- `otp`

---

## Query Filter Parameters

### Available Query Parameters

```php
?user_id=5              // Filter by user ID
&event=created          // Filter by event type (created|updated|deleted)
&from_date=2026-03-01   // Start date (YYYY-MM-DD)
&to_date=2026-03-02     // End date (YYYY-MM-DD)
&keyword=Subject        // Search keyword in model/ID/URL
```

### Example URLs

#### View all activity for User ID 5:

```
/erp/admin/access-control/activity-logs?user_id=5
```

#### View only CREATE operations between two dates:

```
/erp/admin/access-control/activity-logs?event=created&from_date=2026-03-01&to_date=2026-03-02
```

#### Search for all Subject model activities:

```
/erp/admin/access-control/activity-logs?keyword=Subject
```

#### Combined filters:

```
/erp/admin/access-control/activity-logs?user_id=5&event=updated&from_date=2026-03-01&keyword=Subject
```

---

## Global Event Listeners (AppServiceProvider)

These events are automatically triggered for all Eloquent models in `App\Models\`:

### Event Hooks (bootstrap app/Providers/AppServiceProvider.php)

```php
Event::listen('eloquent.created: *', function ($eventName, $data) {
    // Logs whenever any model is created
    UserActivityLogger::log('created', $data[0]);
});

Event::listen('eloquent.updated: *', function ($eventName, $data) {
    // Logs whenever any model is updated
    UserActivityLogger::log('updated', $data[0]);
});

Event::listen('eloquent.deleted: *', function ($eventName, $data) {
    // Logs whenever any model is deleted
    UserActivityLogger::log('deleted', $data[0]);
});
```

---

## Logger Service

### Service Class

```php
App\Services\UserActivityLogger
```

### Public Method

```php
UserActivityLogger::log(string $event, Model $model): void
```

### Usage Example (Manual Logging - if needed)

```php
use App\Services\UserActivityLogger;

$subject = Subject::create(['code' => 'CS101', 'title' => 'Computer Science']);
// Auto-logged via Eloquent event, but can also call manually:
UserActivityLogger::log('created', $subject);
```

---

## Models Tracked

All models extending `Illuminate\Database\Eloquent\Model` in `App\Models\` namespace are automatically tracked:

- Subject
- User
- Faculty
- StudentMaster
- BatchMaster
- AdmissionApplication
- And all other App\Models\* classes

### Excluded from Tracking

- `App\Models\UserActivityLog` (logging itself would create infinite loop)

---

## Web UI Features

### List Page Features

✓ Paginated results (50 per page)
✓ Search & filter form
✓ User dropdown selector
✓ Event type dropdown (created/updated/deleted)
✓ Date range picker
✓ Keyword search (model name, ID, URL)
✓ Clear "Reset" button
✓ Color-coded badges (success=created, warning=updated, danger=deleted)
✓ JSON pretty-print for old_values and new_values
✓ IP address display
✓ Timestamps in `d M Y h:i A` format

### Data Displayed Per Row

| Column     | Content                    |
| ---------- | -------------------------- |
| #          | Row number                 |
| Time       | When action occurred       |
| User       | User name & email          |
| Event      | CREATE/UPDATE/DELETE badge |
| Model      | Class name (e.g., Subject) |
| Record ID  | ID of affected record      |
| Old Values | JSON of previous state     |
| New Values | JSON of new state          |
| IP         | IP address of request      |

---

## Database Query Examples

### Get all activities for a user

```php
UserActivityLog::where('user_id', 5)->latest('id')->get();
```

### Get all updates made today

```php
UserActivityLog::where('event', 'updated')
    ->whereDate('created_at', today())
    ->get();
```

### Get deletions from a specific model

```php
UserActivityLog::where('auditable_type', 'App\Models\Subject')
    ->where('event', 'deleted')
    ->get();
```

### Track changes to a specific record

```php
UserActivityLog::where('auditable_type', 'App\Models\Subject')
    ->where('auditable_id', 42)
    ->latest('id')
    ->get();
```

---

## Eloquent Relationships

### On User Model

```php
// Get all activity logs for a user
$user->activityLogs();

// Example
$user = User::find(5);
$logs = $user->activityLogs()->latest('id')->get();
```

### On UserActivityLog Model

```php
// Get the user who performed the action
$log->user();

// Example
$log = UserActivityLog::first();
$actor = $log->user()->first();
echo $actor->name; // "John Doe"
```

---

## Important Notes

1. **Performance**: All database queries are indexed on (user_id, created_at), (event, created_at), and (auditable_type, auditable_id) for fast filtering.

2. **No Updated_at Filtering**: The `updated_at` field is excluded from tracked changes to avoid noise in logs.

3. **Request Context**: Each log captures:
    - IP Address
    - HTTP Method (POST, PUT, DELETE, etc.)
    - Full URL
    - User Agent (browser/client details)

4. **Authentication Required**: All activity must occur within an authenticated session (captured via `Auth::id()`).

5. **Unauthenticated Actions**: Activities from unauthenticated requests will have `user_id = NULL`.

---

## Testing the Feature

### Create a test activity

```php
// Create a subject (automatically logged)
$subject = Subject::create([
    'code' => 'TEST101',
    'title' => 'Test Subject',
    'campus_id' => 1,
    'main_program_type' => 'UG'
]);

// Check the log
$log = UserActivityLog::where('auditable_id', $subject->id)->first();
dd($log);
```

### View in Admin Panel

```
URL: http://yourapp.com/erp/admin/access-control/activity-logs
Navigate: Admin Dashboard → Security and Authorization → Activity Logs
```

---

## Logging Lifecycle

```
1. Model is created/updated/deleted
        ↓
2. Eloquent fires appropriate event (eloquent.created, eloquent.updated, eloquent.deleted)
        ↓
3. AppServiceProvider listener catches event
        ↓
4. UserActivityLogger::log() is called
        ↓
5. Service validates: Is it an App\Models\ class? Does table exist?
        ↓
6. Data is sanitized (passwords masked)
        ↓
7. Record is inserted into user_activity_logs table
        ↓
8. User can view in /erp/admin/access-control/activity-logs
```

---

## Cache & Performance Considerations

- Table has proper indexes for common queries
- Paginated to 50 rows per page (configurable)
- JSON fields properly indexed
- No N+1 queries (uses eager loading with `with('user')`)
- Old/New values are JSON for efficient comparison

---

**Last Updated**: March 2, 2026  
**Route Version**: 1.0  
**Feature Status**: Active & Production Ready
