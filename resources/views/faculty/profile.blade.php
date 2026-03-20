<?php

use Carbon\Carbon;

?>
@include('includes.header')

<div class="wrapper">
  @include('faculty.sidebar')

  <!--start main wrapper-->
  <main class="page-content">
    <!--start breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Profile</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('faculty.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="container-fluid mt-4">
      <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 col-xl-3">
          <div class="card shadow-sm border-0">
            <div class="card-body text-center">
              <div class="position-relative d-inline-block mb-3">
                <div class="avatar-wrapper" style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 4px solid #f0f0f0; margin: 0 auto;">
                  @if($faculty->photo ?? false)
                  <img src="{{ Storage::disk('s3')->url($faculty->photo) }}" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
                  @else
                  <div class="bg-primary text-white d-flex align-items-center justify-content-center" style="width: 100%; height: 100%; font-size: 3.5rem; font-weight: bold;">
                    {{ strtoupper(substr($faculty->FIRST_NAME, 0, 1)) }}{{ strtoupper(substr($faculty->lname ?? '', 0, 1)) }}
                  </div>
                  @endif
                </div>
                <button type="button" class="btn btn-sm btn-secondary position-absolute" style="bottom: 10px; right: -10px; border-radius: 100%; width: 45px; height: 45px;" id="editPhotoBtn" title="Change Photo">
                  <i class="fas fa-camera"></i>
                </button>
              </div>
              <h5 class="mb-1 fw-bold">{{ $faculty->FIRST_NAME }} {{ $faculty->LAST_NAME }}</h5>
              <p class="text-muted mb-2">{{ $faculty->designation }}</p>
              <p class="text-muted mb-3"><small>Employee ID: {{ $faculty->USER_CODE }}</small></p>
              <div class="d-flex justify-content-center gap-2 mb-3">
                <span class="badge bg-light-info text-info"><i class="fas fa-envelope me-1"></i>{{ $faculty->MAIL_ID }}</span>
              </div>
              <div class="d-flex justify-content-center gap-2">
                <span class="badge bg-light-success text-success"><i class="fas fa-phone me-1"></i>{{ $faculty->MOBILE_NO }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Stats Card -->
          <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">
              <h6 class="mb-3 fw-bold">Quick Information</h6>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i>Joined</span>
                <span class="fw-bold">{{ $faculty->DOJ ? Carbon::parse($faculty->DOJ)->format('M d, Y') : 'N/A' }}</span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span class="text-muted"><i class="fas fa-birthday-cake me-2"></i>Birthday</span>
                <span class="fw-bold">{{ $faculty->dob ? Carbon::parse($faculty->dob)->format('M d, Y') : 'N/A' }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-muted"><i class="fas fa-venus-mars me-2"></i>Gender</span>
                <span class="fw-bold text-capitalize">{{ $faculty->GENDER == 1 ? 'Male' : ($faculty->GENDER == 2 ? 'Female' : 'Other') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Profile Details & Edit Form -->
        <div class="col-lg-8 col-xl-9">
          <!-- View Mode -->
          <div class="card shadow-sm border-0" id="viewMode">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-user-circle me-2"></i>Profile Information</h5>
              <button type="button" class="btn btn-primary btn-sm" id="editProfileBtn">
                <i class="fas fa-edit me-1"></i> Edit Profile
              </button>
            </div>
            <div class="card-body">
              <div class="row g-4">
                <!-- Personal Information Section -->
                <div class="col-12">
                  <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-user me-2"></i>Personal Information
                  </h6>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>First Name</small></label>
                  <p class="fw-bold">{{ $faculty->FIRST_NAME }}</p>
                </div>

                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Last Name</small></label>
                  <p class="fw-bold">{{ $faculty->LAST_NAME ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Gender</small></label>
                  <p class="fw-bold text-capitalize">{{ $faculty->GENDER == 1 ? 'Male' : ($faculty->GENDER == 2 ? 'Female' : 'Other') }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Date of Birth</small></label>
                  <p class="fw-bold">{{ $faculty->DOB ? Carbon::parse($faculty->DOB)->format('F d, Y') : '-' }}</p>
                </div>

                <!-- Contact Information Section -->
                <div class="col-12 mt-4">
                  <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-address-book me-2"></i>Contact Information
                  </h6>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Email Address</small></label>
                  <p class="fw-bold">{{ $faculty->MAIL_ID ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Phone Number</small></label>
                  <p class="fw-bold">{{ $faculty->MOBILE_NO ?? '-' }}</p>
                </div>
                <div class="col-12">
                  <label class="text-muted mb-1"><small>Address</small></label>
                  <p class="fw-bold">{{ $faculty->ADDRESS ?? '-' }}</p>
                </div>

                <!-- Professional Information Section -->
                <div class="col-12 mt-4">
                  <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-briefcase me-2"></i>Professional Information
                  </h6>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Employee ID</small></label>
                  <p class="fw-bold">{{ $faculty->USER_CODE }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Designation</small></label>
                  <p class="fw-bold">{{ $faculty->designation }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Joining Date</small></label>
                  <p class="fw-bold">{{ $faculty->DOJ ? Carbon::parse($faculty->DOJ)->format('F d, Y') : '-' }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted mb-1"><small>Resignation Date</small></label>
                  <p class="fw-bold">{{ $faculty->resignation_date ? Carbon::parse($faculty->resignation_date)->format('F d, Y') : 'Active' }}</p>
                </div>
                <div class="col-12">
                  <label class="text-muted mb-1"><small>Specialization</small></label>
                  <p class="fw-bold">{{ $faculty->specialization ?? '-' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Edit Mode -->
          <div class="card shadow-sm border-0" id="editMode" style="display: none;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
              <h5 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Edit Profile Information</h5>
              <button type="button" class="btn btn-secondary btn-sm" id="cancelEditBtn">
                <i class="fas fa-times me-1"></i> Cancel
              </button>
            </div>
            <div class="card-body">
              <form action="{{ route('faculty.profile.update') }}" method="POST" id="profileForm">
                @csrf
                @method('PUT')

                <div class="row g-4">
                  <!-- Personal Information Section -->
                  <div class="col-12">
                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                      <i class="fas fa-user me-2"></i>Personal Information
                    </h6>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="fname" value="{{ $faculty->FIRST_NAME }}" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="lname" value="{{ $faculty->LAST_NAME }}">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-select" name="gender" required>
                      <option value="">Select Gender</option>
                      <option value="male" {{ $faculty->GENDER == 1 ? 'selected' : '' }}>Male</option>
                      <option value="female" {{ $faculty->GENDER == 2 ? 'selected' : '' }}>Female</option>
                      <option value="other" {{ $faculty->GENDER == 3 ? 'selected' : '' }}>Other</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="dob" value="{{ $faculty->DOB ? \Carbon\Carbon::parse($faculty->DOB)->format('Y-m-d') : '' }}">
                  </div>

                  <!-- Contact Information Section -->
                  <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                      <i class="fas fa-address-book me-2"></i>Contact Information
                    </h6>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" value="{{ $faculty->MAIL_ID }}" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="phone" value="{{ $faculty->MOBILE_NO }}" required maxlength="15">
                  </div>
                  <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="3">{{ $faculty->ADDRESS }}</textarea>
                  </div>

                  <!-- Professional Information Section -->
                  <div class="col-12 mt-4">
                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                      <i class="fas fa-briefcase me-2"></i>Professional Information
                    </h6>
                  </div>
                  <div class="col-md-12">
                    <label class="form-label">Specialization</label>
                    <textarea class="form-control" name="specialization" rows="3">{{ $faculty->specialization }}</textarea>
                  </div>

                  <!-- Action Buttons -->
                  <div class="col-12 mt-4">
                    <div class="d-flex gap-2">
                      <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                      </button>
                      <button type="button" class="btn btn-secondary" id="cancelEditBtn2">
                        <i class="fas fa-times me-1"></i> Cancel
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!--end main wrapper-->
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="photoUploadModal" tabindex="-1" aria-labelledby="photoUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="photoUploadModalLabel">Change Profile Photo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('faculty.profile.photo') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="photoInput" class="form-label">Choose a new photo</label>
            <input type="file" class="form-control" id="photoInput" name="photo" accept="image/*" required>
            <small class="text-muted">Max file size: 2MB. Accepted formats: JPG, PNG, GIF</small>
          </div>
          <div id="photoPreview" class="text-center" style="display: none;">
            <img id="previewImage" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 10px;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Upload Photo</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('includes.footer')

<style>
  .bg-light-info {
    background-color: #e7f3ff;
  }

  .bg-light-success {
    background-color: #e7f9f0;
  }

  .text-info {
    color: #0dcaf0 !important;
  }

  .text-success {
    color: #198754 !important;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    const editProfileBtn = document.getElementById('editProfileBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const cancelEditBtn2 = document.getElementById('cancelEditBtn2');
    const editPhotoBtn = document.getElementById('editPhotoBtn');
    const photoInput = document.getElementById('photoInput');
    const photoPreview = document.getElementById('photoPreview');
    const previewImage = document.getElementById('previewImage');

    // Toggle to edit mode
    editProfileBtn.addEventListener('click', function() {
      viewMode.style.display = 'none';
      editMode.style.display = 'block';
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });

    // Toggle back to view mode
    [cancelEditBtn, cancelEditBtn2].forEach(btn => {
      btn.addEventListener('click', function() {
        editMode.style.display = 'none';
        viewMode.style.display = 'block';
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    });

    // Photo upload modal
    editPhotoBtn.addEventListener('click', function() {
      const modal = new bootstrap.Modal(document.getElementById('photoUploadModal'));
      modal.show();
    });

    // Photo preview
    photoInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImage.src = e.target.result;
          photoPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });

    // Form validation
    const profileForm = document.getElementById('profileForm');
    profileForm.addEventListener('submit', function(e) {
      const phone = document.querySelector('input[name="phone"]').value;
      if (phone && !/^[0-9+\-\s()]*$/.test(phone)) {
        e.preventDefault();
        Swal.fire({
          icon: 'error',
          title: 'Invalid Phone Number',
          text: 'Please enter a valid phone number',
        });
      }
    });
  });
</script>