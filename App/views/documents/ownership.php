<?php
// App/views/documents/ownership.php
// Professional Standalone Print-Ready Ownership Certificate
// This view is NOT wrapped by the main layout — it renders its own complete HTML document.

$qrData = urlencode(BASE_URL . '/document/ownership/' . (int)$vehicle['id']);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $qrData;

// Helper to get custom field values safely
$cf = [];
if (!empty($vehicle['custom_fields'])) {
    $cf = json_decode($vehicle['custom_fields'], true) ?: [];
}

$getVal = function($key) use ($cf) {
    $val = $cf[$key] ?? '';
    if (is_array($val)) {
        $val = implode(', ', $val);
    }
    $val = trim((string)$val);
    return $val !== '' ? $val : '—';
};

// Helper to format currency values
$formatAmt = function($key) use ($cf) {
    $val = trim((string)($cf[$key] ?? ''));
    return $val !== '' ? '₦' . number_format((float)$val, 2) : '—';
};

// Helper to format date values
$formatDate = function($key) use ($cf) {
    $val = trim((string)($cf[$key] ?? ''));
    return $val !== '' ? date('M j, Y', strtotime($val)) : '—';
};

// Officer types definition
$officerTypes = [
    'Custom Officers' => 'custom_officer',
    'Police Officers' => 'police_officer',
    'DSS Officers' => 'dss_officer',
    'NIA Officers' => 'nia_officer',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ownership Certificate — <?= htmlspecialchars($vehicle['plate_number']) ?> | NVOTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* ────────────────────────── RESET & BASE ────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        :root {
            --navy: #0c1e3a;
            --navy-light: #14304f;
            --emerald: #059669;
            --emerald-light: #10b981;
            --gold: #d4a843;
            --gold-light: #f0d88a;
            --text-dark: #1e293b;
            --text-medium: #475569;
            --text-light: #94a3b8;
            --border: #e2e8f0;
            --border-dark: #cbd5e1;
            --bg-cream: #fefcf8;
            --bg-section: #f8fafc;
            --page-shadow: 0 4px 24px rgba(0,0,0,0.12);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #e2e8f0;
            color: var(--text-dark);
            font-size: 11pt;
            line-height: 1.55;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ────────────────────────── PRINT CONTROLS ────────────────────────── */
        .no-print { display: flex; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .document-page { box-shadow: none; margin: 0; max-width: 100%; }
            @page {
                size: A4;
                margin: 12mm 15mm;
            }
        }

        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        .print-toolbar .toolbar-title {
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .print-toolbar .toolbar-title .shield-icon {
            width: 28px;
            height: 28px;
            background: var(--emerald);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .toolbar-actions { display: flex; gap: 10px; }
        .btn-toolbar {
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print {
            background: var(--emerald);
            color: #fff;
        }
        .btn-print:hover { background: #047857; transform: translateY(-1px); }
        .btn-back {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-back:hover { background: rgba(255,255,255,0.2); }

        /* ────────────────────────── DOCUMENT PAGE ────────────────────────── */
        .document-page {
            max-width: 820px;
            margin: 80px auto 40px;
            background: #fff;
            border-radius: 2px;
            box-shadow: var(--page-shadow);
            overflow: hidden;
        }

        /* ────────────────────────── HEADER BANNER ────────────────────────── */
        .doc-header {
            background: linear-gradient(135deg, var(--navy) 0%, #1a3a5c 50%, var(--navy-light) 100%);
            color: #fff;
            padding: 36px 40px 30px;
            position: relative;
            overflow: hidden;
        }
        .doc-header::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: rgba(5, 150, 105, 0.12);
        }
        .doc-header::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(212, 168, 67, 0.08);
        }
        .header-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .brand-shield {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, var(--emerald) 0%, var(--emerald-light) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
        }
        .brand-text h1 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 2px;
            margin-bottom: 2px;
        }
        .brand-text p {
            font-size: 10px;
            color: rgba(255,255,255,0.6);
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .header-ref {
            display: flex;
            align-items: center;
            gap: 16px;
            text-align: right;
        }
        .header-ref-text {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .header-ref-text .ref-label {
            font-size: 9px;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-ref-text .ref-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: var(--gold-light);
            letter-spacing: 0.5px;
        }
        .header-ref-text .ref-date {
            font-size: 10px;
            color: rgba(255,255,255,0.55);
            margin-top: 4px;
        }
        .header-ref-qr img {
            width: 64px;
            height: 64px;
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 2px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .doc-title-bar {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,0.12);
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .doc-title-bar h2 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold-light);
        }
        .doc-title-bar p {
            font-size: 10px;
            color: rgba(255,255,255,0.5);
            margin-top: 4px;
            letter-spacing: 1px;
        }

        /* ────────────────────────── GOLD ACCENT BAR ────────────────────────── */
        .gold-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light) 50%, var(--gold) 100%);
        }

        /* ────────────────────────── BODY CONTENT ────────────────────────── */
        .doc-body {
            padding: 32px 40px 36px;
        }

        /* ────── Section headers ────── */
        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--navy);
        }
        .section-header .section-icon {
            width: 30px;
            height: 30px;
            background: var(--navy);
            color: #fff;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }
        .section-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* ────── Vehicle Information Grid ────── */
        .vehicle-section {
            margin-bottom: 30px;
        }
        .vehicle-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 0;
            border: 1px solid var(--border-dark);
            border-radius: 8px;
            overflow: hidden;
        }
        .vehicle-photo {
            background: var(--bg-section);
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 1px solid var(--border-dark);
        }
        .vehicle-photo img {
            width: 170px;
            height: 130px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid var(--border);
        }
        .vehicle-photo .plate-tag {
            margin-top: 10px;
            padding: 6px 18px;
            background: var(--navy);
            color: var(--gold-light);
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            border-radius: 4px;
        }
        .vehicle-photo .no-image {
            width: 170px;
            height: 130px;
            background: var(--border);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 42px;
        }
        .vehicle-details {
            padding: 0;
        }
        .detail-row {
            display: flex;
            border-bottom: 1px solid var(--border);
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            width: 150px;
            padding: 8px 14px;
            background: var(--bg-section);
            font-weight: 600;
            font-size: 10px;
            color: var(--text-medium);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            border-right: 1px solid var(--border);
            flex-shrink: 0;
        }
        .detail-value {
            flex: 1;
            padding: 8px 14px;
            font-size: 11px;
            color: var(--text-dark);
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .detail-value code {
            font-family: 'JetBrains Mono', monospace;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            color: var(--navy);
            font-weight: 600;
        }

        /* ────── Ownership Chain ────── */
        .ownership-section {
            margin-bottom: 30px;
        }
        .owner-card {
            border: 1px solid var(--border-dark);
            border-radius: 10px;
            margin-bottom: 16px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .owner-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            background: var(--navy);
            color: #fff;
        }
        .owner-card-header .owner-order {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .owner-number {
            width: 30px;
            height: 30px;
            background: var(--emerald);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
        }
        .owner-card-header .owner-title {
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        .owner-card-header .owner-role {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
        }
        .owner-role.current {
            background: rgba(16, 185, 129, 0.2);
            color: var(--emerald-light);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .owner-role.previous {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.15);
        }

        .owner-card-body {
            padding: 18px;
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 18px;
        }
        .owner-portrait {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .owner-portrait img {
            width: 90px;
            height: 110px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid var(--border-dark);
        }
        .owner-portrait .no-photo {
            width: 90px;
            height: 110px;
            background: var(--bg-section);
            border-radius: 6px;
            border: 2px dashed var(--border-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 28px;
        }
        .owner-portrait .signature-box {
            width: 90px;
            text-align: center;
        }
        .owner-portrait .signature-box img {
            width: 80px;
            height: 35px;
            object-fit: contain;
            border: 1px solid var(--border);
            border-radius: 4px;
        }
        .owner-portrait .signature-label {
            font-size: 7px;
            text-transform: uppercase;
            color: var(--text-light);
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .owner-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }
        .info-field {
            padding: 6px 10px;
            border-bottom: 1px solid var(--border);
        }
        .info-field:nth-child(odd) {
            border-right: 1px solid var(--border);
        }
        .info-field .field-label {
            font-size: 8px;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .info-field .field-value {
            font-size: 10.5px;
            color: var(--text-dark);
            font-weight: 500;
        }
        .info-field.full-width {
            grid-column: 1 / -1;
            border-right: none;
        }

        /* ────── Transaction Details within owner card ────── */
        .transaction-strip {
            display: flex;
            gap: 0;
            border-top: 2px solid var(--emerald);
            background: #f0fdf4;
        }
        .txn-item {
            flex: 1;
            padding: 8px 14px;
            border-right: 1px solid #d1fae5;
            text-align: center;
        }
        .txn-item:last-child { border-right: none; }
        .txn-item .txn-label {
            font-size: 7px;
            text-transform: uppercase;
            color: var(--emerald);
            letter-spacing: 0.8px;
            font-weight: 700;
        }
        .txn-item .txn-value {
            font-size: 10px;
            color: var(--text-dark);
            font-weight: 600;
            margin-top: 2px;
        }

        /* ────── Connector Arrow ────── */
        .chain-connector {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -6px 0;
            position: relative;
            z-index: 2;
        }
        .connector-arrow {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--emerald) 0%, var(--emerald-light) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.35);
        }

        /* ────── Verification Stamp ────── */
        .verification-section {
            margin-bottom: 30px;
        }
        .stamp-box {
            border: 2px solid var(--border-dark);
            border-radius: 10px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-section);
        }
        .stamp-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .stamp-info .stamp-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-light);
            font-weight: 600;
        }
        .stamp-info .stamp-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
        }
        .verification-stamp {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform: rotate(-12deg);
            font-family: 'Playfair Display', serif;
        }
        .stamp-approved {
            border: 3px solid var(--emerald);
            color: var(--emerald);
        }
        .stamp-pending {
            border: 3px dashed var(--gold);
            color: var(--gold);
        }
        .stamp-rejected {
            border: 3px solid #ef4444;
            color: #ef4444;
        }
        .verification-stamp .stamp-text {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        .verification-stamp .stamp-sub {
            font-size: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
        }

        /* ────── Footer ────── */
        .doc-footer {
            background: var(--navy);
            color: rgba(255,255,255,0.5);
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8px;
            letter-spacing: 0.5px;
        }
        .doc-footer .footer-note {
            max-width: 480px;
            line-height: 1.5;
        }
        .doc-footer .footer-qr {
            text-align: right;
        }
        .doc-footer .footer-qr .ref-mono {
            font-family: 'JetBrains Mono', monospace;
            color: var(--gold-light);
            font-size: 9px;
        }

        /* ────── Watermark ────── */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-family: 'Playfair Display', serif;
            font-size: 110px;
            font-weight: 900;
            color: rgba(12, 30, 58, 0.025);
            text-transform: uppercase;
            letter-spacing: 20px;
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
        }

        /* ────── Custom Registry & Officer Section Styles ────── */
        .registry-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1px solid var(--border-dark);
            border-radius: 6px;
            background: #fff;
            overflow: hidden;
        }
        .info-cell {
            padding: 8px 12px;
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .info-cell:nth-child(odd) {
            border-right: 1px solid var(--border);
        }
        .info-cell.full-width {
            grid-column: 1 / -1;
            border-right: none !important;
        }
        /* Clean up borders on the last row */
        .grid-info > .info-cell:last-child {
            border-bottom: none;
        }
        .grid-info > .info-cell:last-child:nth-child(odd) {
            border-right: none;
        }
        .grid-info > .info-cell:nth-last-child(2):nth-child(odd):not(.full-width) {
            border-bottom: none;
        }
        .cell-label {
            font-size: 8px;
            color: var(--text-medium);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        .cell-value {
            font-size: 10.5px;
            color: var(--text-dark);
            font-weight: 500;
        }
        .cell-value code {
            font-family: 'JetBrains Mono', monospace;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            color: var(--navy);
            font-weight: 600;
        }
        .cell-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 8px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .cell-badge.active {
            background: #e6f4ea;
            color: #137333;
            border: 1px solid #c2e7c9;
        }
        .cell-badge.expired {
            background: #fce8e6;
            color: #c5221f;
            border: 1px solid #fad2cf;
        }
        .cell-badge.complete {
            background: #e6f4ea;
            color: #137333;
            border: 1px solid #c2e7c9;
        }

        .cards-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .registry-card {
            border: 1px solid var(--border-dark);
            border-radius: 8px;
            background: var(--bg-section);
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            page-break-inside: avoid;
        }
        .registry-card-header {
            font-family: 'Playfair Display', serif;
            font-size: 11px;
            font-weight: 700;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border-dark);
            padding-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .officers-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .officer-print-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border: 1px solid var(--border-dark);
            border-radius: 8px;
            background: #fff;
            page-break-inside: avoid;
        }
        .officer-badge-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            border: 1.5px solid var(--border-dark);
        }
        .officer-badge-customs { background: #e0f2fe; color: #0369a1; }
        .officer-badge-police { background: #fee2e2; color: #b91c1c; }
        .officer-badge-dss { background: #fef3c7; color: #b45309; }
        .officer-badge-nia { background: #f3e8ff; color: #6d28d9; }

        .officer-details-wrap {
            flex: 1;
            min-width: 0;
        }
        .officer-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
            gap: 6px;
        }
        .officer-name {
            font-size: 9.5px;
            font-weight: 700;
            color: var(--navy);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .officer-slot-label {
            font-size: 7px;
            color: var(--text-light);
            text-transform: uppercase;
        }
        .officer-rank-badge {
            font-size: 7px;
            font-weight: 600;
            padding: 1px 5px;
            border-radius: 3px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .officer-rank-customs { background: #bae6fd; color: #0369a1; }
        .officer-rank-police { background: #fecaca; color: #b91c1c; }
        .officer-rank-dss { background: #fde68a; color: #b45309; }
        .officer-rank-nia { background: #e9d5ff; color: #6d28d9; }

        .officer-contact-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2px;
            font-size: 7.5px;
            color: var(--text-medium);
        }
        .officer-contact-item {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ────── Responsive ────── */
        @media (max-width: 768px) {
            .document-page { margin: 60px 8px 20px; }
            .doc-header { padding: 24px 20px 20px; }
            .doc-body { padding: 20px 20px 24px; }
            .vehicle-grid { grid-template-columns: 1fr; }
            .vehicle-photo { border-right: none; border-bottom: 1px solid var(--border-dark); }
            .owner-card-body { grid-template-columns: 1fr; }
            .owner-info { grid-template-columns: 1fr; }
            .info-field:nth-child(odd) { border-right: none; }
            .stamp-box { flex-direction: column; gap: 16px; text-align: center; }
            .header-top { flex-direction: column; gap: 12px; }
            .header-ref { text-align: left; }
            .doc-footer { flex-direction: column; gap: 10px; text-align: center; }
            
            /* Custom Responsive Styles */
            .cards-row { grid-template-columns: 1fr; }
            .officers-grid { grid-template-columns: 1fr; }
            .grid-info { grid-template-columns: 1fr; }
            .grid-info > .info-cell { border-right: none !important; border-bottom: 1px solid var(--border) !important; }
            .grid-info > .info-cell:last-child { border-bottom: none !important; }
        }
    </style>
</head>
<body>

    <!-- Watermark -->
    <div class="watermark no-print">NVOTS</div>

    <!-- Print Toolbar (hidden on print) -->
    <div class="print-toolbar no-print">
        <div class="toolbar-title">
            <div class="shield-icon">🛡</div>
            NVOTS — Ownership Certificate Preview
        </div>
        <div class="toolbar-actions">
            <button class="btn-toolbar btn-back" onclick="window.history.back()">
                ← Back
            </button>
            <button class="btn-toolbar btn-print" onclick="window.print()">
                🖨 Print / Save PDF
            </button>
        </div>
    </div>

    <!-- Document Page -->
    <div class="document-page">

        <!-- ═══════════════ HEADER ═══════════════ -->
        <div class="doc-header">
            <div class="header-top">
                <div class="header-brand">
                    <div class="brand-shield">🛡</div>
                    <div class="brand-text">
                        <h1>NVOTS NIGERIA</h1>
                        <p>National Vehicle Ownership &amp; Traceability System</p>
                    </div>
                </div>
                <div class="header-ref">
                    <div class="header-ref-text">
                        <div class="ref-label">Document Reference</div>
                        <div class="ref-value"><?= htmlspecialchars($docRef) ?></div>
                        <div class="ref-date">Issued: <?= htmlspecialchars($generatedAt) ?></div>
                    </div>
                    <div class="header-ref-qr animate-fade-in">
                        <img src="<?= $qrUrl ?>" alt="QR Verification">
                    </div>
                </div>
            </div>
            <div class="doc-title-bar">
                <h2>Certificate of Vehicle Ownership</h2>
                <p>Official Record of Ownership History &amp; Chain of Custody</p>
            </div>
        </div>
        <div class="gold-bar"></div>

        <!-- ═══════════════ BODY ═══════════════ -->
        <div class="doc-body">

            <!-- ─── SECTION 1: Vehicle Information ─── -->
            <div class="vehicle-section">
                <div class="section-header">
                    <div class="section-icon">🚗</div>
                    <h3>Vehicle Information</h3>
                </div>

                <div class="vehicle-grid">
                    <div class="vehicle-photo">
                        <?php if (!empty($vehicle['image_path'])): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($vehicle['image_path']) ?>" alt="Vehicle Photo">
                        <?php else: ?>
                            <div class="no-image">🚗</div>
                        <?php endif; ?>
                        <div class="plate-tag"><?= htmlspecialchars($vehicle['plate_number']) ?></div>
                    </div>
                    <div class="vehicle-details">
                        <div class="detail-row">
                            <div class="detail-label">VIN Number</div>
                            <div class="detail-value"><code><?= htmlspecialchars($vehicle['vin']) ?></code></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Engine No.</div>
                            <div class="detail-value"><code><?= htmlspecialchars($vehicle['engine_number']) ?></code></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Chassis No.</div>
                            <div class="detail-value"><code><?= htmlspecialchars($vehicle['chassis_number']) ?></code></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Make / Model</div>
                            <div class="detail-value"><?= htmlspecialchars($vehicle['manufacturer'] . ' ' . $vehicle['model']) ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Year</div>
                            <div class="detail-value"><?= htmlspecialchars($vehicle['year']) ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Color</div>
                            <div class="detail-value"><?= htmlspecialchars($vehicle['color']) ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Fuel / Trans.</div>
                            <div class="detail-value"><?= htmlspecialchars(($vehicle['fuel_type'] ?: '—') . ' / ' . ($vehicle['transmission'] ?: '—')) ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Category</div>
                            <div class="detail-value"><?= htmlspecialchars($vehicle['category'] ?: '—') ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Class</div>
                            <div class="detail-value"><?= htmlspecialchars($vehicle['class'] ?: '—') ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Registered</div>
                            <div class="detail-value"><?= htmlspecialchars(date('M j, Y', strtotime($vehicle['created_at']))) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── SECTION 2: Registry & Acquisition Details ─── -->
            <div class="registry-section">
                <div class="section-header">
                    <div class="section-icon">📍</div>
                    <h3>Registry &amp; Acquisition Details</h3>
                </div>
                <div class="grid-info">
                    <div class="info-cell">
                        <div class="cell-label">Plate Registration State</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('number_plate_state')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Plate Registration LGA</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('number_plate_lga')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Purchase Date</div>
                        <div class="cell-value"><?= htmlspecialchars($formatDate('purchase_date')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Purchase Amount</div>
                        <div class="cell-value"><?= htmlspecialchars($formatAmt('purchase_amount')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Means of Identification</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('means_of_identification')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Insurance Cover Policy</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('insurance_cover')) ?></div>
                    </div>
                </div>
            </div>

            <!-- ─── SECTION 3: Importation & Logistics ─── -->
            <div class="registry-section">
                <div class="section-header">
                    <div class="section-icon">🚢</div>
                    <h3>Importation &amp; Logistics</h3>
                </div>
                <div class="grid-info">
                    <div class="info-cell">
                        <div class="cell-label">Country of Origin</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('country_of_origin')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Country of Manufacture</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('country_of_manufacture')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Year of Importation</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('ship_year')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Custom Papers Status</div>
                        <div class="cell-value">
                            <?php $papers = $getVal('custom_papers_status'); ?>
                            <?php if ($papers !== '—'): ?>
                                <span class="cell-badge <?= strtolower($papers) === 'complete' ? 'complete' : 'expired' ?>"><?= htmlspecialchars($papers) ?></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Departure Port</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('ship_departure_port')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Departure Date</div>
                        <div class="cell-value"><?= htmlspecialchars($formatDate('ship_departure_date')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Landing Date</div>
                        <div class="cell-value"><?= htmlspecialchars($formatDate('ship_landing_date')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Port Operator Name</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('port_name')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Port Company</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('port_company')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Port Contact Phone</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('port_tel')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Port Contact Email</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('port_email')) ?></div>
                    </div>
                    <div class="info-cell full-width">
                        <div class="cell-label">Port Office Address</div>
                        <div class="cell-value"><?= htmlspecialchars($getVal('port_address')) ?></div>
                    </div>
                </div>
            </div>

            <!-- ─── SECTION 4: Importer & Agent Profiles ─── -->
            <div class="registry-section">
                <div class="section-header">
                    <div class="section-icon">👤</div>
                    <h3>Importer &amp; Agent Profiles</h3>
                </div>
                
                <div class="cards-row">
                    <!-- Importer Profile Card -->
                    <div class="registry-card">
                        <div class="registry-card-header">
                            ✈️ Importer Profile
                        </div>
                        <div class="officer-contact-grid">
                            <div class="officer-contact-item"><strong>Name:</strong> <?= htmlspecialchars($getVal('importer_name')) ?></div>
                            <div class="officer-contact-item"><strong>Company:</strong> <?= htmlspecialchars($getVal('importer_company')) ?></div>
                            <div class="officer-contact-item"><strong>Phone:</strong> <?= htmlspecialchars($getVal('importer_tel')) ?></div>
                            <div class="officer-contact-item"><strong>Email:</strong> <?= htmlspecialchars($getVal('importer_email')) ?></div>
                            <div class="officer-contact-item full-span"><strong>Address:</strong> <?= htmlspecialchars($getVal('importer_address')) ?></div>
                        </div>
                    </div>

                    <!-- Clearing Agent Profile Card -->
                    <div class="registry-card">
                        <div class="registry-card-header">
                            📝 Clearing Agent Info
                        </div>
                        <div class="officer-contact-grid">
                            <div class="officer-contact-item"><strong>Name:</strong> <?= htmlspecialchars($getVal('clearing_agent_name')) ?></div>
                            <div class="officer-contact-item"><strong>Company:</strong> <?= htmlspecialchars($getVal('clearing_agent_company')) ?></div>
                            <div class="officer-contact-item"><strong>Phone:</strong> <?= htmlspecialchars($getVal('clearing_agent_tel')) ?></div>
                            <div class="officer-contact-item"><strong>Email:</strong> <?= htmlspecialchars($getVal('clearing_agent_email')) ?></div>
                            <div class="officer-contact-item full-span"><strong>Address:</strong> <?= htmlspecialchars($getVal('clearing_agent_address')) ?></div>
                        </div>
                    </div>
                </div>

                <div class="cards-row">
                    <!-- Foreign Export Office Card -->
                    <div class="registry-card">
                        <div class="registry-card-header">
                            🌐 Foreign Export Office
                        </div>
                        <div class="officer-contact-grid">
                            <div class="officer-contact-item"><strong>Rep Name:</strong> <?= htmlspecialchars($getVal('foreign_office_name')) ?></div>
                            <div class="officer-contact-item"><strong>Company:</strong> <?= htmlspecialchars($getVal('foreign_office_company')) ?></div>
                            <div class="officer-contact-item"><strong>Phone:</strong> <?= htmlspecialchars($getVal('foreign_office_tel')) ?></div>
                            <div class="officer-contact-item"><strong>Email:</strong> <?= htmlspecialchars($getVal('foreign_office_email')) ?></div>
                            <div class="officer-contact-item full-span"><strong>Address:</strong> <?= htmlspecialchars($getVal('foreign_office_address')) ?></div>
                        </div>
                    </div>

                    <!-- Local Onboarding Agent Card -->
                    <div class="registry-card">
                        <div class="registry-card-header">
                            🤝 Local Onboarding Agent
                        </div>
                        <div class="officer-contact-grid">
                            <div class="officer-contact-item"><strong>Agent Name:</strong> <?= htmlspecialchars($getVal('agent_name')) ?></div>
                            <div class="officer-contact-item"><strong>Phone:</strong> <?= htmlspecialchars($getVal('agent_tel')) ?></div>
                            <div class="officer-contact-item"><strong>Email:</strong> <?= htmlspecialchars($getVal('agent_email')) ?></div>
                            <div class="officer-contact-item full-span"><strong>Address:</strong> <?= htmlspecialchars($getVal('agent_address')) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── SECTION 5: Tax & Particulars Ledgers ─── -->
            <div class="registry-section">
                <div class="section-header">
                    <div class="section-icon">💵</div>
                    <h3>Tax &amp; Vehicle Particulars Ledgers</h3>
                </div>
                <div class="grid-info">
                    <div class="info-cell">
                        <div class="cell-label">Tax Identification Number (TIN)</div>
                        <div class="cell-value"><code><?= htmlspecialchars($getVal('tax_number')) ?></code></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Vehicle Particulars License Number</div>
                        <div class="cell-value"><code><?= htmlspecialchars($getVal('vehicle_particulars_number')) ?></code></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Particulars Purchase Amount</div>
                        <div class="cell-value"><?= htmlspecialchars($formatAmt('vehicle_particulars_amount')) ?></div>
                    </div>
                    <div class="info-cell">
                        <div class="cell-label">Particulars Purchase Date</div>
                        <div class="cell-value"><?= htmlspecialchars($formatDate('vehicle_particulars_purchase_date')) ?></div>
                    </div>
                    <div class="info-cell full-width">
                        <div class="cell-label">Particulars Expiry Date</div>
                        <div class="cell-value">
                            <?php 
                                $expiryVal = $getVal('vehicle_particulars_expiry_date'); 
                                $expiryText = $formatDate('vehicle_particulars_expiry_date');
                                if ($expiryVal !== '—') {
                                    $expired = strtotime($expiryVal) < time();
                                    if ($expired) {
                                        echo '<span class="cell-badge expired">⚠️ ' . htmlspecialchars($expiryText) . ' (Expired)</span>';
                                    } else {
                                        echo '<span class="cell-badge active">✓ ' . htmlspecialchars($expiryText) . ' (Active)</span>';
                                    }
                                } else {
                                    echo '—';
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── SECTION 6: Security Clearances ─── -->
            <div class="registry-section">
                <div class="section-header">
                    <div class="section-icon">🛡️</div>
                    <h3>Security Clearances</h3>
                </div>
                
                <div class="cards-row">
                    <!-- Police Clearance Card -->
                    <div class="registry-card">
                        <div class="registry-card-header">
                            👮 Police Clearance Certificate
                        </div>
                        <div class="officer-contact-grid">
                            <div class="officer-contact-item"><strong>Signee Name:</strong> <?= htmlspecialchars($getVal('pol_clearance_name')) ?></div>
                            <div class="officer-contact-item"><strong>Rank:</strong> <?= htmlspecialchars($getVal('pol_clearance_rank')) ?></div>
                            <div class="officer-contact-item"><strong>State:</strong> <?= htmlspecialchars($getVal('pol_clearance_state')) ?></div>
                            <div class="officer-contact-item"><strong>LGA:</strong> <?= htmlspecialchars($getVal('pol_clearance_local_govt')) ?></div>
                            <div class="officer-contact-item"><strong>Phone:</strong> <?= htmlspecialchars($getVal('pol_clearance_tel')) ?></div>
                            <div class="officer-contact-item"><strong>Email:</strong> <?= htmlspecialchars($getVal('pol_clearance_email')) ?></div>
                            <div class="officer-contact-item full-span"><strong>Station Address:</strong> <?= htmlspecialchars($getVal('pol_clearance_office_address')) ?></div>
                        </div>
                    </div>

                    <!-- Driver's License Signee Card -->
                    <div class="registry-card">
                        <div class="registry-card-header">
                            🪪 Driver's License Signee
                        </div>
                        <div class="officer-contact-grid">
                            <div class="officer-contact-item"><strong>Signee Name:</strong> <?= htmlspecialchars($getVal('dl_name')) ?></div>
                            <div class="officer-contact-item"><strong>Rank:</strong> <?= htmlspecialchars($getVal('dl_rank')) ?></div>
                            <div class="officer-contact-item"><strong>Phone:</strong> <?= htmlspecialchars($getVal('dl_tel')) ?></div>
                            <div class="officer-contact-item"><strong>Email:</strong> <?= htmlspecialchars($getVal('dl_email')) ?></div>
                            <div class="officer-contact-item full-span"><strong>Station/Address:</strong> <?= htmlspecialchars($getVal('dl_address')) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── SECTION 7: National Security Officers Audits ─── -->
            <div class="registry-section">
                <div class="section-header">
                    <div class="section-icon">🔐</div>
                    <h3>National Security Officers Audits</h3>
                </div>
                
                <div class="officers-grid">
                    <?php 
                    $activeOfficersCount = 0;
                    foreach ($officerTypes as $officerTitle => $prefix): 
                        for ($i = 1; $i <= 3; $i++): 
                            $name = $getVal("{$prefix}_{$i}_name");
                            $rank = $getVal("{$prefix}_{$i}_rank");
                            $tel = $getVal("{$prefix}_{$i}_tel");
                            $email = $getVal("{$prefix}_{$i}_email");
                            $address = $getVal("{$prefix}_{$i}_address");
                            
                            $isFilled = ($name !== '—' || $rank !== '—' || $tel !== '—');
                            if ($isFilled):
                                $activeOfficersCount++;
                                $serviceIcon = match($prefix) {
                                    'custom_officer' => '⚓',
                                    'police_officer' => '🛡',
                                    'dss_officer'    => '🔍',
                                    'nia_officer'    => '🌐',
                                };
                                $serviceClass = match($prefix) {
                                    'custom_officer' => 'customs',
                                    'police_officer' => 'police',
                                    'dss_officer'    => 'dss',
                                    'nia_officer'    => 'nia',
                                };
                    ?>
                                <div class="officer-print-card">
                                    <div class="officer-badge-circle officer-badge-<?= $serviceClass ?>" title="<?= htmlspecialchars($officerTitle) ?>">
                                        <?= $serviceIcon ?>
                                    </div>
                                    <div class="officer-details-wrap">
                                        <div class="officer-meta-row">
                                            <div class="officer-name"><?= htmlspecialchars($name) ?></div>
                                            <span class="officer-rank-badge officer-rank-<?= $serviceClass ?>">
                                                <?= htmlspecialchars($rank !== '—' ? $rank : 'Officer') ?>
                                            </span>
                                        </div>
                                        <div style="font-size: 7px; color: var(--text-light); text-transform: uppercase; margin-bottom: 4px;">
                                            <?= htmlspecialchars($officerTitle) ?> — Slot <?= $i ?>
                                        </div>
                                        <div class="officer-contact-grid">
                                            <div class="officer-contact-item">📞 <?= htmlspecialchars($tel) ?></div>
                                            <div class="officer-contact-item">✉️ <?= htmlspecialchars($email) ?></div>
                                            <div class="officer-contact-item full-span">🏢 <?= htmlspecialchars($address) ?></div>
                                        </div>
                                    </div>
                                </div>
                    <?php 
                            endif;
                        endfor; 
                    endforeach; 
                    ?>
                </div>
                
                <?php if ($activeOfficersCount === 0): ?>
                    <div style="text-align: center; padding: 20px; border: 1px dashed var(--border-dark); border-radius: 8px; color: var(--text-light); font-size: 10px;">
                        No active security service officer audits have been logged for this vehicle.
                    </div>
                <?php endif; ?>
            </div>

            <!-- ─── SECTION 8: Custom Dynamic Form Fields ─── -->
            <?php
                $displayedKeys = [
                    'number_plate_state', 'number_plate_lga',
                    'country_of_origin', 'country_of_manufacture', 'ship_year', 'ship_departure_port', 'ship_departure_date', 'ship_landing_date', 'custom_papers_status',
                    'purchase_date', 'purchase_amount', 'means_of_identification', 'insurance_cover',
                    'importer_name', 'importer_company', 'importer_address', 'importer_email', 'importer_tel',
                    'clearing_agent_name', 'clearing_agent_company', 'clearing_agent_address', 'clearing_agent_email', 'clearing_agent_tel',
                    'foreign_office_name', 'foreign_office_company', 'foreign_office_address', 'foreign_office_email', 'foreign_office_tel',
                    'port_name', 'port_company', 'port_address', 'port_email', 'port_tel',
                    'agent_name', 'agent_address', 'agent_tel', 'agent_email',
                    'tax_number', 'vehicle_particulars_number', 'vehicle_particulars_purchase_date', 'vehicle_particulars_amount', 'vehicle_particulars_expiry_date',
                    'pol_clearance_name', 'pol_clearance_rank', 'pol_clearance_office_address', 'pol_clearance_local_govt', 'pol_clearance_state', 'pol_clearance_tel', 'pol_clearance_email',
                    'dl_name', 'dl_rank', 'dl_address', 'dl_tel', 'dl_email'
                ];
                foreach ($officerTypes as $prefix) {
                    for ($i = 1; $i <= 3; $i++) {
                        foreach (['name', 'rank', 'address', 'tel', 'email'] as $suffix) {
                            $displayedKeys[] = "{$prefix}_{$i}_{$suffix}";
                        }
                    }
                }
                $extraFields = [];
                if (!empty($cf)) {
                    foreach ($cf as $fieldKey => $fieldValue) {
                        if (in_array($fieldKey, $displayedKeys, true)) {
                            continue;
                        }
                        if (is_array($fieldValue)) {
                            $fieldValue = implode(', ', $fieldValue);
                        }
                        $fieldValue = trim((string)$fieldValue);
                        if ($fieldValue !== '') {
                            $extraFields[$fieldKey] = $fieldValue;
                        }
                    }
                }
            ?>

            <?php if (!empty($extraFields)): ?>
                <div class="registry-section">
                    <div class="section-header">
                        <div class="section-icon">⚙️</div>
                        <h3>Additional Registry Information</h3>
                    </div>
                    <div class="grid-info">
                        <?php foreach ($extraFields as $fieldKey => $fieldValue): ?>
                            <div class="info-cell">
                                <div class="cell-label"><?= htmlspecialchars(ucwords(str_replace(['_', '-'], ' ', preg_replace('/^custom_/', '', $fieldKey)))) ?></div>
                                <div class="cell-value"><?= htmlspecialchars($fieldValue) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ─── SECTION 2: Ownership Chain ─── -->
            <div class="ownership-section">
                <div class="section-header">
                    <div class="section-icon">🔗</div>
                    <h3>Chain of Ownership — Custody Record</h3>
                </div>

                <?php 
                $totalOwners = count($ownershipHistory);
                foreach ($ownershipHistory as $idx => $owner): 
                    $isCurrent = ($idx === $totalOwners - 1);
                    $orderNum = $idx + 1;
                ?>
                    <?php if ($idx > 0): ?>
                        <div class="chain-connector">
                            <div class="connector-arrow">↓</div>
                        </div>
                    <?php endif; ?>

                    <div class="owner-card">
                        <div class="owner-card-header">
                            <div class="owner-order">
                                <div class="owner-number"><?= $orderNum ?></div>
                                <span class="owner-title"><?= htmlspecialchars($owner['full_name']) ?></span>
                            </div>
                            <span class="owner-role <?= $isCurrent ? 'current' : 'previous' ?>">
                                <?= $isCurrent ? '● Current Owner' : 'Previous Owner' ?>
                            </span>
                        </div>

                        <div class="owner-card-body">
                            <div class="owner-portrait">
                                <?php if (!empty($owner['passport_photo_path'])): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($owner['passport_photo_path']) ?>" alt="<?= htmlspecialchars($owner['full_name']) ?>">
                                <?php else: ?>
                                    <div class="no-photo">👤</div>
                                <?php endif; ?>

                                <?php if (!empty($owner['signature_path'])): ?>
                                    <div class="signature-box">
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($owner['signature_path']) ?>" alt="Signature">
                                        <div class="signature-label">Signature</div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="owner-info">
                                <div class="info-field">
                                    <div class="field-label">Full Name</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['full_name']) ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">Phone Number</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['phone']) ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">Email Address</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['email'] ?: '—') ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">Date of Birth</div>
                                    <div class="field-value"><?= !empty($owner['date_of_birth']) ? htmlspecialchars(date('M j, Y', strtotime($owner['date_of_birth']))) : '—' ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">Gender</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['gender'] ?: '—') ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">Nationality</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['nationality'] ?: '—') ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">Occupation</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['occupation'] ?: '—') ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">NIN</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['nin'] ?: '—') ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">BVN</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['bvn'] ?: '—') ?></div>
                                </div>
                                <div class="info-field">
                                    <div class="field-label">State / LGA</div>
                                    <div class="field-value"><?= htmlspecialchars(($owner['state'] ?: '—') . ' / ' . ($owner['lga'] ?: '—')) ?></div>
                                </div>
                                <div class="info-field full-width">
                                    <div class="field-label">Residential Address</div>
                                    <div class="field-value"><?= htmlspecialchars($owner['address'] ?: '—') ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction/Acquisition Strip -->
                        <div class="transaction-strip">
                            <div class="txn-item">
                                <div class="txn-label">Acquisition Date</div>
                                <div class="txn-value"><?= htmlspecialchars(date('M j, Y', strtotime($owner['purchase_date']))) ?></div>
                            </div>
                            <div class="txn-item">
                                <div class="txn-label">Amount Paid</div>
                                <div class="txn-value">₦<?= number_format($owner['purchase_amount'], 2) ?></div>
                            </div>
                            <div class="txn-item">
                                <div class="txn-label">Market / Dealer</div>
                                <div class="txn-value"><?= htmlspecialchars($owner['market_name'] ?: '—') ?></div>
                            </div>
                            <div class="txn-item">
                                <div class="txn-label">Seller</div>
                                <div class="txn-value"><?= htmlspecialchars($owner['seller_name'] ?: '—') ?></div>
                            </div>
                            <div class="txn-item">
                                <div class="txn-label">Witness</div>
                                <div class="txn-value"><?= htmlspecialchars($owner['witness_name'] ?: '—') ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($ownershipHistory)): ?>
                    <div style="text-align: center; padding: 30px; color: var(--text-light);">
                        <p>No ownership records found for this vehicle.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ─── SECTION 3: Verification & Issuer ─── -->
            <div class="verification-section">
                <div class="section-header">
                    <div class="section-icon">✓</div>
                    <h3>Verification &amp; Issuing Authority</h3>
                </div>

                <div class="stamp-box">
                    <div style="display: flex; gap: 40px;">
                        <div class="stamp-info">
                            <div class="stamp-label">Issuing Officer</div>
                            <div class="stamp-value"><?= htmlspecialchars($issuerName) ?></div>
                        </div>
                        <div class="stamp-info">
                            <div class="stamp-label">Officer Email</div>
                            <div class="stamp-value"><?= htmlspecialchars($issuerEmail) ?></div>
                        </div>
                        <div class="stamp-info">
                            <div class="stamp-label">Date Issued</div>
                            <div class="stamp-value"><?= htmlspecialchars($generatedAt) ?></div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 24px;">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 4px; border-left: 1px solid var(--border-dark); padding-left: 24px;">
                            <img src="<?= $qrUrl ?>" alt="Verification QR Code" style="width: 76px; height: 76px; border: 1px solid var(--border-dark); border-radius: 6px; padding: 2px; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <span style="font-size: 7px; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; margin-top: 2px;">Scan to Verify</span>
                        </div>

                        <?php
                        $vStatus = $verification['status'] ?? 'PENDING';
                        $stampClass = $vStatus === 'APPROVED' ? 'stamp-approved' : ($vStatus === 'REJECTED' ? 'stamp-rejected' : 'stamp-pending');
                        ?>
                        <div class="verification-stamp <?= $stampClass ?>">
                            <div class="stamp-text"><?= htmlspecialchars($vStatus) ?></div>
                            <div class="stamp-sub">NVOTS Registry</div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /doc-body -->

        <!-- ═══════════════ FOOTER ═══════════════ -->
        <div class="doc-footer">
            <div class="footer-note">
                This document is an official record generated by the National Vehicle Ownership &amp; Traceability System (NVOTS). 
                The information herein constitutes the complete chain of custody for the above-described vehicle. 
                Any tampering, falsification, or unauthorized reproduction of this document is punishable under Nigerian law.
                For verification, contact the NVOTS Registry Office with the reference number.
            </div>
            <div class="footer-qr">
                <div class="ref-mono"><?= htmlspecialchars($docRef) ?></div>
                <div style="margin-top: 4px;">© <?= date('Y') ?> NVOTS Nigeria</div>
            </div>
        </div>

    </div><!-- /document-page -->

</body>
</html>
