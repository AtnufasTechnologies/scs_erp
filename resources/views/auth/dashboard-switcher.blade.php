@include('includes.header')

<div class="wrapper">
  <main class="page-content">
    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <h4 class="mb-2">Choose Dashboard</h4>
              <p class="text-muted mb-4">This account has multiple roles. Select where you want to continue.</p>

              @if(session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
              @endif

              <div class="d-grid gap-2">
                @foreach($roleOptions as $option)
                <form method="POST" action="{{ route('dashboard.switch') }}">
                  @csrf
                  <input type="hidden" name="role" value="{{ $option['role'] }}">
                  <button type="submit" class="btn {{ ($activeRole === ($option['role'] ?? '')) ? 'btn-primary' : 'btn-outline-primary' }} text-start w-100">
                    {{ $option['label'] }}
                  </button>
                </form>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')