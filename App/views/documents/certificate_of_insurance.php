<?php
// App/views/documents/certificate_of_insurance.php

$logoPath = !empty($settings['platform_logo']) ? BASE_URL . '/' . $settings['platform_logo'] : '';

// Insurance details mock if not present in DB
$certNo = $insurance['policy_number'] ?? 'FX' . time() . '25';
$policyNo = $insurance['policy_number'] ?? 'FX/PM/2025/10/' . rand(1000, 9999);
$startDate = $insurance['start_date'] ?? date('Y-m-d');
$endDate = $insurance['end_date'] ?? date('Y-m-d', strtotime('+1 year'));
$amount = $insurance['amount'] ?? '15000.00';
$provider = $insurance['provider'] ?? 'APPROVED INSURANCE LTD';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Insurance — <?= htmlspecialchars($vehicle['plate_number']) ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; background: #e2e8f0; margin: 0; padding: 20px; color: #111; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn-print { padding: 10px 20px; background: #059669; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 15mm; }
        }
        .cert-container {
            width: 180mm;
            min-height: 250mm;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            opacity: 0.05; width: 60%; z-index: 0; pointer-events: none;
        }
        .header { text-align: center; margin-bottom: 30px; position: relative; z-index: 1; }
        .header-top { font-size: 10px; text-transform: uppercase; margin-bottom: 15px; font-weight: bold; }
        .header h1 { font-size: 24px; margin: 0; letter-spacing: 1px; font-weight: normal; }
        
        .ref-bar { display: flex; justify-content: space-between; font-weight: bold; font-size: 13px; margin-bottom: 30px; position: relative; z-index: 1; }
        
        .main-content { font-size: 13px; line-height: 1.6; position: relative; z-index: 1; }
        .list-item { display: flex; margin-bottom: 10px; }
        .list-num { width: 30px; }
        .list-body { flex: 1; }
        .list-title { margin-bottom: 2px; }
        .list-value { font-family: 'Courier New', monospace; font-weight: bold; font-size: 14px; margin-left: 20px; }
        
        .sub-list { margin-left: 20px; margin-top: 5px; font-size: 12px; }
        .sub-list p { margin: 2px 0; }
        
        .banner { background: #eee; text-align: center; padding: 10px; font-weight: bold; font-size: 18px; margin: 20px 0; letter-spacing: 2px; border: 1px solid #ccc; }
        
        .small-print { font-size: 9px; font-style: italic; color: #555; text-align: justify; margin-bottom: 20px; }
        
        .price-banner { text-align: center; font-size: 18px; font-weight: bold; font-style: italic; margin-bottom: 30px; }
        
        .cert-footer { font-size: 10px; text-align: justify; margin-bottom: 40px; font-weight: bold; }
        
        .signatures { display: flex; justify-content: space-between; align-items: flex-end; position: relative; z-index: 1; margin-top: 50px; }
        .stamp-round { width: 80px; height: 80px; border-radius: 50%; border: 2px solid #333; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center; transform: rotate(-20deg); }
        .sig-block { text-align: center; width: 200px; }
        .sig-img { height: 50px; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 5px; }
        
        .provider-logo { text-align: center; margin-bottom: 20px; }
        .provider-logo img { height: 50px; filter: grayscale(100%); }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print Certificate</button>
    </div>

    <div class="cert-container">
        <?php if ($logoPath): ?>
            <img src="<?= $logoPath ?>" class="watermark" alt="Watermark">
        <?php endif; ?>

        <div class="header">
            <div class="header-top">
                MOTOR VEHICLES (THIRD PARTY INSURANCE) ORDINANCE, 1945<br>
                (NIGERIA)
            </div>
            <h1>Certificate of Insurance</h1>
        </div>

        <div class="ref-bar">
            <div>Certificate No: <?= htmlspecialchars($certNo) ?><br><span style="font-weight: normal; font-size: 11px;"><?= htmlspecialchars($vehicle['make'] . ' - ' . $vehicle['model']) ?></span></div>
            <div>Policy No: <?= htmlspecialchars($policyNo) ?></div>
        </div>

        <div class="main-content">
            <div class="list-item">
                <div class="list-num">1.</div>
                <div class="list-body">
                    <div class="list-title">Index mark and Registration Number of vehicle</div>
                    <div class="list-value"><?= htmlspecialchars($vehicle['plate_number']) ?></div>
                </div>
            </div>
            
            <div class="list-item">
                <div class="list-num">2.</div>
                <div class="list-body">
                    <div class="list-title">Name of Policy Holder</div>
                    <div class="list-value"><?= htmlspecialchars($owner['full_name'] ?? 'N/A') ?></div>
                </div>
            </div>
            
            <div class="list-item">
                <div class="list-num">3.</div>
                <div class="list-body">
                    <div class="list-title">Effective date of the commencement of insurance for the purposes of the Ordinance(s)</div>
                    <div class="list-value"><?= date('Y-m-d', strtotime($startDate)) ?></div>
                </div>
            </div>
            
            <div class="list-item">
                <div class="list-num">4.</div>
                <div class="list-body">
                    <div class="list-title">Date of expiry of Insurance</div>
                    <div class="list-value"><?= date('Y-m-d', strtotime($endDate)) ?></div>
                </div>
            </div>
            
            <div class="list-item">
                <div class="list-num">5.</div>
                <div class="list-body">
                    <div class="list-title">Persons or classes of persons entitled to drive*</div>
                    <div class="sub-list">
                        <p>a. The Policy Holder.</p>
                        <p>b. Any person who is driving on the Policy holder's order or with his permission.</p>
                        <p style="margin-top: 10px;">Provided that the person driving is permitted in accordance with the licensing or other laws or regulations to drive the Motor Vehicle or has been so permitted and is not disqualified by order of a Court of Law or by reason of any enactment or regulation in that behalf from driving such Motor Vehicle.</p>
                    </div>
                </div>
            </div>
            
            <div class="list-item" style="margin-top: 15px;">
                <div class="list-num">6.</div>
                <div class="list-body">
                    <div class="list-title" style="font-weight: bold;">Limitations as to use COMMERCIAL(FOR COMMERCIAL USE ONLY)</div>
                    <div class="sub-list">
                        <p><strong>Use in connection with the Policy holders business; whilst the vehicle is being so used the Carriage of passengers (other than for hire or reward) is permitted.</strong></p>
                        <p><strong>Use for social domestic and pleasure purposes.</strong></p>
                        <p style="font-style: italic; margin-top: 10px;">The Policy does not cover -</p>
                        <p>(1) Use for hire or reward or for racing, pace-making, reliability trial or speed-testing.</p>
                        <p>(2) Use whilst drawing a trailer except the towing of any one disabled mechanically Propelled vehicle.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="banner">THIRD PARTY ONLY</div>

        <div class="small-print">
            *Limitations rendered inoperative by the provisions of the Motor Vehicles (Third Party Insurance) Ordinance, 1945 (Nigeria), the Motor Vehicles (Third Party Insurance) Act, 1958 (Ghana), the Motor Vehicles (Third Party Insurance) Ordinance, 1948 (Gambia), and the Motor Vehicles (Third Party Insurance), 1949 (Sierra Leone) are not to be included under this heading. "Please verify the status of your policy. By SMS: text policy No.* Plate No to 33125 or visit www.askniid.org.<br>
            Remember: If this policy is not on NIID, it could be fake and you may be embarrassed by Law Enforcement Agents.
        </div>

        <div class="price-banner">
            PRIVATE NGN<?= number_format((float)$amount, 2) ?>
        </div>

        <div class="cert-footer">
            I/WE HEREBY CERTIFY that the policy to which this certificate relates is issued in accordance with the provisions of the Motor Vehicles (Third Party Insurance) Ordinance, 1945 (Nigeria), the Motor Vehicles (Third Party Insurance) Act, 1958 (Ghana), the Motor Vehicles (Third Party Insurance) Ordinance, 1948 (Gambia), and the Motor Vehicles (Third Party Insurance) Ordinance, 1949 (Sierra Leone)
        </div>
        
        <div class="provider-logo">
            <?php if ($logoPath): ?>
                <img src="<?= $logoPath ?>" alt="Provider Logo">
            <?php else: ?>
                <div style="font-size: 24px; font-weight: bold; font-family: sans-serif;">=||=</div>
            <?php endif; ?>
            <div style="font-size: 10px; font-weight: bold; margin-top: 5px;"><?= htmlspecialchars($provider) ?></div>
        </div>

        <div class="signatures">
            <div class="stamp-round">
                Examined<br>&<br>Verified
            </div>
            <div class="sig-block">
                <div class="sig-img" style="font-family: 'Brush Script MT', cursive; font-size: 24px; padding-top: 15px;">
                    <?= htmlspecialchars($issuerName) ?>
                </div>
                <div>Managing director</div>
            </div>
        </div>
    </div>
</body>
</html>
