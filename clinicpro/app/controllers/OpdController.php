<?php
// app/controllers/OpdController.php

class OpdController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('opd','read');

        $filters = [
            'search'    => sanitize($_GET['q'] ?? ''),
            'date'      => sanitize($_GET['date'] ?? ''),
            'doctor_id' => sanitize_int($_GET['doctor'] ?? 0) ?: null,
        ];
        $page   = max(1, sanitize_int($_GET['page'] ?? 1));
        $where  = ['v.clinic_id=:cid'];
        $params = [':cid' => clinic_id()];

        if (!empty($filters['search'])) {
            $where[] = '(p.name LIKE :s OR p.patient_id LIKE :s)';
            $params[':s'] = '%'.$filters['search'].'%';
        }
        if (!empty($filters['date'])) {
            $where[] = 'v.visit_date=:dt';
            $params[':dt'] = $filters['date'];
        }
        if (!empty($filters['doctor_id'])) {
            $where[] = 'v.doctor_id=:did';
            $params[':did'] = $filters['doctor_id'];
        }

        $w   = implode(' AND ', $where);
        $cnt = $this->db->prepare("SELECT COUNT(*) FROM opd_visits v JOIN patients p ON p.id=v.patient_id WHERE $w");
        foreach ($params as $k=>$v) $cnt->bindValue($k,$v);
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();
        $pages = paginate($total, $page);

        $stmt = $this->db->prepare("
            SELECT v.*, p.name AS patient_name, p.patient_id, d.name AS doctor_name
            FROM opd_visits v
            JOIN patients p ON p.id=v.patient_id
            JOIN doctors  d ON d.id=v.doctor_id
            WHERE $w ORDER BY v.visit_date DESC, v.id DESC
            LIMIT :lim OFFSET :off
        ");
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->bindValue(':lim', $pages['per_page'], PDO::PARAM_INT);
        $stmt->bindValue(':off', $pages['offset'],   PDO::PARAM_INT);
        $stmt->execute();
        $visits = $stmt->fetchAll();

        $doctors = $this->db->prepare("SELECT id,name FROM doctors WHERE clinic_id=? AND status='active' ORDER BY name");
        $doctors->execute([clinic_id()]);
        $doctors = $doctors->fetchAll();

        render('opd/index', compact('visits','pages','filters','doctors','total'));
    }

    public function create(): void {
        AuthMiddleware::check();
        require_permission('opd','create');

        $patientId    = sanitize_int($_GET['patient_id'] ?? 0);
        $appointmentId = sanitize_int($_GET['appointment_id'] ?? 0);
        $doctorId     = sanitize_int($_GET['doctor_id'] ?? 0);

        $patient = null;
        if ($patientId) {
            $s = $this->db->prepare("SELECT * FROM patients WHERE id=? AND clinic_id=? LIMIT 1");
            $s->execute([$patientId, clinic_id()]);
            $patient = $s->fetch();
        }

        $doctors = $this->db->prepare("SELECT id,name,specialization FROM doctors WHERE clinic_id=? AND status='active' ORDER BY name");
        $doctors->execute([clinic_id()]);
        $doctors = $doctors->fetchAll();

        render('opd/create', compact('patient','doctors','appointmentId','doctorId'));
    }

    public function store(): void {
        AuthMiddleware::check();
        require_permission('opd','create');
        verify_csrf();

        $data = [
            'clinic_id'      => clinic_id(),
            'appointment_id' => sanitize_int($_POST['appointment_id']) ?: null,
            'patient_id'     => sanitize_int($_POST['patient_id']),
            'doctor_id'      => sanitize_int($_POST['doctor_id']),
            'visit_date'     => sanitize($_POST['visit_date'] ?? date('Y-m-d')),
            'chief_complaint'=> sanitize($_POST['chief_complaint'] ?? ''),
            'symptoms'       => sanitize($_POST['symptoms'] ?? ''),
            'examination'    => sanitize($_POST['examination'] ?? ''),
            'diagnosis'      => sanitize($_POST['diagnosis'] ?? ''),
            'treatment_plan' => sanitize($_POST['treatment_plan'] ?? ''),
            'notes'          => sanitize($_POST['notes'] ?? ''),
            'follow_up_date' => $_POST['follow_up_date'] ?: null,
            'follow_up_notes'=> sanitize($_POST['follow_up_notes'] ?? ''),
            'vitals_bp'      => sanitize($_POST['vitals_bp'] ?? ''),
            'vitals_pulse'   => sanitize_int($_POST['vitals_pulse'] ?? 0) ?: null,
            'vitals_temp'    => sanitize_float($_POST['vitals_temp'] ?? 0) ?: null,
            'vitals_weight'  => sanitize_float($_POST['vitals_weight'] ?? 0) ?: null,
            'vitals_height'  => sanitize_float($_POST['vitals_height'] ?? 0) ?: null,
            'vitals_spo2'    => sanitize_int($_POST['vitals_spo2'] ?? 0) ?: null,
            'created_by'     => auth()['id'],
        ];

        if (!$data['patient_id'] || !$data['doctor_id']) {
            flash('error', 'Patient and doctor are required.');
            redirect(APP_URL.'/opd.php?action=create');
        }

        $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
        $vals = implode(',', array_map(fn($k)=>":$k", array_keys($data)));
        $stmt = $this->db->prepare("INSERT INTO opd_visits ($cols) VALUES ($vals)");
        $stmt->execute($data);
        $visitId = (int)$this->db->lastInsertId();

        // Mark appointment completed if linked
        if ($data['appointment_id']) {
            $this->db->prepare("UPDATE appointments SET status='completed', consulted_at=NOW() WHERE id=? AND clinic_id=?")
                ->execute([$data['appointment_id'], clinic_id()]);
        }

        audit('opd','create',$visitId,[],$data);
        flash('success', 'OPD visit recorded successfully.');
        redirect(APP_URL.'/opd.php?action=show&id='.$visitId);
    }

    public function show(int $id): void {
        AuthMiddleware::check();
        require_permission('opd','read');

        $stmt = $this->db->prepare("
            SELECT v.*, p.name AS patient_name, p.patient_id, p.phone AS patient_phone,
                   p.dob, p.blood_group, p.gender,
                   d.name AS doctor_name, d.specialization, d.qualification
            FROM opd_visits v
            JOIN patients p ON p.id=v.patient_id
            JOIN doctors  d ON d.id=v.doctor_id
            WHERE v.id=? AND v.clinic_id=? LIMIT 1
        ");
        $stmt->execute([$id, clinic_id()]);
        $visit = $stmt->fetch();

        if (!$visit) { flash('error','Visit not found.'); redirect(APP_URL.'/opd.php'); }

        // Get prescriptions for this visit
        $rxStmt = $this->db->prepare("SELECT * FROM prescriptions WHERE visit_id=? AND clinic_id=?");
        $rxStmt->execute([$id, clinic_id()]);
        $prescriptions = $rxStmt->fetchAll();

        // Get lab orders for this visit
        $labStmt = $this->db->prepare("SELECT lo.*, COUNT(loi.id) AS test_count FROM lab_orders lo LEFT JOIN lab_order_items loi ON loi.order_id=lo.id WHERE lo.visit_id=? AND lo.clinic_id=? GROUP BY lo.id");
        $labStmt->execute([$id, clinic_id()]);
        $labOrders = $labStmt->fetchAll();

        render('opd/show', compact('visit','prescriptions','labOrders'));
    }
}
