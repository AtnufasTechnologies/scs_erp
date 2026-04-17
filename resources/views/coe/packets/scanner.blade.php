@include('includes.header')

<div class="wrapper">
  @include('coe.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Barcode Scanner</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('coe.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('coe.packets.index') }}">Packets</a></li>
            <li class="breadcrumb-item active" aria-current="page">Scanner</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card gradient-coe shadow-lg border-0">
            <div class="card-body p-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h3 class="text-white fw-bold mb-2"><i class="fas fa-qrcode me-2"></i>Barcode Scanner</h3>
                  <p class="text-white-50 mb-0">Scan packet barcodes using your camera or enter manually</p>
                </div>
                <div class="col-md-4 text-md-end">
                  <a href="{{ route('coe.packets.barcodes.tracking') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-map-marker-alt me-2"></i>Tracking Dashboard
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Scanner Column -->
        <div class="col-lg-6 mb-4">
          <!-- Camera Scanner -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-camera me-2 text-primary"></i>Camera Scanner</h5>
              <button type="button" class="btn btn-sm btn-outline-primary" id="toggleCameraBtn">
                <i class="fas fa-video me-1"></i>Start Camera
              </button>
            </div>
            <div class="card-body text-center">
              <div id="scanner-container" style="display:none;">
                <div id="reader" style="width: 100%;"></div>
              </div>
              <div id="scanner-placeholder">
                <i class="fas fa-camera fa-4x text-muted mb-3 d-block"></i>
                <p class="text-muted">Click "Start Camera" to begin scanning</p>
              </div>
            </div>
          </div>

          <!-- Manual Entry -->
          <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-keyboard me-2 text-success"></i>Manual Entry</h5>
            </div>
            <div class="card-body">
              <div class="input-group">
                <input type="text" class="form-control form-control-lg" id="manualBarcodeInput"
                  placeholder="Enter or paste barcode..." maxlength="100"
                  autocomplete="off" autofocus>
                <button class="btn btn-primary btn-lg" type="button" id="manualLookupBtn">
                  <i class="fas fa-search me-1"></i>Lookup
                </button>
              </div>
            </div>
          </div>

          <!-- Scan Action Form -->
          <div class="card shadow-sm border-0" id="scanActionCard" style="display:none;">
            <div class="card-header bg-white">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-bolt me-2 text-warning"></i>Record Action</h5>
            </div>
            <div class="card-body">
              <input type="hidden" id="scannedBarcode">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Action <span class="text-danger">*</span></label>
                  <select class="form-select" id="scanAction" required>
                    <option value="received">Received</option>
                    <option value="transferred">Transferred</option>
                    <option value="returned">Returned</option>
                    <option value="status_update">Update Status</option>
                  </select>
                </div>
                <div class="col-md-6" id="statusUpdateGroup" style="display:none;">
                  <label class="form-label fw-semibold">New Status</label>
                  <select class="form-select" id="scanNewStatus">
                    <option value="">-- Select --</option>
                    <option value="generated">Generated</option>
                    <option value="assigned">Assigned</option>
                    <option value="evaluating">Evaluating</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Current Holder Name</label>
                  <input type="text" class="form-control" id="scanHolderName" placeholder="Who has it now?" maxlength="255">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Holder Role</label>
                  <select class="form-select" id="scanHolderRole">
                    <option value="">-- Select --</option>
                    <option value="COE Office">COE Office</option>
                    <option value="Evaluator">Evaluator</option>
                    <option value="Moderator">Moderator</option>
                    <option value="Tabulator">Tabulator</option>
                    <option value="Store">Store</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Remarks</label>
                  <textarea class="form-control" id="scanRemarks" rows="2" placeholder="Optional remarks..." maxlength="500"></textarea>
                </div>
                <div class="col-12">
                  <button type="button" class="btn btn-success btn-lg w-100" id="submitScanBtn">
                    <i class="fas fa-check-circle me-2"></i>Submit Scan
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Result Column -->
        <div class="col-lg-6 mb-4">
          <!-- Packet Info Card -->
          <div class="card shadow-sm border-0 mb-4" id="packetInfoCard" style="display:none;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-box me-2 text-info"></i>Packet Details</h5>
              <span class="badge fs-6" id="packetStatusBadge"></span>
            </div>
            <div class="card-body">
              <table class="table table-borderless mb-0">
                <tr>
                  <td class="fw-semibold text-muted" width="40%">Packet Number</td>
                  <td id="infoPacketNumber" class="fw-bold"></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Barcode</td>
                  <td id="infoBarcode"><code></code></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Subject</td>
                  <td id="infoSubject"></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Session</td>
                  <td id="infoSession"></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Total Scripts</td>
                  <td id="infoScripts"></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Evaluator</td>
                  <td id="infoEvaluator"></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Current Holder</td>
                  <td id="infoHolder"></td>
                </tr>
                <tr>
                  <td class="fw-semibold text-muted">Last Scanned</td>
                  <td id="infoLastScan"></td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Recent Scans for this packet -->
          <div class="card shadow-sm border-0" id="recentScansCard" style="display:none;">
            <div class="card-header bg-white">
              <h5 class="mb-0 fw-semibold"><i class="fas fa-history me-2 text-secondary"></i>Recent Scan History</h5>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Action</th>
                      <th>By</th>
                      <th>Holder</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody id="recentScansBody">
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Scan Success Alert -->
          <div class="alert alert-success border-0 shadow-sm" id="scanSuccessAlert" style="display:none;">
            <i class="fas fa-check-circle me-2 fs-5"></i>
            <span id="scanSuccessMsg"></span>
          </div>

          <!-- Scan Error Alert -->
          <div class="alert alert-danger border-0 shadow-sm" id="scanErrorAlert" style="display:none;">
            <i class="fas fa-exclamation-circle me-2 fs-5"></i>
            <span id="scanErrorMsg"></span>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Hidden inputs for JS -->
