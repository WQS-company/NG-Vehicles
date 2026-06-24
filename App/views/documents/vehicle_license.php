<?php
// App/views/documents/vehicle_license.php

$qrData = urlencode(BASE_URL . '/document/vehicle_license/' . (int)$vehicle['id']);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $qrData;

$cf = [];
if (!empty($vehicle['custom_fields'])) {
    $cf = json_decode($vehicle['custom_fields'], true) ?: [];
}

$logoPath = !empty($settings['platform_logo']) ? BASE_URL . '/' . $settings['platform_logo'] : '';

// Calculate expiry date (1 year from registration)
$regDate = strtotime($vehicle['registration_date'] ?? date('Y-m-d'));
$expiryDateStr = date('M-Y', strtotime('+1 year', $regDate));

// Mocking some payments based on typical Nigerian license fees
$fees = [
    'Vehicle license' => 3125.00,
    'Road Worthiness' => 1500.00,
    'MOT / VEHICLE INSPECTION' => 3250.00,
    'Gaseous Emission Charge' => 100.00,
    'Proof of Ownership' => 1000.00,
    'RADIO AND TELEVISION RATES' => 1200.00,
];

$insuranceAmount = 0;
if ($insurance) {
    $insuranceAmount = (float)$insurance['amount'];
    $fees['Insurance policy: ' . $insurance['policy_number']] = $insuranceAmount;
} else {
    $fees['Insurance policy: N/A'] = 0.00;
}

$totalFees = array_sum($fees);

