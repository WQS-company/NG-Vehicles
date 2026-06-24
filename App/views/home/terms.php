<?php
// App/views/home/terms.php
// Public Terms & Conditions view
?>
<div class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card glass-panel border-0 p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary border-opacity-25 pb-3">
                    <h3 class="text-white m-0">
                        <i class="fa-solid fa-file-contract text-success me-2"></i>Terms &amp; Conditions
                    </h3>
                    <a href="<?= BASE_URL ?>/" class="btn btn-outline-light btn-sm">
                        <i class="fa-solid fa-house me-1"></i> Return Home
                    </a>
                </div>

                <div class="text-secondary leading-relaxed">
                    <?php if (!empty($terms_conditions)): ?>
                        <div class="policy-content text-white-50">
                            <?= nl2br(htmlspecialchars($terms_conditions)) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-file-signature text-muted fa-3x mb-3"></i>
                            <p class="text-secondary mb-0">The NVOTS Platform Terms &amp; Conditions are currently being updated by the Federal Registry administration. Please check back later.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-5 pt-4 border-top border-secondary border-opacity-10 text-center text-muted small">
                    Last updated: <?= date('F d, Y') ?> &bull; National Vehicle Ownership &amp; Traceability Registry
                </div>
            </div>
        </div>
    </div>
</div>
