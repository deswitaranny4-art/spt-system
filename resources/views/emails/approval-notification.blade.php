<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .header { padding: 24px 32px; }
        .header h2 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 32px; }
        .info-box { background: #f3f4f6; border-radius: 6px; padding: 16px; margin: 20px 0; font-size: 14px; }
        .info-row { display: flex; gap: 12px; margin-bottom: 10px; }
        .info-row:last-child { margin-bottom: 0; }
        .label { color: #6b7280; min-width: 110px; }
        .value { color: #111827; font-weight: 500; }
        .btn { display: inline-block; margin-top: 24px; padding: 12px 28px; color: #fff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; }
        .footer { padding: 16px 32px; background: #f9fafb; font-size: 12px; color: #9ca3af; border-top: 1px solid #e0e0e0; }
    </style>
</head>
<body>
<div class="container">

    @if($action === 'REJECTED')
        <div class="header" style="background:#dc2626;">
            <h2>Document Rejected</h2>
        </div>
        <div class="body">
            <p style="color:#374151; font-size:14px; margin-top:0;">
                Dokumen <strong>{{ $docNumber }}</strong> telah
                <strong style="color:#dc2626;">ditolak</strong>
                oleh <strong>{{ $approvedBy }}</strong>
                ({{ $nextRole }} - {{ $nextDept }}).
            </p>

    @elseif($action === 'FULLY_APPROVED')
        <div class="header" style="background:#16a34a;">
            <h2>Document Fully Approved</h2>
        </div>
        <div class="body">
            <p style="color:#374151; font-size:14px; margin-top:0;">
                Dokumen <strong>{{ $docNumber }}</strong> telah
                <strong style="color:#16a34a;">disetujui sepenuhnya</strong>
                oleh semua approver.
            </p>

    @else
        <div class="header" style="background:#1a56db;">
            <h2>Action Required — Document Approval</h2>
        </div>
        <div class="body">
            <p style="color:#374151; font-size:14px; margin-top:0;">
                Hai <strong>{{ $nextRole }} - {{ $nextDept }}</strong>,
            </p>
            <p style="color:#374151; font-size:14px;">
                Dokumen berikut telah disetujui oleh
                <strong>{{ $approvedBy }}</strong>
                dan membutuhkan persetujuan Anda.
            </p>
    @endif

        <div class="info-box">
            <div class="info-row">
                <span class="label">Doc Number</span>
                <span class="value">{{ $docNumber }}</span>
            </div>
            <div class="info-row">
                <span class="label">Supplier</span>
                <span class="value">{{ $supplier }}</span>
            </div>
            <div class="info-row">
                <span class="label">Diproses oleh</span>
                <span class="value">{{ $approvedBy }}</span>
            </div>
        </div>

        @if($action !== 'REJECTED')
            <a href="{{ url('/approval') }}"
               class="btn"
               style="background: {{ $action === 'FULLY_APPROVED' ? '#16a34a' : '#1a56db' }}">
                Buka Halaman Approval
            </a>
        @endif

    </div>
    <div class="footer">
        Email ini dikirim otomatis oleh SPT System. Tidak perlu membalas email ini.
    </div>
</div>
</body>
</html>