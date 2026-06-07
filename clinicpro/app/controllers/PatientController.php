<?php
// app/controllers/PatientController.php

class PatientController {

    private PatientRepository $repo;

    public function __construct() {
        $this->repo = new PatientRepository();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('patients','read');

        $filters = [
            'search' => sanitize($_GET['q'] ?? ''),
            'gender' => sanitize($_GET['gender'] ?? ''),
            'status' => sanitize($_GET['status'] ?? ''),
        ];
        $page  = max(1, sanitize_int($_GET['page'] ?? 1));
        $total = $this->repo->count(clinic_id(), $filters);
        $pages = paginate($total, $page);
        $patients = $this->repo->getAll(clinic_id(), $filters, $pages['per_page'], $pages['offset']);

        render('patients/index', compact('patients','pages','filters','total'));
    }

    public function create(): void {
        AuthMiddleware::check();
        require_permission('patients','create');
        render('patients/create', ['title' => 'New Patient']);
    }

    public function store(): void {
        AuthMiddleware::check();
        require_permission('patients','create');
        verify_csrf();

        $data = $this->validate($_POST);
        if (!$data) {
            flash('error', 'Validation failed. Check required fields.');
            redirect(APP_URL . '/patients.php?action=create');
        }
        $data['clinic_id']    = clinic_id();
        $data['registered_by'] = auth()['id'];

        $id = $this->repo->create($data);
        audit('patients','create',$id,[],$data);
        flash('success', 'Patient registered successfully.');
        redirect(APP_URL . '/patients.php?action=show&id=' . $id);
    }

    public function show(int $id): void {
        AuthMiddleware::check();
        require_permission('patients','read');

        $patient = $this->repo->findById($id, clinic_id());
        if (!$patient) { flash('error','Patient not found.'); redirect(APP_URL.'/patients.php'); }

        $history = $this->repo->getHistory($id, clinic_id());
        render('patients/show', compact('patient','history'));
    }

    public function edit(int $id): void {
        AuthMiddleware::check();
        require_permission('patients','update');
        $patient = $this->repo->findById($id, clinic_id());
        if (!$patient) { flash('error','Patient not found.'); redirect(APP_URL.'/patients.php'); }
        render('patients/edit', compact('patient'));
    }

    public function update(int $id): void {
        AuthMiddleware::check();
        require_permission('patients','update');
        verify_csrf();

        $old  = $this->repo->findById($id, clinic_id());
        $data = $this->validate($_POST);
        if (!$data) { flash('error','Validation failed.'); redirect(APP_URL.'/patients.php?action=edit&id='.$id); }

        $this->repo->update($id, clinic_id(), $data);
        audit('patients','update',$id,$old,$data);
        flash('success','Patient updated.');
        redirect(APP_URL.'/patients.php?action=show&id='.$id);
    }

    public function search(): void {
        AuthMiddleware::check();
        $q = sanitize($_GET['q'] ?? '');
        $patients = $this->repo->getAll(clinic_id(), ['search' => $q], 10, 0);
        json_response(array_map(fn($p) => [
            'id'    => $p['id'],
            'text'  => $p['name'] . ' — ' . $p['patient_id'] . ' — ' . $p['phone'],
            'name'  => $p['name'],
            'pid'   => $p['patient_id'],
            'phone' => $p['phone'],
        ], $patients));
    }

    private function validate(array $post): array|false {
        $name  = sanitize($post['name'] ?? '');
        $phone = sanitize($post['phone'] ?? '');
        if (empty($name) || empty($phone)) return false;

        return array_filter([
            'name'               => $name,
            'phone'              => $phone,
            'email'              => sanitize($post['email'] ?? '') ?: null,
            'dob'                => $post['dob'] ?: null,
            'age'                => $post['age'] ? sanitize_int($post['age']) : null,
            'gender'             => in_array($post['gender'],['male','female','other']) ? $post['gender'] : 'male',
            'blood_group'        => $post['blood_group'] ?? 'unknown',
            'address'            => sanitize($post['address'] ?? '') ?: null,
            'city'               => sanitize($post['city'] ?? '') ?: null,
            'state'              => sanitize($post['state'] ?? '') ?: null,
            'pincode'            => sanitize($post['pincode'] ?? '') ?: null,
            'emergency_name'     => sanitize($post['emergency_name'] ?? '') ?: null,
            'emergency_phone'    => sanitize($post['emergency_phone'] ?? '') ?: null,
            'emergency_relation' => sanitize($post['emergency_relation'] ?? '') ?: null,
            'notes'              => sanitize($post['notes'] ?? '') ?: null,
        ], fn($v) => $v !== null);
    }
}
