<div class="modal fade" id="manualPayModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Record Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="{{ route('manual.fee.payment') }}">
        @csrf

        <input type="hidden" name="student_id" id="modal_student_id">
        <input type="hidden" name="fee_structure_id" id="modal_fee_id">

        <div class="modal-body">

          <div class="mb-3">
            <label class="fw-bold">Roll No *</label>
            <input type="text" id="modal_roll_no" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Student Name *</label>
            <input type="text" id="modal_student_name" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Quarter *</label>
            <input type="text" id="modal_quarter" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Amount *</label>
            <input type="number" id="amount" name="amount" class="form-control" readonly>
          </div>


          <div class="mb-3">
            <label class="fw-bold">Transaction Date *</label>
            <input type="date" class="form-control" name="transaction_date" required>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Transaction Ref *</label>
            <input type="text" class="form-control" name="transaction_ref" required>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Payment Gateway *</label>
            <select name="gateway_type_id" class="form-select" required>
              <option value="1">Easebuzz</option>
              <option value="2">Billdesk</option>
              <option value="3">Cash</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Record Payment</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.manualPayBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        modal_student_id.value = btn.dataset.studentId;
        modal_fee_id.value = btn.dataset.feeId;
        modal_roll_no.value = btn.dataset.rollno;
        modal_student_name.value = btn.dataset.studentName;
        modal_quarter.value = btn.dataset.quarter;
        amount.value = btn.dataset.amount;
      });
    });
  });
</script>