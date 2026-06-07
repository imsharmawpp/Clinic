<?php
// app/controllers/SettingsController.php

class SettingsController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('settings','manage');

        $clinic = $this->db->prepare("SELECT * FROM clinics WHERE id=? LIMIT 1");
        $clinic->execute([clinic_id()]);
        $clinic = $clinic->fetch();

        $settings = $this->db->prepare("SELECT `key`,value FROM settings WHERE clinic_id=?");
        $settings->execute([clinic_id()]);
        $settings = array_column($settings->fetchAll(), 'value', 'key');

        $users = $this->db->prepare("
            SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id
            WHERE u.clinic_id=? ORDER BY u.name
        ");
        $users->execute([clinic_id()]);
        $users = $users->fetchAll();

        $roles = $this->db->prepare("SELECT * FROM roles WHERE clinic_id=? ORDER BY name");
        $roles->execute([clinic_id()]);
        $roles = $roles->fetchAll();

        render('settings/index', compact('clinic','settings','users','roles'));
    }

    public function updateClinic(): void {
        AuthMiddleware::check();
        require_permission('settings','manage');
        verify_csrf();

        $data = [
            'name'            => sanitize($_POST['name'] ?? ''),
            'tagline'         => sanitize($_POST['tagline'] ?? '') ?: null,
            'email'           => sanitize($_POST['email'] ?? '') ?: null,
            'phone'           => sanitize($_POST['phone'] ?? '') ?: null,
            'address'         => sanitize($_POST['address'] ?? '') ?: null,
            'city'            => sanitize($_POST['city'] ?? '') ?: null,
            'state'           => sanitize($_POST['state'] ?? '') ?: null,
            'pincode'         => sanitize($_POST['pincode'] ?? '') ?: null,
            'gstin'           => sanitize($_POST['gstin'] ?? '') ?: null,
            'registration_no' => sanitize($_POST['registration_no'] ?? '') ?: null,
            'website'         => sanitize($_POST['website'] ?? '') ?: null,
            'theme_color'     => sanitize($_POST['theme_color'] ?? '#1a6eff'),
        ];

        $sets = implode(',', array_map(fn($k)=>"`$k`=:$k", array_keys($data)));
        $data['id'] = clinic_id();
        $this->db->prepare("UPDATE clinics SET $sets WHERE id=:id")->execute($data);

        // Update settings
        $settingKeys = ['invoice_prefix','invoice_terms','gst_number','appointment_prefix','patient_prefix'];
        $sStmt = $this->db->prepare("INSERT INTO settings (clinic_id,`key`,value) VALUES (?,?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)");
        foreach ($settingKeys as $key) {
            if (isset($_POST[$key])) {
                $sStmt->execute([clinic_id(), $key, sanitize($_POST[$key])]);
            }
        }

        flash('success','Clinic settings updated.');
        redirect(APP_URL.'/settings.php');
    }

    public function createUser(): void {
        AuthMiddleware::check();
        require_permission('users','manage');
        verify_csrf();

        $password = password_hash(sanitize($_POST['password']), PASSWORD_BCRYPT);
        $roleId   = sanitize_int($_POST['role_id']);
        $email    = sanitize($_POST['email'] ?? '');

        $check = $this->db->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetchColumn()) { flash('error','Email already exists.'); redirect(APP_URL.'/settings.php#users'); }

        $stmt = $this->db->prepare("INSERT INTO users (clinic_id,role_id,name,email,phone,password,status) VALUES (?,?,?,?,?,?,'active')");
        $stmt->execute([clinic_id(), $roleId, sanitize($_POST['name']), $email, sanitize($_POST['phone']??''), $password]);

        flash('success','User created.');
        redirect(APP_URL.'/settings.php#users');
    }

    public function toggleUser(): void {
        AuthMiddleware::check();
        require_permission('users','manage');
        verify_csrf();

        $id = sanitize_int($_POST['id']);
        $this->db->prepare("UPDATE users SET status=CASE WHEN status='active' THEN 'inactive' ELSE 'active' END WHERE id=? AND clinic_id=?")
            ->execute([$id, clinic_id()]);
        json_response(['success'=>true]);
    }
}
