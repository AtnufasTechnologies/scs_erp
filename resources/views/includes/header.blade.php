<!doctype html>
<html lang="en" class="semi-dark">

<head>
  @php
  $assetVersion = function ($path) {
  $fullPath = public_path($path);
  $version = file_exists($fullPath) ? filemtime($fullPath) : time();
  return asset($path) . '?v=' . $version;
  };
  @endphp
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="shortcut icon" href="{{asset('admin/images/logo.png')}}" type="image/x-icon">
  <!--plugins-->
  <link href="{{ $assetVersion('admin/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
  <link href="{{ $assetVersion('admin/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ $assetVersion('admin/plugins/metismenu/css/metisMenu.min.css') }}" rel="stylesheet" />
  <link rel="shortcut icon" href="{{asset('admin/images/logo.png')}}" type="image/x-icon">
  <!-- CSS Files -->
  <link href="{{ $assetVersion('admin/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ $assetVersion('admin/css/bootstrap-extended.css') }}" rel="stylesheet">
  <link href="{{ $assetVersion('admin/css/custom.css') }}" rel="stylesheet">
  <link href="{{ $assetVersion('admin/css/style.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ $assetVersion('admin/css/uiux.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
  <!--Theme Styles-->
  <link href="{{ $assetVersion('admin/css/dark-theme.css') }}" rel="stylesheet" />
  <link href="{{ $assetVersion('admin/css/semi-dark.css') }}" rel="stylesheet" />
  <link href="{{ $assetVersion('admin/css/header-colors.css') }}" rel="stylesheet" />
  <!-- Extras -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.1/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="{{ $assetVersion('admin/fontawesomepro/all.min.css') }}" />
  <!--BS Multi Select  -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@dashboardcode/bsmultiselect@1.1.18/dist/css/BsMultiSelect.bs4.min.css">
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
  <link rel="stylesheet" href="{{ $assetVersion('admin/css/icofont.min.css') }}">
  <link rel="stylesheet" href="https://unpkg.com/@jarstone/dselect/dist/css/dselect.css">
  <title>SCMS | Salesian College Autonomous (Siliguri & Sonada) </title>

</head>

<body>
  @include('includes.alert')
  @auth
  @php
  $dashboardRoleOptions = collect(session('dashboard_role_options', []));
  @endphp
  @if($dashboardRoleOptions->count() > 1)
  <style>
    .dashboard-fab {
      position: fixed;
      top: 14px;
      right: 14px;
      z-index: 1055;
      border-radius: 999px;
      min-height: 56px;
      padding: 0 16px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
      color: #fff;
      font-weight: 600;
      box-shadow: 0 10px 22px rgba(101, 163, 13, 0.38);
      text-decoration: none;
      transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .dashboard-fab:hover {
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(101, 163, 13, 0.46);
    }

    .dashboard-fab .fab-icon {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.18);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .dashboard-fab .fab-label {
      font-size: 13px;
      letter-spacing: 0.2px;
      line-height: 1;
      white-space: nowrap;
    }

    @media (max-width: 768px) {
      .dashboard-fab {
        min-height: 52px;
        padding: 0 14px;
      }

      .dashboard-fab .fab-label {
        display: none;
      }
    }
  </style>

  <a
    href="{{ route('dashboard.switcher', ['return_to' => url()->full()]) }}"
    id="dashboard-switch-fab"
    class="dashboard-fab"
    title="Switch Dashboard"
    aria-label="Switch Dashboard">
    <span class="fab-icon"><i class="fa fa-exchange-alt"></i></span>
    <span class="fab-label">Switch Dashboard</span>
  </a>

  <script>
    (function() {
      const fab = document.getElementById('dashboard-switch-fab');
      if (!fab) return;
      // Remove legacy saved drag position so the button remains at its default fixed location.
      try {
        localStorage.removeItem('dashboard-switch-fab-pos-v1');
      } catch (e) {
        // Ignore storage access errors.
      }
    })();
  </script>
  @endif
  @endauth
  <!--start wrapper-->
  <div class="wrapper">