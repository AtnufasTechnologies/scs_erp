<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Packet Barcode Labels - Print</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Courier New', monospace;
      background: #fff;
    }

    .print-controls {
      padding: 20px;
      text-align: center;
      background: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
    }

    .print-controls button {
      padding: 10px 30px;
      font-size: 16px;
      cursor: pointer;
      border: none;
      border-radius: 5px;
      margin: 0 5px;
    }

    .btn-print {
      background: #667eea;
      color: #fff;
    }

    .btn-back {
      background: #6c757d;
      color: #fff;
    }

    .labels-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 20px;
      gap: 15px;
    }

    .barcode-label {
      width: 320px;
      border: 2px solid #333;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
      page-break-inside: avoid;
      background: #fff;
    }

    .barcode-label .institution {
      font-size: 10px;
      font-weight: bold;
      text-transform: uppercase;
      margin-bottom: 5px;
      color: #333;
    }

    .barcode-label .label-title {
      font-size: 9px;
      color: #666;
      margin-bottom: 8px;
    }

    .barcode-label .packet-number {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 5px;
      color: #000;
    }

    .barcode-label .subject-info {
      font-size: 11px;
      margin-bottom: 3px;
      color: #444;
    }

    .barcode-label .scripts-info {
      font-size: 10px;
      margin-bottom: 10px;
      color: #666;
    }

    .barcode-label .barcode-visual {
      margin: 10px auto;
      padding: 8px;
      background: #fff;
      border: 1px solid #eee;
    }

    .barcode-label .barcode-visual svg {
      display: block;
      margin: 0 auto;
    }

    .barcode-label .barcode-text {
      font-size: 11px;
      font-family: 'Courier New', monospace;
      letter-spacing: 1px;
      margin-top: 5px;
      color: #000;
    }

    .barcode-label .footer-info {
      font-size: 8px;
      color: #999;
      margin-top: 8px;
      border-top: 1px dashed #ccc;
      padding-top: 5px;
    }

    @media print {
      .print-controls {
        display: none !important;
      }

      .labels-container {
        padding: 10px;
        gap: 10px;
      }

      .barcode-label {
        border: 1.5px solid #000;
        width: 300px;
      }

      body {
        background: #fff;
      }
    }
  </style>
</head>

<body>
  <div class="print-controls">
    <button class="btn-print" onclick="window.print()">
      🖨️ Print Labels
    </button>
    <button class="btn-back" onclick="window.history.back()">
      ← Go Back
    </button>
    <p style="margin-top: 10px; color: #666; font-size: 14px;">
      {{ $packets->count() }} label(s) ready to print
    </p>
  </div>

  <div class="labels-container">
    @forelse($packets as $packet)
    <div class="barcode-label">
      <div class="institution">Controller of Examinations</div>
      <div class="label-title">Exam Packet Label</div>
      <div class="packet-number">{{ $packet->packet_number }}</div>
      <div class="subject-info">
        {{ $packet->subjectMaster->subject_code ?? '' }} - {{ $packet->subjectMaster->name ?? 'N/A' }}
      </div>
      <div class="scripts-info">
        Scripts: {{ $packet->total_scripts }} |
        Session: {{ $packet->examSession->name ?? 'Session #'.$packet->exam_session_id }}
      </div>
      <div class="barcode-visual">
        <svg class="barcode-svg" data-value="{{ $packet->barcode }}"></svg>
      </div>
      <div class="barcode-text">{{ $packet->barcode }}</div>
      <div class="footer-info">
        Generated: {{ $packet->created_at->format('d M Y') }} |
        Status: {{ ucfirst($packet->status) }}
      </div>
    </div>
    @empty
    <div style="text-align: center; padding: 50px; color: #999;">
      <h3>No packets with barcodes found</h3>
      <p>Generate barcodes first from the packets management page.</p>
    </div>
    @endforelse
  </div>

  <!-- JsBarcode library for rendering barcodes -->
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.barcode-svg').forEach(function(svg) {
        var value = svg.getAttribute('data-value');
        if (value) {
          try {
            JsBarcode(svg, value, {
              format: 'CODE128',
              width: 1.5,
              height: 50,
              displayValue: false,
              margin: 0
            });
          } catch (e) {
            svg.parentNode.innerHTML = '<p style="color:red;font-size:10px;">Barcode error</p>';
          }
        }
      });
    });
  </script>
</body>

</html>