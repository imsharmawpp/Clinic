<?php
// database/seeds/install.php
// Run via: php database/seeds/install.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

$pdo = Database::getInstance();

echo "=== ClinicPro Install Seeder ===\n";

// Read and execute migration
$sql = file_get_contents(__DIR__ . '/../migrations/001_schema.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $stmt) {
    if (empty($stmt) || str_starts_with($stmt, '--')) continue;
    try {
        $pdo->exec($stmt . ';');
    } catch (PDOException $e) {
        // Ignore duplicate entry errors
        if ($e->getCode() !== '23000') {
            echo "WARNING: " . $e->getMessage() . "\n";
        }
    }
}
echo "✓ Schema created\n";

// Get license id
$licenseId = $pdo->query("SELECT id FROM license_keys WHERE license_key='DEMO-CLINIC-PRO-2024-XXXX' LIMIT 1")->fetchColumn();

// Create default clinic
$stmt = $pdo->prepare("INSERT IGNORE INTO clinics (license_id,name,slug,email,phone,address,city,state,status) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->execute([$licenseId,'Demo Clinic','demo-clinic','admin@democlinic.com','9999999999','123 Health Street','New Delhi','Delhi','active']);
$clinicId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM clinics WHERE slug='demo-clinic' LIMIT 1")->fetchColumn();
echo "✓ Default clinic created (ID: $clinicId)\n";

// Create system roles
$roles = [
    ['Super Admin','super_admin',1],
    ['Clinic Admin','clinic_admin',1],
    ['Doctor','doctor',1],
    ['Receptionist','receptionist',1],
    ['Lab Technician','lab_technician',1],
    ['Pharmacist','pharmacist',1],
    ['Accountant','accountant',1],
];
$roleStmt = $pdo->prepare("INSERT IGNORE INTO roles (clinic_id,name,slug,is_system) VALUES (?,?,?,?)");
foreach ($roles as $r) {
    $roleStmt->execute([$clinicId, $r[0], $r[1], $r[2]]);
}
echo "✓ Roles created\n";

// Get super_admin role id
$adminRoleId = $pdo->query("SELECT id FROM roles WHERE clinic_id=$clinicId AND slug='super_admin' LIMIT 1")->fetchColumn();

// Assign all permissions to super_admin
$allPermIds = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
$rpStmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id,permission_id) VALUES (?,?)");
foreach ($allPermIds as $pid) {
    $rpStmt->execute([$adminRoleId, $pid]);
}
echo "✓ Permissions assigned\n";

// Create super admin user
$password = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost'=>12]);
$uStmt = $pdo->prepare("INSERT IGNORE INTO users (clinic_id,role_id,name,email,phone,password,is_super_admin,status,email_verified_at) VALUES (?,?,?,?,?,?,1,'active',NOW())");
$uStmt->execute([$clinicId,$adminRoleId,'Super Admin','admin@democlinic.com','9999999999',$password]);
echo "✓ Admin user created\n";
echo "  Email: admin@democlinic.com\n";
echo "  Password: Admin@1234\n";

// Create demo doctors
$doctorStmt = $pdo->prepare("INSERT IGNORE INTO doctors (clinic_id,name,specialization,qualification,phone,email,experience_years,consultation_fee,status) VALUES (?,?,?,?,?,?,?,?,?)");
$doctors = [
    [$clinicId,'Dr. Anil Kumar','General Medicine','MBBS, MD','9876543210','anil@democlinic.com',10,500.00,'active'],
    [$clinicId,'Dr. Priya Sharma','Gynaecology','MBBS, MS','9876543211','priya@democlinic.com',8,700.00,'active'],
    [$clinicId,'Dr. Rahul Verma','Orthopaedics','MBBS, DNB','9876543212','rahul@democlinic.com',12,600.00,'active'],
];
foreach ($doctors as $d) $doctorStmt->execute($d);
echo "✓ Demo doctors created\n";

// Create demo patients
$patStmt = $pdo->prepare("INSERT IGNORE INTO patients (clinic_id,patient_id,name,dob,gender,blood_group,phone,email,city,status) VALUES (?,?,?,?,?,?,?,?,?,?)");
$patients = [
    [$clinicId,'PT-000001','Ramesh Gupta','1985-04-12','male','B+','9800000001','ramesh@email.com','Delhi','active'],
    [$clinicId,'PT-000002','Sunita Devi','1990-08-22','female','O+','9800000002','sunita@email.com','Gurgaon','active'],
    [$clinicId,'PT-000003','Mukesh Singh','1978-11-05','male','A+','9800000003','mukesh@email.com','Noida','active'],
    [$clinicId,'PT-000004','Kavita Sharma','2000-02-14','female','AB+','9800000004','kavita@email.com','Delhi','active'],
    [$clinicId,'PT-000005','Arjun Patel','1995-07-30','male','B-','9800000005','arjun@email.com','Faridabad','active'],
];
foreach ($patients as $p) $patStmt->execute($p);
echo "✓ Demo patients created\n";

// Lab tests
$labStmt = $pdo->prepare("INSERT IGNORE INTO lab_tests_master (clinic_id,name,short_name,category,price,unit) VALUES (?,?,?,?,?,?)");
$labs = [
    [$clinicId,'Complete Blood Count','CBC','Haematology',300.00,'cells/μL'],
    [$clinicId,'Blood Sugar Fasting','BSF','Biochemistry',80.00,'mg/dL'],
    [$clinicId,'Thyroid Profile','TFT','Endocrinology',600.00,'mIU/L'],
    [$clinicId,'Lipid Profile','LFT','Biochemistry',500.00,'mg/dL'],
    [$clinicId,'Urine Routine','UR','Microbiology',100.00,'—'],
    [$clinicId,'HbA1c','HbA1c','Biochemistry',400.00,'%'],
    [$clinicId,'Chest X-Ray','CXR','Radiology',400.00,'—'],
];
foreach ($labs as $l) $labStmt->execute($l);
echo "✓ Lab tests seeded\n";

// Default settings
$settingsData = [
    ['invoice_prefix','INV'],
    ['appointment_prefix','APT'],
    ['patient_prefix','PT'],
    ['lab_prefix','LAB'],
    ['prescription_prefix','RX'],
    ['payment_prefix','PAY'],
    ['gst_number',''],
    ['invoice_terms','Payment is due within 7 days.'],
    ['appointment_reminder_hours','24'],
    ['sms_enabled','0'],
    ['email_enabled','1'],
];
$setStmt = $pdo->prepare("INSERT IGNORE INTO settings (clinic_id,`key`,value) VALUES (?,?,?)");
foreach ($settingsData as $s) $setStmt->execute([$clinicId, $s[0], $s[1]]);
echo "✓ Default settings seeded\n";

echo "\n=== Installation Complete ===\n";
echo "Login URL: " . APP_URL . "/login.php\n";
echo "Admin: admin@democlinic.com / Admin@1234\n";
