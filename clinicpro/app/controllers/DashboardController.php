<?php
// app/controllers/DashboardController.php

class DashboardController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();

        $cid   = clinic_id();
        $today = date('Y-m-d');
        $month = date('Y-m-01');

        // Today's appointments
        $apptStats = $this->db->prepare("
            SELECT
                COUNT(*) AS total,
                SUM(status IN('scheduled','confirmed')) AS upcoming,
                SUM(status='completed') AS completed,
                SUM(status='waiting')   AS waiting,
                SUM(status='cancelled') AS cancelled
            FROM appointments WHERE clinic_id=? AND appointment_date=?
        ");
        $apptStats->execute([$cid,$today]);
        $apptStats = $apptStats->fetch();

        // Total patients
        $totalPatients = $this->db->prepare("SELECT COUNT(*) FROM patients WHERE clinic_id=? AND status='active'");
        $totalPatients->execute([$cid]);
        $totalPatients = (int)$totalPatients->fetchColumn();

        // New patients this month
        $newPatients = $this->db->prepare("SELECT COUNT(*) FROM patients WHERE clinic_id=? AND created_at>=?");
        $newPatients->execute([$cid,$month.' 00:00:00']);
        $newPatients = (int)$newPatients->fetchColumn();

        // Revenue today
        $revenueToday = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.clinic_id=? AND p.payment_date=?");
        $revenueToday->execute([$cid,$today]);
        $revenueToday = (float)$revenueToday->fetchColumn();

        // Revenue this month
        $revenueMonth = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.clinic_id=? AND p.payment_date>=?");
        $revenueMonth->execute([$cid,$month]);
        $revenueMonth = (float)$revenueMonth->fetchColumn();

        // Pending bills
        $pendingBills = $this->db->prepare("SELECT COALESCE(SUM(balance_amount),0) FROM invoices WHERE clinic_id=? AND status IN('pending','partial')");
        $pendingBills->execute([$cid]);
        $pendingBills = (float)$pendingBills->fetchColumn();

        // Active doctors
        $activeDoctors = $this->db->prepare("SELECT COUNT(*) FROM doctors WHERE clinic_id=? AND status='active'");
        $activeDoctors->execute([$cid]);
        $activeDoctors = (int)$activeDoctors->fetchColumn();

        // Today's appointments list
        $todayAppts = $this->db->prepare("
            SELECT a.*, p.name AS patient_name, p.phone AS patient_phone,
                   d.name AS doctor_name, d.specialization
            FROM appointments a
            JOIN patients p ON p.id=a.patient_id
            JOIN doctors  d ON d.id=a.doctor_id
            WHERE a.clinic_id=? AND a.appointment_date=?
            ORDER BY a.appointment_time ASC LIMIT 10
        ");
        $todayAppts->execute([$cid,$today]);
        $todayAppts = $todayAppts->fetchAll();

        // Recent patients
        $recentPatients = $this->db->prepare("SELECT * FROM patients WHERE clinic_id=? ORDER BY id DESC LIMIT 5");
        $recentPatients->execute([$cid]);
        $recentPatients = $recentPatients->fetchAll();

        // Last 7 days revenue chart
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $rev = $this->db->prepare("SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN invoices i ON i.id=p.invoice_id WHERE i.clinic_id=? AND p.payment_date=?");
            $rev->execute([$cid,$d]);
            $chartData[] = ['date'=>date('d M',strtotime($d)),'revenue'=>(float)$rev->fetchColumn()];
        }

        // Low stock alert
        $lowStock = $this->db->prepare("
            SELECT m.name, COALESCE(SUM(ms.quantity),0) AS qty
            FROM medicines m
            LEFT JOIN medicine_stock ms ON ms.medicine_id=m.id
            WHERE m.clinic_id=? AND m.status='active'
            GROUP BY m.id HAVING qty < 10 LIMIT 5
        ");
        $lowStock->execute([$cid]);
        $lowStock = $lowStock->fetchAll();

        render('dashboard/index', compact(
            'apptStats','totalPatients','newPatients','revenueToday','revenueMonth',
            'pendingBills','activeDoctors','todayAppts','recentPatients','chartData','lowStock'
        ));
    }
}
