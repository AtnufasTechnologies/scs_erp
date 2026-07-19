<?php

use App\Models\Faculty;

$faculties = Faculty::all();
?>
@include('includes.header')
@include('includes.dept-sidebar')

<style>
  .stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 24px;
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
  }

  .stats-card.gradient-green {
    background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
  }

  .stats-card.gradient-orange {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  }

  .stats-card.gradient-blue {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  }

  .stats-card.gradient-purple {
    background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%);
  }

  .faculty-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
  }

  .faculty-card:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }

  .table-modern {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }

  .btn-modern {
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
  }

  .btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  }

  .search-box {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
  }

  .search-box:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .faculty-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
  }

  .badge-modern {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
  }

  .action-btn {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    border: none;
    transition: all 0.2s ease;
  }

  .action-btn:hover {
    transform: scale(1.05);
  }
</style>

<main class="page-content">
  <div class="main-content">

    <div class="container-fluid py-4">
      <!-- Header Section -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold mb-1" style="color: #6a37cf;">Faculty Members</h2>
          <p class="text-muted mb-0">View department faculty and their analytics</p>
          <button class="btn btn-modern" style="background: #43cea2; color: white;" data-bs-toggle="modal" data-bs-target="#addFaculty">
            <i class="fas fa-plus me-2"></i>Add Faculty
          </button>
        </div>

      </div>


      <!-- Search and Filter Section -->
      <div class="table-modern mb-4">
        <div class="p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="position-relative">
                <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                <input type="text" id="facultySearch" class="form-control search-box" placeholder="Search by name, email, or code..." style="padding-left: 45px;">
              </div>
            </div>
            <div class="col-md-3">
              <select id="accessFilter" class="form-select search-box">
                <option value="">All Faculty</option>
                <option value="with-access">With Portal Access</option>
                <option value="no-access">No Portal Access</option>
              </select>
            </div>
            <div class="col-md-3">
              <button class="btn btn-modern w-100" style="background: #f3f4f6; color: #374151;" onclick="resetFilters()">
                <i class="fas fa-redo me-2"></i>Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Faculty Cards Grid -->
      <div class="row g-4 mb-4">
        @forelse($data as $item)
        <div class="col-lg-6 col-xl-4 faculty-row" data-search="{{ strtolower($item->faculty->FIRST_NAME . ' ' . $item->faculty->LAST_NAME . ' ' . $item->faculty->USER_CODE . ' ' . $item->faculty->MAIL_ID) }}" data-access="{{ $item->access_id ? 'with-access' : 'no-access' }}">
          <div class="faculty-card h-100">
            <!-- Card Header with Avatar and Status -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex align-items-center gap-3">
                <div class="faculty-avatar" style="width: 64px; height: 64px; font-size: 24px;">
                  @if($item->faculty->photo)
                  <img src="{{ Storage::disk('s3')->url($item->faculty->photo) }}" alt="Faculty" style="width: 64px; height: 64px; border-radius: 12px; object-fit: cover;">
                  @else
                  {{ strtoupper(substr($item->faculty->FIRST_NAME ?? 'F', 0, 1)) }}{{ strtoupper(substr($item->faculty->LAST_NAME ?? 'N', 0, 1)) }}
                  @endif
                </div>
                <div>
                  <h6 class="mb-1" style="color: #1a1a1a; font-weight: 700; font-size: 16px;">
                    {{ $item->faculty->FIRST_NAME ?? '-' }} {{ $item->faculty->LAST_NAME ?? '-' }}
                  </h6>
                  <div style="color: #6b7280; font-size: 13px; margin-bottom: 4px;">
                    @if($item->faculty->GENDER == 1)
                    <i class="fas fa-mars me-1" style="color: #3b82f6;"></i>Male
                    @elseif($item->faculty->GENDER == 2)
                    <i class="fas fa-venus me-1" style="color: #ec4899;"></i>Female
                    @else
                    <i class="fas fa-user me-1"></i>N/A
                    @endif
                  </div>
                  <span class="badge badge-modern" style="background: #e0e7ff; color: #4338ca; font-size: 11px;">
                    {{ $item->faculty->USER_CODE ?? '-' }}
                  </span>
                </div>
              </div>
              @if($item->access_id && $item->useraccess)
              <span class="badge badge-modern" style="background: #d1fae5; color: #065f46;">
                <i class="fas fa-check-circle me-1"></i>Portal Access
              </span>
              @else
              <span class="badge badge-modern" style="background: #fee2e2; color: #991b1b;">
                <i class="fas fa-times-circle me-1"></i>No Access
              </span>
              @endif
            </div>

            <!-- Divider -->
            <hr style="margin: 16px 0; border-color: #f0f0f0;">

            <!-- Contact Information -->
            <div class="mb-3">
              <div class="d-flex align-items-start gap-2 mb-2">
                <i class="fas fa-envelope mt-1" style="color: #667eea; font-size: 14px;"></i>
                <div>
                  <small style="color: #6b7280; font-size: 11px; display: block; margin-bottom: 2px;">Email</small>
                  <div style="color: #1a1a1a; font-size: 13px; word-break: break-all;">
                    {{ $item->faculty->MAIL_ID ?? 'Not provided' }}
                  </div>
                </div>
              </div>
              <div class="d-flex align-items-start gap-2">
                <i class="fas fa-phone mt-1" style="color: #667eea; font-size: 14px;"></i>
                <div>
                  <small style="color: #6b7280; font-size: 11px; display: block; margin-bottom: 2px;">Phone</small>
                  <div style="color: #1a1a1a; font-size: 13px;">
                    {{ $item->faculty->MOBILE_NO ?? 'Not provided' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Joining Date -->
            @if($item->faculty->DOJ)
            <div class="mb-3">
              <div class="d-flex align-items-center gap-2" style="background: #f9fafb; padding: 12px; border-radius: 10px;">
                <i class="fas fa-calendar-check" style="color: #667eea;"></i>
                <div>
                  <small style="color: #6b7280; font-size: 11px; display: block;">Joined on</small>
                  <strong style="color: #1a1a1a; font-size: 13px;">
                    {{ \Carbon\Carbon::parse($item->faculty->DOJ)->format('d M Y') }}
                  </strong>
                </div>
              </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="d-flex gap-2 mt-auto pt-2">
              <a href="{{ route('department.faculty.timetable', $item->faculty->id) }}" class="btn btn-modern flex-fill" style="background: #dbeafe; color: #1e40af; padding: 10px; font-size: 13px;" title="View Timetable">
                <i class="fas fa-calendar me-1"></i> Timetable
              </a>

              @if(!$item->access_id && !$item->useraccess)

              <button class="btn btn-modern flex-fill" style="background: #d1fae5; color: #065f46; padding: 10px; font-size: 13px;" title="Grant Access" data-bs-toggle="modal" data-bs-target="#grantAccessModal{{ $item->id }}">
                <i class="fas fa-key me-1"></i> Grant
              </button>
              @endif

              <form action="{{ route('department.faculty.delete', $item->faculty->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this faculty from the department?');" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-modern" style="background: #fee2e2; color: #991b1b; padding: 10px 16px; font-size: 13px;" title="Remove">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </div>
          </div>

          <!-- Grant Access Modal for each faculty -->
          <div class="modal fade" id="grantAccessModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                  <div>
                    <h5 class="modal-title mb-1" style="font-weight: 700;">
                      <i class="fas fa-key me-2"></i>Grant Portal Access
                    </h5>
                    <small style="opacity: 0.9;">Create login credentials for faculty member</small>
                  </div>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('department.faculty.grant-access') }}" method="POST">
                  @csrf
                  <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" name="faculty_id" value="{{ $item->faculty->id }}">
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <!-- Faculty Info Card -->
                    <div class="alert" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                      <div class="d-flex align-items-center gap-3">
                        <div class="faculty-avatar" style="width: 48px; height: 48px; font-size: 18px;">
                          @if($item->faculty->photo)
                          <img src="{{ Storage::disk('s3')->url($item->faculty->photo) }}" alt="Faculty" style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover;">
                          @else
                          {{ strtoupper(substr($item->faculty->FIRST_NAME ?? 'F', 0, 1)) }}{{ strtoupper(substr($item->faculty->LAST_NAME ?? 'N', 0, 1)) }}
                          @endif
                        </div>
                        <div>
                          <h6 class="mb-0" style="color: #1a1a1a; font-weight: 600;">
                            {{ $item->faculty->FIRST_NAME }} {{ $item->faculty->LAST_NAME }}
                          </h6>
                          <small style="color: #6b7280;">{{ $item->faculty->USER_CODE }}</small>
                        </div>
                      </div>
                    </div>

                    <!-- Email Display -->
                    <div class="mb-3">
                      <label class="form-label" style="font-weight: 600; color: #374151;">
                        <i class="fas fa-envelope me-1" style="color: #667eea;"></i>Login Email
                      </label>
                      <div class="input-group">
                        <span class="input-group-text" style="background: #f9fafb; border-color: #e5e7eb;">
                          <i class="fas fa-at"></i>
                        </span>
                        <input type="text" class="form-control" value="{{ $item->faculty->MAIL_ID }}" disabled style="background: #f9fafb; border-color: #e5e7eb;">
                      </div>
                      <small class="text-muted">This email will be used for login</small>
                    </div>

                    <!-- Password Input with Toggle -->
                    <div class="mb-3">
                      <label class="form-label" style="font-weight: 600; color: #374151;">
                        <i class="fas fa-lock me-1" style="color: #667eea;"></i>Create Password
                      </label>
                      <div class="input-group">
                        <span class="input-group-text" style="background: #f9fafb; border-color: #e5e7eb;">
                          <i class="fas fa-key"></i>
                        </span>
                        <input type="password" name="password" id="password{{ $item->id }}" class="form-control" required minlength="6" placeholder="Enter secure password (min 6 characters)" style="border-color: #e5e7eb;">
                        <button class="btn" type="button" onclick="togglePassword({{ $item->id }})" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                          <i class="fas fa-eye" id="toggleIcon{{ $item->id }}"></i>
                        </button>
                      </div>
                      <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>Use a strong password with letters, numbers, and symbols
                      </small>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <small style="color: #6b7280; font-weight: 600;">Password Strength</small>
                        <small id="strengthText{{ $item->id }}" style="color: #6b7280;">-</small>
                      </div>
                      <div class="progress" style="height: 6px; border-radius: 10px; background: #f3f4f6;">
                        <div id="strengthBar{{ $item->id }}" class="progress-bar" role="progressbar" style="width: 0%; transition: all 0.3s ease;"></div>
                      </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-info" style="border-radius: 12px; border: none; background: #e0f2fe; color: #075985; padding: 14px;">
                      <div class="d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                          <strong>What happens next?</strong>
                          <ul class="mb-0 mt-1" style="padding-left: 20px; font-size: 13px;">
                            <li>Faculty member can login with their email and this password</li>
                            <li>They'll have access to faculty portal features</li>
                            <li>You can revoke access anytime from the faculty list</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 16px 24px;">
                    <button type="button" class="btn btn-modern" style="background: #f3f4f6; color: #374151;" data-bs-dismiss="modal">
                      <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                      <i class="fas fa-unlock-alt me-2"></i>Grant Access
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>





        @empty
        <div class="col-12">
          <div class="text-center" style="padding: 80px 20px; background: white; border-radius: 16px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
            <div style="width: 120px; height: 120px; margin: 0 auto 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0.1;">
              <i class="fas fa-users" style="font-size: 60px; color: white;"></i>
            </div>
            <h4 style="color: #1a1a1a; font-weight: 700; margin-bottom: 12px;">No Faculty Members Yet</h4>
            <p style="color: #6b7280; font-size: 16px; margin-bottom: 24px;">Get started by adding your first faculty member to this department.</p>
            <button class="btn btn-modern" style="background: #5b4cdb; color: white; padding: 12px 32px;" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
              <i class="fas fa-plus-circle me-2"></i>Add Your First Faculty
            </button>
          </div>
        </div>
        @endforelse
      </div>
    </div>
  </div>


  <!-- Add Faculty Modal -->
  <div class="modal fade" id="addFaculty" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border-radius: 20px; border: none;">
        <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 24px;">
          <h5 class="modal-title" style="color: #1a1a1a; font-weight: 700;" id="exampleModalLabel">Add Faculty for {{$subject->title}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{route('dept.add.faculty.master')}}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="modal-body" style="padding: 24px;">
            <label for="" style="color: #1a1a1a; font-weight: 600; margin-bottom: 8px;">Select Faculty</label>
            <select name="faculty[]" class="form-select mb-3 select-multiple" style="border-radius: 12px; border: 1px solid #e5e7eb; padding: 12px;" multiple>
              @foreach ($faculties as $faculty)

              <option value="{{$faculty->id}}">{{$faculty->USER_CODE}} - {{$faculty->FIRST_NAME}} {{$faculty->LAST_NAME}}</option>
              @endforeach
            </select>
            <input type="hidden" name="subject_id" value="{{$subject->id}}">
          </div>
          <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 24px;">
            <button type="button" class="btn btn-modern" style="background: #f5f7fa; color: #6b7280;" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-modern" style="background: #43cea2; color: white;">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>



