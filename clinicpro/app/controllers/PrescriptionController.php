<?php
// app/controllers/PrescriptionController.php

class PrescriptionController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('prescriptions','read');

        $search = sanitize($_GET['q'] ?? '');
        $page   = max(1, sanitize_int($_GET['page'] ?? 1));

        $where  = ['rx.clinic_id=:cid'];
        $params = [':cid' => clinic_id()];
        if ($search) {
            $where[] = '(p.name LIKE :s OR rx.prescription_no LIKE :s)';
            $params[':s'] = '%'.$search.'%';
        }
        $w   = implode(' AND ', $where);
        $cnt = $this->db->prepare("SELECT COUNT(*) FROM prescriptions rx JOIN patients p ON p.id=rx.patient_id WHERE $w");
        foreach ($params as $k=>$v) $cnt->bindValue($k,$v);
        $cnt->execute();
        $total = (int)$cnt->fetchColumn();
        $pages = paginate($total, $page);

        $stmt = $this->db->prepare("
            SELECT rx.*, p.name AS patient_name, p.patient_id, d.name AS doctor_name
            FROM prescriptions rx
            JOIN patients p ON p.id=rx.patient_id
            JOIN doctors  d ON d.id=rx.doctor_id
            WHERE $w ORDER BY rx.prescription_date DESC, rx.id DESC
            LIMIT :lim OFFSET :off
        ");
        foreach ($params as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->bindValue(':lim', $pages['per_page'], PDO::PARAM_INT);
        $stmt->bindValue(':off', $pages['offset'],   PDO::PARAM_INT);
        $stmt->execute();
        $prescriptions = $stmt->fetchAll();

        render('prescriptions/index', compact('prescriptions','pages','search','total'));
    }

    public function create(): void {
        AuthMiddleware::check();
        require_permission('prescriptions','create');

        $visitId   = sanitize_int($_GET['visit_id'] ?? 0);
        $patientId = sanitize_int($_GET['patient_id'] ?? 0);
        $doctorId  = sanitize_int($_GET['doctor_id'] ?? 0);

        $visit   = null;
        $patient = null;

        if ($visitId) {
            $s = $this->db->prepare("SELECT * FROM opd_visits WHERE id=? AND clinic_id=? LIMIT 1");
            $s->execute([$visitId, clinic_id()]);
            $visit = $s->fetch();
            if ($visit) { $patientId = $visit['patient_id']; $doctorId = $visit['doctor_id']; }
        }
        if ($patientId) {
            $s = $this->db->prepare("SELECT * FROM patients WHERE id=? AND clinic_id=? LIMIT 1");
            $s->execute([$patientId, clinic_id()]);
            $patient = $s->fetch();
        }

        $doctors = $this->db->prepare("SELECT id,name,specialization FROM doctors WHERE clinic_id=? AND status='active' ORDER BY name");
        $doctors->execute([clinic_id()]);
        $doctors = $doctors->fetchAll();

        $medicines = $this->db->prepare("SELECT id,name,type,unit FROM medicines WHERE clinic_id=? AND status='active' ORDER BY name LIMIT 200");
        $medicines->execute([clinic_id()]);
        $medicines = $medicines->fetchAll();

        render('prescriptions/create', compact('visit','patient','doctors','medicines','visitId','doctorId'));
    }

    public function store(): void {
        AuthMiddleware::check();
        require_permission('prescriptions','create');
        verify_csrf();

        $patientId = sanitize_int($_POST['patient_id']);
        $doctorId  = sanitize_int($_POST['doctor_id']);

        if (!$patientId || !$doctorId) {
            flash('error','Patient and doctor required.');
            redirect(APP_URL.'/prescriptions.php?action=create');
        }

        $next = next_sequence('prescriptions','prescription_no', clinic_id());
        $rxNo = generate_id('RX', $next);

        $stmt = $this->db->prepare("INSERT INTO prescriptions (clinic_id,prescription_no,visit_id,patient_id,doctor_id,prescription_date,diagnosis,notes,follow_up_date,status) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            clinic_id(), $rxNo,
            sanitize_int($_POST['visit_id']) ?: null,
            $patientId, $doctorId,
            date('Y-m-d'),
            sanitize($_POST['diagnosis'] ?? ''),
            sanitize($_POST['notes'] ?? ''),
            $_POST['follow_up_date'] ?: null,
            'active'
        ]);
        $rxId = (int)$this->db->lastInsertId();

        // Items
        $medicines  = $_POST['medicine_name'] ?? [];
        $iStmt = $this->db->prepare("INSERT INTO prescription_items (prescription_id,medicine_id,medicine_name,dosage,frequency,duration,route,instructions,quantity,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)");
        foreach ($medicines as $i => $medName) {
            if (empty(trim($medName))) continue;
            $iStmt->execute([
                $rxId,
                sanitize_int($_POST['medicine_id'][$i] ?? 0) ?: null,
                sanitize($medName),
                sanitize($_POST['dosage'][$i] ?? ''),
                sanitize($_POST['frequency'][$i] ?? ''),
                sanitize($_POST['duration'][$i] ?? ''),
                sanitize($_POST['route'][$i] ?? 'oral'),
                sanitize($_POST['instructions'][$i] ?? ''),
                sanitize_int($_POST['quantity'][$i] ?? 1),
                $i,
            ]);
        }

        audit('prescriptions','create',$rxId);
        flash('success','Prescription created.');
        redirect(APP_URL.'/prescriptions.php?action=show&id='.$rxId);
    }

    public function show(int $id): void {
        AuthMiddleware::check();
        require_permission('prescriptions','read');

        $stmt = $this->db->prepare("
            SELECT rx.*, p.name AS patient_name, p.patient_id, p.dob, p.gender, p.phone AS patient_phone,
                   d.name AS doctor_name, d.specialization, d.qualification, d.registration_no AS doctor_reg
            FROM prescriptions rx
            JOIN patients p ON p.id=rx.patient_id
            JOIN doctors  d ON d.id=rx.doctor_id
            WHERE rx.id=? AND rx.clinic_id=? LIMIT 1
        ");
        $stmt->execute([$id, clinic_id()]);
        $rx = $stmt->fetch();
        if (!$rx) { flash('error','Prescription not found.'); redirect(APP_URL.'/prescriptions.php'); }

        $items = $this->db->prepare("SELECT * FROM prescription_items WHERE prescription_id=? ORDER BY sort_order");
        $items->execute([$id]);
        $rx['items'] = $items->fetchAll();

        $db    = $this->db;
        $clinic = $db->prepare("SELECT * FROM clinics WHERE id=? LIMIT 1");
        $clinic->execute([clinic_id()]);
        $clinic = $clinic->fetch();

        render('prescriptions/show', compact('rx','clinic'));
    }
}
