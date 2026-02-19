<?php

use App\Http\Controllers\StaticController;

?>
@include('includes.header')
@include('admin.sidebar')

<h3><span class="text-uppercase">SMS Template Manager</span></h3>
<p>As per <strong>TRAI (Telecom Regulatory Authority of India)</strong> guidelines</p>

<h5>Authorized Header - SCSCLG</h5>
<!-- Button trigger modal -->
<button class="cst-button mb-3" style="--clr: #21d9c7ff;" data-bs-toggle="modal" data-bs-target="#addTemplate">
  <span class="button-decor"></span>
  <div class="button-content">
    <div class="button__icon">
      <i class="fa fa-plus-circle"></i>
    </div>
    <span class="button__text">Add New</span>
  </div>
</button>

<div class="modal fade" id="addTemplate" tabindex="-1" aria-labelledby="addTemplateLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTemplateLabel">New SMS Template</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('sms.template.store')}}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <label for="">Template Name *</label>
          <input type="text" name="template_name" class="form-control mb-3" placeholder="Type Here...">

          <label for="">Template Content *</label>
          <div class="mb-3">
            <textarea name="template_content" id="template_content" class="form-control mb-2" rows="4" placeholder="Type Here..."></textarea>
            Add Variable
            <div class="d-flex gap-2 flex-wrap">

              <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertVariable('{#numeric#}')">Numeric</button>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertVariable('{#url#}')">Url</button>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertVariable('{#urlott#}')">UrlOtt</button>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertVariable('{#cbn#}')">Cbn</button>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertVariable('{#email#}')">Email</button>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertVariable('{#alphanumeric#}')">Alphanumeric</button>

            </div>
            <script>
              document.getElementById('addVariableBtn').addEventListener('click', function() {
                var modal = new bootstrap.Modal(document.getElementById('variableModal'));
                modal.show();
              });
            </script>
            <small class="d-block mt-2">
              Character Count: <span id="charCount">0</span>/160 | Messages: <span id="msgCount">0</span>
            </small>
          </div>



          <script>
            function insertVariable(variable) {
              const textarea = document.getElementById('template_content');
              textarea.value += variable;
              updateCharCount();
              bootstrap.Modal.getInstance(document.getElementById('variableModal')).hide();
            }

            function updateCharCount() {
              const content = document.getElementById('template_content').value;
              const charCount = content.length;
              const msgCount = Math.ceil(charCount / 160) || 0;
              document.getElementById('charCount').textContent = charCount;
              document.getElementById('msgCount').textContent = msgCount;
            }

            document.getElementById('template_content').addEventListener('input', updateCharCount);
          </script>

        </div>


        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="container mt-4">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h5>SMS Template Approval</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Template ID</th>
                <th>Template Name</th>
                <th>Template Content</th>
                <th>Status</th>
                <th>Actions</th>

                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($templates as $item)
              <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{ $item->template_identifier != null ? $item->template_identifier : 'Pending' }}</td>
                <td>{{ $item->template_name }}</td>
                <td>{{ $item->template_content }}</td>
                <td>
                  <span class="badge bg-{{ $item->status === 'approved' ? 'success' : 'warning' }}">
                    {{ ucfirst($item->status) }}
                  </span>
                </td>

                <td>
                  @if($item->status == 'approved')
                  <a href="{{ url('admin/sms-templates/suspend/' . $item->id) }}" class="btn btn-warning">Suspend</a>
                  @else
                  <a href="{{ route('sms.template.delete', $item->id) }}" onclick="return confirm('Are you sure you want to delete this template?');">
                    <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                  </a>
                  @endif
                </td>
                <td></td>

              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


@include('includes.footer')