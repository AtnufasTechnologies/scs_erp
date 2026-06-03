<?php

use App\Models\StudentCourseInfo;
?>
@include('includes.header')
@include('admin.sidebar')

<style>
  .student-master-container {
    padding: 20px;
    background: transparent;
    min-height: 100vh;
  }

  .page-header {
    background: linear-gradient(135deg, #0fb3b6 0%, #620fb6 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
  }

  .page-header h2 {
    margin: 0;
    font-weight: 600;
    font-size: 32px;
    color: #fff;
  }

  .search-container {
    background: white;
    padding: 25px;
    border-radius: 15px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .search-box {
    position: relative;
    margin-bottom: 20px;
  }

  .search-box input {
    width: 100%;
    padding: 15px 50px 15px 20px;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
  }

  .search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .search-box i {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
    font-size: 20px;
  }

  .search-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #64748b;
    font-size: 14px;
  }

  .cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
  }

  .student-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e1e8ed;
  }

  .student-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
  }

  .student-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
  }

  .student-info {
    flex: 1;
  }

  .student-name {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
    text-transform: capitalize;
  }

  .student-roll {
    display: inline-block;
    background: linear-gradient(135deg, #4cbe9c 0%, #15d1b8 100%);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    text-transform: uppercase;
    transition: all 0.3s ease;
  }

  .student-roll:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    color: white;
    text-decoration: none;
  }

  .gender-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .gender-male {
    background: #dbeafe;
    color: #1e40af;
  }

  .gender-female {
    background: #fce7f3;
    color: #be185d;
  }

  .student-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
  }

  .detail-label {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
  }

  .detail-value {
    font-size: 14px;
    color: #1e293b;
    font-weight: 500;
  }

  .detail-value a {
    color: #667eea;
    text-decoration: none;
  }

  .detail-value a:hover {
    text-decoration: underline;
  }

  .academic-info {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #f1f5f9;
  }

  .academic-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .academic-tag {
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    color: #475569;
    font-weight: 500;
  }

  .no-results {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .no-results i {
    font-size: 64px;
    color: #cbd5e1;
    margin-bottom: 20px;
  }

  .no-results h3 {
    color: #64748b;
    font-weight: 600;
    margin-bottom: 10px;
  }

  .no-results p {
    color: #94a3b8;
  }

  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 15px;
    z-index: 10;
  }

  .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }

    100% {
      transform: rotate(360deg);
    }
  }

  .search-box.loading i {
    animation: pulse 1.5s ease-in-out infinite;
  }

  @keyframes pulse {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: 0.5;
    }
  }

  @media (max-width: 768px) {
    .cards-grid {
      grid-template-columns: 1fr;
    }

    .student-details {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="student-master-container">
  <div class="page-header">
    <h2>👨‍🎓 Student Master</h2>
    <p style="margin: 10px 0 0 0; opacity: 0.9;">Manage and view all student records</p>
  </div>

  <div class="search-container">
    <div class="search-box">
      <input type="text" id="searchInput" placeholder="Search by name, roll no, register no, email, department, program..." autocomplete="off">
      <i class="fas fa-search"></i>
    </div>
    <div class="search-stats">
      <span id="resultCount">Showing <strong>{{ count($data) }}</strong> students</span>
      <span id="searchStatus"></span>
    </div>
    <input type="hidden" id="totalStudents" value="{{ $data->total() ?? count($data) }}">
  </div>

  <div class="cards-grid" id="studentsGrid">
    @if (count($data))
    @foreach ($data as $item)
    <?php
    $semester = StudentCourseInfo::where('student_id', $item->id)
      ->distinct('semester')
      ->orderby('semester', 'desc')
      ->value('semester');
    ?>

    <div class="student-card" data-search-content="{{ strtolower($item->first_name . ' ' . $item->last_name . ' ' . $item->roll_no . ' ' . $item->register_no . ' ' . $item->mail_id . ' ' . ($item->deptmaster != null ? $item->deptmaster->name : '') . ' ' . ($item->stdprogramenrolled != null ? $item->stdprogramenrolled->code : '') . ' ' . ($item->stdprogramenrolled != null ? $item->stdprogramenrolled->name : '') . ' ' . ($item->campusmaster != null ? $item->campusmaster->name : '') . ' ' . ($item->batchmaster != null ? $item->batchmaster->batch_name : '')) }}">

      <div class="student-card-header">
        <div class="student-info">
          <div class="student-name">{{ $item->first_name }} {{ $item->last_name }}</div>
          <a href="{{ url('erp/admin/'.$item->id.'/std-profile/'.$item->roll_no) }}" class="student-roll">
            {{ $item->roll_no }}
          </a>
        </div>
        <span class="gender-badge {{ $item->gender == '1' ? 'gender-male' : 'gender-female' }}">
          {{ $item->gender == '1' ? 'Male' : 'Female' }}
        </span>
      </div>

      <div class="student-details">
        <div class="detail-item">
          <span class="detail-label">Register No</span>
          <span class="detail-value">{{ $item->register_no }}</span>
        </div>

        <div class="detail-item">
          <span class="detail-label">Date of Birth</span>
          <span class="detail-value">{{ $item->dob }}</span>
        </div>

        <div class="detail-item">
          <span class="detail-label">Email</span>
          <span class="detail-value">
            <a href="mailto:{{ $item->mail_id }}">{{ $item->mail_id }}</a>
          </span>
        </div>

        <div class="detail-item">
          <span class="detail-label">Phone</span>
          <span class="detail-value">{{ $item->mobile_no }}</span>
        </div>

        <div class="detail-item">
          <span class="detail-label">Religion</span>
          <span class="detail-value text-capitalize">{{ $item->religionmaster != null ? $item->religionmaster->name : 'N/A' }}</span>
        </div>

        <div class="detail-item">
          <span class="detail-label">Campus</span>
          <span class="detail-value">{{ $item->campusmaster != null ? $item->campusmaster->name : 'N/A' }}</span>
        </div>


        <div class="detail-item">
          <span class="detail-label">Program Enrolled</span>
          <span class="detail-value">{{ $item->stdprogramenrolled != null ? $item->stdprogramenrolled->code : 'N/A' }} - {{ $item->stdprogramenrolled != null ? $item->stdprogramenrolled->name : 'N/A' }}</span>
        </div>
      </div>

      <div class="academic-info">
        <div class="academic-tags">
          <span class="academic-tag">📅 Batch: {{ $item->batchmaster != null ? $item->batchmaster->batch_name : 'N/A' }}</span>
          <span class="academic-tag">📖 Sem: {{ $semester ?? 'N/A' }}</span>
          <span class="academic-tag">📊 Year: {{ $item->current_year }}</span>
        </div>
      </div>
    </div>
    @endforeach
    @else
    <div class="no-results">
      <i class="fas fa-user-slash"></i>
      <h3>No Students Found</h3>
      <p>There are no student records available at the moment.</p>
    </div>
    @endif


  </div>
  {{$data->links('vendor.pagination.bootstrap-5')}}

  <div id="noResultsMessage" class="no-results" style="display: none;">
    <i class="fas fa-search"></i>
    <h3>No Results Found</h3>
    <p>Try adjusting your search terms</p>
  </div>
</div>

<script>
  // AJAX-based live search functionality
  const searchInput = document.getElementById('searchInput');
  const studentsGrid = document.getElementById('studentsGrid');
  const resultCount = document.getElementById('resultCount');
  const searchStatus = document.getElementById('searchStatus');
  const noResultsMessage = document.getElementById('noResultsMessage');
  const searchBox = document.querySelector('.search-box');
  const studentMasterContainer = document.querySelector('.student-master-container');

  let searchTimeout;
  let isSearching = false;
  const currentUrl = window.location.pathname;
  const campusId = currentUrl.includes('sonada') ? 1 : 2;
  const totalStudents = parseInt(document.getElementById('totalStudents').value) || 0;

  function showLoading() {
    searchBox.classList.add('loading');
    searchStatus.innerHTML = '<span style="color: #667eea;">🔄 Searching...</span>';
  }

  function hideLoading() {
    searchBox.classList.remove('loading');
  }

  function renderStudentCard(student) {
    return `
      <div class="student-card" style="opacity: 0; transform: translateY(20px);">
        <div class="student-card-header">
          <div class="student-info">
            <div class="student-name">${student.first_name} ${student.last_name}</div>
            <a href="/erp/admin/${student.id}/std-profile/${student.roll_no}" class="student-roll">
              ${student.roll_no}
            </a>
          </div>
          <span class="gender-badge ${student.gender == '1' ? 'gender-male' : 'gender-female'}">
            ${student.gender == '1' ? 'Male' : 'Female'}
          </span>
        </div>

        <div class="student-details">
          <div class="detail-item">
            <span class="detail-label">Register No</span>
            <span class="detail-value">${student.register_no || 'N/A'}</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Date of Birth</span>
            <span class="detail-value">${student.dob || 'N/A'}</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Email</span>
            <span class="detail-value">
              <a href="mailto:${student.mail_id}">${student.mail_id || 'N/A'}</a>
            </span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Phone</span>
            <span class="detail-value">${student.mobile_no || 'N/A'}</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Religion</span>
            <span class="detail-value text-capitalize">${student.religionmaster?.name || 'N/A'}</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Campus</span>
            <span class="detail-value">${student.campusmaster?.name || 'N/A'}</span>
          </div>

          <div class="detail-item">
            <span class="detail-label">Program Enrolled</span>
            <span class="detail-value">${student.stdprogramenrolled ? `${student.stdprogramenrolled.code} - ${student.stdprogramenrolled.name}` : 'N/A'}</span>
          </div>
        </div>

        <div class="academic-info">
          <div class="academic-tags">
            <span class="academic-tag">📅 Batch: ${student.batchmaster?.batch_name || 'N/A'}</span>
            <span class="academic-tag">📖 Sem: ${student.current_semester || 'N/A'}</span>
            <span class="academic-tag">📊 Year: ${student.current_year || 'N/A'}</span>
          </div>
        </div>
      </div>
    `;
  }

  function animateCards() {
    const cards = document.querySelectorAll('.student-card');
    cards.forEach((card, index) => {
      setTimeout(() => {
        card.style.transition = 'all 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
      }, index * 50);
    });
  }

  function performSearch(searchTerm) {
    if (isSearching) return;

    isSearching = true;
    showLoading();

    fetch(`/erp/admin/student-search?search=${encodeURIComponent(searchTerm)}&campus_id=${campusId}`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        hideLoading();
        isSearching = false;

        if (data.students && data.students.length > 0) {
          studentsGrid.innerHTML = data.students.map(student => renderStudentCard(student)).join('');
          studentsGrid.style.display = 'grid';
          noResultsMessage.style.display = 'none';

          resultCount.innerHTML = `Showing <strong>${data.students.length}</strong> of <strong>${data.total}</strong> students`;
          searchStatus.innerHTML = '<span style="color: #10b981;">✓ Search complete</span>';

          // Animate cards
          animateCards();
        } else {
          studentsGrid.style.display = 'none';
          noResultsMessage.style.display = 'block';
          resultCount.innerHTML = `Showing <strong>0</strong> of <strong>${data.total}</strong> students`;
          searchStatus.innerHTML = '<span style="color: #ef4444;">😔 No matches</span>';
        }

        // Clear status after 2 seconds
        setTimeout(() => {
          if (!searchInput.value.trim()) {
            searchStatus.textContent = '';
          }
        }, 2000);
      })
      .catch(error => {
        console.error('Search error:', error);
        hideLoading();
        isSearching = false;
        searchStatus.innerHTML = '<span style="color: #ef4444;">❌ Search failed</span>';
      });
  }

  searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);

    const searchTerm = this.value.trim();

    if (searchTerm === '') {
      // Reload page to show all students with pagination
      searchStatus.textContent = '';
      window.location.href = currentUrl;
      return;
    }

    searchTimeout = setTimeout(() => {
      performSearch(searchTerm);
    }, 500); // Debounce for 500ms
  });

  // Initial animation for cards on page load
  document.addEventListener('DOMContentLoaded', function() {
    animateCards();
  });
</script>

@include('includes.footer')