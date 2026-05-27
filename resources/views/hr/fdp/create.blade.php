@include('includes.header')
<div class="wrapper">
@include('hr.sidebar')
<main class="page-content">
<div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
<div class="breadcrumb-title pe-3">FDP Programs</div>
<div class="ps-2"><nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 p-0">
<li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
<li class="breadcrumb-item"><a href="{{ route('hr.fdp.index') }}">FDP List</a></li>
<li class="breadcrumb-item active">Create FDP</li>
</ol></nav></div></div>
<div class="card"><div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Create FDP Program</h5></div>
<div class="card-body"><form action="{{ route('hr.fdp.store') }}" method="POST">@csrf
<div class="row mb-3"><div class="col-md-6"><label>Program Code <span class="text-danger">*</span></label>
<input type="text" name="program_code" class="form-control" required></div>
<div class="col-md-6"><label>Program Title <span class="text-danger">*</span></label>
<input type="text" name="program_title" class="form-control" required></div></div>
<div class="row mb-3"><div class="col-md-6"><label>Program Type <span class="text-danger">*</span></label>
<select name="program_type" class="form-select" required>
<option value="">Select Type</option>
<option value="workshop">Workshop</option>
<option value="seminar">Seminar</option>
<option value="conference">Conference</option>
<option value="training">Training</option>
</select></div>
<div class="col-md-6"><label>Status</label>
<select name="status" class="form-select">
<option value="draft">Draft</option>
<option value="open">Open for Registration</option>
<option value="ongoing">Ongoing</option>
<option value="completed">Completed</option>
</select></div></div>
<div class="row mb-3"><div class="col-md-6"><label>Start Date <span class="text-danger">*</span></label>
<input type="date" name="start_date" class="form-control" required></div>
<div class="col-md-6"><label>End Date <span class="text-danger">*</span></label>
<input type="date" name="end_date" class="form-control" required></div></div>
<div class="row mb-3"><div class="col-md-12"><label>Description</label>
<textarea name="description" class="form-control" rows="3"></textarea></div></div>
<div class="row"><div class="col-12">
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create</button>
<a href="{{ route('hr.fdp.index') }}" class="btn btn-secondary"><i class="fas fa-times me-1"></i>Cancel</a>
</div></div></form></div></div>
</main></div>
@include('includes.footer')
