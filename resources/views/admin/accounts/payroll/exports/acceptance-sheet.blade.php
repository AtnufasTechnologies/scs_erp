<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <title>{{ $title }}</title>
  <style>
    @page {
      margin: 14px;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 10px;
      color: #222;
    }

    .header {
      text-align: center;
      margin-bottom: 8px;
      line-height: 1.2;
    }

    .header .org {
      font-size: 14px;
      font-weight: 700;
    }

    .header .title {
      font-size: 11px;
      font-weight: 700;
      margin-top: 3px;
      text-transform: uppercase;
    }

    .header .meta {
      font-size: 9px;
      margin-top: 2px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    th,
    td {
      border: 1px solid #333;
      padding: 4px 3px;
      vertical-align: middle;
      word-wrap: break-word;
    }

    th {
      background: #f2f2f2;
      text-align: center;
      font-size: 9px;
    }

    td.num {
      text-align: right;
    }

    td.center {
      text-align: center;
    }

    .signature-cell {
      height: 34px;
    }

    .footer-note {
      margin-top: 8px;
      font-size: 9px;
    }

    .slip-no {
      display: block;
      margin-top: 2px;
      font-size: 8px;
      color: #444;
    }
  </style>
</head>

<body>
  <div class="header">
    <div class="org">Salesian College (Autonomous), Siliguri</div>
    <div class="title">{{ $title }}</div>
    <div class="meta">Financial Year: {{ $activeFinancialYear->title ?? 'N/A' }}</div>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 3%;">Sl.</th>
        <th style="width: 14%;">Name</th>
        <th style="width: 8%;">Basic</th>
        <th style="width: 6%;">DA</th>
        <th style="width: 6%;">HRA</th>
        <th style="width: 7%;">Special Allow.</th>
        <th style="width: 6%;">MA</th>
        <th style="width: 8%;">Gross</th>
        <th style="width: 6%;">PF</th>
        <th style="width: 5%;">PT</th>
        <th style="width: 6%;">Insurance</th>
        <th style="width: 7%;">Advance Rec.</th>
        <th style="width: 7%;">Salary Deduction</th>
        <th style="width: 8%;">NET</th>
        <th style="width: 9%;">Signature</th>
      </tr>
    </thead>
    <tbody>
      @foreach($salarySlips as $index => $slip)
      @php
      $salaryDeduction = max((float) $slip->total_deductions - ((float) $slip->pf + (float) $slip->professional_tax + (float) $slip->esi + (float) $slip->loan_deduction), 0);
      @endphp
      <tr>
        <td class="center">{{ $index + 1 }}</td>
        <td>
          {{ trim(($slip->faculty->FIRST_NAME ?? '') . ' ' . ($slip->faculty->LAST_NAME ?? '')) }}
          <span class="slip-no">{{ $slip->salary_slip_number }}</span>
        </td>
        <td class="num">{{ number_format((float) $slip->basic_salary, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->da, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->hra, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->special_allowance, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->medical_allowance, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->gross_salary, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->pf, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->professional_tax, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->esi, 2) }}</td>
        <td class="num">{{ number_format((float) $slip->loan_deduction, 2) }}</td>
        <td class="num">{{ number_format($salaryDeduction, 2) }}</td>
        <td class="num"><strong>{{ number_format((float) $slip->net_salary, 2) }}</strong></td>
        <td class="signature-cell"></td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer-note">
    Signature column is to be signed by each faculty as payroll acceptance.
  </div>
</body>

</html>