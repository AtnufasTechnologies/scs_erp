@include('includes.header')

@php
$weatherThemes = [
['slug' => 'sunny', 'tagline' => 'Quick Access'],
['slug' => 'storm', 'tagline' => 'Role Workspace'],
['slug' => 'mist', 'tagline' => 'Continue As'],
['slug' => 'clear', 'tagline' => 'Dashboard Mode'],
];
$userCardImage = asset('admin/images/male.png');
@endphp

<style>
  .switcher-sky-bg {
    min-height: calc(100vh - 70px);
    background:
      radial-gradient(900px 350px at -15% -10%, #e8f5ff 0%, rgba(232, 245, 255, 0) 70%),
      radial-gradient(1000px 420px at 110% -5%, #dcecff 0%, rgba(220, 236, 255, 0) 72%),
      linear-gradient(180deg, #f8fbff 0%, #eef4fb 55%, #e9f1f9 100%);
    padding-top: 28px;
    padding-bottom: 36px;
  }

  .switcher-title {
    color: #30455f;
    font-weight: 800;
    letter-spacing: 0.2px;
  }

  .switcher-subtitle {
    color: #6c7d90;
    font-size: 14px;
  }

  .switcher-topbar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
  }

  .switcher-logout-btn {
    border: 0;
    border-radius: 999px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #ff6b6b 0%, #e53935 100%);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.2px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 12px 20px rgba(229, 57, 53, 0.3);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    white-space: nowrap;
  }

  .switcher-back-btn {
    border: 0;
    border-radius: 999px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #5f6f82 0%, #3f4f63 100%);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.2px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 12px 20px rgba(63, 79, 99, 0.28);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    white-space: nowrap;
  }

  .switcher-back-btn:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 16px 24px rgba(63, 79, 99, 0.34);
  }

  .switcher-actions {
    display: inline-flex;
    gap: 10px;
    align-items: center;
  }

  .switcher-logout-btn:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 16px 24px rgba(229, 57, 53, 0.35);
  }

  .weather-role-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .role-card-button {
    width: 100%;
    border: 0;
    border-radius: 22px;
    min-height: 156px;
    padding: 0;
    background: transparent;
  }

  .weather-role-card {
    position: relative;
    overflow: hidden;
    border-radius: 22px;
    min-height: 156px;
    padding: 24px 24px 20px 24px;
    box-shadow: 0 24px 38px rgba(22, 54, 91, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-align: left;
    transition: transform 0.2s ease, box-shadow 0.22s ease;
  }

  .role-card-button:hover .weather-role-card,
  .role-card-button:focus .weather-role-card {
    transform: translateY(-2px);
    box-shadow: 0 30px 42px rgba(22, 54, 91, 0.28);
  }

  .weather-role-card.theme-sunny {
    background: linear-gradient(120deg, #2dc6d9 0%, #31a7d3 100%);
    color: #ffffff;
  }

  .weather-role-card.theme-storm {
    background: linear-gradient(120deg, #5e7cd8 0%, #3c5fbf 100%);
    color: #ffffff;
  }

  .weather-role-card.theme-mist {
    background: linear-gradient(120deg, #f1eee7 0%, #e0ddd6 100%);
    color: #546170;
  }

  .weather-role-card.theme-clear {
    background: linear-gradient(120deg, #2ba7ff 0%, #2a8ee0 100%);
    color: #ffffff;
  }

  .weather-role-card .temp {
    font-size: 16px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.45px;
    text-shadow: 0 2px 8px rgba(10, 28, 48, 0.16);
  }

  .weather-role-card .meta {
    display: flex;
    flex-direction: column;
    margin-left: 14px;
    align-self: center;
  }

  .weather-role-card .weather-label {
    font-size: 21px;
    font-weight: 800;
    line-height: 1.05;
    letter-spacing: 0.2px;
  }

  .weather-role-card .role-label {
    margin-top: 8px;
    font-size: 13px;
    opacity: 0.9;
    font-weight: 600;
    letter-spacing: 0.22px;
  }

  .weather-left {
    display: inline-flex;
    align-items: center;
    gap: 14px;
  }

  .weather-glyph {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.2);
    border: 3px solid rgba(255, 255, 255, 0.62);
    box-shadow: 0 14px 22px rgba(0, 0, 0, 0.17);
    flex: 0 0 auto;
  }

  .weather-glyph img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .weather-role-card.theme-mist .temp,
  .weather-role-card.theme-mist .weather-label,
  .weather-role-card.theme-mist .role-label {
    color: #495867;
    text-shadow: none;
  }

  .weather-role-card.active-card {
    outline: 4px solid rgba(255, 255, 255, 0.74);
    box-shadow: 0 34px 48px rgba(16, 51, 88, 0.34);
  }

  @media (max-width: 992px) {
    .weather-role-grid {
      grid-template-columns: 1fr;
    }

    .weather-role-card {
      min-height: 146px;
    }

    .switcher-topbar {
      align-items: stretch;
      flex-direction: column;
    }

    .switcher-logout-btn {
      width: fit-content;
    }
  }
</style>

<div class="wrapper">
  <main class="page-content">
    <div class="container-fluid switcher-sky-bg">
      <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10 col-md-11">
          <div class="card border-0" style="background: transparent; box-shadow: none;">
            <div class="card-body px-0">
              <div class="switcher-topbar mb-3">
                <div>
                  <h3 class="mb-2 switcher-title">Choose Dashboard</h3>
                  <p class="switcher-subtitle mb-0">This account has multiple roles. Select where you want to continue.</p>
                </div>
                <div class="switcher-actions">
                  <a href="{{ $returnTo ?? route('admin.dashboard') }}" class="switcher-back-btn" aria-label="Back to previous page">
                    <i class="fa fa-arrow-left"></i>
                    <span>Back</span>
                  </a>
                  <a href="{{ route('scms.logout') }}" class="switcher-logout-btn" aria-label="Logout">
                    <i class="fa fa-sign-out-alt"></i>
                    <span>Logout</span>
                  </a>
                </div>
              </div>

              @if(session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
              @endif

              <div class="weather-role-grid">
                @foreach($roleOptions as $option)
                @php
                $theme = $weatherThemes[$loop->index % count($weatherThemes)];
                $isActive = ($activeRole === ($option['role'] ?? ''));
                @endphp
                <form method="POST" action="{{ route('dashboard.switch') }}">
                  @csrf
                  <input type="hidden" name="role" value="{{ $option['role'] }}">
                  <button type="submit" class="role-card-button" aria-label="Switch to {{ $option['label'] }} dashboard">
                    <div class="weather-role-card theme-{{ $theme['slug'] }} {{ $isActive ? 'active-card' : '' }}">
                      <div class="weather-left">
                        <div class="weather-glyph">
                          <img src="{{ $userCardImage }}" alt="User">
                        </div>

                        <h2 class="temp">{{ $theme['tagline'] }}</h2>
                      </div>

                      <div class="meta">
                        <span class="weather-label">User Dashboard</span>
                        <span class="role-label">{{ $option['label'] }}</span>
                      </div>
                    </div>
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