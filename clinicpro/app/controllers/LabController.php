<?php
// app/controllers/LabController.php

class LabController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('lab','read');

        $search = sanitize($_GET['q'] ?? '');
        $status = sanitize($_GET['status'] ?? '');
        $page   = max(1, sanitize_int($_GET['page'] ?? 1));

        $where  = ['lo.clinic_id=:cid'];
        $params = [':cid' => clinic_id()];
        if ($search) { $where[] = '(p.name LIKE :s OR lo.order_no LIKE :s)'; $params[':s']='%'.$search.'%'; }
        if ($status) { $where[] = 'lo.status=:st'; $params[':st']=$status; }

        $w   = implode(' AND ', $where);
        $cnt = $this->db->prepare("SELECT COUNT(*) FROM lab_orders lo JOIN patients p ON p.id=lo.patient_id WHERE $w");
        foreach ($params as $k=>$v) $cnt->bindValue($k,$v);
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();
        $pages = paginate($total, $page);

        $stmt = $this->db->prepare("
            SELECT lo.*, p.name AS patient_name, p.patient_id, d.name AS doctor_name,
                   COUNT(loi.id) AS test_count, SUM(loi.status='completed') AS done_count
            FROM lab_orders lo
            JOIN patients p ON p.id=lo.patient_id
            JOIN doctors  d ON d.id=lo.doctor_id
            LEFT JOIN lab_order_items loi ON loi.order_id=lo.id
            WHERE $w GROUP BY lo.id ORDER BY lo.order_date DESC, lo.id DESC
            LIMIT :lim OFFSET :off
        ");
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->bindValue(':lim', $pages['per_page'], PDO::PARAM_INT);
        $stmt->bindValue(':off', $pages['offset'],   PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll();

        render('lab/index', compact('orders','pages','search','status','total'));
    }

    public function create(): void {
        AuthMiddleware::check();
        require_permission('lab','create');

        $patientId = sanitize_int($_GET['patient_id'] ?? 0);
        $visitId   = sanitize_int($_GET['visit_id'] ?? 0);
        $doctorId  = sanitize_int($_GET['doctor_id'] ?? 0);

        $patient = null;
        if ($patientId) {
            $s = $this->db->prepare("SELECT * FROM patients WHERE id=? AND clinic_id=? LIMIT 1");
            $s->execute([$patientId, clinic_id()]);
            $patient = $s->fetch();
        }

        $doctors = $this->db->prepare("SELECT id,name FROM doctors WHERE clinic_id=? AND status='active' ORDER BY name");
        $doctors->execute([clinic_id()]);
        $doctors = $doctors->fetchAll();

        $tests = $this->db->prepare("SELECT * FROM lab_tests_master WHERE clinic_id=? AND status='active' ORDER BY category,name");
        $tests->execute([clinic_id()]);
        $tests = $tests->fetchAll();

        render('lab/create', compact('patient','doctors','tests','visitId','doctorId'));
    }

    public function store(): void {
        AuthMiddleware::check();
        require_permission('lab','create');
        verify_csrf();

        $patientId = sanitize_int($_POST['patient_id']);
        $doctorId  = sanitize_int($_POST['doctor_id']);
        $testIds   = array_map('intval', $_POST['test_ids'] ?? []);

        if (!$patientId || !$doctorId || empty($testIds)) {
            flash('error','Patient, doctor and tests required.');
            redirect(APP_URL.'/lab.php?action=create');
        }

        // Calc total
        $phs   = implode(',', array_fill(0, count($testIds), '?'));
        $tests = $this->db->prepare("SELECT * FROM lab_tests_master WHERE id IN($phs) AND clinic_id=?");
        $tests->execute([...$testIds, clinic_id()]);
        $tests = $tests->fetchAll();
        $total = array_sum(array_column($tests,'price'));

        $next  = next_sequence('lab_orders','order_no', clinic_id());
        $orderNo = generate_id('LAB', $next);

        $stmt = $this->db->prepare("INSERT INTO lab_orders (clinic_id,order_no,patient_id,doctor_id,visit_id,order_date,status,total_amount) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            clinic_id(), $orderNo, $patientId, $doctorId,
            sanitize_int($_POST['visit_id']) ?: null,
            date('Y-m-d'), 'ordered', $total
        ]);
        $orderId = (int)$this->db->lastInsertId();

        $iStmt = $this->db->prepare("INSERT INTO lab_order_items (order_id,test_id,price) VALUES (?,?,?)");
        foreach ($tests as $t) $iStmt->execute([$orderId, $t['id'], $t['price']]);

        audit('lab','create',$orderId);
        flash('success','Lab order created.');
        redirect(APP_URL.'/lab.php?action=show&id='.$orderId);
    }

    public function show(int $id): void {
        AuthMiddleware::check();
        require_permission('lab','read');

        $stmt = $this->db->prepare("
            SELECT lo.*, p.name AS patient_name, p.patient_id, p.phone AS patient_phone,
                   p.dob, p.gender, d.name AS doctor_name
            FROM lab_orders lo
            JOIN patients p ON p.id=lo.patient_id
            JOIN doctors  d ON d.id=lo.doctor_id
            WHERE lo.id=? AND lo.clinic_id=? LIMIT 1
        ");
        $stmt->execute([$id, clinic_id()]);
        $order = $stmt->fetch();
        if (!$order) { flash('error','Lab order not found.'); redirect(APP_URL.'/lab.php'); }

        $items = $this->db->prepare("
            SELECT loi.*, lt.name AS test_name, lt.category, lt.unit, lt.normal_range
            FROM lab_order_items loi
            JOIN lab_tests_master lt ON lt.id=loi.test_id
            WHERE loi.order_id=?
        ");
        $items->execute([$id]);
        $order['items'] = $items->fetchAll();

        render('lab/show', compact('order'));
    }

    public function updateStatus(): void {
        AuthMiddleware::check();
        verify_csrf();
        $id     = sanitize_int($_POST['id']);
        $status = sanitize($_POST['status']);
        $this->db->prepare("UPDATE lab_orders SET status=? WHERE id=? AND clinic_id=?")
            ->execute([$status, $id, clinic_id()]);
        json_response(['success'=>true]);
    }

    public function saveResults(): void {
        AuthMiddleware::check();
        require_permission('lab','results');
        verify_csrf();

        $orderId = sanitize_int($_POST['order_id']);
        $itemIds = $_POST['item_id'] ?? [];
        $results = $_POST['result'] ?? [];

        $stmt = $this->db->prepare("UPDATE lab_order_items SET result=?, status='completed', result_date=NOW() WHERE id=? AND order_id=?");
        foreach ($itemIds as $i => $itemId) {
            $stmt->execute([sanitize($results[$i] ?? ''), (int)$itemId, $orderId]);
        }

        // Check if all done
        $pending = $this->db->prepare("SELECT COUNT(*) FROM lab_order_items WHERE order_id=? AND status='pending'");
        $pending->execute([$orderId]);
        if ((int)$pending->fetchColumn() === 0) {
            $this->db->prepare("UPDATE lab_orders SET status='completed' WHERE id=?")->execute([$orderId]);
        } else {
            $this->db->prepare("UPDATE lab_orders SET status='processing' WHERE id=?")->execute([$orderId]);
        }

        flash('success','Results saved.');
        redirect(APP_URL.'/lab.php?action=show&id='.$orderId);
    }

    public function tests(): void {
        AuthMiddleware::check();
        $stmt = $this->db->prepare("SELECT * FROM lab_tests_master WHERE clinic_id=? AND status='active' ORDER BY category,name");
        $stmt->execute([clinic_id()]);
        $tests = $stmt->fetchAll();
        render('lab/tests', compact('tests'));
    }
}
