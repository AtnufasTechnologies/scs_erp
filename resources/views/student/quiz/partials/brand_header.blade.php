<style>
  .fa1-brand-header {
    background: linear-gradient(135deg, #0b3a65 0%, #155799 60%, #1f6fb2 100%);
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(16, 48, 82, 0.18);
    padding: 0.85rem 1rem;
    margin-bottom: 1rem;
    color: #ffffff;
  }

  .fa1-brand-wrap {
    display: flex;
    align-items: center;
    gap: 0.9rem;
  }

  .fa1-brand-logo {
    width: 56px;
    height: 56px;
    object-fit: contain;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
    padding: 6px;
    border: 1px solid rgba(255, 255, 255, 0.25);
  }

  .fa1-brand-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.25;
    letter-spacing: 0.01em;
    color: #fff;
  }

  .fa1-brand-subtitle {
    margin: 0.2rem 0 0;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.88);
  }

  @media (max-width: 576px) {
    .fa1-brand-header {
      padding: 0.8rem;
    }

    .fa1-brand-wrap {
      gap: 0.7rem;
    }

    .fa1-brand-logo {
      width: 48px;
      height: 48px;
    }

    .fa1-brand-title {
      font-size: 0.95rem;
    }
  }
</style>

<div class="fa1-brand-header">
  <div class="fa1-brand-wrap">
    <img src="{{ asset('admin/images/logo.png') }}" alt="Salesian College Autonomous" class="fa1-brand-logo">
    <div>
      <p class="fa1-brand-subtitle">FA1 Examination Portal</p>
    </div>
  </div>
</div>