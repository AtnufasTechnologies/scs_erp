@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Mail Thread</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.mailbox.index') }}">Inbox</a></li>
            <li class="breadcrumb-item active" aria-current="page">Thread</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div id="threadToastData" class="d-none" data-success="{{ e((string) session('success', '')) }}" data-error="{{ e((string) session('error', '')) }}"></div>

      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h5 class="mb-1 fw-bold">{{ $thread->subject }}</h5>
            <div class="text-muted small">Company: {{ $thread->company->company_name ?? 'N/A' }} ({{ $thread->company->mailing_email ?? 'N/A' }})</div>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('tpo.training-placement.mailbox.compose.page') }}" class="btn btn-primary btn-sm">Compose</a>
            <a href="{{ route('tpo.training-placement.mailbox.sent') }}" class="btn btn-outline-primary btn-sm">Sent</a>
            <a href="{{ route('tpo.training-placement.mailbox.trash') }}" class="btn btn-outline-danger btn-sm">Trash</a>
            @if($thread->status !== 'trash')
            <form method="POST" action="{{ route('tpo.training-placement.mailbox.move-to-trash', $thread->id) }}" onsubmit="return confirm('Move this thread to trash?');">
              @csrf
              <button class="btn btn-outline-danger btn-sm" type="submit">Move to Trash</button>
            </form>
            @else
            <form method="POST" action="{{ route('tpo.training-placement.mailbox.restore', $thread->id) }}">
              @csrf
              <button class="btn btn-outline-success btn-sm" type="submit">Restore</button>
            </form>
            <form method="POST" action="{{ route('tpo.training-placement.mailbox.permanent-delete', $thread->id) }}" onsubmit="return confirm('Permanently delete this thread and attachments?');">
              @csrf
              @method('DELETE')
              <button class="btn btn-danger btn-sm" type="submit">Delete Permanently</button>
            </form>
            @endif
            <a href="{{ route('tpo.training-placement.mailbox.index') }}" class="btn btn-outline-secondary btn-sm">Back to Inbox</a>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent fw-bold">Conversation</div>
        <div class="card-body">
          @forelse($thread->messages as $message)
          <div class="border rounded p-3 mb-3 {{ $message->sender_type === 'tpo' ? 'bg-light' : '' }}">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
              <div>
                <span class="badge {{ $message->sender_type === 'tpo' ? 'bg-primary' : 'bg-warning text-dark' }}">{{ strtoupper($message->sender_type) }}</span>
                <strong class="ms-2">{{ $message->sender_name ?: ($message->sender_type === 'tpo' ? 'TPO' : 'Company') }}</strong>
                <small class="text-muted">{{ $message->sender_email ? ' <' . $message->sender_email . '>' : '' }}</small>
              </div>
              <small class="text-muted">
                {{ optional($message->sent_at ?? $message->received_at ?? $message->created_at)->format('d M Y h:i A') }}
              </small>
            </div>
            @if($message->recipient_to)
            <div class="small text-muted mb-1"><strong>To:</strong> {{ $message->recipient_to }}</div>
            @endif
            @if($message->recipient_cc)
            <div class="small text-muted mb-1"><strong>CC:</strong> {{ $message->recipient_cc }}</div>
            @endif
            @if($message->recipient_bcc)
            <div class="small text-muted mb-1"><strong>BCC:</strong> {{ $message->recipient_bcc }}</div>
            @endif
            <div class="mt-2">{!! $message->body_html ?: nl2br(e($message->body_text ?? '')) !!}</div>

            @if($message->attachments->isNotEmpty())
            <div class="mt-2">
              <strong class="small">Attachments:</strong>
              <ul class="mb-0">
                @foreach($message->attachments as $attachment)
                <li>
                  <a href="{{ \Illuminate\Support\Facades\Storage::disk('s3')->url($attachment->file_path) }}" target="_blank">{{ $attachment->file_name }}</a>
                </li>
                @endforeach
              </ul>
            </div>
            @endif

            <div class="mt-3 d-flex gap-2">
              <a href="{{ route('tpo.training-placement.mailbox.compose.page', ['forward_message_id' => $message->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-share me-1"></i>Forward
              </a>
            </div>
          </div>
          @empty
          <div class="alert alert-info mb-0">No messages yet.</div>
          @endforelse
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent fw-bold">Reply</div>
        <div class="card-body">
          @if($thread->status === 'trash')
          <div class="alert alert-warning mb-0">This thread is in trash. Restore it to send a reply.</div>
          @else
          <form method="POST" action="{{ route('tpo.training-placement.mailbox.reply', $thread->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-2 mb-2">
              <div class="col-md-6">
                <label class="form-label">CC</label>
                <input type="text" name="cc" class="form-control" placeholder="mail1@example.com, mail2@example.com">
              </div>
              <div class="col-md-6">
                <label class="form-label">BCC</label>
                <input type="text" name="bcc" class="form-control">
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label">Message</label>
              <textarea name="message" class="form-control" rows="6" required></textarea>
            </div>
            <div class="mb-2">
              <label class="form-label">Attachments</label>
              <input type="file" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.zip">
            </div>
            <div class="row g-2 align-items-end">
              <div class="col-md-4">
                <label class="form-label">Thread Status</label>
                <select name="status" class="form-select">
                  <option value="open" {{ $thread->status === 'open' ? 'selected' : '' }}>Open</option>
                  <option value="closed" {{ $thread->status === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
              </div>
              <div class="col-md-8 text-md-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-reply me-1"></i>Send Reply</button>
              </div>
            </div>
          </form>
          @endif
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swal === 'undefined') {
      return;
    }

    const toastData = document.getElementById('threadToastData');
    const successMessage = toastData ? (toastData.getAttribute('data-success') || '').trim() : '';
    const errorMessage = toastData ? (toastData.getAttribute('data-error') || '').trim() : '';

    if (successMessage) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Mail Sent',
        text: successMessage,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
      });
    }

    if (errorMessage) {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: 'Mail Not Sent',
        text: errorMessage,
        showConfirmButton: false,
        timer: 4200,
        timerProgressBar: true,
      });
    }
  });
</script>