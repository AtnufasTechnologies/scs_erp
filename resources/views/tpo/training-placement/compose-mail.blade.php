@include('includes.header')

<style>
  .gmail-compose-shell {
    max-width: 920px;
    margin: 0 auto;
  }

  .gmail-compose-card {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #dfe3eb;
    box-shadow: 0 12px 30px rgba(28, 46, 84, 0.12);
    background: #fff;
  }

  .gmail-compose-head {
    background: #f1f3f4;
    border-bottom: 1px solid #e4e8f0;
    color: #202124;
    font-weight: 600;
    font-size: 14px;
    padding: 12px 16px;
    letter-spacing: 0.2px;
  }

  .gmail-row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #edf0f5;
    min-height: 46px;
    padding: 0 14px;
    gap: 8px;
  }

  .gmail-row label {
    width: 62px;
    margin: 0;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #5f6368;
    font-weight: 600;
  }

  .gmail-row .form-control,
  .gmail-row .form-select {
    border: 0;
    border-radius: 0;
    box-shadow: none;
    padding-left: 0;
    min-height: 40px;
    font-size: 14px;
    background: transparent;
  }

  .gmail-editor {
    border-bottom: 1px solid #edf0f5;
    padding: 12px 14px;
  }

  .gmail-editor textarea {
    border: 0;
    box-shadow: none;
    min-height: 280px;
    resize: vertical;
    padding: 0;
    font-size: 14px;
    line-height: 1.45;
  }

  .gmail-editor .ck.ck-editor {
    border-radius: 10px;
    overflow: hidden;
  }

  .gmail-editor .ck.ck-toolbar {
    background: #f8fafc;
    border-color: #e2e8f0;
  }

  .gmail-editor .ck.ck-editor__main>.ck-editor__editable {
    min-height: 280px;
    border-color: #e2e8f0;
    font-size: 14px;
    line-height: 1.5;
  }

  .gmail-attachment {
    padding: 12px 14px;
    border-bottom: 1px solid #edf0f5;
  }

  .gmail-attachment .form-control {
    border: 1px solid #d9e1ec;
    border-radius: 8px;
    font-size: 13px;
  }

  .gmail-compose-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: #fafbfc;
  }

  .gmail-compose-footer .hint {
    color: #6b7280;
    font-size: 12px;
  }

  .gmail-send-btn {
    border-radius: 20px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
  }
</style>

<div class="wrapper">
  @include('tpo.sidebar')

  <main class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center gap-2">
      <div class="breadcrumb-title pe-3">Compose Mail</div>
      <div class="ps-2">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item"><a href="{{ route('tpo.training-placement.mailbox.index') }}">Inbox</a></li>
            <li class="breadcrumb-item active" aria-current="page">Compose</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container-fluid py-4">
      <div id="composeToastData" class="d-none" data-success="{{ e((string) session('success', '')) }}" data-error="{{ e((string) session('error', '')) }}"></div>

      <div class="gmail-compose-shell">
        <div class="gmail-compose-card">
          <div class="gmail-compose-head">New Message</div>
          <form id="composeMailForm" method="POST" action="{{ route('tpo.training-placement.mailbox.compose') }}" enctype="multipart/form-data">
            @csrf

            <div class="gmail-row">
              <label>To</label>
              <select id="companySelect" name="company_id" class="form-select dselect-example" required>
                <option value="" selected disabled>Select company recipient</option>
                @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ (int) old('company_id', $prefillCompanyId ?? 0) === (int) $company->id ? 'selected' : '' }}>
                  {{ $company->company_name }} ({{ $company->mailing_email }})
                </option>
                @endforeach
              </select>
            </div>

            <div class="gmail-row">
              <label>CC</label>
              <input type="text" name="cc" class="form-control" value="{{ old('cc') }}" placeholder="mail1@example.com, mail2@example.com">
            </div>

            <div class="gmail-row">
              <label>BCC</label>
              <input type="text" name="bcc" class="form-control" value="{{ old('bcc') }}" placeholder="optional">
            </div>

            <div class="gmail-row">
              <label>Subject</label>
              <input type="text" name="subject" class="form-control" value="{{ old('subject', $subjectDraft ?? '') }}" placeholder="Write subject" required>
            </div>

            <div class="gmail-editor">
              <textarea id="compose-editor" name="message" class="form-control" placeholder="Write your message..." required>{{ old('message', $messageDraft ?? '') }}</textarea>
            </div>

            <div class="gmail-attachment">
              <label class="form-label mb-2 fw-semibold">Attachments</label>
              <input type="file" name="attachments[]" multiple class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt,.zip">
            </div>

            <div class="gmail-compose-footer">
              <span class="hint">Max 10MB each file</span>
              <div class="d-flex gap-2">
                <button id="composeSendBtn" class="btn btn-primary gmail-send-btn" type="submit">
                  <i class="fas fa-paper-plane me-1"></i>Send
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

@include('includes.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const target = document.querySelector('#compose-editor');
    const form = document.getElementById('composeMailForm');
    const sendBtn = document.getElementById('composeSendBtn');
    let composeEditor = null;

    const showToast = function(type, title, text) {
      if (typeof Swal === 'undefined') {
        return;
      }
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: title,
        text: text || '',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
      });
    };

    const toastData = document.getElementById('composeToastData');
    const successMessage = toastData ? (toastData.getAttribute('data-success') || '').trim() : '';
    const errorMessage = toastData ? (toastData.getAttribute('data-error') || '').trim() : '';

    if (successMessage) {
      showToast('success', 'Mail Sent', successMessage);
    }
    if (errorMessage) {
      showToast('error', 'Mail Not Sent', errorMessage);
    }

    if (!target || !form) {
      return;
    }

    const updateTextareaValue = function() {
      if (!composeEditor) {
        return;
      }
      target.value = composeEditor.getData();
    };

    form.addEventListener('submit', function(event) {
      updateTextareaValue();

      const plain = (target.value || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
      if (!plain) {
        event.preventDefault();
        showToast('error', 'Message Required', 'Please enter your message before sending.');
        return;
      }

      if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
      }
    });

    if (!target || typeof ClassicEditor === 'undefined') {
      return;
    }

    target.removeAttribute('required');

    ClassicEditor.create(target, {
      removePlugins: [
        'CKFinderUploadAdapter',
        'CKFinder',
        'EasyImage',
        'Image',
        'ImageCaption',
        'ImageStyle',
        'ImageToolbar',
        'ImageUpload',
        'MediaEmbed'
      ],
      toolbar: [
        'heading', '|',
        'bold', 'italic', 'underline', 'strikethrough', '|',
        'numberedList', 'bulletedList', '|',
        'link', 'blockQuote', '|',
        'undo', 'redo'
      ]
    }).then(function(editor) {
      composeEditor = editor;
    }).catch(function(error) {
      console.error('Compose editor init failed:', error);
    });
  });
</script>