<!doctype html>
<html lang="en" class="semi-dark">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Theme Diagnostic</title>

  <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/css/bootstrap-extended.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/css/custom.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/css/style.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/css/uiux.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/css/dark-theme.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/css/semi-dark.css') }}" rel="stylesheet">
  <link href="{{ asset('admin/css/header-colors.css') }}" rel="stylesheet">

  <style>
    body {
      min-height: 100vh;
      padding: 20px;
      background: #f4f7fb;
    }

    .diag-wrap {
      max-width: 1080px;
      margin: 0 auto;
    }

    .diag-card {
      border-radius: 14px;
      border: 1px solid #e7ebf2;
      background: #fff;
      box-shadow: 0 10px 30px rgba(12, 26, 75, 0.06);
      margin-bottom: 16px;
    }

    .diag-card .card-body {
      padding: 18px;
    }

    .sidebar-probe {
      border-radius: 10px;
      min-height: 120px;
      border: 1px dashed rgba(255, 255, 255, 0.35);
    }

    #diag-report {
      white-space: pre-wrap;
      font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
      font-size: 13px;
      margin: 0;
      background: #0f172a;
      color: #dbeafe;
      border-radius: 10px;
      padding: 12px;
      min-height: 220px;
      overflow: auto;
    }
  </style>
</head>

