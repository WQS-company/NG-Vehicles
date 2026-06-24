<?php
// App/views/verifications/certificate.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Verification Certificate - <?= htmlspecialchars($cert['plate_number']) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            font-family: 'Times New Roman', Times, serif;
            padding: 3rem 0;
        }

        .certificate-container {
            max-width: 850px;
            background: #ffffff;
            margin: 0 auto;
            border: 15px double #1e3a8a;
            padding: 3rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
        }

        .cert-header {
            text-align: center;
            border-bottom: 3px double #10b981;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .gov-crest {
            font-size: 3.5rem;
            color: #1e3a8a;
            margin-bottom: 1rem;
        }

        .gov-title {
            font-family: 'Outfit', Arial, sans-serif;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-size: 1.6rem;
            margin: 0;
        }

        .gov-sub {
            font-family: 'Outfit', Arial, sans-serif;
            font-weight: 500;
            color: #10b981;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 1rem;
            margin-top: 0.25rem;
        }

        .cert-body {
            line-height: 1.6;
        }

        .cert-title {
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .photo-box {
            border: 2px solid #cbd5e1;
            padding: 4px;
            border-radius: 4px;
            background-color: #f8fafc;
        }

        .data-table td {
            padding: 8px 12px;
        }

        .cert-footer {
            margin-top: 3rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 1.5rem;
        }

        .seal-img {
            max-width: 130px;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .certificate-container {
                box-shadow: none;
                border: 15px double #1e3a8a;
                margin: 0;
                padding: 2rem;
                page-break-inside: avoid;
            }
            .btn-print-ctrl {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="container text-center mb-3 btn-print-ctrl">
        <button onclick="window.print();" class="btn btn-primary px-4 py-2"><i class="fa-solid fa-print me-1"></i> Print Certificate</button>
    </div>

    <div class="certificate-container">
        <!-- Federal Logo & Header -->
        <div class="cert-header">
            <div class="gov-crest"><i class="fa-solid fa-shield-halved"></i></div>
            <h1 class="gov-title">Federal Republic of Nigeria</h1>
            <h4 class="gov-sub">National Vehicle Registry & Traceability System (NVOTS)</h4>
        </div>

        <div class="cert-body">
            <h2 class="cert-title">Certificate of Vehicle Verification</h2>
            
            <p class="text-center mb-4">
                This is to officially certify that the vehicle described below has undergone a full integrity audit, customs verification, and ownership traceability analysis, and has been verified active and legally compliant on the national database registry.
            </p>

            <div class="row g-4">
                <!-- Vehicle and Owner Data -->
                <div class="col-8">
                    <h5 class="text-primary border-bottom pb-1 mb-2 fw-bold">Vehicle Specifications</h5>
                    <table class="table table-sm table-borderless data-table small mb-4">
                        <tr>
                            <td width="35%"><b>Plate Number:</b></td>
                            <td><span class="badge bg-dark text-white px-2 py-1"><?= htmlspecialchars($cert['plate_number']) ?></span></td>
                        </tr>
                        <tr>
                            <td><b>VIN Number:</b></td>
                            <td><code><?= htmlspecialchars($cert['vin']) ?></code></td>
                        </tr>
                        <tr>
                            <td><b>Engine Number:</b></td>
                            <td><code><?= htmlspecialchars($cert['engine_number']) ?></code></td>
                        </tr>
                        <tr>
                            <td><b>Chassis Number:</b></td>
                            <td><code><?= htmlspecialchars($cert['chassis_number']) ?></code></td>
                        </tr>
                        <tr>
                            <td><b>Make / Model:</b></td>
                            <td><?= htmlspecialchars($cert['manufacturer'] . ' ' . $cert['model'] . ' (' . $cert['year'] . ')') ?></td>
                        </tr>
                        <tr>
                            <td><b>Vehicle Color:</b></td>
                            <td><?= htmlspecialchars($cert['color']) ?></td>
                        </tr>
                    </table>

                    <h5 class="text-primary border-bottom pb-1 mb-2 fw-bold">Registered Legal Owner</h5>
                    <table class="table table-sm table-borderless data-table small m-0">
                        <tr>
                            <td width="35%"><b>Full Name:</b></td>
                            <td><b><?= htmlspecialchars($cert['owner_name']) ?></b></td>
                        </tr>
                        <tr>
                            <td><b>Phone / NIN:</b></td>
                            <td><?= htmlspecialchars($cert['owner_phone']) ?> | NIN: <?= htmlspecialchars($cert['owner_nin'] ?? 'N/A') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Photos & QR Code -->
                <div class="col-4 text-center">
                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Owner Photograph</label>
                        <div class="photo-box d-inline-block">
                            <?php if (!empty($cert['passport_photo_path'])): ?>
                                <img src="<?= BASE_URL ?>/<?= $cert['passport_photo_path'] ?>" width="110" height="110" class="object-fit-cover" alt="Owner photo">
                            <?php else: ?>
                                <img src="<?= BASE_URL ?>/public/images/no-avatar.png" width="110" height="110" alt="Avatar">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label class="small text-muted d-block mb-1">Database Verification QR</label>
                        <div class="photo-box d-inline-block">
                            <img src="<?= $qrCodeUrl ?>" width="110" height="110" alt="QR Code Verification">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Ownership Chain -->
            <?php if (!empty($ownershipHistory)): ?>
            <div class="mt-4 border-top pt-3">
                <h5 class="text-primary pb-1 mb-3 fw-bold"><i class="fa-solid fa-timeline me-2"></i>Ownership Traceability Chain</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm data-table small" style="border-color:#cbd5e1;">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Owner Name</th>
                                <th>NIN</th>
                                <th>Acquisition Date</th>
                                <th>Transfer Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ownershipHistory as $index => $history): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($history['owner_name']) ?></td>
                                    <td><?= htmlspecialchars($history['owner_nin'] ?? 'N/A') ?></td>
                                    <td><?= date('d M Y', strtotime($history['purchase_date'])) ?></td>
                                    <td>
                                        <?php if ($index === 0): ?>
                                            <span class="badge bg-primary text-white">First Registry Onboarding</span>
                                        <?php else: ?>
                                            Transferred from: <?= htmlspecialchars($history['seller_name']) ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="cert-footer row align-items-center">
            <div class="col-6 small">
                <p class="m-0"><b>Certificate No:</b> <code class="text-dark fw-bold"><?= $verificationNumber ?></code></p>
                <p class="m-0"><b>Date Verified:</b> <?= date('d M Y', strtotime($cert['verified_at'])) ?></p>
                <p class="m-0"><b>Registry Auditor:</b> <?= htmlspecialchars($cert['verifier_email']) ?></p>
            </div>
            
            <div class="col-6 text-end">
                <div class="d-inline-block text-center border-top border-dark pt-1 px-4 small" style="min-width: 180px;">
                    <p class="m-0 fw-bold">Registrar General</p>
                    <p class="text-muted m-0" style="font-size: 11px;">FGN Vehicle Registry</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
