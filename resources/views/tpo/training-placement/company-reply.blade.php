<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Reply to TPO</title>
  <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0">
          <div class="card-header bg-white">
            <h5 class="mb-1">Reply to TPO</h5>
            <small class="text-muted">Subject: {{ $thread->subject }}</small>
          </div>
          <div class="card-body">
            <p class="text-muted mb-3">Company: {{ $thread->company->company_name ?? 'N/A' }}</p>

            <form method="POST" action="{{ route('tpo.training-placement.company-reply.submit', ['thread' => $thread->id, 'token' => $token, 'signature' => request('signature'), 'expires' => request('expires')]) }}" enctype="multipart/form-data">
              @csrf
              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label">Your Name</label>
                  <input type="text" name="sender_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Your Email</label>
                  <input type="email" name="sender_email" class="form-control" required>
                </div>
              </div>
              <div class="mb-2">
                <label class="form-label">Message</label>
                <textarea name="message" rows="7" class="form-control" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Attachments</label>
                <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.zip">
              </div>
              <button type="submit" class="btn btn-primary">Send Reply</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>