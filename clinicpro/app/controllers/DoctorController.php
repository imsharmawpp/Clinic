<?php
// app/controllers/DoctorController.php

class DoctorController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('doctors','read');

        $stmt = $this->db->prepare("
            SELECT d.*,
                   COUNT(DISTINCT a.id)   AS total_appointments,
                   COUNT(DISTINCT v.id)   AS total_visits
            FROM doctors d
            LEFT JOIN appointments a ON a.doctor_id=d.id AND a.clinic_id=d.clinic_id
            LEFT JOIN opd_visits   v ON v.doctor_id=d.id AND v.clinic_id=d.clinic_id
            WHERE d.clinic_id=?
            GROUP BY d.id ORDER BY d.name
        ");
        $stmt->execute([clinic_id()]);
        $doctors = $stmt->fetchAll();

        render('doctors/index', compact('doctors'));
    }

    public function create(): void {
        AuthMiddleware::check();
        require_permission('doctors','create');
        render('doctors/create', ['doctor' => null]);
    }

    public function store(): void {
        AuthMiddleware::check();
        require_permission('doctors','create');
        verify_csrf();

        $data = $this->validate($_POST);
        $data['clinic_id'] = clinic_id();

        $cols = implode(',', array_map(fn($k)=>"`$k`", array_keys($data)));
        $vals = implode(',', array_map(fn($k)=>":$k", array_keys($data)));
        $stmt = $this->db->prepare("INSERT INTO doctors ($cols) VALUES ($vals)");
        $stmt->execute($data);
        $id = (int)$this->db->lastInsertId();

        // Schedules
        $this->saveSchedules($id, $_POST['schedule'] ?? []);

        audit('doctors','create',$id);
        flash('success','Doctor added successfully.');
        redirect(APP_URL.'/doctors.php?action=show&id='.$id);
    }

    public function show(int $id): void {
        AuthMiddleware::check();
        require_permission('doctors','read');

        $doctor = $this->findOrFail($id);

        $schedules = $this->db->prepare("SELECT * FROM doctor_schedules WHERE doctor_id=? AND is_active=1 ORDER BY day_of_week");
        $schedules->execute([$id]);
        $schedules = $schedules->fetchAll();

        // Recent appointments
        $appts = $this->db->prepare("
            SELECT a.*, p.name AS patient_name FROM appointments a
            JOIN patients p ON p.id=a.patient_id
            WHERE a.doctor_id=? AND a.clinic_id=?
            ORDER BY a.appointment_date DESC LIMIT 10
        ");
        $appts->execute([$id, clinic_id()]);
        $appointments = $appts->fetchAll();

        // Revenue stats
        $rev = $this->db->prepare("
            SELECT COALESCE(SUM(py.amount),0) AS revenue, COUNT(DISTINCT inv.id) AS invoices
            FROM invoices inv
            LEFT JOIN payments py ON py.invoice_id=inv.id
            WHERE inv.doctor_id=? AND inv.clinic_id=?
        ");
        $rev->execute([$id, clinic_id()]);
        $stats = $rev->fetch();

        render('doctors/show', compact('doctor','schedules','appointments','stats'));
    }

    public function edit(int $id): void {
        AuthMiddleware::check();
        require_permission('doctors','update');
        $doctor = $this->findOrFail($id);
        $schedules = $this->db->prepare("SELECT * FROM doctor_schedules WHERE doctor_id=? ORDER BY day_of_week");
        $schedules->execute([$id]);
        $existingSchedules = $schedules->fetchAll();
        render('doctors/create', compact('doctor','existingSchedules'));
    }

    public function update(int $id): void {
        AuthMiddleware::check();
        require_permission('doctors','update');
        verify_csrf();

        $data = $this->validate($_POST);
        $sets = implode(',', array_map(fn($k)=>"`$k`=:$k", array_keys($data)));
        $data['id'] = $id;
        $data['cid'] = clinic_id();
        $this->db->prepare("UPDATE doctors SET $sets WHERE id=:id AND clinic_id=:cid")->execute($data);

        $this->db->prepare("DELETE FROM doctor_schedules WHERE doctor_id=?")->execute([$id]);
        $this->saveSchedules($id, $_POST['schedule'] ?? []);

        audit('doctors','update',$id);
        flash('success','Doctor updated.');
        redirect(APP_URL.'/doctors.php?action=show&id='.$id);
    }

    private function validate(array $post): array {
        return array_filter([
            'name'             => sanitize($post['name'] ?? ''),
            'specialization'   => sanitize($post['specialization'] ?? '') ?: null,
            'qualification'    => sanitize($post['qualification'] ?? '') ?: null,
            'registration_no'  => sanitize($post['registration_no'] ?? '') ?: null,
            'email'            => sanitize($post['email'] ?? '') ?: null,
            'phone'            => sanitize($post['phone'] ?? '') ?: null,
            'experience_years' => sanitize_int($post['experience_years'] ?? 0),
            'consultation_fee' => sanitize_float($post['consultation_fee'] ?? 0),
            'bio'              => sanitize($post['bio'] ?? '') ?: null,
            'status'           => in_array($post['status']??'',['active','inactive','on_leave']) ? $post['status'] : 'active',
        ], fn($v) => $v !== null);
    }

    private function saveSchedules(int $doctorId, array $schedule): void {
        $stmt = $this->db->prepare("INSERT INTO doctor_schedules (doctor_id,day_of_week,start_time,end_time,slot_mins,max_slots,is_active) VALUES (?,?,?,?,?,?,1)");
        foreach ($schedule as $dow => $s) {
            if (empty($s['enabled'])) continue;
            $stmt->execute([
                $doctorId,
                (int)$dow,
                sanitize($s['start'] ?? '09:00'),
                sanitize($s['end'] ?? '17:00'),
                sanitize_int($s['slot'] ?? 15),
                sanitize_int($s['max'] ?? 20),
            ]);
        }
    }

    private function findOrFail(int $id): array {
        $stmt = $this->db->prepare("SELECT * FROM doctors WHERE id=? AND clinic_id=? LIMIT 1");
        $stmt->execute([$id, clinic_id()]);
        $d = $stmt->fetch();
        if (!$d) { flash('error','Doctor not found.'); redirect(APP_URL.'/doctors.php'); }
        return $d;
    }
}
