<?php
// App/views/documents/proof_of_ownership.php

$qrData = urlencode(BASE_URL . '/document/proof_of_ownership/' . (int)$vehicle['id']);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $qrData;

$cf = [];
if (!empty($vehicle['custom_fields'])) {
    $cf = json_decode($vehicle['custom_fields'], true) ?: [];
}
$getVal = function($key) use ($cf) {
    return trim((string)($cf[$key] ?? '—'));
};

$logoPath = !empty($settings['platform_logo']) ? BASE_URL . '/' . $settings['platform_logo'] : '';

// Calculate expiry date (1 year from registration)
$regDate = strtotime($vehicle['registration_date'] ?? date('Y-m-d'));
$expiryDate = date('M Y', strtotime('+1 year', $regDate));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proof of Ownership — <?= htmlspecialchars($vehicle['plate_number']) ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; background: #e2e8f0; margin: 0; padding: 20px; color: #111; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn-print { padding: 10px 20px; background: #059669; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            @page { size: A4 landscape; margin: 0; }
        }
        .cert-container {
            width: 270mm;
            height: 180mm;
            margin: 0 auto;
            position: relative;
            background: #fff;
            background-image: radial-gradient(circle at center, #e8f5e9 0%, #fff 100%);
            border: 20px solid transparent;
            border-image: repeating-linear-gradient(45deg, #2e7d32, #2e7d32 10px, #a5d6a7 10px, #a5d6a7 20px) 20;
            box-sizing: border-box;
            padding: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .watermark {
            position: absolute;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            opacity: 0.05; width: 60%; z-index: 0; pointer-events: none;
        }
        .header {
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .header img { height: 80px; margin-bottom: 10px; }
        .header h1 { font-size: 24px; margin: 5px 0; color: #1b5e20; text-transform: uppercase; letter-spacing: 2px; }
        .header h2 { font-size: 20px; margin: 0; color: #333; font-weight: normal; }
        
        .content {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            position: relative;
            z-index: 1;
            font-size: 15px;
            line-height: 1.8;
        }
        .col { width: 45%; }
        .row-item { display: flex; border-bottom: 1px dotted #ccc; padding: 4px 0; }
        .label { width: 140px; font-weight: bold; color: #444; }
        .value { flex: 1; text-transform: uppercase; font-family: 'Courier New', monospace; font-weight: bold; }
        
        .footer-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            position: relative;
            z-index: 1;
        }
        .expiry { font-size: 28px; font-weight: bold; color: #1b5e20; letter-spacing: 2px; }
        .qr-code img { width: 100px; height: 100px; border: 2px solid #ccc; padding: 2px; }
        .signature { text-align: center; width: 200px; }
        .signature img { height: 50px; }
        .sig-line { border-top: 1px solid #333; margin-top: 5px; padding-top: 5px; font-weight: bold; font-size: 12px; }
        
        .corner-dec { position: absolute; width: 40px; height: 40px; background: #2e7d32; z-index: 2; }
        .tl { top: -20px; left: -20px; transform: rotate(45deg); }
        .tr { top: -20px; right: -20px; transform: rotate(45deg); }
        .bl { bottom: -20px; left: -20px; transform: rotate(45deg); }
        .br { bottom: -20px; right: -20px; transform: rotate(45deg); }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print Certificate</button>
    </div>

    <div class="cert-container">
        <div class="corner-dec tl"></div>
        <div class="corner-dec tr"></div>
        <div class="corner-dec bl"></div>
        <div class="corner-dec br"></div>
        
        <?php if ($logoPath): ?>
            <img src="<?= $logoPath ?>" class="watermark" alt="Watermark">
        <?php endif; ?>

        <div class="header">
            <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Logo">
            <?php endif; ?>
            <h1><?= htmlspecialchars($settings['platform_title'] ?? 'Federal Republic of Nigeria') ?></h1>
            <p style="margin: 0; font-size: 12px;">(Uniform Licensing Scheme)</p>
            <h2>Proof of Ownership Certificate</h2>
        </div>

        <div class="content">
            <div class="col">
                <div class="row-item">
                    <div class="label">Plate Number:</div>
                    <div class="value"><?= htmlspecialchars($vehicle['plate_number']) ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Vehicle Make:</div>
                    <div class="value"><?= htmlspecialchars($vehicle['make']) ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Vehicle Type:</div>
                    <div class="value"><?= htmlspecialchars($vehicle['model']) ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Chassis Number:</div>
                    <div class="value"><?= htmlspecialchars($vehicle['chassis_number']) ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Reg Date:</div>
                    <div class="value"><?= date('M Y', strtotime($vehicle['registration_date'])) ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Name of Owner:</div>
                    <div class="value"><?= htmlspecialchars($owner['full_name'] ?? 'N/A') ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Address:</div>
                    <div class="value"><?= htmlspecialchars($owner['address'] ?? 'N/A') ?></div>
                </div>
            </div>
            
            <div class="col" style="width: 40%;">
                <div class="row-item">
                    <div class="label">Previous No.:</div>
                    <div class="value">N/A</div>
                </div>
                <div class="row-item">
                    <div class="label">State:</div>
                    <div class="value"><?= htmlspecialchars($owner['state'] ?? 'N/A') ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Model:</div>
                    <div class="value"><?= htmlspecialchars($vehicle['model']) ?></div>
                </div>
                <div class="row-item">
                    <div class="label">Colour:</div>
                    <div class="value"><?= htmlspecialchars($vehicle['color']) ?></div>
                </div>
            </div>
        </div>

        <div class="footer-section">
            <div class="expiry">
                <?= $expiryDate ?>
            </div>
            <div class="qr-code">
                <img src="<?= $qrUrl ?>" alt="QR Code">
            </div>
            <div class="signature">
                <div style="height: 50px; font-family: 'Brush Script MT', cursive; font-size: 24px; color: #000; padding-top: 10px;">
                    <?= htmlspecialchars($issuerName) ?>
                </div>
                <div class="sig-line">
                    EXECUTIVE CHAIRMAN / ISSUING OFFICER
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 15px; font-size: 10px; color: #666; font-family: monospace;">
            <?= htmlspecialchars($docRef) ?> - <?= htmlspecialchars(BASE_URL) ?>
        </div>
    </div>
</body>
</html>
