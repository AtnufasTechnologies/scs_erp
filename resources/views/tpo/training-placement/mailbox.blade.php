@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Received Mails</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Inbox & Compose</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif
      @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      @endif

      <div class="row g-4">
        <div class="col-xl-12">
          <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="mb-0 fw-bold">Received Inbox</h6>
                <div class="d-flex gap-2">
                  <a href="{{ route('tpo.training-placement.mailbox.compose.page') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-pen me-1"></i>Compose
                  </a>
                  <a href="{{ route('tpo.training-placement.mailbox.sent') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-paper-plane me-1"></i>Sent
                  </a>
                  <a href="{{ route('tpo.training-placement.mailbox.trash') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash me-1"></i>Trash
                  </a>
                </div>
              </div>

              <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                  <label class="form-label fw-semibold mb-1">Search Received Mail</label>
                  <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Subject or company">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold mb-1">Filter Company</label>
                  <select name="company_id" class="form-select">
                    <option value="0">All Companies</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ (int) ($selectedCompanyId ?? 0) === (int) $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                  <button class="btn btn-primary w-100" type="submit">Apply</button>
                  @if(!empty($search) || (int)($selectedCompanyId ?? 0) > 0)
                  <a href="{{ route('tpo.training-placement.mailbox.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                  @endif
                </div>
              </form>
            </div>
          </div>

          <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
              <span class="fw-bold">Received Threads</span>
              <span class="badge bg-secondary">Total: {{ $threads->count() }}</span>
            </div>
            <div class="card-body">
              @forelse($threads as $thread)
              @php
              $latest = $thread->latestMessage;
              @endphp
              <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                  <div>
                    <h6 class="mb-1 fw-bold">{{ $thread->subject }}</h6>
                    <div class="small text-muted">Company: {{ $thread->company->company_name ?? 'N/A' }}</div>
                    <div class="small text-muted">
                      Last: {{ $thread->last_message_at ? $thread->last_message_at->format('d M Y h:i A') : 'N/A' }}
                      @if($latest)
                      | {{ \Illuminate\Support\Str::limit(strip_tags($latest->body_text ?? ''), 90) }}
                      @endif
                    </div>
                  </div>
                  <div class="text-end">
                    <span class="badge {{ $thread->last_message_direction === 'incoming' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">{{ ucfirst($thread->last_message_direction) }}</span>
                    <span class="badge {{ $thread->status === 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($thread->status) }}</span>
                    <div class="mt-2 d-flex gap-2 justify-content-end">
                      <a href="{{ route('tpo.training-placement.mailbox.show', $thread->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                      <form method="POST" action="{{ route('tpo.training-placement.mailbox.move-to-trash', $thread->id) }}" onsubmit="return confirm('Move this thread to trash?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Trash</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              @empty
              <div class="alert alert-info mb-0">No received mails found.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')