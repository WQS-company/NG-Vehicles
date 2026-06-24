<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Commission;

class CommissionController extends Controller {
    protected $model;

    public function __construct() {
        Auth::requireRole(ROLE_SUPER_ADMIN);
        $this->model = new Commission();
    }

    // Commission board
    public function index() {
        $recipients = $this->model->allRecipients();
        $csrfToken = $this->generateCsrfToken();
        $this->render('admin/commissions', [
            'recipients' => $recipients,
            'csrfToken' => $csrfToken,
            'activePage' => 'commission'
        ]);
    }

    // Add recipient
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('commission');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token');
        }

        $data = [
            'user_id' => !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null,
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'bank_name' => trim($_POST['bank_name'] ?? ''),
            'bank_code' => trim($_POST['bank_code'] ?? ''),
            'account_number' => trim($_POST['account_number'] ?? ''),
            'account_name' => trim($_POST['account_name'] ?? ''),
            'percentage_share' => (float)($_POST['percentage_share'] ?? 0)
        ];

        $this->model->addRecipient($data);
        $this->redirect('commission');
    }

    // Calculate shares for a provided revenue amount (AJAX)
    public function calculate() {
        $revenue = (float)($_POST['revenue'] ?? 0);
        $allocations = $this->model->calculateShares($revenue);
        $this->json(['status' => true, 'allocations' => $allocations]);
    }

    // Create payout event and optionally execute payments
    public function payout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('commission');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token');
        }

        $revenue = (float)($_POST['revenue'] ?? 0);
        $title = trim($_POST['title'] ?? ('Commission Payout - ' . date('Y-m-d H:i:s')));
        $selected = $_POST['selected'] ?? []; // array of recipient ids

        $allocations = $this->model->calculateShares($revenue);

        // Filter allocations if selection provided
        if (!empty($selected) && is_array($selected)) {
            $allocations = array_filter($allocations, function($a) use ($selected) {
                return in_array($a['recipient']['id'], $selected);
            });
        }

        $processedBy = $_SESSION['user_id'] ?? 0;
        $payoutId = $this->model->createPayoutEvent($title, $revenue, $processedBy, $allocations);

        // If admin requested immediate payment
        if (!empty($_POST['execute']) && $_POST['execute'] == '1') {
            foreach ($this->model->getPayoutItems($payoutId) as $item) {
                // Get recipient
                $recipient = $this->model->findRecipient($item['recipient_id']);
                $amount = (float)$item['amount'];
                $res = $this->model->executeTransfer($recipient, $amount);
                if ($res['status']) {
                    // mark success
                    Database::getInstance()->update('commission_payout_items', [
                        'status' => 'SUCCESS',
                        'paystack_transfer_id' => $res['data']['id'] ?? $res['data']['transfer_code'] ?? null,
                        'response' => json_encode($res['data']),
                        'paid_at' => date('Y-m-d H:i:s')
                    ], 'id = :where_id', ['where_id' => $item['id']]);

                    // update recipient total_paid
                    Database::getInstance()->query('UPDATE commission_recipients SET total_paid = total_paid + :amt WHERE id = :id', ['amt' => $amount, 'id' => $recipient['id']]);
                } else {
                    Database::getInstance()->update('commission_payout_items', [
                        'status' => 'FAILED',
                        'response' => json_encode($res)
                    ], 'id = :where_id', ['where_id' => $item['id']]);
                }
            }
        }

        $this->redirect('commission/history/' . $payoutId);
    }

    public function history($payoutId = null) {
        // If id provided, show specific payout
        if ($payoutId) {
            $payout = $this->model->getPayoutById($payoutId);
            $items = $this->model->getPayoutItems($payoutId);
            $this->render('admin/commissions_history', ['payout' => $payout, 'items' => $items, 'activePage' => 'commission']);
            return;
        }

        $payouts = Database::getInstance()->fetchAll('SELECT p.*, u.email as processed_by_email FROM commission_payouts p LEFT JOIN users u ON u.id = p.processed_by ORDER BY p.created_at DESC');
        $this->render('admin/commissions_history', ['payouts' => $payouts, 'activePage' => 'commission']);
    }

    public function report() {
        // Basic summary report
        $summary = Database::getInstance()->fetchAll("SELECT r.name, r.email, r.percentage_share, r.total_paid FROM commission_recipients r ORDER BY r.name");
        $this->render('admin/commissions_report', ['summary' => $summary, 'activePage' => 'commission']);
    }

    // Export report as CSV
    public function exportReport() {
        $rows = Database::getInstance()->fetchAll("SELECT r.name, r.email, r.percentage_share, r.total_paid FROM commission_recipients r ORDER BY r.name");

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=commission_report_' . date('Ymd_His') . '.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Email', 'Percentage Share', 'Total Paid']);
        foreach ($rows as $r) {
            fputcsv($output, [$r['name'], $r['email'], $r['percentage_share'], $r['total_paid']]);
        }
        fclose($output);
        exit;
    }
}