<script>
  // Search and filter functionality
  document.getElementById('facultySearch').addEventListener('keyup', function() {
    filterFaculty();
  });

  document.getElementById('accessFilter').addEventListener('change', function() {
    filterFaculty();
  });

  function filterFaculty() {
    const searchText = document.getElementById('facultySearch').value.toLowerCase();
    const accessFilter = document.getElementById('accessFilter').value;
    const cards = document.querySelectorAll('.faculty-row');

    cards.forEach(card => {
      const searchContent = card.getAttribute('data-search');
      const accessStatus = card.getAttribute('data-access');

      const matchesSearch = searchContent.includes(searchText);
      const matchesFilter = !accessFilter || accessStatus === accessFilter;

      if (matchesSearch && matchesFilter) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
  }

  function resetFilters() {
    document.getElementById('facultySearch').value = '';
    document.getElementById('accessFilter').value = '';
    filterFaculty();
  }

  // Password visibility toggle
  function togglePassword(id) {
    const passwordInput = document.getElementById('password' + id);
    const toggleIcon = document.getElementById('toggleIcon' + id);

    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleIcon.classList.remove('fa-eye');
      toggleIcon.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      toggleIcon.classList.remove('fa-eye-slash');
      toggleIcon.classList.add('fa-eye');
    }
  }

  // Password strength indicator
  document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all password inputs
    const passwordInputs = document.querySelectorAll('input[name="password"]');

    passwordInputs.forEach(input => {
      input.addEventListener('input', function() {
        const id = this.id.replace('password', '');
        checkPasswordStrength(this.value, id);
      });
    });
  });

  function checkPasswordStrength(password, id) {
    const strengthBar = document.getElementById('strengthBar' + id);
    const strengthText = document.getElementById('strengthText' + id);

    if (!strengthBar || !strengthText) return;

    let strength = 0;
    let text = '';
    let color = '';

    if (password.length === 0) {
      strengthBar.style.width = '0%';
      strengthText.textContent = '-';
      return;
    }

    // Length check
    if (password.length >= 6) strength += 25;
    if (password.length >= 10) strength += 15;

    // Contains lowercase
    if (/[a-z]/.test(password)) strength += 15;

    // Contains uppercase
    if (/[A-Z]/.test(password)) strength += 15;

    // Contains numbers
    if (/[0-9]/.test(password)) strength += 15;

    // Contains special characters
    if (/[^A-Za-z0-9]/.test(password)) strength += 15;

    // Determine strength level
    if (strength <= 30) {
      text = 'Weak';
      color = '#ef4444';
    } else if (strength <= 50) {
      text = 'Fair';
      color = '#f59e0b';
    } else if (strength <= 75) {
      text = 'Good';
      color = '#10b981';
    } else {
      text = 'Strong';
      color = '#059669';
    }

    strengthBar.style.width = strength + '%';
    strengthBar.style.backgroundColor = color;
    strengthText.textContent = text;
    strengthText.style.color = color;
  }
</script>

@include('includes.footer')