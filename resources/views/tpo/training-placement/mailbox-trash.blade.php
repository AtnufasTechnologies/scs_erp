@include('includes.header')

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Trash</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.mailbox.index') }}">Inbox</a></li>
            <li class="breadcrumb-item active" aria-current="page">Trash</li>
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

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h6 class="mb-0 fw-bold">Trashed Threads</h6>
            <div class="d-flex gap-2">
              <a href="{{ route('tpo.training-placement.mailbox.index') }}" class="btn btn-outline-secondary btn-sm">Inbox</a>
              <a href="{{ route('tpo.training-placement.mailbox.sent') }}" class="btn btn-outline-primary btn-sm">Sent</a>
            </div>
          </div>

          <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label fw-semibold mb-1">Search Trash</label>
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
              <a href="{{ route('tpo.training-placement.mailbox.trash') }}" class="btn btn-outline-secondary w-100">Reset</a>
              @endif
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
          <span class="fw-bold">Trash Bin</span>
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
                <span class="badge bg-danger">Trashed</span>
                <div class="mt-2 d-flex gap-2 justify-content-end">
                  <a href="{{ route('tpo.training-placement.mailbox.show', $thread->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                  <form method="POST" action="{{ route('tpo.training-placement.mailbox.restore', $thread->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success">Restore</button>
                  </form>

                </div>
              </div>
            </div>
          </div>
          @empty
          <div class="alert alert-info mb-0">Trash is empty.</div>
          @endforelse
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')