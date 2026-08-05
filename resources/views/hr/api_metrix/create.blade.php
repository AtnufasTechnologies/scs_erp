@include('includes.header')

<div class="wrapper">
  @include('hr.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">API Metrix</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('hr.api-metrix.index') }}">Category Master</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header bg-white">
        <h5 class="mb-0">Create API Metrix Category</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('hr.api-metrix.store') }}" method="POST">
          @csrf
          @include('hr.api_metrix._form')

          <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-1"></i>Save Category
            </button>
            <a href="{{ route('hr.api-metrix.index') }}" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')