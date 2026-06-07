<?php
// app/controllers/AppointmentController.php

class AppointmentController {

    private AppointmentRepository $repo;

    public function __construct() {
        $this->repo = new AppointmentRepository();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('appointments','read');

        $filters = [
            'date'      => sanitize($_GET['date'] ?? date('Y-m-d')),
            'doctor_id' => sanitize_int($_GET['doctor'] ?? 0) ?: null,
            'status'    => sanitize($_GET['status'] ?? ''),
            'search'    => sanitize($_GET['q'] ?? ''),
        ];
        $page  = max(1, sanitize_int($_GET['page'] ?? 1));
        $total = $this->repo->count(clinic_id(), $filters);
        $pages = paginate($total, $page);
        $appointments = $this->repo->getAll(clinic_id(), $filters, $pages['per_page'], $pages['offset']);

        $db = Database::getInstance();
        $doctors = $db->prepare("SELECT id,name,specialization FROM doctors WHERE clinic_id=? AND status='active' ORDER BY name");
        $doctors->execute([clinic_id()]);
        $doctors = $doctors->fetchAll();

        $stats = $this->repo->todayStats(clinic_id());
        render('appointments/index', compact('appointments','pages','filters','doctors','stats','total'));
    }

    public function create(): void {
        AuthMiddleware::check();
        require_permission('appointments','create');

        $db = Database::getInstance();
        $doctors = $db->prepare("SELECT id,name,specialization,consultation_fee FROM doctors WHERE clinic_id=? AND status='active' ORDER BY name");
        $doctors->execute([clinic_id()]);
        $doctors = $doctors->fetchAll();
        render('appointments/create', compact('doctors'));
    }

    public function store(): void {
        AuthMiddleware::check();
        require_permission('appointments','create');
        verify_csrf();

        $data = [
            'clinic_id'        => clinic_id(),
            'patient_id'       => sanitize_int($_POST['patient_id']),
            'doctor_id'        => sanitize_int($_POST['doctor_id']),
            'appointment_date' => sanitize($_POST['appointment_date']),
            'appointment_time' => sanitize($_POST['appointment_time']),
            'type'             => sanitize($_POST['type'] ?? 'opd'),
            'reason'           => sanitize($_POST['reason'] ?? ''),
            'status'           => 'scheduled',
            'booked_by'        => auth()['id'],
        ];

        if (!$data['patient_id'] || !$data['doctor_id'] || !$data['appointment_date']) {
            flash('error','Required fields missing.');
            redirect(APP_URL.'/appointments.php?action=create');
        }

        $id = $this->repo->create($data);
        audit('appointments','create',$id,[],$data);
        flash('success','Appointment booked successfully.');
        redirect(APP_URL.'/appointments.php');
    }

    public function updateStatus(): void {
        AuthMiddleware::check();
        verify_csrf();

        $id     = sanitize_int($_POST['id']);
        $status = sanitize($_POST['status']);
        $reason = sanitize($_POST['reason'] ?? '');

        $allowed = ['scheduled','confirmed','waiting','in_progress','completed','cancelled','no_show'];
        if (!in_array($status, $allowed, true)) {
            json_response(['error'=>'Invalid status'],400);
        }

        $this->repo->updateStatus($id, clinic_id(), $status, $reason);
        audit('appointments','status_change',$id,[],['status'=>$status]);
        json_response(['success'=>true,'status'=>$status]);
    }

    public function slots(): void {
        AuthMiddleware::check();
        $doctorId = sanitize_int($_GET['doctor_id']);
        $date     = sanitize($_GET['date'] ?? date('Y-m-d'));
        $slots    = $this->repo->getAvailableSlots($doctorId, $date);
        json_response($slots);
    }
}
