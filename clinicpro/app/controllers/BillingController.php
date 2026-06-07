<?php
// app/controllers/BillingController.php

class BillingController {

    private BillingRepository $repo;

    public function __construct() {
        $this->repo = new BillingRepository();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('billing','read');

        $filters = [
            'search' => sanitize($_GET['q'] ?? ''),
            'status' => sanitize($_GET['status'] ?? ''),
            'from'   => sanitize($_GET['from'] ?? date('Y-m-01')),
            'to'     => sanitize($_GET['to'] ?? date('Y-m-d')),
        ];
        $page   = max(1, sanitize_int($_GET['page'] ?? 1));
        $total  = 0;
        $invoices = $this->repo->getAll(clinic_id(), $filters);
        $stats  = $this->repo->revenueStats(clinic_id(), $filters['from'], $filters['to']);
        render('billing/index', compact('invoices','filters','stats','total'));
    }

    public function create(): void {
        AuthMiddleware::check();
        require_permission('billing','create');
        $db = Database::getInstance();
        $doctors = $db->prepare("SELECT id,name FROM doctors WHERE clinic_id=? AND status='active'");
        $doctors->execute([clinic_id()]);
        $doctors = $doctors->fetchAll();
        render('billing/create', compact('doctors'));
    }

    public function store(): void {
        AuthMiddleware::check();
        require_permission('billing','create');
        verify_csrf();

        $invoice = [
            'clinic_id'      => clinic_id(),
            'patient_id'     => sanitize_int($_POST['patient_id']),
            'doctor_id'      => sanitize_int($_POST['doctor_id']) ?: null,
            'discount_type'  => sanitize($_POST['discount_type'] ?? 'fixed'),
            'discount_value' => sanitize_float($_POST['discount_value'] ?? 0),
            'notes'          => sanitize($_POST['notes'] ?? ''),
            'created_by'     => auth()['id'],
        ];

        $items = [];
        $descriptions = $_POST['description'] ?? [];
        foreach ($descriptions as $i => $desc) {
            if (empty(trim($desc))) continue;
            $items[] = [
                'item_type'   => sanitize($_POST['item_type'][$i] ?? 'consultation'),
                'description' => sanitize($desc),
                'quantity'    => sanitize_float($_POST['quantity'][$i] ?? 1),
                'unit_price'  => sanitize_float($_POST['unit_price'][$i] ?? 0),
                'discount'    => sanitize_float($_POST['item_discount'][$i] ?? 0),
                'gst_percent' => sanitize_float($_POST['gst_percent'][$i] ?? 0),
                'gst_amount'  => 0,
                'total'       => 0,
            ];
        }

        if (empty($items)) { flash('error','Add at least one item.'); redirect(APP_URL.'/billing.php?action=create'); }

        $id = $this->repo->create($invoice, $items);
        audit('billing','create',$id);
        flash('success','Invoice created.');
        redirect(APP_URL.'/billing.php?action=show&id='.$id);
    }

    public function show(int $id): void {
        AuthMiddleware::check();
        require_permission('billing','read');
        $invoice = $this->repo->findById($id, clinic_id());
        if (!$invoice) { flash('error','Invoice not found.'); redirect(APP_URL.'/billing.php'); }

        $db = Database::getInstance();
        $clinic = $db->prepare("SELECT * FROM clinics WHERE id=? LIMIT 1");
        $clinic->execute([clinic_id()]);
        $clinic = $clinic->fetch();
        render('billing/show', compact('invoice','clinic'));
    }

    public function payment(): void {
        AuthMiddleware::check();
        require_permission('billing','create');
        verify_csrf();

        $invoiceId = sanitize_int($_POST['invoice_id']);
        $amount    = sanitize_float($_POST['amount']);
        $method    = sanitize($_POST['method']);
        $ref       = sanitize($_POST['reference_no'] ?? '');
        $date      = sanitize($_POST['payment_date'] ?? date('Y-m-d'));

        if ($amount <= 0) { json_response(['error'=>'Invalid amount'],400); }
        $ok = $this->repo->recordPayment($invoiceId, clinic_id(), $amount, $method, $ref, $date);
        audit('billing','payment',$invoiceId,[],['amount'=>$amount,'method'=>$method]);
        json_response(['success'=>$ok]);
    }
}
