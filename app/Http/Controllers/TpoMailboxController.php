<?php

namespace App\Http\Controllers;

use App\Models\TpoConnectedCompany;
use App\Models\ITCellMailRoleAccess;
use App\Models\ITCellMailServerSetting;
use App\Models\TpoMailAttachment;
use App\Models\TpoMailMessage;
use App\Models\TpoMailThread;
use App\Models\UserHasRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class TpoMailboxController extends Controller
{
  private static bool $trashStatusReadyChecked = false;
  private static bool $trashStatusReady = false;
  private ?string $lastMailError = null;

  public function composePage(Request $request)
  {
    $this->assertTpoMailboxRoleAccess();

    $companies = TpoConnectedCompany::where('is_active', 1)
      ->orderBy('company_name')
      ->get();

    $prefillCompanyId = (int) $request->input('compose_company_id', 0);
    $subjectDraft = trim((string) $request->input('subject', ''));
    $messageDraft = trim((string) $request->input('message', ''));

    $forwardMessageId = (int) $request->input('forward_message_id', 0);
    if ($forwardMessageId > 0) {
      $forwardMessage = TpoMailMessage::with('thread.company')->find($forwardMessageId);
      if ($forwardMessage && $forwardMessage->thread) {
        $thread = $forwardMessage->thread;
        $subjectDraft = $subjectDraft !== '' ? $subjectDraft : ('Fwd: ' . $thread->subject);

        $baseMessage = trim((string) ($forwardMessage->body_text ?? ''));
        $quoted = "\n\n---------- Forwarded message ----------\n";
        $quoted .= 'From: ' . ($forwardMessage->sender_name ?: 'Unknown') . "\n";
        if (!empty($forwardMessage->sender_email)) {
          $quoted .= 'Email: ' . $forwardMessage->sender_email . "\n";
        }
        $quoted .= 'Date: ' . optional($forwardMessage->sent_at ?? $forwardMessage->received_at ?? $forwardMessage->created_at)->format('d M Y h:i A') . "\n";
        $quoted .= 'Subject: ' . ($thread->subject ?: 'N/A') . "\n";
        $quoted .= 'Company: ' . ($thread->company->company_name ?? 'N/A') . "\n\n";
        $quoted .= $baseMessage;

        $messageDraft = $messageDraft !== '' ? $messageDraft : $quoted;
      }
    }

    return view('tpo.training-placement.compose-mail', [
      'companies' => $companies,
      'prefillCompanyId' => $prefillCompanyId,
      'subjectDraft' => $subjectDraft,
      'messageDraft' => $messageDraft,
    ]);
  }

  public function index(Request $request)
  {
    $this->assertTpoMailboxRoleAccess();

    $search = trim((string) $request->input('search', ''));
    $companyId = (int) $request->input('company_id', 0);
    $trashStatusReady = $this->ensureTrashStatusEnumReady();

    $threadsQuery = TpoMailThread::with(['company', 'latestMessage'])
      ->where('last_message_direction', 'incoming')
      ->when($search !== '', function ($query) use ($search) {
        $like = '%' . $search . '%';
        $query->where(function ($inner) use ($like) {
          $inner->where('subject', 'like', $like)
            ->orWhereHas('company', function ($companyQuery) use ($like) {
              $companyQuery->where('company_name', 'like', $like)
                ->orWhere('mailing_email', 'like', $like);
            });
        });
      })
      ->when($companyId > 0, function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
      });

    if ($trashStatusReady) {
      $threadsQuery->where('status', '!=', 'trash');
    }

    $threads = $threadsQuery
      ->orderByDesc('last_message_at')
      ->orderByDesc('id')
      ->get();

    $companies = TpoConnectedCompany::where('is_active', 1)
      ->orderBy('company_name')
      ->get();

    return view('tpo.training-placement.mailbox', [
      'threads' => $threads,
      'companies' => $companies,
      'search' => $search,
      'selectedCompanyId' => $companyId,
      'prefillCompanyId' => (int) $request->input('compose_company_id', 0),
    ]);
  }

  public function sentIndex(Request $request)
  {
    $this->assertTpoMailboxRoleAccess();

    $search = trim((string) $request->input('search', ''));
    $companyId = (int) $request->input('company_id', 0);
    $trashStatusReady = $this->ensureTrashStatusEnumReady();

    $messagesQuery = TpoMailMessage::with([
      'thread.company',
      'attachments',
    ])
      ->where('sender_type', 'tpo')
      ->when($search !== '', function ($query) use ($search) {
        $like = '%' . $search . '%';
        $query->where(function ($inner) use ($like) {
          $inner->where('body_text', 'like', $like)
            ->orWhereHas('thread', function ($threadQuery) use ($like) {
              $threadQuery->where('subject', 'like', $like)
                ->orWhereHas('company', function ($companyQuery) use ($like) {
                  $companyQuery->where('company_name', 'like', $like)
                    ->orWhere('mailing_email', 'like', $like);
                });
            });
        });
      })
      ->when($companyId > 0, function ($query) use ($companyId) {
        $query->whereHas('thread', function ($threadQuery) use ($companyId) {
          $threadQuery->where('company_id', $companyId);
        });
      });

    if ($trashStatusReady) {
      $messagesQuery->whereHas('thread', function ($threadQuery) {
        $threadQuery->where('status', '!=', 'trash');
      });
    }

    $messages = $messagesQuery
      ->orderByDesc('sent_at')
      ->orderByDesc('id')
      ->get();

    $companies = TpoConnectedCompany::where('is_active', 1)
      ->orderBy('company_name')
      ->get();

    return view('tpo.training-placement.sent-messages', [
      'messages' => $messages,
      'companies' => $companies,
      'search' => $search,
      'selectedCompanyId' => $companyId,
    ]);
  }

  public function trashIndex(Request $request)
  {
    $this->assertTpoMailboxRoleAccess();

    if (!$this->ensureTrashStatusEnumReady()) {
      return redirect()->route('tpo.training-placement.mailbox.sent')
        ->with('error', 'Trash is not enabled in DB schema yet. Run: ALTER TABLE tpo_mail_threads MODIFY status ENUM(\'open\',\'closed\',\'trash\') NOT NULL DEFAULT \'open\';');
    }

    $search = trim((string) $request->input('search', ''));
    $companyId = (int) $request->input('company_id', 0);

    $threads = TpoMailThread::with(['company', 'latestMessage'])
      ->where('status', 'trash')
      ->when($search !== '', function ($query) use ($search) {
        $like = '%' . $search . '%';
        $query->where(function ($inner) use ($like) {
          $inner->where('subject', 'like', $like)
            ->orWhereHas('company', function ($companyQuery) use ($like) {
              $companyQuery->where('company_name', 'like', $like)
                ->orWhere('mailing_email', 'like', $like);
            });
        });
      })
      ->when($companyId > 0, function ($query) use ($companyId) {
        $query->where('company_id', $companyId);
      })
      ->orderByDesc('last_message_at')
      ->orderByDesc('id')
      ->get();

    $companies = TpoConnectedCompany::where('is_active', 1)
      ->orderBy('company_name')
      ->get();

    return view('tpo.training-placement.mailbox-trash', [
      'threads' => $threads,
      'companies' => $companies,
      'search' => $search,
      'selectedCompanyId' => $companyId,
    ]);
  }

  public function storeCompose(Request $request)
  {
    $this->assertTpoMailboxRoleAccess();

    $validated = $request->validate([
      'company_id' => 'required|integer|exists:tpo_connected_companies,id',
      'subject' => 'required|string|max:255',
      'message' => 'required|string',
      'cc' => 'nullable|string',
      'bcc' => 'nullable|string',
      'attachments' => 'nullable|array',
      'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,zip',
    ]);

    $company = TpoConnectedCompany::findOrFail((int) $validated['company_id']);
    $cc = $this->parseEmailList($validated['cc'] ?? '');
    $bcc = $this->parseEmailList($validated['bcc'] ?? '');

    $thread = null;
    $mailDelivered = false;

    DB::transaction(function () use ($validated, $company, $cc, $bcc, &$thread, &$mailDelivered, $request) {
      $sanitizedHtml = $this->sanitizeEditorHtml((string) $validated['message']);
      $plainText = $this->htmlToPlainText($sanitizedHtml);

      $thread = TpoMailThread::create([
        'company_id' => $company->id,
        'subject' => $validated['subject'],
        'status' => 'open',
        'last_message_direction' => 'outgoing',
        'last_message_at' => now(),
        'created_by' => Auth::id(),
        'company_reply_token' => Str::random(64),
      ]);

      $message = TpoMailMessage::create([
        'thread_id' => $thread->id,
        'sender_type' => 'tpo',
        'sender_user_id' => Auth::id(),
        'sender_name' => Auth::user()->name ?? 'TPO',
        'sender_email' => Auth::user()->email ?? config('mail.from.address'),
        'recipient_to' => $company->mailing_email,
        'recipient_cc' => implode(',', $cc),
        'recipient_bcc' => implode(',', $bcc),
        'body_text' => $plainText,
        'body_html' => $sanitizedHtml,
        'has_attachments' => false,
        'sent_at' => now(),
      ]);

      $hasAttachments = $this->storeAttachments($request, $message, Auth::id());
      if ($hasAttachments) {
        $message->update(['has_attachments' => true]);
      }

      $mailDelivered = $this->sendEmailToCompany($thread, $message, $company, $cc, $bcc);
    });

    if (!$thread instanceof TpoMailThread) {
      return back()->with('error', 'Unable to create mail thread right now.');
    }

    if ($mailDelivered) {
      return redirect()->route('tpo.training-placement.mailbox.show', $thread->id)
        ->with('success', 'Mail sent and saved in TPO sent history.');
    }

    $failureReason = $this->lastMailError ? (' Reason: ' . $this->lastMailError) : '';

    return redirect()->route('tpo.training-placement.mailbox.show', $thread->id)
      ->with('error', 'Mail saved in sent history, but delivery failed.' . $failureReason);
  }

  public function showThread(TpoMailThread $thread)
  {
    $this->assertTpoMailboxRoleAccess();

    $thread->load([
      'company',
      'messages' => function ($query) {
        $query->with('attachments')->orderBy('created_at');
      }
    ]);

    return view('tpo.training-placement.mailbox-thread', compact('thread'));
  }

  public function moveToTrash(TpoMailThread $thread)
  {
    $this->assertTpoMailboxRoleAccess();

    if (!$this->ensureTrashStatusEnumReady()) {
      return back()->with('error', 'Trash status is not enabled in DB schema yet. Run: ALTER TABLE tpo_mail_threads MODIFY status ENUM(\'open\',\'closed\',\'trash\') NOT NULL DEFAULT \'open\';');
    }

    if ($thread->status === 'trash') {
      return back()->with('success', 'Thread is already in trash.');
    }

    $thread->update([
      'status' => 'trash',
    ]);

    return redirect()->route('tpo.training-placement.mailbox.trash')
      ->with('success', 'Thread moved to trash.');
  }

  public function bulkMoveToTrash(Request $request)
  {
    $this->assertTpoMailboxRoleAccess();

    if (!$this->ensureTrashStatusEnumReady()) {
      return back()->with('error', 'Trash status is not enabled in DB schema yet. Run: ALTER TABLE tpo_mail_threads MODIFY status ENUM(\'open\',\'closed\',\'trash\') NOT NULL DEFAULT \'open\';');
    }

    $validated = $request->validate([
      'thread_ids' => 'required|array|min:1',
      'thread_ids.*' => 'integer|exists:tpo_mail_threads,id',
    ]);

    $ids = collect($validated['thread_ids'])
      ->map(fn($id) => (int) $id)
      ->unique()
      ->values()
      ->all();

    $updated = TpoMailThread::whereIn('id', $ids)
      ->where('status', '!=', 'trash')
      ->update(['status' => 'trash']);

    if ($updated === 0) {
      return back()->with('error', 'No threads were moved to trash.');
    }

    return redirect()->route('tpo.training-placement.mailbox.trash')
      ->with('success', $updated . ' thread(s) moved to trash.');
  }

  public function restoreFromTrash(TpoMailThread $thread)
  {
    $this->assertTpoMailboxRoleAccess();

    if (!$this->ensureTrashStatusEnumReady()) {
      return back()->with('error', 'Trash status is not enabled in DB schema yet. Run: ALTER TABLE tpo_mail_threads MODIFY status ENUM(\'open\',\'closed\',\'trash\') NOT NULL DEFAULT \'open\';');
    }

    if ($thread->status !== 'trash') {
      return back()->with('error', 'Only trashed threads can be restored.');
    }

    $thread->update([
      'status' => 'open',
    ]);

    return back()->with('success', 'Thread restored successfully.');
  }

  public function permanentDelete(TpoMailThread $thread)
  {
    $this->assertTpoMailboxRoleAccess();

    DB::transaction(function () use ($thread) {
      $thread->load('messages.attachments');

      foreach ($thread->messages as $message) {
        foreach ($message->attachments as $attachment) {
          if (!empty($attachment->file_path) && Storage::disk('s3')->exists($attachment->file_path)) {
            Storage::disk('s3')->delete($attachment->file_path);
          }
          $attachment->delete();
        }
        $message->delete();
      }

      $thread->delete();
    });

    return redirect()->route('tpo.training-placement.mailbox.trash')
      ->with('success', 'Thread permanently deleted.');
  }

  public function replyThread(Request $request, TpoMailThread $thread)
  {
    $this->assertTpoMailboxRoleAccess();

    $validated = $request->validate([
      'message' => 'required|string',
      'cc' => 'nullable|string',
      'bcc' => 'nullable|string',
      'attachments' => 'nullable|array',
      'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,zip',
      'status' => 'nullable|in:open,closed',
    ]);

    $thread->load('company');
    $company = $thread->company;
    if (!$company) {
      return back()->with('error', 'Company record is missing for this thread.');
    }

    $cc = $this->parseEmailList($validated['cc'] ?? '');
    $bcc = $this->parseEmailList($validated['bcc'] ?? '');
    $mailDelivered = false;

    DB::transaction(function () use ($validated, $thread, $company, $cc, $bcc, &$mailDelivered, $request) {
      $message = TpoMailMessage::create([
        'thread_id' => $thread->id,
        'sender_type' => 'tpo',
        'sender_user_id' => Auth::id(),
        'sender_name' => Auth::user()->name ?? 'TPO',
        'sender_email' => Auth::user()->email ?? config('mail.from.address'),
        'recipient_to' => $company->mailing_email,
        'recipient_cc' => implode(',', $cc),
        'recipient_bcc' => implode(',', $bcc),
        'body_text' => $this->htmlToPlainText($this->sanitizeEditorHtml((string) $validated['message'])),
        'body_html' => $this->sanitizeEditorHtml((string) $validated['message']),
        'has_attachments' => false,
        'sent_at' => now(),
      ]);

      $hasAttachments = $this->storeAttachments($request, $message, Auth::id());
      if ($hasAttachments) {
        $message->update(['has_attachments' => true]);
      }

      $thread->update([
        'status' => $validated['status'] ?? 'open',
        'last_message_direction' => 'outgoing',
        'last_message_at' => now(),
      ]);

      $mailDelivered = $this->sendEmailToCompany($thread, $message, $company, $cc, $bcc);
    });

    if ($mailDelivered) {
      return back()->with('success', 'Reply sent and stored.');
    }

    $failureReason = $this->lastMailError ? (' Reason: ' . $this->lastMailError) : '';

    return back()->with('error', 'Reply saved in thread, but delivery failed.' . $failureReason);
  }

  public function companyReplyForm(Request $request, TpoMailThread $thread, string $token)
  {
    if (!$request->hasValidSignature() || !hash_equals($thread->company_reply_token, $token)) {
      abort(403);
    }

    $thread->load('company');

    return view('tpo.training-placement.company-reply', compact('thread', 'token'));
  }

  public function companyReplySubmit(Request $request, TpoMailThread $thread, string $token)
  {
    if (!$request->hasValidSignature() || !hash_equals($thread->company_reply_token, $token)) {
      abort(403);
    }

    $validated = $request->validate([
      'sender_name' => 'required|string|max:255',
      'sender_email' => 'required|email|max:255',
      'message' => 'required|string',
      'attachments' => 'nullable|array',
      'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,txt,zip',
    ]);

    DB::transaction(function () use ($validated, $thread, $request) {
      $message = TpoMailMessage::create([
        'thread_id' => $thread->id,
        'sender_type' => 'company',
        'sender_name' => $validated['sender_name'],
        'sender_email' => $validated['sender_email'],
        'body_text' => $validated['message'],
        'body_html' => nl2br(e($validated['message'])),
        'has_attachments' => false,
        'received_at' => now(),
      ]);

      $hasAttachments = $this->storeAttachments($request, $message, null);
      if ($hasAttachments) {
        $message->update(['has_attachments' => true]);
      }

      $thread->update([
        'status' => 'open',
        'last_message_direction' => 'incoming',
        'last_message_at' => now(),
      ]);
    });

    return back()->with('success', 'Reply sent successfully. Your message has been delivered to TPO inbox.');
  }

  private function parseEmailList(string $raw): array
  {
    return collect(explode(',', $raw))
      ->map(fn($email) => strtolower(trim($email)))
      ->filter()
      ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
      ->unique()
      ->values()
      ->all();
  }

  private function storeAttachments(Request $request, TpoMailMessage $message, ?int $uploadedBy): bool
  {
    $files = $request->file('attachments', []);
    if (!is_array($files) || count($files) === 0) {
      return false;
    }

    $created = false;
    foreach ($files as $file) {
      $path = StaticController::s3_file_uploader($file, 'tpo_mail_attachments');
      TpoMailAttachment::create([
        'message_id' => $message->id,
        'file_name' => $file->getClientOriginalName(),
        'file_path' => $path,
        'mime_type' => $file->getClientMimeType(),
        'file_size' => $file->getSize(),
        'uploaded_by' => $uploadedBy,
      ]);
      $created = true;
    }

    return $created;
  }

  private function sendEmailToCompany(TpoMailThread $thread, TpoMailMessage $message, TpoConnectedCompany $company, array $cc, array $bcc): bool
  {
    $this->lastMailError = null;

    $replyUrl = URL::temporarySignedRoute(
      'tpo.training-placement.company-reply.form',
      now()->addDays(60),
      ['thread' => $thread->id, 'token' => $thread->company_reply_token]
    );

    $body = $message->body_text . "\n\nReply securely using this link:\n" . $replyUrl;
    $tpoFromAddress = (string) config('mail.tpo_from.address', config('mail.from.address'));
    $tpoFromName = (string) config('mail.tpo_from.name', config('mail.from.name'));

    $configuredMailer = trim((string) config('mail.tpo_mailer', ''));
    $mailers = (array) config('mail.mailers', []);

    $setting = ITCellMailServerSetting::query()
      ->where('module_key', 'tpo')
      ->where('is_active', 1)
      ->first();

    if ($setting) {
      $runtimeMailer = 'itcell_runtime_tpo';
      $decryptedPassword = null;

      if (!empty($setting->smtp_password)) {
        try {
          $decryptedPassword = Crypt::decryptString((string) $setting->smtp_password);
        } catch (\Throwable $e) {
          $this->lastMailError = 'Saved SMTP password could not be decrypted. Please re-save ITCELL mail settings.';
          return false;
        }
      }

      config()->set('mail.mailers.' . $runtimeMailer, [
        'transport' => $setting->mailer ?: 'smtp',
        'host' => $setting->smtp_host,
        'port' => (int) ($setting->smtp_port ?: 587),
        'encryption' => $setting->smtp_encryption ?: null,
        'username' => $setting->smtp_username,
        'password' => $decryptedPassword,
        'timeout' => null,
        'local_domain' => $setting->smtp_ehlo_domain ?: null,
      ]);

      $mailers = (array) config('mail.mailers', []);
      $configuredMailer = $runtimeMailer;
      $tpoFromAddress = (string) ($setting->from_address ?: $tpoFromAddress);
      $tpoFromName = (string) ($setting->from_name ?: $tpoFromName);
    }

    // Guard against empty/unknown mailer names from env/config.
    if ($configuredMailer === '' || !array_key_exists($configuredMailer, $mailers)) {
      $configuredMailer = array_key_exists('tpo_smtp', $mailers)
        ? 'tpo_smtp'
        : ((string) config('mail.default', 'smtp') ?: 'smtp');
    }

    if (!array_key_exists($configuredMailer, $mailers)) {
      $configuredMailer = array_key_exists('smtp', $mailers) ? 'smtp' : 'log';
    }

    try {
      Mail::mailer($configuredMailer)->send([], [], function ($mail) use ($thread, $company, $cc, $bcc, $body, $message, $tpoFromAddress, $tpoFromName) {
        if (!empty($tpoFromAddress)) {
          $mail->from($tpoFromAddress, $tpoFromName ?: null);
        }

        $mail->to($company->mailing_email)
          ->subject($thread->subject)
          ->text('emails.plain-text', ['body' => $body]);

        if (!empty($cc)) {
          $mail->cc($cc);
        }
        if (!empty($bcc)) {
          $mail->bcc($bcc);
        }

        foreach ($message->attachments as $attachment) {
          if (!$attachment->file_path || !Storage::disk('s3')->exists($attachment->file_path)) {
            continue;
          }

          $data = Storage::disk('s3')->get($attachment->file_path);
          $mail->attachData($data, $attachment->file_name, [
            'mime' => $attachment->mime_type ?: 'application/octet-stream',
          ]);
        }
      });
      return true;
    } catch (\Throwable $e) {
      // Keep message in sent history even when SMTP fails.
      $raw = trim((string) $e->getMessage());
      $clean = preg_replace('/\s+/', ' ', $raw) ?? $raw;
      $this->lastMailError = mb_substr($clean, 0, 220);
      return false;
    }
  }

  private function sanitizeEditorHtml(string $rawHtml): string
  {
    $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><a><h1><h2><h3><h4><h5><h6>';
    $clean = strip_tags($rawHtml, $allowedTags);

    // Prevent inline script/event payloads from surviving in attributes.
    $clean = preg_replace('/\s+on[a-zA-Z]+\s*=\s*(["\']).*?\1/i', '', $clean) ?? $clean;
    $clean = preg_replace('/\s+style\s*=\s*(["\']).*?\1/i', '', $clean) ?? $clean;
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean) ?? $clean;

    return trim($clean);
  }

  private function htmlToPlainText(string $html): string
  {
    $normalized = preg_replace('/<\s*br\s*\/?>/i', "\n", $html) ?? $html;
    $normalized = preg_replace('/<\/(p|div|h[1-6]|li|blockquote)>/i', "$0\n", $normalized) ?? $normalized;
    $text = html_entity_decode(strip_tags($normalized), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace("/\r\n|\r/", "\n", $text) ?? $text;
    $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

    return trim($text);
  }

  private function ensureTrashStatusEnumReady(): bool
  {
    if (self::$trashStatusReadyChecked) {
      return self::$trashStatusReady;
    }

    self::$trashStatusReadyChecked = true;

    try {
      $column = DB::selectOne("SHOW COLUMNS FROM tpo_mail_threads LIKE 'status'");
      $type = strtolower((string) ($column->Type ?? ''));

      if (str_contains($type, "'trash'")) {
        self::$trashStatusReady = true;
        return true;
      }

      DB::statement("ALTER TABLE tpo_mail_threads MODIFY status ENUM('open','closed','trash') NOT NULL DEFAULT 'open'");
      self::$trashStatusReady = true;
      return true;
    } catch (\Throwable $e) {
      self::$trashStatusReady = false;
      return false;
    }
  }

  private function assertTpoMailboxRoleAccess(): void
  {
    $userId = (int) Auth::id();
    if ($userId <= 0) {
      abort(403, 'Unauthorized access.');
    }

    $allowedRoles = ITCellMailRoleAccess::query()
      ->where('module_key', 'tpo')
      ->pluck('role_name')
      ->map(fn($role) => strtolower(trim((string) $role)))
      ->filter()
      ->unique()
      ->values();

    // Keep existing behavior when ITCELL has not configured restrictions yet.
    if ($allowedRoles->isEmpty()) {
      return;
    }

    $userRoles = UserHasRole::query()
      ->where('user_id', $userId)
      ->pluck('role_name')
      ->map(fn($role) => strtolower(trim((string) $role)))
      ->filter()
      ->unique()
      ->values();

    if ($userRoles->intersect($allowedRoles)->isEmpty()) {
      abort(403, 'Your role is not allowed to use TPO mailbox. Contact ITCELL.');
    }
  }
}
