<!doctype html>
<html lang="en" class="semi-dark">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="shortcut icon" href="{{asset('admin/images/logo.png')}}" type="image/x-icon">
  <!--plugins-->
  <link href="{{asset('admin/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
  <link href="{{asset('admin/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
  <link href="{{asset('admin/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
  <link rel="shortcut icon" href="{{asset('admin/images/logo.png')}}" type="image/x-icon">
  <!-- CSS Files -->
  <link href="{{asset('admin/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{asset('admin/css/bootstrap-extended.css')}}" rel="stylesheet">
  <link href="{{asset('admin/css/custom.css')}}" rel="stylesheet">
  <link href="{{asset('admin/css/style.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="{{asset('admin/css/uiux.css')}}">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
  <!--Theme Styles-->
  <link href="{{asset('admin/css/dark-theme.css')}}" rel="stylesheet" />
  <link href="{{asset('admin/css/semi-dark.css')}}" rel="stylesheet" />
  <link href="{{asset('admin/css/header-colors.css')}}" rel="stylesheet" />
  <!-- Extras -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.1/css/buttons.dataTables.min.css">
  <link rel="stylesheet" href="{{ asset('admin/fontawesomepro/all.min.css') }}" />
  <!--BS Multi Select  -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@dashboardcode/bsmultiselect@1.1.18/dist/css/BsMultiSelect.bs4.min.css">
  <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
  <link rel="stylesheet" href="{{asset('admin/css/icofont.min.css')}}">
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
      background: linear-gradient(135deg, #0d6efd 0%, #4f9bff 100%);
      color: #fff;
      font-weight: 600;
      box-shadow: 0 10px 22px rgba(13, 110, 253, 0.36);
      cursor: grab;
      user-select: none;
      -webkit-user-select: none;
      -webkit-tap-highlight-color: transparent;
      text-decoration: none;
      transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .dashboard-fab:hover {
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(13, 110, 253, 0.42);
    }

    .dashboard-fab:active {
      cursor: grabbing;
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

      const storageKey = 'dashboard-switch-fab-pos-v1';
      let dragging = false;
      let moved = false;
      let startX = 0;
      let startY = 0;
      let pointerOffsetX = 0;
      let pointerOffsetY = 0;

      function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
      }

      function getBounds() {
        const margin = 8;
        const maxX = window.innerWidth - fab.offsetWidth - margin;
        const maxY = window.innerHeight - fab.offsetHeight - margin;
        return {
          minX: margin,
          minY: margin,
          maxX: Math.max(margin, maxX),
          maxY: Math.max(margin, maxY)
        };
      }

      function setPosition(x, y) {
        const bounds = getBounds();
        const safeX = clamp(x, bounds.minX, bounds.maxX);
        const safeY = clamp(y, bounds.minY, bounds.maxY);

        fab.style.left = safeX + 'px';
        fab.style.top = safeY + 'px';
        fab.style.right = 'auto';
        fab.style.bottom = 'auto';
      }

      function savePosition() {
        const x = parseInt(fab.style.left || '0', 10);
        const y = parseInt(fab.style.top || '0', 10);
        if (Number.isFinite(x) && Number.isFinite(y)) {
          localStorage.setItem(storageKey, JSON.stringify({
            x,
            y
          }));
        }
      }

      function snapToEdge() {
        if (!fab.style.left || !fab.style.top) return;

        const currentX = parseInt(fab.style.left, 10);
        const currentY = parseInt(fab.style.top, 10);
        const bounds = getBounds();
        const centerX = currentX + (fab.offsetWidth / 2);
        const viewportCenterX = window.innerWidth / 2;

        const targetX = centerX < viewportCenterX ? bounds.minX : bounds.maxX;

        fab.style.transition = 'left 220ms cubic-bezier(.2,.8,.2,1), top 220ms cubic-bezier(.2,.8,.2,1), box-shadow .2s ease, transform .2s ease';
        setPosition(targetX, currentY);

        window.setTimeout(function() {
          fab.style.transition = 'box-shadow .2s ease, transform .2s ease';
          savePosition();
        }, 240);
      }

      function restorePosition() {
        try {
          const raw = localStorage.getItem(storageKey);
          if (!raw) return;
          const pos = JSON.parse(raw);
          if (typeof pos.x === 'number' && typeof pos.y === 'number') {
            setPosition(pos.x, pos.y);
          }
        } catch (e) {
          // Ignore invalid saved position.
        }
      }

      function pointerDown(event) {
        const point = event.touches ? event.touches[0] : event;
        dragging = true;
        moved = false;
        startX = point.clientX;
        startY = point.clientY;

        const rect = fab.getBoundingClientRect();
        pointerOffsetX = point.clientX - rect.left;
        pointerOffsetY = point.clientY - rect.top;

        fab.style.cursor = 'grabbing';
      }

      function pointerMove(event) {
        if (!dragging) return;

        const point = event.touches ? event.touches[0] : event;
        const deltaX = Math.abs(point.clientX - startX);
        const deltaY = Math.abs(point.clientY - startY);

        if (deltaX > 3 || deltaY > 3) {
          moved = true;
        }

        const nextX = point.clientX - pointerOffsetX;
        const nextY = point.clientY - pointerOffsetY;
        setPosition(nextX, nextY);

        if (event.cancelable) {
          event.preventDefault();
        }
      }

      function pointerUp() {
        if (!dragging) return;
        dragging = false;
        fab.style.cursor = 'grab';

        if (moved) {
          snapToEdge();
        }
      }

      fab.addEventListener('mousedown', pointerDown);
      window.addEventListener('mousemove', pointerMove);
      window.addEventListener('mouseup', pointerUp);

      fab.addEventListener('touchstart', pointerDown, {
        passive: true
      });
      window.addEventListener('touchmove', pointerMove, {
        passive: false
      });
      window.addEventListener('touchend', pointerUp);
      window.addEventListener('touchcancel', pointerUp);

      fab.addEventListener('click', function(event) {
        if (moved) {
          event.preventDefault();
          event.stopPropagation();
          moved = false;
        }
      });

      window.addEventListener('resize', function() {
        if (!fab.style.left || !fab.style.top) return;
        setPosition(parseInt(fab.style.left, 10), parseInt(fab.style.top, 10));
        savePosition();
      });

      restorePosition();
    })();
  </script>
  @endif
  @endauth
  <!--start wrapper-->
  <div class="wrapper">