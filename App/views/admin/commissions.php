<?php
// Admin Commissions Board
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="text-white">Commission Board</h4>
    <div>
        <a href="<?= BASE_URL ?>/commission/history" class="btn btn-sm" style="margin-right:8px;">History</a>
        <a href="<?= BASE_URL ?>/commission/report" class="btn btn-sm">Report</a>
    </div>
</div>

<div class="mb-4">
    <form method="POST" action="<?= BASE_URL ?>/commission/add">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <div class="row g-2">
            <div class="col-md-3"><input type="text" name="name" placeholder="Name" class="form-control" required></div>
            <div class="col-md-2"><input type="email" name="email" placeholder="Email" class="form-control"></div>
            <div class="col-md-2">
                <select name="bank_name" class="form-control">
                    <option value="">— Select Bank —</option>
                    <?php $banks = require BASE_PATH . '/config/paystack_banks.php'; foreach ($banks as $code => $label): ?>
                        <option value="<?= htmlspecialchars($label) ?>" data-code="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="bank_code" class="form-control" id="bankCodeSelect">
                    <option value="">— Bank Code —</option>
                    <?php foreach ($banks as $code => $label): ?>
                        <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($code) ?> - <?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input type="text" name="account_number" placeholder="Account #" class="form-control"></div>
            <div class="col-md-1"><input type="number" step="0.01" min="0" max="100" name="percentage_share" placeholder="%" class="form-control" required></div>
        </div>
        <div class="mt-2">
            <button class="btn btn-success btn-sm">Add Recipient</button>
        </div>
    </form>
</div>

<div class="card p-3 mb-3">
    <div class="d-flex gap-2 align-items-center mb-2">
        <label class="mb-0">Revenue Amount:</label>
        <input type="number" id="revenueAmount" class="form-control form-control-sm" style="width:160px;" placeholder="0.00">
        <button id="btnCalculate" class="btn btn-primary btn-sm">Calculate</button>
        <button id="btnPaySelected" class="btn btn-success btn-sm">Pay Selected</button>
        <button id="btnPayAll" class="btn btn-success btn-sm">Pay All</button>
    </div>

    <form id="payoutForm" method="POST" action="<?= BASE_URL ?>/commission/payout">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <input type="hidden" name="revenue" id="formRevenue" value="0">
        <input type="hidden" name="execute" id="formExecute" value="0">
        <input type="hidden" name="title" value="Manual Commission Payout">

        <table class="table table-striped table-dark">
            <thead>
                <tr>
                    <th><input type="checkbox" id="chkAll"></th>
                    <th>Name</th>
                    <th>Bank</th>
                    <th>Account</th>
                    <th>% Share</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody id="recipientsTable">
                <?php foreach ($recipients as $r): ?>
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="<?= $r['id'] ?>"></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><?= htmlspecialchars($r['bank_name']) ?> (<?= htmlspecialchars($r['bank_code']) ?>)</td>
                        <td><?= htmlspecialchars($r['account_number']) ?> / <?= htmlspecialchars($r['account_name']) ?></td>
                        <td><?= htmlspecialchars($r['percentage_share']) ?>%</td>
                        <td class="amount-cell">—</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>

<script>
document.getElementById('btnCalculate').addEventListener('click', function(e){
    e.preventDefault();
    const rev = parseFloat(document.getElementById('revenueAmount').value || 0);
    document.getElementById('formRevenue').value = rev;
    fetch('<?= BASE_URL ?>/commission/calculate', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'revenue=' + encodeURIComponent(rev)
    }).then(r=>r.json()).then(resp=>{
        if(resp.status){
            const rows = document.querySelectorAll('#recipientsTable tr');
            resp.allocations.forEach((a, idx)=>{
                const amount = a.amount.toFixed(2);
                const row = rows[idx];
                if(row) row.querySelector('.amount-cell').innerText = amount;
            });
        }
    });
});

document.getElementById('btnPaySelected').addEventListener('click', function(e){
    e.preventDefault();
    document.getElementById('formExecute').value = '1';
    document.getElementById('payoutForm').submit();
});

document.getElementById('btnPayAll').addEventListener('click', function(e){
    e.preventDefault();
    // Check all boxes
    document.querySelectorAll('#recipientsTable input[type=checkbox]').forEach(cb=>cb.checked = true);
    document.getElementById('formExecute').value = '1';
    document.getElementById('payoutForm').submit();
});

document.getElementById('chkAll').addEventListener('change', function(){
    const v = this.checked;
    document.querySelectorAll('#recipientsTable input[type=checkbox]').forEach(cb=>cb.checked = v);
});
</script>