<body>
  <div class="diag-wrap">
    <div class="diag-card card">
      <div class="card-body">
        <h4 class="mb-2">Theme Diagnostic Page</h4>
        <p class="text-muted mb-3">Use this page on server to verify whether theme classes, CSS variables and button/sidebar styles are being applied.</p>

        <div class="d-flex flex-wrap gap-2 mb-3">
          <button id="mode-light" class="btn btn-outline-primary">Set light-theme</button>
          <button id="mode-dark" class="btn btn-outline-primary">Set dark-theme</button>
          <button id="mode-semi" class="btn btn-outline-primary">Set semi-dark</button>
          <button id="sidebar-color3" class="btn btn-outline-secondary">Set color-sidebar sidebarcolor3</button>
          <button id="sidebar-clear" class="btn btn-outline-secondary">Clear sidebar color classes</button>
          <button id="refresh-report" class="btn btn-primary">Refresh Report</button>
          <button id="copy-report" class="btn btn-secondary">Copy Report</button>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="p-3 sidebar-probe sidebar-wrapper" id="sidebar-probe">
              Sidebar probe (.sidebar-wrapper)
            </div>
          </div>
          <div class="col-md-6 d-flex align-items-start gap-2">
            <button class="btn btn-primary" id="probe-btn-primary">Primary Button</button>
            <button class="btn btn-secondary" id="probe-btn-secondary">Secondary Button</button>
            <button class="btn btn-danger" id="probe-btn-danger">Danger Button</button>
          </div>
        </div>
      </div>
    </div>

    <div class="diag-card card">
      <div class="card-body">
        <h5 class="mb-3">Live Report</h5>
        <pre id="diag-report"></pre>
      </div>
    </div>
  </div>

  <script src="{{ asset('admin/js/jquery.min.js') }}"></script>
  <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('admin/js/main.js') }}"></script>

  <script>
    (function() {
      const MODE_CLASSES = ["light-theme", "dark-theme", "semi-dark"];
      const SIDEBAR_COLOR_CLASSES = [
        "sidebarcolor1",
        "sidebarcolor2",
        "sidebarcolor3",
        "sidebarcolor4",
        "sidebarcolor5",
        "sidebarcolor6",
        "sidebarcolor7",
        "sidebarcolor8"
      ];

      function removeClasses(el, list) {
        list.forEach(function(name) {
          el.classList.remove(name);
        });
      }

      function setMode(modeName) {
        const html = document.documentElement;
        removeClasses(html, MODE_CLASSES);
        html.classList.add(modeName);
        refreshReport();
      }

      function setSidebarColor(sidebarName) {
        const html = document.documentElement;
        html.classList.add("semi-dark");
        html.classList.add("color-sidebar");
        removeClasses(html, SIDEBAR_COLOR_CLASSES);
        html.classList.add(sidebarName);
        refreshReport();
      }

      function clearSidebarColor() {
        const html = document.documentElement;
        html.classList.remove("color-sidebar");
        removeClasses(html, SIDEBAR_COLOR_CLASSES);
        refreshReport();
      }

      function pickVars(style, keys) {
        const out = {};
        keys.forEach(function(k) {
          out[k] = style.getPropertyValue(k).trim();
        });
        return out;
      }

      function safeSelectorRuleDump(selector) {
        const hits = [];
        for (const sheet of document.styleSheets) {
          let rules;
          try {
            rules = sheet.cssRules;
          } catch (err) {
            continue;
          }
          if (!rules) continue;

          for (const rule of rules) {
            if (rule.type === CSSRule.STYLE_RULE && rule.selectorText && rule.selectorText.indexOf(selector) !== -1) {
              hits.push((sheet.href || "inline") + " :: " + rule.selectorText);
            }
          }
        }
        return hits;
      }

      function refreshReport() {
        const html = document.documentElement;
        const rootStyle = getComputedStyle(html);
        const btnPrimary = document.getElementById("probe-btn-primary");
        const sidebarProbe = document.getElementById("sidebar-probe");
        const btnStyle = getComputedStyle(btnPrimary);
        const sidebarStyle = getComputedStyle(sidebarProbe);

        const payload = {
          url: window.location.href,
          htmlClassList: html.className,
          assetHints: {
            styleCss: document.querySelector('link[href*="/admin/css/style.css"]') ? "loaded" : "missing",
            uiuxCss: document.querySelector('link[href*="/admin/css/uiux.css"]') ? "loaded" : "missing",
            customCss: document.querySelector('link[href*="/admin/css/custom.css"]') ? "loaded" : "missing",
            bootstrapExtendedCss: document.querySelector('link[href*="/admin/css/bootstrap-extended.css"]') ? "loaded" : "missing",
            mainJs: document.querySelector('script[src*="/admin/js/main.js"]') ? "loaded" : "missing"
          },
          rootVars: pickVars(rootStyle, [
            "--theme-sidebar-gradient-start",
            "--theme-sidebar-gradient-end",
            "--theme-btn-primary-bg",
            "--theme-btn-primary-hover-bg"
          ]),
          probeComputed: {
            btnPrimaryBackgroundImage: btnStyle.backgroundImage,
            btnPrimaryBackgroundColor: btnStyle.backgroundColor,
            btnPrimaryBorderColor: btnStyle.borderColor,
            sidebarBackgroundImage: sidebarStyle.backgroundImage,
            sidebarBackgroundColor: sidebarStyle.backgroundColor
          },
          matchingSelectors: {
            btnPrimary: safeSelectorRuleDump(".btn-primary").slice(0, 30),
            sidebarWrapper: safeSelectorRuleDump(".sidebar-wrapper").slice(0, 30)
          }
        };

        document.getElementById("diag-report").textContent = JSON.stringify(payload, null, 2);
      }

      document.getElementById("mode-light").addEventListener("click", function() {
        setMode("light-theme");
      });
      document.getElementById("mode-dark").addEventListener("click", function() {
        setMode("dark-theme");
      });
      document.getElementById("mode-semi").addEventListener("click", function() {
        setMode("semi-dark");
      });
      document.getElementById("sidebar-color3").addEventListener("click", function() {
        setSidebarColor("sidebarcolor3");
      });
      document.getElementById("sidebar-clear").addEventListener("click", function() {
        clearSidebarColor();
      });
      document.getElementById("refresh-report").addEventListener("click", refreshReport);
      document.getElementById("copy-report").addEventListener("click", function() {
        const report = document.getElementById("diag-report").textContent;
        navigator.clipboard.writeText(report).then(function() {
          alert("Report copied");
        }).catch(function() {
          alert("Could not copy report");
        });
      });

      refreshReport();
    })();
  </script>
</body>

</html>