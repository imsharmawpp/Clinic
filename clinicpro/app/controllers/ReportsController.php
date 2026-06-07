<?php
// app/controllers/ReportsController.php

class ReportsController {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function index(): void {
        AuthMiddleware::check();
        require_permission('reports','view');

        $from = sanitize($_GET['from'] ?? date('Y-m-01'));
        $to   = sanitize($_GET['to']   ?? date('Y-m-d'));
        $type = sanitize($_GET['type'] ?? 'revenue');
        $cid  = clinic_id();

        $data = match($type) {
            'revenue'      => $this->revenueReport($cid, $from, $to),
            'appointments' => $this->appointmentReport($cid, $from, $to),
            'patients'     => $this->patientReport($cid, $from, $to),
            'doctors'      => $this->doctorReport($cid, $from, $to),
            default        => $this->revenueReport($cid, $from, $to),
        };

        render('reports/index', compact('data','from','to','type'));
    }

    private function revenueReport(int $cid, string $from, string $to): array {
        $daily = $this->db->prepare("
            SELECT DATE(py.payment_date) AS day,
                   SUM(py.amount) AS revenue,
                   COUNT(DISTINCT py.invoice_id) AS invoices,
                   py.method
            FROM payments py
            JOIN invoices inv ON inv.id=py.invoice_id
            WHERE inv.clinic_id=? AND py.payment_date BETWEEN ? AND ?
            GROUP BY DATE(py.payment_date), py.method
            ORDER BY day
        ");
        $daily->execute([$cid,$from,$to]);
        $rows = $daily->fetchAll();

        $summary = $this->db->prepare("
            SELECT
                COALESCE(SUM(py.amount),0) AS total_collected,
                COALESCE(SUM(inv.balance_amount),0) AS total_pending,
                COUNT(DISTINCT inv.id) AS total_invoices,
                AVG(inv.total_amount) AS avg_invoice
            FROM invoices inv
            LEFT JOIN payments py ON py.invoice_id=inv.id
            WHERE inv.clinic_id=? AND inv.invoice_date BETWEEN ? AND ?
        ");
        $summary->execute([$cid,$from,$to]);

        $byMethod = $this->db->prepare("
            SELECT method, SUM(amount) AS total FROM payments py
            JOIN invoices inv ON inv.id=py.invoice_id
            WHERE inv.clinic_id=? AND py.payment_date BETWEEN ? AND ?
            GROUP BY method
        ");
        $byMethod->execute([$cid,$from,$to]);

        return ['rows'=>$rows, 'summary'=>$summary->fetch(), 'by_method'=>$byMethod->fetchAll()];
    }

    private function appointmentReport(int $cid, string $from, string $to): array {
        $stmt = $this->db->prepare("
            SELECT d.name AS doctor_name, d.specialization,
                   COUNT(a.id) AS total,
                   SUM(a.status='completed') AS completed,
                   SUM(a.status='cancelled') AS cancelled,
                   SUM(a.status='no_show') AS no_show
            FROM appointments a JOIN doctors d ON d.id=a.doctor_id
            WHERE a.clinic_id=? AND a.appointment_date BETWEEN ? AND ?
            GROUP BY d.id ORDER BY total DESC
        ");
        $stmt->execute([$cid,$from,$to]);
        return ['rows'=>$stmt->fetchAll()];
    }

    private function patientReport(int $cid, string $from, string $to): array {
        $daily = $this->db->prepare("
            SELECT DATE(created_at) AS day, COUNT(*) AS count, gender
            FROM patients WHERE clinic_id=? AND created_at BETWEEN ? AND ?
            GROUP BY DATE(created_at), gender ORDER BY day
        ");
        $daily->execute([$cid, $from.' 00:00:00', $to.' 23:59:59']);
        $by_city = $this->db->prepare("
            SELECT COALESCE(city,'Unknown') AS city, COUNT(*) AS count
            FROM patients WHERE clinic_id=? AND created_at BETWEEN ? AND ?
            GROUP BY city ORDER BY count DESC LIMIT 10
        ");
        $by_city->execute([$cid, $from.' 00:00:00', $to.' 23:59:59']);
        return ['rows'=>$daily->fetchAll(), 'by_city'=>$by_city->fetchAll()];
    }

    private function doctorReport(int $cid, string $from, string $to): array {
        $stmt = $this->db->prepare("
            SELECT d.name, d.specialization,
                   COUNT(DISTINCT v.id) AS visits,
                   COUNT(DISTINCT rx.id) AS prescriptions,
                   COALESCE(SUM(py.amount),0) AS revenue
            FROM doctors d
            LEFT JOIN opd_visits v   ON v.doctor_id=d.id AND v.visit_date BETWEEN ? AND ?
            LEFT JOIN prescriptions rx ON rx.doctor_id=d.id AND rx.prescription_date BETWEEN ? AND ?
            LEFT JOIN invoices inv  ON inv.doctor_id=d.id AND inv.invoice_date BETWEEN ? AND ?
            LEFT JOIN payments py   ON py.invoice_id=inv.id
            WHERE d.clinic_id=?
            GROUP BY d.id ORDER BY revenue DESC
        ");
        $stmt->execute([$from,$to,$from,$to,$from,$to,$cid]);
        return ['rows'=>$stmt->fetchAll()];
    }
}