// Helper to convert number to words (simple mock for demo)
function numberToWords($num) {
    return strtoupper((new NumberFormatter("en", NumberFormatter::SPELLOUT))->format($num));
}
$totalInWords = "TOTAL PAID"; // Fallback if intl not loaded

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vehicle License — <?= htmlspecialchars($vehicle['plate_number']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #e2e8f0; margin: 0; padding: 20px; color: #111; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn-print { padding: 10px 20px; background: #059669; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 10mm; }
        }
        .cert-container {
            width: 190mm;
            min-height: 270mm;
            margin: 0 auto;
            position: relative;
            background: #fff;
            box-sizing: border-box;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .watermark {
            position: absolute;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            opacity: 0.1; width: 70%; z-index: 0; pointer-events: none;
        }
        .header { display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; border-bottom: 2px solid #ccc; padding-bottom: 15px; margin-bottom: 20px; }
        .header-logo img { height: 80px; }
        .header-title { text-align: center; flex: 1; }
        .header-title h1 { font-size: 20px; margin: 0; text-transform: uppercase; }
        .header-title p { margin: 5px 0 0 0; font-size: 14px; }
        .header-qr img { width: 90px; height: 90px; }
        
        .info-grid {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 8px 15px;
            font-size: 13px;
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }
        .info-label { font-weight: bold; color: #555; }
        .info-value { font-family: 'Courier New', monospace; font-weight: bold; text-transform: uppercase; }
        
        .table-section { position: relative; z-index: 1; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; font-family: 'Courier New', monospace; font-weight: bold; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        .text-right { text-align: right; }
        
        .footer-text { text-align: center; font-size: 11px; margin-bottom: 30px; position: relative; z-index: 1; }
        .footer-text p { margin: 4px 0; }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
            margin-top: 40px;
        }
        .stamp-round {
            width: 100px; height: 100px; border-radius: 50%; border: 2px dashed #666;
            display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666;
            text-align: center; transform: rotate(-15deg);
        }
        .signature-block { text-align: center; }
        .signature-block img { height: 60px; }
        .signature-block .name { font-weight: bold; font-size: 13px; text-transform: uppercase; margin-top: 5px; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .signature-block .title { font-size: 11px; margin-top: 2px; }
        
        .footer-bottom {
            margin-top: 40px; font-size: 9px; text-align: center; color: #888; border-top: 1px solid #eee; padding-top: 10px;
            position: relative; z-index: 1;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print License</button>
    </div>

    <div class="cert-container">
        <?php if ($logoPath): ?>
            <img src="<?= $logoPath ?>" class="watermark" alt="Watermark">
        <?php endif; ?>

        <div class="header">
            <div class="header-logo">
                <?php if ($logoPath): ?>
                    <img src="<?= $logoPath ?>" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="header-title">
                <h1><?= htmlspecialchars($settings['platform_title'] ?? 'Vehicle Registry') ?></h1>
                <p>Vehicle License Certificate</p>
                <div style="margin-top: 5px; font-size: 11px; font-family: monospace;">Ref: <?= htmlspecialchars($docRef) ?></div>
            </div>
            <div class="header-qr">
                <img src="<?= $qrUrl ?>" alt="QR Code">
            </div>
        </div>

        <div class="info-grid">
            <div class="info-label">Owner:</div>
            <div class="info-value"><?= htmlspecialchars($owner['full_name'] ?? 'N/A') ?></div>
            
            <div class="info-label">Owner ID:</div>
            <div class="info-value"><?= htmlspecialchars($owner['nin'] ?? $owner['id'] ?? 'N/A') ?></div>
            
            <div class="info-label">Engine Capacity:</div>
            <div class="info-value"><?= htmlspecialchars($cf['Engine Capacity'] ?? 'N/A') ?></div>
            
            <div class="info-label">Phone:</div>
            <div class="info-value"><?= htmlspecialchars($owner['phone'] ?? 'N/A') ?></div>
            
            <div class="info-label">Number plate:</div>
            <div class="info-value"><?= htmlspecialchars($vehicle['plate_number']) ?></div>
            
            <div class="info-label">Chassis No.:</div>
            <div class="info-value"><?= htmlspecialchars($vehicle['chassis_number']) ?></div>
            
            <div class="info-label">Vehicle Type:</div>
            <div class="info-value"><?= htmlspecialchars($vehicle['make']) ?></div>
            
            <div class="info-label">Vehicle Model:</div>
            <div class="info-value"><?= htmlspecialchars($vehicle['model']) ?></div>
            
            <div class="info-label">Vehicle Colour:</div>
            <div class="info-value"><?= htmlspecialchars($vehicle['color']) ?></div>
            
            <div class="info-label">Vehicle Usage:</div>
            <div class="info-value">PRIVATE</div>
        </div>

        <div class="table-section">
            <table>
                <tbody>
                    <?php $i = 1; foreach($fees as $name => $amount): ?>
                    <tr>
                        <td width="5%"><?= $i++ ?></td>
                        <td><?= htmlspecialchars($name) ?> <?= (strpos($name, 'Insurance') === false) ? 'Expiry Date: ' . $expiryDateStr : '' ?></td>
                        <td class="text-right">N<?= number_format($amount, 2) ?>=PAID=</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="2">Total: <span style="text-transform: uppercase;">Total amount paid for services</span></td>
                        <td class="text-right">N<?= number_format($totalFees, 2) ?>=PAID=</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="footer-text">
            <p>The Above QR Carries Registration Data:</p>
            <p>Number Plate Receipt, Vehicle Licence Certificate, Road Worthiness Certificate,</p>
            <p>MOT Certificate and Motor Insurance.</p>
            <p>Please confirm the genuineness of this V/L receipt by scanning.</p>
            <p><strong>THIS DOCUMENT IS A RECEIPT: An Evidence of Having Paid for Vehicle Licence</strong></p>
        </div>

        <div class="signature-section">
            <div class="stamp-round">
                EXAMINED &<br>VERIFIED<br><br><?= date('M Y') ?>
            </div>
            <div class="signature-block">
                <div style="height: 60px; font-family: 'Brush Script MT', cursive; font-size: 28px; padding-top: 15px;">
                    <?= htmlspecialchars($issuerName) ?>
                </div>
                <div class="name"><?= htmlspecialchars($issuerName) ?></div>
                <div class="title">Issuing Officer / Chairman</div>
            </div>
            <div class="header-qr">
                <!-- Secondary QR for aesthetics as in original -->
                <img src="<?= $qrUrl ?>" alt="QR Code" style="width: 70px; height: 70px; opacity: 0.8;">
            </div>
        </div>
        
        <div class="footer-bottom">
            All rights Reserved Copyright &copy; Printed on <?= $generatedAt ?><br>
            <?= htmlspecialchars($vehicle['plate_number']) ?> - <?= htmlspecialchars($vehicle['chassis_number']) ?>
        </div>
    </div>
</body>
</html>