<input type="hidden" id="jsCsrfToken" value="{{ csrf_token() }}">
<input type="hidden" id="jsLookupUrl" value="{{ route('coe.packets.barcodes.lookup') }}">
<input type="hidden" id="jsScanUrl" value="{{ route('coe.packets.barcodes.scan') }}">

<style>
  .gradient-coe {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }

  #reader {
    border-radius: 8px;
    overflow: hidden;
  }

  #reader video {
    border-radius: 8px;
  }

  .scan-log-row {
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-5px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<!-- html5-qrcode library for camera barcode scanning -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = document.getElementById('jsCsrfToken').value;
    var lookupUrl = document.getElementById('jsLookupUrl').value;
    var scanUrl = document.getElementById('jsScanUrl').value;

    var html5QrcodeScanner = null;
    var cameraRunning = false;

    // Status badge mapping
    function getStatusBadge(status) {
      var map = {
        'generated': 'bg-warning text-dark',
        'assigned': 'bg-info',
        'evaluating': 'bg-primary',
        'completed': 'bg-success'
      };
      return map[status] || 'bg-secondary';
    }

    // Show/hide status update dropdown
    document.getElementById('scanAction').addEventListener('change', function() {
      var group = document.getElementById('statusUpdateGroup');
      group.style.display = this.value === 'status_update' ? 'block' : 'none';
    });

    // Toggle camera
    document.getElementById('toggleCameraBtn').addEventListener('click', function() {
      if (cameraRunning) {
        stopCamera();
      } else {
        startCamera();
      }
    });

    function startCamera() {
      document.getElementById('scanner-container').style.display = 'block';
      document.getElementById('scanner-placeholder').style.display = 'none';

      html5QrcodeScanner = new Html5Qrcode('reader');
      html5QrcodeScanner.start({
          facingMode: 'environment'
        }, {
          fps: 10,
          qrbox: {
            width: 250,
            height: 150
          },
          formatsToSupport: [
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.QR_CODE
          ]
        },
        function(decodedText) {
          // Success callback
          onBarcodeScanned(decodedText);
        },
        function(errorMessage) {
          // Error callback - ignore continuous scan errors
        }
      ).then(function() {
        cameraRunning = true;
        var btn = document.getElementById('toggleCameraBtn');
        btn.innerHTML = '<i class="fas fa-video-slash me-1"></i>Stop Camera';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-outline-danger');
      }).catch(function(err) {
        alert('Camera access failed: ' + err);
        document.getElementById('scanner-container').style.display = 'none';
        document.getElementById('scanner-placeholder').style.display = 'block';
      });
    }

    function stopCamera() {
      if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(function() {
          cameraRunning = false;
          var btn = document.getElementById('toggleCameraBtn');
          btn.innerHTML = '<i class="fas fa-video me-1"></i>Start Camera';
          btn.classList.remove('btn-outline-danger');
          btn.classList.add('btn-outline-primary');
          document.getElementById('scanner-container').style.display = 'none';
          document.getElementById('scanner-placeholder').style.display = 'block';
        });
      }
    }

    // On barcode scanned (from camera)
    function onBarcodeScanned(barcode) {
      // Pause camera briefly to avoid duplicate scans
      if (html5QrcodeScanner && cameraRunning) {
        html5QrcodeScanner.pause(true);
        setTimeout(function() {
          if (cameraRunning && html5QrcodeScanner) {
            try {
              html5QrcodeScanner.resume();
            } catch (e) {}
          }
        }, 3000);
      }

      document.getElementById('manualBarcodeInput').value = barcode;
      lookupBarcode(barcode);
    }

    // Manual lookup
    document.getElementById('manualLookupBtn').addEventListener('click', function() {
      var barcode = document.getElementById('manualBarcodeInput').value.trim();
      if (barcode) {
        lookupBarcode(barcode);
      }
    });

    // Enter key on manual input
    document.getElementById('manualBarcodeInput').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        var barcode = this.value.trim();
        if (barcode) {
          lookupBarcode(barcode);
        }
      }
    });

    // Lookup barcode
    function lookupBarcode(barcode) {
      hideAlerts();

      fetch(lookupUrl + '?barcode=' + encodeURIComponent(barcode), {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          }
        })
        .then(function(r) {
          return r.json().then(function(d) {
            return {
              ok: r.ok,
              data: d
            };
          });
        })
        .then(function(result) {
          if (result.ok && result.data.success) {
            showPacketInfo(result.data.packet);
            showRecentScans(result.data.recent_scans);
            document.getElementById('scannedBarcode').value = barcode;
            document.getElementById('scanActionCard').style.display = 'block';
          } else {
            showError(result.data.message || 'Barcode not found.');
            document.getElementById('packetInfoCard').style.display = 'none';
            document.getElementById('recentScansCard').style.display = 'none';
            document.getElementById('scanActionCard').style.display = 'none';
          }
        })
        .catch(function() {
          showError('Network error. Please check your connection.');
        });
    }

    // Display packet info
    function showPacketInfo(packet) {
      document.getElementById('packetInfoCard').style.display = 'block';
      document.getElementById('infoPacketNumber').textContent = packet.packet_number;
      document.getElementById('infoBarcode').innerHTML = '<code>' + escapeHtml(packet.barcode) + '</code>';
      document.getElementById('infoSubject').textContent = packet.subject;
      document.getElementById('infoSession').textContent = packet.session;
      document.getElementById('infoScripts').textContent = packet.total_scripts;
      document.getElementById('infoEvaluator').textContent = packet.evaluator;
      document.getElementById('infoHolder').textContent = packet.current_holder + (packet.current_holder_role !== 'N/A' ? ' (' + packet.current_holder_role + ')' : '');
      document.getElementById('infoLastScan').textContent = packet.last_scanned_at;

      var badge = document.getElementById('packetStatusBadge');
      badge.className = 'badge fs-6 ' + getStatusBadge(packet.status);
      badge.textContent = packet.status.charAt(0).toUpperCase() + packet.status.slice(1);
    }

    // Display recent scans
    function showRecentScans(scans) {
      var card = document.getElementById('recentScansCard');
      var tbody = document.getElementById('recentScansBody');
      tbody.innerHTML = '';

      if (scans && scans.length > 0) {
        card.style.display = 'block';
        scans.forEach(function(scan) {
          var tr = document.createElement('tr');
          tr.className = 'scan-log-row';
          tr.innerHTML = '<td><span class="badge ' + escapeHtml(scan.action_badge) + '">' + escapeHtml(scan.action) + '</span></td>' +
            '<td>' + escapeHtml(scan.scanned_by) + '</td>' +
            '<td>' + escapeHtml(scan.holder) + '</td>' +
            '<td><small>' + escapeHtml(scan.date) + '</small></td>';
          tbody.appendChild(tr);
        });
      } else {
        card.style.display = 'none';
      }
    }

    // Submit scan action
    document.getElementById('submitScanBtn').addEventListener('click', function() {
      var barcode = document.getElementById('scannedBarcode').value;
      var action = document.getElementById('scanAction').value;
      var holderName = document.getElementById('scanHolderName').value.trim();
      var holderRole = document.getElementById('scanHolderRole').value;
      var newStatus = document.getElementById('scanNewStatus').value;
      var remarks = document.getElementById('scanRemarks').value.trim();

      if (!barcode) {
        showError('No barcode scanned. Please scan first.');
        return;
      }

      var btn = this;
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
      hideAlerts();

      var body = {
        barcode: barcode,
        action: action,
        holder_name: holderName || null,
        holder_role: holderRole || null,
        remarks: remarks || null
      };

      if (action === 'status_update' && newStatus) {
        body.new_status = newStatus;
      }

      // Try to get geolocation
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function(pos) {
            body.latitude = pos.coords.latitude;
            body.longitude = pos.coords.longitude;
            submitScanRequest(body, btn);
          },
          function() {
            submitScanRequest(body, btn);
          }, {
            timeout: 3000
          }
        );
      } else {
        submitScanRequest(body, btn);
      }
    });

    function submitScanRequest(body, btn) {
      fetch(scanUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify(body)
        })
        .then(function(r) {
          return r.json().then(function(d) {
            return {
              ok: r.ok,
              data: d
            };
          });
        })
        .then(function(result) {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Submit Scan';

          if (result.ok && result.data.success) {
            showSuccess(result.data.message);
            showPacketInfo(result.data.packet);
            // Refresh recent scans
            lookupBarcode(body.barcode);
            // Clear form
            document.getElementById('scanRemarks').value = '';
          } else {
            showError(result.data.message || 'Scan failed.');
          }
        })
        .catch(function() {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Submit Scan';
          showError('Network error. Please try again.');
        });
    }

    function showSuccess(msg) {
      hideAlerts();
      var el = document.getElementById('scanSuccessAlert');
      document.getElementById('scanSuccessMsg').textContent = msg;
      el.style.display = 'block';
      setTimeout(function() {
        el.style.display = 'none';
      }, 5000);
    }

    function showError(msg) {
      hideAlerts();
      var el = document.getElementById('scanErrorAlert');
      document.getElementById('scanErrorMsg').textContent = msg;
      el.style.display = 'block';
      setTimeout(function() {
        el.style.display = 'none';
      }, 5000);
    }

    function hideAlerts() {
      document.getElementById('scanSuccessAlert').style.display = 'none';
      document.getElementById('scanErrorAlert').style.display = 'none';
    }

    function escapeHtml(str) {
      if (!str) return '';
      var div = document.createElement('div');
      div.appendChild(document.createTextNode(str));
      return div.innerHTML;
    }
  });
</script>

@include('includes.footer')