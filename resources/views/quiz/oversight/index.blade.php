@include('includes.header')

@if($role === 'principal')
<div class="wrapper">
  @include('principal.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">FA1 Quiz Monitor</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('principal.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Faculty Quizzes</li>
          </ol>
        </nav>
      </div>
    </div>

    @include('quiz.oversight.partials.table')
  </main>
</div>
@else
@include('includes.dept-sidebar')

<div class="main-content">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0">FA1 Quiz Monitor</h4>
        <small class="text-muted">All faculty-created quizzes with questions and departments.</small>
      </div>
      <a href="{{ route('department.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    @include('quiz.oversight.partials.table')
  </div>
</div>
@endif

@include('includes.footer')